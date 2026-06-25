<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        if (! Schema::hasTable('pages')) {
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

            return;
        }

        Schema::table('pages', function (Blueprint $table) {
            if (! Schema::hasColumn('pages', 'title')) {
                $table->string('title')->nullable()->after('id');
            }

            if (! Schema::hasColumn('pages', 'menu_label')) {
                $table->string('menu_label')->nullable()->after('slug');
            }

            if (! Schema::hasColumn('pages', 'summary')) {
                $table->text('summary')->nullable()->after('menu_label');
            }

            if (! Schema::hasColumn('pages', 'hero_media_id')) {
                $table->foreignId('hero_media_id')->nullable()->after('content')->constrained('media')->nullOnDelete();
            }

            if (! Schema::hasColumn('pages', 'is_published')) {
                $table->boolean('is_published')->default(true)->after('hero_media_id');
            }

            if (! Schema::hasColumn('pages', 'sort')) {
                $table->unsignedInteger('sort')->default(0)->after('is_published');
            }
        });

        if (Schema::hasColumn('pages', 'name')) {
            DB::table('pages')->whereNull('title')->update(['title' => DB::raw('name')]);
        }

        if (Schema::hasColumn('pages', 'is_active')) {
            DB::table('pages')->where('is_active', false)->update(['is_published' => false]);
        }

        DB::table('pages')->whereNull('menu_label')->update(['menu_label' => DB::raw('title')]);
        DB::table('pages')->whereNull('title')->update(['title' => 'Page']);
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            if (Schema::hasColumn('pages', 'sort')) {
                $table->dropColumn('sort');
            }

            if (Schema::hasColumn('pages', 'is_published')) {
                $table->dropColumn('is_published');
            }

            if (Schema::hasColumn('pages', 'hero_media_id')) {
                $table->dropConstrainedForeignId('hero_media_id');
            }

            if (Schema::hasColumn('pages', 'summary')) {
                $table->dropColumn('summary');
            }

            if (Schema::hasColumn('pages', 'menu_label')) {
                $table->dropColumn('menu_label');
            }
        });

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