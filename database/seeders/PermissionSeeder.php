<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Modules that follow the standard View/Create/Update/Delete matrix.
     */
    private const MODULES = [
        'members',
        'bills',
        'payments',
        'attendance',
        'offers',
        'reports',
        'settings',
        'users',
        'membership_plans',
        'personal_training',
    ];

    private const ACTIONS = ['view', 'create', 'update', 'delete'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [];

        foreach (self::MODULES as $module) {
            foreach (self::ACTIONS as $action) {
                $permissions[] = "{$module}.{$action}";
            }
        }

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        Role::findByName('Super Admin', 'web')->syncPermissions($permissions);

        // Admin manages daily operations fully, but can only view (not modify) staff accounts and system settings.
        $adminPermissions = array_filter($permissions, function ($permission) {
            $isRestrictedModule = str_starts_with($permission, 'users.') || str_starts_with($permission, 'settings.');

            return ! $isRestrictedModule || str_ends_with($permission, '.view');
        });

        Role::findByName('Admin', 'web')->syncPermissions($adminPermissions);

        Role::findByName('Reception', 'web')->syncPermissions([
            'members.view', 'members.create', 'members.update',
            'bills.view', 'bills.create',
            'payments.view', 'payments.create',
            'attendance.view', 'attendance.create',
            'offers.view',
            'reports.view',
            'membership_plans.view',
        ]);

        Role::findByName('Trainer', 'web')->syncPermissions([
            'members.view',
            'attendance.view', 'attendance.create',
            'personal_training.view', 'personal_training.update',
        ]);
    }
}
