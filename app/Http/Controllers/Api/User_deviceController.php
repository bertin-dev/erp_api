<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Index as Resource;
use App\Models\User_device as user_device;
use Illuminate\Http\Request;

class User_deviceController extends BaseController



{



    /**



     * Display a listing of the resource.



     *



     * @return \Illuminate\Http\Response



     */



    public function index()

    {

        $user_device = user_device::all();

        return $this->sendResponse(Resource::collection($user_device), 'les UserDevices ont été renvoyer avec succés.');

    }







    public function show($user_device_id){



        $user_device = user_device::showUserDevice($user_device_id);



        if (is_null($user_device)) {



            return $this->sendError('le UserDevice n\'existe pas.');



        }else{



            return $this->sendResponse(new Resource($user_device), 'le UserDevice numero '.$user_device_id.' a été trouver avec succés.');



        }



    }







    public function store(Request $request)

    {
        $input = $request->all();

        $validator = Validator::make($input, [



            'starting_possession'=> 'required',



            'end_possession'=> 'required',



            'user_id'=>'required',



            'device_id'=>'required'



        ]);

        if($validator->fails()){

            return $this->sendError('Erreur de validation.', $validator->errors());

        }else{
            if(!user_device::where([['user_id', $request->user_id],['device_id', $request->device_id]])->first()){
                $user_device = user_device::createUserDevice($input);
                return $this->sendResponse(new Resource($user_device), 'le UserDevice a été créer avec succés.');
            }
            return $this->sendError('Cet appareil est déja attribuer a cet utilisateur.');
        }



    }







    public function desattribuer(Request $request){

        $user_device = user_device::where([['user_id', $request->user_id],['device_id', $request->device_id]])->first();
        if($user_device)
            return json_encode($user_device->delete());
        return $this->sendError('Cet appareil est déja desattribuer a cet utilisateur.');
    }



}



