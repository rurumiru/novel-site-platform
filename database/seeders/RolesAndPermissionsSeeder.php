<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder {
    public function run(): void {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'access_admin',
            'manage_users',
            'manage_settings',
            'manage_novels',
            'create_novels',
            'publish_novels',
            'manage_comments',
            'manage_finance',
            'manage_roles',
            'manage_editors',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $owner = Role::firstOrCreate(['name' => 'owner']);
        $owner->syncPermissions(Permission::all());

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        $deputyAdmin = Role::firstOrCreate(['name' => 'deputy_admin']);
        $deputyAdmin->syncPermissions([
            'access_admin', 'manage_novels', 'manage_comments',
            'manage_users', 'manage_editors',
        ]);

        $moderator = Role::firstOrCreate(['name' => 'moderator']);
        $moderator->syncPermissions([
            'access_admin', 'manage_novels', 'manage_comments',
        ]);

        $editor = Role::firstOrCreate(['name' => 'editor']);
        $editor->syncPermissions(['manage_novels']);

        $author = Role::firstOrCreate(['name' => 'author']);
        $author->syncPermissions(['create_novels']);

        Role::firstOrCreate(['name' => 'user']);
    }
}
