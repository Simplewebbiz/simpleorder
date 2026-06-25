<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class UserController extends Controller
{
    private const STAFF_ROLES = [
        User::ROLE_OWNER,
        User::ROLE_MANAGER,
        User::ROLE_FULFILLMENT,
    ];

    public function index()
    {
        return $this->renderIndex();
    }

    public function create()
    {
        return $this->renderIndex(showForm: true);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->guardOwnerRole($data['role']);
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('tenant.admin.users.index')->with('success', 'Staff member created.');
    }

    public function edit(User $user)
    {
        return $this->renderIndex($user, true);
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user);
        $this->guardOwnerRole($data['role'], $user);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if ($user->isOwner() && $data['role'] !== User::ROLE_OWNER && $this->ownerCount() <= 1) {
            throw ValidationException::withMessages([
                'role' => ['Each restaurant must keep at least one owner.'],
            ]);
        }

        $user->update($data);

        return redirect()->route('tenant.admin.users.index')->with('success', 'Staff member updated.');
    }

    public function destroy(User $user)
    {
        if (auth('tenant')->id() === $user->id) {
            return back()->with('error', 'You cannot remove your own account.');
        }

        if ($user->isOwner() && ! auth('tenant')->user()?->isOwner()) {
            return back()->with('error', 'Only an owner can remove another owner.');
        }

        if ($user->isOwner() && $this->ownerCount() <= 1) {
            return back()->with('error', 'Each restaurant must keep at least one owner.');
        }

        $user->delete();

        return redirect()->route('tenant.admin.users.index')->with('success', 'Staff member removed.');
    }

    private function guardOwnerRole(string $role, ?User $user = null): void
    {
        if (auth('tenant')->user()?->isOwner()) {
            return;
        }

        if ($role === User::ROLE_OWNER || $user?->isOwner()) {
            throw ValidationException::withMessages([
                'role' => ['Only an owner can create or change owner accounts.'],
            ]);
        }
    }

    private function renderIndex(?User $editing = null, bool $showForm = false)
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::query()
                ->whereIn('role', self::STAFF_ROLES)
                ->orderByRaw("FIELD(role, 'owner', 'manager', 'fulfillment')")
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'phone', 'role', 'created_at']),
            'editing' => $editing?->only(['id', 'name', 'email', 'phone', 'role']),
            'showForm' => $showForm,
            'roles' => [
                ['value' => User::ROLE_OWNER, 'label' => 'Owner', 'description' => 'Full access, including billing, settings, staff, reports, menu, and orders.'],
                ['value' => User::ROLE_MANAGER, 'label' => 'Manager', 'description' => 'Can manage menu, settings, reports, staff, and orders.'],
                ['value' => User::ROLE_FULFILLMENT, 'label' => 'Kitchen Staff', 'description' => 'Can view orders and move them through preparation.'],
            ],
        ]);
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'phone' => 'nullable|string|max:30',
            'role' => ['required', Rule::in(self::STAFF_ROLES)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
        ]);
    }

    private function ownerCount(): int
    {
        return User::query()->where('role', User::ROLE_OWNER)->count();
    }
}