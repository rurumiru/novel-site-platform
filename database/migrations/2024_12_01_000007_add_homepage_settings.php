<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        $defaults = [
            'show_slider' => '1',
            'show_popular' => '1',
            'show_recommended' => '1',
            'show_discounts' => '1',
            'show_updates' => '1',
            'show_info_sidebar' => '1',
        ];

        foreach ($defaults as $key => $value) {
            DB::table('settings')->insertOrIgnore(['key' => $key, 'value' => $value]);
        }
    }
};
