<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            if (! Schema::hasColumn('media', 'alt')) {
                $table->string('alt')->nullable()->after('name');
            }

            if (! Schema::hasColumn('media', 'folder')) {
                $table->string('folder')->default('general')->after('size');
            }
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('menu_label')->nullable();
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->foreignId('hero_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');

        Schema::table('media', function (Blueprint $table) {
            if (Schema::hasColumn('media', 'folder')) {
                $table->dropColumn('folder');
            }

            if (Schema::hasColumn('media', 'alt')) {
                $table->dropColumn('alt');
            }
        });
    }
};