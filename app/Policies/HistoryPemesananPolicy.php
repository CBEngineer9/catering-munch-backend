<?php

namespace App\Policies;

use App\Models\HistoryPemesanan;
use App\Models\Users;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class HistoryPemesananPolicy
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
     * @param  \App\Models\HistoryPemesanan  $historyPemesanan
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Users $users, HistoryPemesanan $historyPemesanan)
    {
        return $users->users_id === $historyPemesanan->users_customer
        || $users->users_id === $historyPemesanan->users_provider
        ? Response::allow()
        : Response::deny('You do not own this resource.');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\Users  $users
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Users $users)
    {
        return $users->users_role === 'customer'
        ? Response::allow()
        : Response::deny('You cannot create this resource.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Users  $users
     * @param  \App\Models\HistoryPemesanan  $historyPemesanan
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Users $users, HistoryPemesanan $historyPemesanan)
    {
        return $users->users_id === $historyPemesanan->users_customer 
            || $users->users_id === $historyPemesanan->users_provider
            ? Response::allow()
            : Response::deny('You do not own this resource.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Users  $users
     * @param  \App\Models\HistoryPemesanan  $historyPemesanan
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function rate(Users $users, HistoryPemesanan $historyPemesanan)
    {
        return $users->users_id === $historyPemesanan->users_customer 
            ? Response::allow()
            : Response::deny('You cannot rate this pesanan.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Users  $users
     * @param  \App\Models\HistoryPemesanan  $historyPemesanan
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Users $users, HistoryPemesanan $historyPemesanan)
    {
        return $users->users_id === $historyPemesanan->users_customer 
            || $users->users_id === $historyPemesanan->users_provider
            ? Response::allow()
            : Response::deny('You do not own this resource.');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\Users  $users
     * @param  \App\Models\HistoryPemesanan  $historyPemesanan
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(Users $users, HistoryPemesanan $historyPemesanan)
    {
        return $users->users_id === $historyPemesanan->users_customer 
            || $users->users_id === $historyPemesanan->users_provider
            ? Response::allow()
            : Response::deny('You do not own this resource.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\Users  $users
     * @param  \App\Models\HistoryPemesanan  $historyPemesanan
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(Users $users, HistoryPemesanan $historyPemesanan)
    {
        return $users->isAdministrator()
            ? Response::allow()
            : Response::deny('You are not authorized to do this action.');
    }
}
