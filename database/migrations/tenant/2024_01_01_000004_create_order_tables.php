<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('increment_id'); // human-friendly order number, per tenant
            $table->string('key')->unique(); // for public order status URL
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cart_id')->nullable()->constrained()->nullOnDelete();

            $table->string('method'); // pickup | delivery
            $table->string('status')->default('placed'); // placed | received | ready | complete | cancelled

            // Contact
            $table->string('contact_firstname');
            $table->string('contact_lastname');
            $table->string('contact_email');
            $table->string('contact_phone');

            // Delivery
            $table->string('delivery_address')->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_state', 2)->nullable();
            $table->string('delivery_zip', 10)->nullable();

            // Billing
            $table->string('billing_firstname');
            $table->string('billing_lastname');
            $table->string('billing_address');
            $table->string('billing_city');
            $table->string('billing_state', 2);
            $table->string('billing_zip', 10);

            // Payment
            $table->string('stripe_payment_intent');
            $table->string('stripe_charge_id')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('card_last4', 4)->nullable();

            // Totals (stored in dollars)
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('delivery', 10, 2)->default(0);
            $table->decimal('tip', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->text('comments')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained()->nullOnDelete();
            // Snapshot fields — stored at time of order so changes to menu don't affect history
            $table->string('name');
            $table->string('sku')->nullable();
            $table->boolean('taxable')->default(true);
            $table->string('type')->default('food');
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('price', 10, 2)->default(0);
            $table->text('comments')->nullable();
        });

        Schema::create('order_item_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->string('label'); // snapshot
        });

        Schema::create('order_item_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_option_id')->constrained()->cascadeOnDelete();
            $table->string('label'); // snapshot
            $table->decimal('price', 10, 2)->default(0); // snapshot
            $table->string('price_type')->default('flat'); // snapshot
        });

        // Auto-increment order numbers per tenant
        Schema::create('order_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('next_id')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_sequences');
        Schema::dropIfExists('order_item_option_values');
        Schema::dropIfExists('order_item_options');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
