<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guardName = 'api';

        $permissions = [
            'dashboard.view',

            // إدارة النظام الأساسية
            'user.view', 'user.create', 'user.update', 'user.delete',
            'role.view', 'role.create', 'role.update', 'role.delete',
            'setting.view', 'setting.update',
            'backup.view', 'backup.create', 'backup.delete', 'backup.download',

            // الكيانات القاموسية
            'city.view', 'city.create', 'city.update', 'city.delete',
            'department.view', 'department.create', 'department.update', 'department.delete',

            // كيانات التوظيف الأساسية
            'applicant.view', 'applicant.create', 'applicant.update', 'applicant.delete',
            'job_request.view', 'job_request.create', 'job_request.update', 'job_request.delete',
            'application.view', 'application.create', 'application.update', 'application.delete',
            'interview.view', 'interview.create', 'interview.update', 'interview.delete',
            'document.view', 'document.create', 'document.update', 'document.delete',

            // 🌟 صلاحيات المترشحين الجديدة
            'candidate.view', 'candidate.create', 'candidate.update', 'candidate.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => $guardName,
            ]);
        }

        Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => $guardName,
        ]);

        $recruitmentOfficer = Role::firstOrCreate([
            'name' => 'Recruitment Officer',
            'guard_name' => $guardName,
        ]);

        $officerPermissions = [
            'dashboard.view',
            'applicant.view', 'applicant.create', 'applicant.update', 'applicant.delete',
            'application.view', 'application.create', 'application.update', 'application.delete',
            'interview.view', 'interview.create', 'interview.update', 'interview.delete',
            'document.view', 'document.create', 'document.update', 'document.delete',
            'job_request.view',
            'city.view', 'department.view',
            // 🌟 إضافة صلاحيات المترشحين لموظف التوظيف
            'candidate.view', 'candidate.create', 'candidate.update', 'candidate.delete',
        ];
        $recruitmentOfficer->syncPermissions($officerPermissions);

        $departmentManager = Role::firstOrCreate([
            'name' => 'Department Manager',
            'guard_name' => $guardName,
        ]);

        $managerPermissions = [
            'dashboard.view',
            'job_request.view', 'job_request.create', 'job_request.update', 'job_request.delete',
            'applicant.view',
            'application.view', 'application.update',
            'interview.view', 'interview.update',
            'document.view',
            // 🌟 إضافة صلاحية عرض المترشحين لمدير الإدارة
            'candidate.view',
        ];
        $departmentManager->syncPermissions($managerPermissions);
    }
}
