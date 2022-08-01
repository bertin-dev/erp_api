<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InfosUserController extends BaseResponseController
{
    // generer les champs des categories pour Bertin
    public function infos (){
        return Category::with('role')->get();
    }

    // fonction servant a remplire la table des roles
    public function postinfosrole (Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }else{
            $role =  Role::create([
                'name'=> $request->name,
            ]);
            $success['name'] =  $role->name;
            return $this->sendResponse($success, 'role register successfully.');
        }

    }

    // fonction pour remplire la table des categries
    public function postinfoscategorie (Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'role_id' =>  'required|max:255',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $categorie =  Category::create([
            'name'=> $request->name,
            'role_id'=> $request->role_id
        ]);
        $success['name'] =  $categorie->name;
        return $this->sendResponse($success, 'categorie register successfully.');
    }

}

