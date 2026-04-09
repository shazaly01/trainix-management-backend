<?php

namespace App\Policies;

use App\Models\Candidate;
use App\Models\User;

class CandidatePolicy
{
    /**
     * تحديد من يمكنه عرض قائمة المترشحين
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('candidate.view');
    }

    /**
     * تحديد من يمكنه عرض تفاصيل مترشح محدد
     */
    public function view(User $user, Candidate $candidate): bool
    {
        return $user->hasPermissionTo('candidate.view');
    }

    /**
     * تحديد من يمكنه إضافة مترشح جديد
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('candidate.create');
    }

    /**
     * تحديد من يمكنه تعديل بيانات المترشح
     */
    public function update(User $user, Candidate $candidate): bool
    {
        return $user->hasPermissionTo('candidate.update');
    }

    /**
     * تحديد من يمكنه حذف (أرشفة) المترشح
     */
    public function delete(User $user, Candidate $candidate): bool
    {
        return $user->hasPermissionTo('candidate.delete');
    }

    /**
     * تحديد من يمكنه استرجاع المترشح من الأرشيف
     */
    public function restore(User $user, Candidate $candidate): bool
    {
        return $user->hasPermissionTo('candidate.delete');
    }

    /**
     * تحديد من يمكنه الحذف النهائي (مقصور على مدير النظام)
     */
    public function forceDelete(User $user, Candidate $candidate): bool
    {
        return $user->hasRole('Super Admin');
    }
}
