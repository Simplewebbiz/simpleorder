<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_key')->unique()->index();
            $table->string('method')->nullable(); // pickup | delivery
            $table->string('delivery_address')->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_state', 2)->nullable();
            $table->string('delivery_zip', 10)->nullable();
            $table->decimal('tip', 10, 2)->nullable();
            $table->string('stripe_intent')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('qty')->default(1);
            $table->text('comments')->nullable();
            $table->timestamps();
        });

        Schema::create('cart_item_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_option_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('cart_item_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_item_option_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_option_value_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_item_option_values');
        Schema::dropIfExists('cart_item_options');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
