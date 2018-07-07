<?php

namespace App\Policies;

use App\Models\user;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    //用户修改信息策略
    public function update(User $currentUser, User $user)
    {
        return $currentUser->id === $user->id;
    }

    public function destroy(User $currentuser, User $user)
    {
        return $currentuser->is_admin && $currentuser->id !== $user->id;
    }
}
