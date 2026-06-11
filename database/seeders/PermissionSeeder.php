<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder {
    public function run(): void {
        $permissions = [
            'access_admin',
            'manage_users',
            'manage_settings',
            'manage_novels',
            'create_novels',
            'publish_novels',
            'manage_comments',
            'manage_finance',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
    }
}
