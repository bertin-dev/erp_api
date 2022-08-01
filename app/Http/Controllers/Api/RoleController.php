<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role as role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource as Resource;

class RoleController extends BaseController

{

    /**

     * Display a listing of the resource.

     *

     * @return \Illuminate\Http\Response

     */

    public function index(){
        $roles = role::where('type','particulier')->get();
        return $this->sendResponse(new Resource($roles), 'les Roles ont été renvoyer avec succés.');
    }



    public function roleWithPermission(){

        $roles = role::with('permissions')->get();

        return $this->sendResponse(new Resource($roles), 'les roles avec les permissions sont renvoyée avec succés');

    }



    public function show($role_id){

        $role= role::showRole($role_id);

        if (is_null($role)) {

            return $this->sendError('le role n\'existe pas.');

        }else{

            return $this->sendResponse(new Resource($role), 'le role numero '.$role_id.' a été renvoyé avec succés.');

        }

    }



    public function entreprise(){

        $roles = role::where('type','entreprise')->get();

        return $this->sendResponse(new Resource($roles),'roles des entreprise');

    }



    public function edit(Role $role){
        $this->authorize('edit', $role);
        $roles = role::where('id', $role->id)->with('permissions')->first();
        return $this->sendResponse(new Resource($roles), 'edition du role');

    }



    public function store(Request $request){
        $input = $request->all();
        $valid = $this->validation($input, [
            'role_name'=>'required|max:255',
            'role_slug'=>'required|max:255',
        ]);

        if($valid->fails())
            return $this->sendError('erreur de validation', $valid->errors());

        $role = new Role();
        $role->name = $request->role_name;
        $role->slug = $request->role_slug;
        $role->save();

        foreach($request->roles_permissions as $permission){
            $role->permissions()->attach($permission);
            $role->save();
        }



        return $this->sendResponse(new Resource($role), 'creation terminé');

    }



    public function update(Request $request, Role $role){
        $this->authorize('update', $role);
        $input = $request->all();

        $valid = $this->validation($input, [

            'role_name'=>'required|max:255',

            'role_slug'=>'required|max:255',

        ]);



        if($valid->fails())

            return $this->sendError('erreur de validation', $valid->errors());

        $role->name = $request->role_name;
        $role->slug = $request->role_slug;
        $role->save();
        $role->permissions()->detach();
        $role->permissions()->delete();

        foreach($request->roles_permissions as $permission){
            $role->permissions()->attach($permission);
            $role->save();
        }

        return $this->sendResponse(new Resource($role), 'modification terminé');

    }



    public function destroy(Role $role){

        $role->permissions()->delete();

        $role->delete();

        $role->permissions()->detach();

        return $this->sendResponse(new Resource($role), 'suppression reussie');

    }





}


