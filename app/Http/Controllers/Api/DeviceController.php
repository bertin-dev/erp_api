<?php

namespace App\Http\Controllers\Api;

use App\Models\Device as device;
use App\Http\Controllers\Controller;
use App\Http\Resources\Index as Resource;
use App\Models\User_device as device_user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $device = device::getAllDevice();
        return $this->sendResponse(Resource::collection($device), 'les Devices ont été renvoyer avec succés.');
    }

    public function show($device){
        switch ($device) {
            case 'tpe':
                $device = device::where('device_type','TPE')->get();
                break;

            case 'kit':
                $device = device::where('device_type','Kit NFC')->get();
                break;

            case 'telephone':
                $device = device::where('device_type','TELEPHONE')->get();
                break;

            default:
                $device = device::showDevice($device);
                break;
        }


        if (is_null($device)) {
            return $this->sendError('le device n\'existe pas.');
        }else{
            return $this->sendResponse(new Resource($device), 'succés.');
        }
    }

    public function destroy(Device $device){
        return $device->delete();
    }

    public function edit(Device $device){
        return $this->sendResponse(new Resource($device), 'le device renvoyée avec succés');
    }

    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'designation'=>'required',
            'device_type'=> 'required',
            'serial_number'=> 'required',
            'passerel'=> 'required',
            'mobile'=> 'required',
            'manifacturer'=> 'required',
            'branch'=> 'required',
            'provider'=> 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Erreur de validation.', $validator->errors());
        }else{
            $device = device::createDevice($input);
            return $this->sendResponse(new Resource($device), 'la carte a été créer avec succés.');
        }
    }

    public function update(Request $request, Device $device)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'designation'=>'required',
            'device_type'=> 'required',
            'serial_number'=> 'required',
            'passerel'=> 'required',
            'mobile'=> 'required',
            'manifacturer'=> 'required',
            'branch'=> 'required',
            'provider'=> 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Erreur de validation.', $validator->errors());
        }else{
            device::hydratation((array) $input);
            $device = device::updateDevice($device);
            if($device['result']){
                return $this->sendResponse(new Resource($device['device']), 'l\'appareil a été modifier avec succés.');
            }else{
                return $this->sendError('Erreur!!!, les informations n\'ont pas été mises à jour');
            }
        }
    }


    public function attribution($type){
        $device_attribute = [];
        $device_no_attribute = [];
        $devices = device::all();
        foreach($devices as $device){
            if(device_user::where('device_id', $device->id)->get()->toArray()){
                $device_attribute[] = $device;
            }else{
                $device_no_attribute[] = $device;
            }
        }

        if($type == "attribuer"){
            return $this->sendResponse(new Resource($device_attribute),'devices set attribute');
        }else{
            return $this->sendResponse(new Resource($device_no_attribute),'devices set not attribute');
        }
    }
}

