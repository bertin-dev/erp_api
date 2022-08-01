<?php

namespace App\Http\Controllers\Api;

use App\Models\Campaign as campaign;
use App\Models\Category as category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Index as Resource;

class CampaignController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $campagnes = campaign::all();
        foreach ($campagnes as $campagne) {
            $campagne['categorie'] = category::find($campagne->category_id);
        }
        return json_encode($campagnes);

        return $this->sendResponse(new Resource($data), 'les Campagnes ont été renvoyer avec succés.');
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
        $campagne = new campaign();
        $campagne->starting_date = $request->dateDebutCampagne;
        $campagne->end_date = $request->dateFinCampagne;
        $campagne->discount = $request->remiseCampagne;
        $campagne->category_id = $request->categorie;
        $campagne->save();
        return $this->sendResponse(new Resource($campagne), 'la campagne a été créer avec succés.');
    }

    public function destroy(Category $categorie){
        return category::delete($categorie);
    }

    public function categorieWithRole(){
        return Category::with('role')->get();
    }


}

