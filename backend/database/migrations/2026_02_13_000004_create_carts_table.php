<?php

use App\Models\Cart;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('travel_id')->constrained('travels')->cascadeOnDelete();
            $table->string('email');
            $table->unsignedTinyInteger('seats');
            $table->string('status')->default(Cart::STATUS_PENDING);
            $table->timestamp('expires_at');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['travel_id', 'status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
