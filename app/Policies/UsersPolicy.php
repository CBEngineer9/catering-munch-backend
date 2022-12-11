<?php

namespace App\Policies;

use App\Models\Users;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UsersPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     *
     * @param  \App\Models\Users  $user
     * @param  string  $ability ability to check
     * @return void|bool
     */
    public function before(Users $user, $ability)
    {
        if ($user->isAdministrator()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\Users  $users
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(Users $users)
    {
        return Response::allow();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Users  $users
     * @param  \App\Models\Users  $other_user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Users $users, Users $other_user)
    {
        // all provider profile are public
        return $other_user->users_role === 'provider'
        ? Response::allow()
        : Response::deny('You cannot view this resource.');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\Users  $users
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Users $users)
    {
        return Response::allow();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Users  $users
     * @param  \App\Models\Users  $other_user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function ban(Users $users, Users $other_user)
    {
        return $users->isAdministrator()
        ? Response::allow()
        : Response::deny('You cannot force delete this resource.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Users  $users
     * @param  \App\Models\Users  $other_user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function unban(Users $users, Users $other_user)
    {
        return $users->isAdministrator()
        ? Response::allow()
        : Response::deny('You cannot force delete this resource.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Users  $users
     * @param  \App\Models\Users  $other_user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function approve(Users $users, Users $other_user)
    {
        return $users->isAdministrator()
        ? Response::allow()
        : Response::deny('You cannot force delete this resource.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Users  $users
     * @param  \App\Models\Users  $other_user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Users $users, Users $other_user)
    {
        return $users->isAdministrator()
        ? Response::allow()
        : Response::deny('You cannot force delete this resource.');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\Users  $users
     * @param  \App\Models\Users  $other_user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(Users $users, Users $other_user)
    {
        return $users->isAdministrator()
        ? Response::allow()
        : Response::deny('You cannot force delete this resource.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\Users  $users
     * @param  \App\Models\Users  $other_user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(Users $users, Users $other_user)
    {
        return $users->isAdministrator()
        ? Response::allow()
        : Response::deny('You cannot force delete this resource.');
    }
}
