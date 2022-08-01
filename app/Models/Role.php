<?php

namespace App\Models;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
    ];

    public static function getAllRole(){
        $roles = self::all();
        /*$rolesWithPermissions = [];
        $roles = self::with('permissions')->orderBy('id','desc')->get();
        /*dd($roles);
        foreach ($roles as $role) {
            dd($role);
            $rolesWithPermissions[] = self::find($role->id)->with('permissions')->get();
        }*/
        return $roles;
    }

    public static function showRole($role_id){
        $role = self::find($role_id);
        if($role != null){
            self::hydratation($role->toArray());
        }
        return $role;
    }

    public static function getRoleUserCard($user_id_card, $role){
        if(!$user_id_card){
            return self::where('name', $role)->first()->users()->where('id', $user_id_card)->get()->toArray();
        }
        return NULL;
    }

    public function permissions(){
        return $this->belongsToMany(Permission::class, 'roles_permissions');
    }

    public function users(){
        return $this->hasMany(User::class);
    }

    public function categories(){
        return $this->hasMany(Category::class);
    }
}
