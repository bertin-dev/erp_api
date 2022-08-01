<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
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


    public function before($user, $abulity){
        if($user->particulier->first()->isAdmin() || $user->particulier->first()->isUser()){
            return true;
        }
    }

    /**
     * Determine whether the User can view any models.
     *
     * @param  \App\Models\User  $User
     * @return mixed
     */
    public function viewAny(User $User)
    {
        //
    }

    /**
     * Determine whether the User can view the model.
     *
     * @param  \App\Models\User  $User
     * @param  \App\Models\Role  $role
     * @return mixed
     */
    public function view(User $User, Role $role)
    {
        //
    }

    /**
     * Determine whether the User can create models.
     *
     * @param  \App\Models\User  $User
     * @return mixed
     */
    public function create(User $User)
    {
        //
    }

    /**
     * Determine whether the User can update the model.
     *
     * @param  \App\Models\User  $User
     * @param  \App\Models\Role  $role
     * @return mixed
     */
    public function update(User $user, Role $role)
    {
        if($user->particulier->first()->roles->contains('slug', 'administrateur')){
            return true;
        }elseif($user->particulier->first()->permissions->contains('slug', 'modifier-role')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the User can update the model.
     *
     * @param  \App\Models\User  $User
     * @param  \App\Models\Role  $role
     * @return mixed
     */
    public function edit(User $user, Role $role)
    {
        if($user->particulier->first()->roles->contains('slug', 'administrateur')){
            return true;
        }elseif($user->particulier->first()->permissions->contains('slug', 'editer-role')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the User can delete the model.
     *
     * @param  \App\Models\User  $User
     * @param  \App\Models\Role  $role
     * @return mixed
     */
    public function delete(User $User, Role $role)
    {
        if($User->roles->contains('slug', 'administrateur')){
            return true;
        }elseif($user->permissions->contains('slug', 'edit-delete')){
            return true;
        }

        return false;
    }

    /**
     * Determine whether the User can restore the model.
     *
     * @param  \App\Models\User  $User
     * @param  \App\Models\Role  $role
     * @return mixed
     */
    public function restore(User $User, Role $role)
    {
        //
    }

    /**
     * Determine whether the User can permanently delete the model.
     *
     * @param  \App\Models\User  $User
     * @param  \App\Models\Role  $role
     * @return mixed
     */
    public function forceDelete(User $User, Role $role)
    {
        //
    }
}
