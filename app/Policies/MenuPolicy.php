<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\Users;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MenuPolicy
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
    public function viewAny(Users $users = null)
    {
        return Response::allow();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Users  $users
     * @param  \App\Models\Menu  $menu
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(?Users $users = null, Menu $menu)
    {
        return Response::allow();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\Users  $users
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Users $users)
    {
        // return $users->users_role === 'provider' && $users->users_status !== 'menunggu'
        // ? Response::allow()
        // : Response::deny('You cannot create this resource.');
        if ($users->users_role !== 'provider') {
            return Response::deny('You cannot create this resource.');
        } else if ($users->users_status === 'menunggu') {
            return Response::deny('Waiting provider cannot create menu.');
        } else {
            return Response::allow();
        }
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Users  $users
     * @param  \App\Models\Menu  $menu
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Users $users, Menu $menu)
    {
        return $users->users_id === $menu->users_id
        ? Response::allow()
        : Response::deny('You do not own this resource.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Users  $users
     * @param  \App\Models\Menu  $menu
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Users $users, Menu $menu)
    {
        return $users->users_id === $menu->users_id
        ? Response::allow()
        : Response::deny('You do not own this resource.');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\Users  $users
     * @param  \App\Models\Menu  $menu
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(Users $users, Menu $menu)
    {
        return $users->users_id === $menu->users_id
        ? Response::allow() 
        : Response::deny('You do not own this resource.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\Users  $users
     * @param  \App\Models\Menu  $menu
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(Users $users, Menu $menu)
    {
        return $users->isAdministrator()
        ? Response::allow()
        : Response::deny('You cannot force delete this resource.');
    }
}
