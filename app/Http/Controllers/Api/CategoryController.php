<?php

namespace App\Http\Controllers\Api;

use App\Models\Category as category;
use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Resources\Index as Resource;

class CategoryController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = category::with('role')->get();
        $roles = Role::all();
        $data['categories'] = $categories;
        $data['roles'] = $roles;
        return $this->sendResponse(new Resource($data), 'les Catgories ont été renvoyer avec succés.');
    }

    public function show($categorie_id){
        $categorie= category::showCategorie($categorie_id);
        if (is_null($categorie)) {
            return $this->sendError('la categorie n\'existe pas.');
        }else{
            return $this->sendResponse(new Resource($categorie), 'la categorie numero '.$categorie_id.' a été renvoyé avec succés.');
        }
    }

    public function store(Request $request)
    {
        $categorie = new category();
        $categorie->role_id = $request->role_id;
        $categorie->name = $request->name;
        $categorie->save();
        return $this->sendResponse(new Resource($categorie), 'la categorie a été créer avec succés.');
    }

    public function destroy(Category $categorie){
        return category::delete($categorie);
    }

    public function categorieWithRole(){
        return Category::with('role')->get();
    }


}

