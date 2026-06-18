<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Logo is stored as a setting key 'logo_url' pointing to the uploaded file path.
// This migration just ensures the media table has an 'alt' column for accessibility.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->string('alt')->nullable()->after('name');
            $table->string('folder')->default('general')->after('alt');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn(['alt', 'folder']);
        });
    }
};
