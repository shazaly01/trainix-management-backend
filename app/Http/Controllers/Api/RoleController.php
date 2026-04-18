<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\Api\PermissionResource;
use App\Http\Resources\Api\RoleResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        // استخدام authorizeResource لتطبيق الـ Policy تلقائيًا
        // نحتاج إلى تحديد 'parameter' لأن Laravel تتوقع 'role' ونحن نستخدم 'role'
        $this->authorizeResource(Role::class, 'role');
    }

    public function index()
    {
        $roles = Role::with('permissions')->latest()->paginate(15);
        return RoleResource::collection($roles);
    }

    public function store(StoreRoleRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $role = Role::create(['name' => $validated['name'], 'guard_name' => 'api']);
            $role->syncPermissions($validated['permissions']);
            DB::commit();

            return new RoleResource($role->load('permissions'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create role.', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Role $role)
    {
        return new RoleResource($role->load('permissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $role->update(['name' => $validated['name']]);
            $role->syncPermissions($validated['permissions']);
            DB::commit();

            return new RoleResource($role->load('permissions'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update role.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Role $role)
    {
        // 1. استدعاء الـ Policy للتحقق من الصلاحية
        $this->authorize('delete', $role);

        // --- بداية التعديل: إعادة قاعدة العمل ---
        // 2. التحقق من قاعدة العمل (يتم تنفيذها دائمًا بعد التحقق من الصلاحية)
        if (in_array($role->name, ['Super Admin', 'Admin', 'User'])) {
            // استخدام abort helper function هو الأنسب هنا
            abort(Response::HTTP_FORBIDDEN, 'Cannot delete default roles.');
        }
        // --- نهاية التعديل ---

        // 3. تنفيذ الحذف إذا تم تجاوز كل عمليات التحقق
        $role->delete();

        return response()->noContent();
    }
    /**
     * Get all available permissions.
     * هذه الدالة لا يتم حمايتها بـ authorizeResource، لذا نضيف الصلاحية يدويًا
     */
 public function getAllPermissions()
{
    $this->authorize('viewAny', Role::class);

    // 1. قاموس الترجمة المحدث (بناءً على الشاشات الأربعة + إدارة النظام)
    $groupTranslations = [
        'dashboard'    => 'لوحة التحكم',
        'user'         => 'المستخدمون',
        'role'         => 'الأدوار والتحكم',
        'department'   => 'الإدارات',
        'candidate'    => 'المترشحين',
        'document'     => 'المستندات',
        'job_request'  => 'طلبات التدريب',
        'setting'      => 'الإعدادات',
        'backup'       => 'النسخ الاحتياطي',
    ];

    $actionTranslations = [
        'view' => 'عرض',
        'create' => 'إنشاء',
        'update' => 'تعديل',
        'delete' => 'حذف',
        'download' => 'تحميل',
        'view_isfit' => 'عرض حالة اللياقة',
        'update_isfit' => 'تعديل حالة اللياقة',
    ];

    // 2. جلب كل الصلاحيات وتجميعها حسب المجموعة (ما قبل النقطة)
    $permissions = Permission::where('guard_name', 'api')->get()->groupBy(function ($permission) {
        return explode('.', $permission->name)[0];
    });

    // 3. بناء هيكل المجموعات (groups)
    $structuredGroups = [];
    foreach ($permissions as $groupKey => $permissionGroup) {
        $groupPermissions = [];
        foreach ($permissionGroup as $p) {
            $parts = explode('.', $p->name);
            $actionKey = $parts[1] ?? null;

            // نتحقق من وجود الإجراء في القاموس لإضافته
            if ($actionKey && array_key_exists($actionKey, $actionTranslations)) {
                $groupPermissions[] = [
                    'id' => $p->id,
                    'action' => $actionKey,
                    'display_name' => $actionTranslations[$actionKey], // مريح للفرونت إند
                ];
            }
        }

        // أضف المجموعة فقط إذا كانت تحتوي على صلاحيات وموجودة في قاموسنا
        if (!empty($groupPermissions) && array_key_exists($groupKey, $groupTranslations)) {
            $structuredGroups[] = [
                'key' => $groupKey,
                'display_name' => $groupTranslations[$groupKey],
                'permissions' => $groupPermissions,
            ];
        }
    }

    // 4. بناء هيكل الإجراءات (actions) المتاح للعرض العام في الواجهة
    $allActions = collect($actionTranslations)->map(function ($displayName, $key) {
        return ['key' => $key, 'display_name' => $displayName];
    })->values();

    // 5. إرجاع الاستجابة النهائية
    return response()->json([
        'groups' => $structuredGroups,
        'actions' => $allActions,
    ]);
}
}
