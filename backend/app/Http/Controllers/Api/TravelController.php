<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Travel;
use Illuminate\Http\JsonResponse;

class TravelController extends Controller
{
    public function index(): JsonResponse
    {
        $travels = Travel::query()
            ->orderBy('starting_date')
            ->get()
            ->map(fn (Travel $travel): array => $this->serializeTravel($travel))
            ->values();

        return response()->json($travels);
    }

    public function show(Travel $travel): JsonResponse
    {
        return response()->json($this->serializeTravel($travel));
    }

    private function serializeTravel(Travel $travel): array
    {
        return [
            'id' => $travel->id,
            'slug' => $travel->slug,
            'name' => $travel->name,
            'description' => $travel->description,
            'starting_date' => $travel->starting_date?->toDateString(),
            'ending_date' => $travel->ending_date?->toDateString(),
            'price' => $travel->price,
            'moods' => $travel->moods ?? [],
            'seats_left' => $travel->seatsLeft(),
        ];
    }
}
