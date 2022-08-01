<?php

namespace App\Http\Controllers\Api;

use App\Models\Category as categorie;
use App\Http\Controllers\Controller;
use App\Models\Role as role;
use App\Models\Tarif as tarif;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource as Resource;

class TarifController extends BaseController{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tarif['index'] = tarif::getAllTarif();
        $tarif['categories'] = categorie::all();
        $tarif['roles'] = role::all();
        return $this->sendResponse(new Resource($tarif), 'les Tarifs ont été renvoyer avec succés.');
    }

    public function edit(Tarif $tarif){
        $res['index'] = tarif::showTarif($tarif->id);
        $res['categorie'] = categorie::all();
        $res['role'] = role::all();
        if (is_null($res)) {
            return $this->sendError('le tarif n\'existe pas.');
        }else{
            return $this->sendResponse(new Resource($res), 'le tarif numero '.$tarif->id.' a été trouver avec succés.');
        }
    }

    public function show($tarif_id){
        $tarif = tarif::showTarif($tarif_id);
        if (is_null($tarif)) {
            return $this->sendError('le tarif n\'existe pas.');
        }else{
            return $this->sendResponse(new Resource($tarif), 'le tarif numero '.$tarif_id.' a été trouver avec succés.');
        }
    }

    public function store(Request $request){
        $input = $request->all();

        $validator = $this->validation($input, [
            'tranche_min' => 'required',
            'tranche_max' => 'required',
            'tarif_night' => 'required',
            'tarif_day'=> 'required',
            'role_id'=> 'required',
            'type_tarif'=>'required',
            'categorie_id'=>'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Erreur de validation.', $validator->errors());
        }else{
            foreach ($input['categorie_id'] as $key) {
                $tarif = new tarif();
                $tarif->tranche_min = $input['tranche_min'];
                $tarif->tranche_max = $input['tranche_max'];
                $tarif->tarif_night = $input['tarif_night'];
                $tarif->tarif_day = $input['tarif_day'];
                $tarif->role_id = $input['role_id'];
                $tarif->type_tarif = $input['type_tarif'];
                $tarif->categorie_id = $key;
                $tarif->save();
            }
            return $this->sendResponse(new Resource($tarif), 'le tarif a été créer avec succés.');
        }
    }

    public function destroy(Tarif $tarif){
        return $tarif->delete();
    }

    public function update(Request $request, Tarif $tarif){
        $input = $request->all();

        $validator = $this->validation($input,[
            'tranche_min' => 'required',
            'tranche_max' => 'required',
            'tarif_night' => 'required',
            'tarif_day'=> 'required',
            'role_id'=> 'required',
            'categorie_id'=>'required'
        ]);

        if($validator->fails())
            return $this->sendError('Erreur de validation.', $validator->errors());

        $tarif->tranche_min = $input['tranche_min'];
        $tarif->tranche_max = $input['tranche_max'];
        $tarif->tarif_day = $input['tarif_day'];
        $tarif->tarif_night = $input['tarif_night'];
        $tarif->type_tarif = $input['type_tarif'];
        $tarif->role_id = $input['role_id'];
        $tarif->categorie_id = $input['categorie_id'];
        $tarif->save();
        return $this->sendResponse(new Resource($tarif), 'le tarif a été modifier avec succés.');
    }
}
