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
        // 1. تصفير الكاش لضمان الحذف التام للصلاحيات القديمة
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guardName = 'api';

        // 2. تعريف الشاشات الأربعة فقط + لوحة التحكم وإدارة النظام الأساسية
        $permissions = [
            'dashboard.view',

            // أ. الإدارات (Departments)
            'department.view', 'department.create', 'department.update', 'department.delete',

            // ب. المترشحين (Candidates)
            'candidate.view', 'candidate.create', 'candidate.update', 'candidate.delete',

            // ج. المستندات (Documents)
            'document.view', 'document.create', 'document.update', 'document.delete',

            // د. طلبات التدريب/الوظائف (Job Requests)
            'job_request.view', 'job_request.create', 'job_request.update', 'job_request.delete',

            // صلاحيات تقنية للمدراء فقط (إدارة المستخدمين والنسخ الاحتياطي)
            'user.view', 'user.create', 'user.update', 'user.delete',
            'role.view', 'role.create', 'role.update', 'role.delete',
            'backup.view', 'backup.create', 'backup.delete', 'backup.download',
            'setting.view', 'setting.update',
        ];

        // إنشاء الصلاحيات
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guardName]);
        }

        // 3. بناء الرتب (Roles) بناءً على الشاشات المختارة

        // --- Super Admin & Admin: يرى كل الشاشات الأربعة + التحكم بالنظام ---
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => $guardName]);
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => $guardName]);

        $adminPermissions = Permission::where('guard_name', $guardName)->get();
        $admin->syncPermissions($adminPermissions);
        $superAdmin->syncPermissions($adminPermissions);

        // --- Data Entry: يرى فقط الشاشات الأربعة التي حددتها (صلاحيات كاملة فيها) ---
        $dataEntry = Role::firstOrCreate(['name' => 'Data Entry', 'guard_name' => $guardName]);
        $dataEntryPermissions = [
            'dashboard.view',
            'department.view', 'department.create', 'department.update', 'department.delete',
            'candidate.view', 'candidate.create', 'candidate.update', 'candidate.delete',
            'document.view', 'document.create', 'document.update', 'document.delete',
            'job_request.view', 'job_request.create', 'job_request.update', 'job_request.delete',
        ];
        $dataEntry->syncPermissions($dataEntryPermissions);

        // --- Auditor: يرى الشاشات الأربعة "عرض فقط" للقراءة والتدقيق ---
        $auditor = Role::firstOrCreate(['name' => 'Auditor', 'guard_name' => $guardName]);
        $auditorPermissions = [
            'dashboard.view',
            'department.view',
            'candidate.view',
            'document.view',
            'job_request.view',
        ];
        $auditor->syncPermissions($auditorPermissions);

        $this->command->info('✅ تم الحصر بنجاح: النظام الآن يتكون من 4 شاشات أساسية فقط.');
    }
}
