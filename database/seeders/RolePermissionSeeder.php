<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // One permission group per module; CRUD verbs per module (RBAC-01).
        $modules = [
            'crew',        // Module 1 — Crew Management
            'selection',   // Module 2 — Crew Selection
            'principal',   // Module 3 — Principal Management
            'salary',      // Module 4 — Salary Management
            'document',    // Module 5 — Document Management
            'staff',       // Module 6 — Staff & Partner
            'license',     // Module 7 — Licence Management
            'accounting',  // Module 8 — Accounting
            'report',
            'settings',
        ];
        $verbs = ['view', 'create', 'edit', 'delete'];

        foreach ($modules as $m) {
            foreach ($verbs as $v) {
                Permission::findOrCreate("{$m}.{$v}", 'web');
            }
        }
        // Special permissions
        foreach (['salary.approve', 'crew.blacklist', 'crew.sync', 'accounting.post'] as $p) {
            Permission::findOrCreate($p, 'web');
        }

        $all = Permission::pluck('name')->all();

        $superAdmin = Role::findOrCreate('Super Admin', 'web');
        $superAdmin->syncPermissions($all);

        $admin = Role::findOrCreate('Admin', 'web');
        $admin->syncPermissions(array_values(array_filter($all, fn ($p) =>
            ! in_array($p, ['salary.approve', 'settings.delete', 'settings.edit'])
        )));

        $staff = Role::findOrCreate('Office Staff', 'web');
        $staff->syncPermissions([
            'crew.view','crew.create','crew.edit',
            'selection.view','selection.create','selection.edit',
            'principal.view','principal.edit',
            'salary.view','salary.create','salary.edit',
            'document.view','document.create','document.edit',
            'report.view',
        ]);

        $partner = Role::findOrCreate('Partner', 'web');
        $partner->syncPermissions([
            'crew.view','crew.create',
            'selection.view','selection.create',
            'principal.view',
            'salary.view',
            'document.view',
            'report.view',
        ]);

        $salaryUser = Role::findOrCreate('Salary User', 'web');
        $salaryUser->syncPermissions([
            'salary.view','salary.create','salary.edit','report.view','crew.view',
            'accounting.view','accounting.create','accounting.edit',
        ]);
    }
}
