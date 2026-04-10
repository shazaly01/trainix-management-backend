<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // مصفوفة المستخدمين المطلوب وجودهم في النظام
        $users = [
            [
                'full_name' => 'Super Admin',
                'username'  => 'superadmin',
                'email'     => 'superadmin@app.com',
                'role'      => 'Super Admin'
            ],
            [
                'full_name' => 'Admin User',
                'username'  => 'admin',
                'email'     => 'admin@app.com',
                'role'      => 'Admin'
            ],
            [
                'full_name' => 'Data Entry User',
                'username'  => 'dataentry',
                'email'     => 'dataentry@app.com',
                'role'      => 'Data Entry'
            ],
            [
                'full_name' => 'Auditor User',
                'username'  => 'auditor',
                'email'     => 'auditor@app.com',
                'role'      => 'Auditor'
            ],
        ];

        foreach ($users as $userData) {
            // استخدام updateOrCreate يمنع تكرار الخطأ ويحدث البيانات إذا تغيرت
            $user = User::updateOrCreate(
                ['username' => $userData['username']], // حقل البحث (فريد)
                [
                    'full_name' => $userData['full_name'],
                    'email'     => $userData['email'],
                    'password'  => bcrypt('12345678'), // يمكنك تغييره لاحقاً
                    'email_verified_at' => now(),
                ]
            );

            // إسناد الرتبة (syncRoles تحذف الرتب القديمة وتضع الجديدة فقط)
            $user->syncRoles([$userData['role']]);
        }

        $this->command->info('✅ تم تحديث المستخدمين وإسناد الرتب الجديدة بنجاح.');
    }
}
