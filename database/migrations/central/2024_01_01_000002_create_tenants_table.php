<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary(); // slug used as ID e.g. "tacoplace"
            $table->string('name');
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();

            // Stripe Connect — tenant's own account for customer payments
            $table->string('stripe_connect_id')->nullable();
            $table->string('stripe_connect_access_token')->nullable();
            $table->boolean('stripe_connect_active')->default(false);

            // Stripe Billing — platform charges tenant
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->string('subscription_status')->nullable(); // active, trialing, past_due, canceled
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            $table->json('data')->nullable(); // stancl/tenancy uses this
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
