<?php

namespace App\Traits;

use App\Models\Role;

use App\Models\Permission;

trait HasRolesAndPermissions

{
    public function isAdmin()
    {
        if ($this->roles->contains('slug', 'administrateur')) {
            return true;
        }
    }

    public function isUser()
    {
        if ($this->roles->contains('slug', 'utilisateur')) {
            return true;
        }
    }


    public function roles()
    {
        return $this->belongsToMany(Role::class, 'users_roles');
    }


    public function permissions()
    {

        return $this->belongsToMany(Permission::class, 'users_permissions');

    }


    public function hasRole($role)
    {

        return $this->roles->contains('slug', $role);

    }

}
