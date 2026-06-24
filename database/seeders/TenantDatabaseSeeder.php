<?php

namespace Database\Seeders;

use App\Models\Tenant\Page;
use App\Models\Tenant\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Setting::defaults() as $key => $value) {
            Setting::set($key, $value);
        }

        Page::seedDefaults();

        if (DB::getSchemaBuilder()->hasTable('order_sequences') && DB::table('order_sequences')->count() === 0) {
            DB::table('order_sequences')->insert(['next_id' => 1]);
        }
    }
}