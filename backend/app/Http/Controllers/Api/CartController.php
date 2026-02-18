<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartRequest;
use App\Models\Cart;
use App\Models\Travel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function store(StoreCartRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request): JsonResponse {
            $travelId = (string) $request->input('travel_id');
            $seatsRequested = (int) $request->input('seats');

            $travel = Travel::query()
                ->lockForUpdate()
                ->findOrFail($travelId);

            $takenSeats = $this->takenSeats($travel->id);
            $seatsLeft = max(0, Travel::MAX_SEATS - $takenSeats);

            if ($seatsRequested > $seatsLeft) {
                return response()->json([
                    'message' => 'Not enough seats',
                    'seats_left' => $seatsLeft,
                ], 422);
            }

            $cart = Cart::query()->create([
                'travel_id' => $travel->id,
                'email' => (string) $request->input('email'),
                'seats' => $seatsRequested,
                'status' => Cart::STATUS_PENDING,
                'expires_at' => now()->addMinutes(15),
            ]);

            $cart->load('travel');

            return response()->json($this->cartPayload($cart), 201);
        });
    }

    public function show(Cart $cart): JsonResponse
    {
        $cart->markExpiredIfNeeded();
        $cart->load('travel');

        return response()->json($this->cartPayload($cart));
    }

    public function pay(Cart $cart): JsonResponse
    {
        return DB::transaction(function () use ($cart): JsonResponse {
            $lockedCart = Cart::query()
                ->lockForUpdate()
                ->with('travel')
                ->findOrFail($cart->id);

            $lockedCart->markExpiredIfNeeded();

            if ($lockedCart->status === Cart::STATUS_EXPIRED) {
                return response()->json([
                    'message' => 'Cart expired',
                ], 422);
            }

            if ($lockedCart->status === Cart::STATUS_PAID) {
                return response()->json([
                    'message' => 'Already paid',
                    'cart' => $this->cartPayload($lockedCart),
                ]);
            }

            $lockedCart->status = Cart::STATUS_PAID;
            $lockedCart->paid_at = now();
            $lockedCart->save();

            return response()->json([
                'message' => 'Payment confirmed',
                'cart' => $this->cartPayload($lockedCart),
            ]);
        });
    }

    private function takenSeats(string $travelId): int
    {
        return (int) Cart::query()
            ->where('travel_id', $travelId)
            ->booked()
            ->sum('seats');
    }

    private function cartPayload(Cart $cart): array
    {
        $expiresIn = $cart->expires_at
            ? max(0, now()->diffInSeconds($cart->expires_at, false))
            : 0;

        return [
            'id' => $cart->id,
            'travel_id' => $cart->travel_id,
            'travel_name' => $cart->travel?->name,
            'travel_slug' => $cart->travel?->slug,
            'travel_price' => $cart->travel?->price,
            'email' => $cart->email,
            'seats' => $cart->seats,
            'status' => $cart->status,
            'expires_at' => $cart->expires_at?->toIso8601String(),
            'expires_in_seconds' => $expiresIn,
            'paid_at' => $cart->paid_at?->toIso8601String(),
        ];
    }
}
