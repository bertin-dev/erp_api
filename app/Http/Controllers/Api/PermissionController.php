<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission as permission;
use Illuminate\Http\Request;
use App\Http\Resources\Index as Resource;

class PermissionController extends BaseController

{

    /**

     * Display a listing of the resource.

     *

     * @return \Illuminate\Http\Response

     */

    public function index(){
        $permissions = permission::all();
        return $this->sendResponse(new Resource($permissions), 'succés.');
    }



    public function edit(Permission $permission){
        //$this->authorize('edit', $permission);
        return $this->sendResponse(new Resource($permission), 'edition');

    }



    public function store(Request $request){
        $input = $request->all();
        $valid = $this->validation($input, [
            'name'=>'required|max:255',
            'slug'=>'required|max:255',
        ]);

        if($valid->fails())
            return $this->sendError('erreur de validation', $valid->errors());

        $permission = new Permission();
        $permission->name = $request->name;
        $permission->slug = $request->slug;
        $permission->save();
        return $this->sendResponse(new Resource($permission), 'creation terminé');

    }



    public function update(Request $request, Permission $permission){
        //$this->authorize('update', $permission);
        $input = $request->all();
        $valid = $this->validation($input, [
            'name'=>'required|max:255',
            'slug'=>'required|max:255',
        ]);
        if($valid->fails())
            return $this->sendError('erreur de validation', $valid->errors());
        $permission->name = $request->name;
        $permission->slug = $request->slug;
        $permission->save();
        return $this->sendResponse(new Resource($permission), 'modification terminé');
    }



    public function destroy(Permission $permission){
        $permission->delete();
        return $this->sendResponse(new Resource($permission), 'suppression reussie');

    }





}

