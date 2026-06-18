<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('src'); // stored filename
            $table->string('mime');
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->nullable()->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('taxable')->default(true);
            $table->string('type')->default('food'); // food | product (affects delivery charge)
            $table->foreignId('image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('category_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort')->default(0);
        });

        Schema::create('item_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->boolean('required')->default(false);
            $table->string('input_type')->default('select'); // select | multiselect
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('item_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_option_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('price_type')->default('flat'); // flat | percent
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_option_values');
        Schema::dropIfExists('item_options');
        Schema::dropIfExists('category_item');
        Schema::dropIfExists('items');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('media');
    }
};
