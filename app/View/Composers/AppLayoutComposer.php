<?php

namespace App\View\Composers;

use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AppLayoutComposer
{
    public function compose(View $view): void
    {
        $user = Auth::guard('web')->user();
        $student = Auth::guard('students')->user();
        $account = $user ?? $student;
        $roles = $this->rolesFor($user);
        $displayName = $this->displayNameFor($account);

        $view->with([
            'appAccount' => $account,
            'appAccountDisplayName' => $displayName,
            'appAccountInitials' => $this->initialsFor($displayName),
            'appAccountMeta' => $this->accountMetaFor($account, $roles),
            'appAccountRoles' => $roles->pluck('name'),
            'appIsStudent' => $student instanceof Student,
            'appPermissions' => $roles->flatMap->permissions->pluck('slug')->unique()->values(),
            'appProfileRoute' => $student instanceof Student ? 'student.profile.edit' : 'profile.edit',
            'unreadNotifications' => $account?->unreadNotifications()->count() ?? 0,
        ]);
    }

    /**
     * @return Collection<int, Role>
     */
    private function rolesFor(?User $user): Collection
    {
        return $user?->roles()
            ->with('permissions:id,slug')
            ->get()
            ->collect() ?? collect();
    }

    private function displayNameFor(User|Student|null $account): string
    {
        return match (true) {
            $account instanceof Student => $account->full_name,
            $account instanceof User => $account->fullname,
            default => 'User',
        };
    }

    private function accountMetaFor(User|Student|null $account, Collection $roles): string
    {
        return match (true) {
            $account instanceof Student => $account->registration_number,
            $account instanceof User => $roles->first()?->name ?? 'User',
            default => 'Account',
        };
    }

    private function initialsFor(string $name): string
    {
        return Str::of($name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }
}
