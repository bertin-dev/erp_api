<?php

namespace App\Http\Controllers\Api;

use App\Models\Category as categorie;
use App\Models\Enterprise as ets;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Transaction as transaction;
use App\Models\User as user;
use App\Models\User_device;
use Illuminate\Http\Request;
use App\Http\Resources\Index as Resource;
use Illuminate\Support\Facades\DB;

class EnterpriseController extends UserController
{

    public function index()
    {
        $enterprise = [];
        // $ets = ets::with(['user'=>function($query){$query->with(['role','cards']);},'compte'])->get();
        $ets =DB::table('enterprises')
            ->leftJoin('users', 'enterprises.user_id', '=', 'users.id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('categories', 'users.category_id', '=', 'categories.id')
            ->leftJoin('comptes', 'users.compte_id', '=', 'comptes.id')
            ->leftJoin('cards', 'users.id', '=', 'cards.user_id')
            ->select('enterprises.id','enterprises.raison_social','users.phone',
                'users.address','roles.name as namerole','categories.name','comptes.account_number','cards.code_number')
            ->get();
        /* foreach ($ets as $key) {

             $key->categorie = categorie::where('id',$key["user"]["category_id"])->first();//->name;
             $enterprise[] = $key;
         }
        // var_dump($enterprise); die();*/
        return $this->sendResponse(new Resource($ets), 'toutes les entreprises ont été renvoyés avec succés.');
        //return $this->sendResponse($ets, 'toutes les entreprises ont été renvoyés avec succés.');
    }

    public function edit(ets $enterprise){
        $roles = Role::where('type','entreprise')->get();
        $categories = categorie::get();

        return $this->sendResponse(new Resource(
            [
                'user'=>$enterprise->user,
                'compte'=>$enterprise->user->compte,
                'enterprise'=>$enterprise,
                'categories'=>$categories,
                'roles'=>$roles
            ]), 'edition entreprise');
    }

    public function show($user_id){
        $transactionsCarte = [];
        $userCreate = [];
        $transactionsCompte = [];
        $entreprise = ets::where('id',$user_id)->with(['user'=>function($query){$query->with(['compte', 'cards','role']);}])->first();
        $entreprise->categorie = categorie::find($entreprise->user->category_id)->name;
        if(isset($entreprise->user->cards[0]))
            $transactionsCarte = $this->transaction('carte', $entreprise->user->cards[0]->id);
        $createUser = $entreprise->user->created_by ? user::where('id',$entreprise->user->created_by)->with(['enterprise','particulier'=>function($query){$query->with('roles');}])->first() : "aucune";
        if($createUser != "aucune"){
            $createUser->categorie = categorie::find($createUser->category_id)->name;
            $userCreate["by"] = $createUser;
        }else{
            $userCreate["by"] = null;
        }
        $userCreate["appareil_attribuer"] = $this->entreprise_appareil_attribuer($entreprise->id);
        $userCreate["create"] = $this->chercherTousLesUtilisateurscreer($entreprise->user->id);

        if($entreprise->user->compte)
            $transactionsCompte =$this->transaction('compte', $entreprise->user->compte->id);
        //$transactionsCompte['userCreate'] = $this->chercherTousLesUtilisateurscreer($particulierStaff->user->create_by);
        return $this->sendResponse(new Resource(['entreprise'=>$entreprise, 'transactionsCarte'=>$transactionsCarte, 'transactionsCompte'=>$transactionsCompte, 'utilisateurs'=>$userCreate]), 'staff succès');
    }

    public function chercherTousLesUtilisateurscreer($created_by) {
        $user = user::where('created_by',$created_by)->with(['compte', 'cards','particulier','enterprise'])->get();
        for ($i=0; $i < count($user) ; $i++) {
            $user[$i]->categorie = categorie::find($user[$i]->category_id)->name;
        }
        return $user;
    }

    public function transaction($type, $id){
        $transaction = [];
        if($type == "carte"){
            $res = transaction::where('card_id_sender', $id)->orWhere('card_id_receiver', $id)->get();
            if(!empty($res)){
                $res = $res->toArray();
            }
        }else{
            $res = transaction::where('account_id_sender', $id)->orWhere('account_id_receiver', $id)->get();
            if(!empty($res)){
                $res = $res->toArray();
            }
        }

        for ($i=0; $i < count($res) ; $i++) {
            $transaction[$i]['Date'] = $res[$i]['starting_date'];
            $transaction[$i]['id'] = $res[$i]['transaction_number'];
            $transaction[$i]['Status'] = $res[$i]['state'];
            $transaction[$i]['Operation'] = $res[$i]['transaction_type'];
            $transaction[$i]['Montant'] = $res[$i]['amount'];
            $transaction[$i]['Frais'] = $res[$i]['servicecharge'];

            if($res[$i]['account_id_sender']){
                $compte = $res[$i]['account_id_sender'];
                $userCompteSender = user::with(['enterprise','particulier','compte'=> function ($query) use ($compte){$query->where('id', $compte);}])->first();
                $transaction[$i]['user'][0] = $userCompteSender->toArray()['particulier'][0];
                $transaction[$i]['user'][0]['type'] = 'emetteur';
                $transaction[$i]['user'][0]['entite'] = 'compte n°: '.$userCompteSender->toArray()['compte']['account_number'];
            }
            if($res[$i]['account_id_receiver']){
                $compte = $res[$i]['account_id_receiver'];
                $userCompteReceiver = user::with(['enterprise','particulier','compte'=> function ($query) use ($compte){$query->where('id', $compte);}])->first();
                $transaction[$i]['user'][1] = $userCompteReceiver->toArray()['particulier'][0];
                $transaction[$i]['user'][1]['type'] = 'destinataire';
                $transaction[$i]['user'][1]['entite'] = 'compte n°: '.$userCompteReceiver->toArray()['compte']['account_number'];
            }

            if($res[$i]['card_id_sender']){
                $card = $res[$i]['card_id_sender'];
                $userCardSender = user::with(['enterprise','particulier','cards'=> function ($query) use ($card){$query->where('id', $card);}])->first();
                $transaction[$i]['user'][0] = $userCardSender->toArray()['particulier'][0];
                $transaction[$i]['user'][0]['type'] = 'emetteur';
                if(!empty($userCardSender->toArray()['cards']))
                    $transaction[$i]['user'][0]['entite'] = 'carte n°: '.$userCardSender->toArray()['cards'][0]['code_number'];
            }

            if($res[$i]['card_id_receiver']){
                $card = $res[$i]['card_id_receiver'];
                $userCardReceiver = user::with(['enterprise','particulier','cards'=> function ($query) use ($card){$query->where('id', $card);}])->first();
                $transaction[$i]['user'][1] = $userCardReceiver->toArray()['particulier'][0];
                $transaction[$i]['user'][1]['type'] = 'destinataire';
                if(!empty($userCardReceiver->toArray()['cards']))
                    $transaction[$i]['user'][1]['entite'] = 'carte n°: '.$userCardReceiver->toArray()['cards'][0]['code_number'];
            }
        }
        return $transaction;
    }

    public function update(Request $request, $enterprise){

        $enterprise = ets::where('id', $enterprise)->with('user')->first();
        $input = $request->all();
        $params = [

            'raison_social'=> '',
            'rccm'=>'',
            'status'=>'',
            'phone' => '',
            'address' => '',
            'ets_principal_id'=>'',
            'principal_account_id'=>'',

        ];

        $result = $this->validation($input, $params);
        if($result->fails())
            return $this->sendError('erreur de validation', $result->errors(), 400);

        $enterprise->raison_social = $request->raison_social;
        $enterprise->rccm = $request->rccm;
        $enterprise->status = $request->status;
        $enterprise->principal_id = $request->ets_principal_id;
        //$enterprise->principal_account_id = $request->principal_account_id;

        $enterprise->user->address = $request->address;
        $enterprise->user->phone = $request->phone;
        $enterprise->user->category_id = $request->categorie;
        $enterprise->user->role_id = $request->role;

        $enterprise->save();
        return $this->sendResponse(new Resource($enterprise), 'modification terminé');
    }

    public function register(Request $request){
        $user = new User();
        $userCtl = new UserController();
        $params = [
            'raison_social'=> 'required|unique:enterprises|string',
            'rccm'=>'required',
            'status'=>'required',
            'ets_principal_id'=>'int',
            'principal_account_id'=>'int',
        ];
        $authc = new UserController();
        $result = $authc->signup($request, $params);
        if($result['valid']->fails()){
            return $this->sendError('erreur de validation', $result['valid']->errors());
        }
        $ets = new ets($result['input']);
        $user::setCompte_id($user::$compte->createAccount($user::$compte));
        $result = $user::createUser($user);
        $ets::setUser_id($result);
        if(!$ets::createEnterprise($ets)){
            return $this->sendError('une erreur c\'est produite lors de la création du compte entreprise');
        }
        $message = 'le compte dont la raison sociale fait l\'objet '.$ets::getRaison_social().' a été enregistrer avec succés. votre login est '.$user::getPhone().', mot de passe est '.$user::getPassword().' et votre numéro de compte est '.$user::$compte::getAccount_number();
        $this->API_AVS($user::getPhone(),$message);
        $enterprise = $userCtl->profileResource($user, $user::getPhone());
        return $this->sendResponse(new Resource($enterprise),$message);

    }

    public function getAllDevice($enterprise_id){
        $enterprise = $this->entreprise_appareil_attribuer($enterprise_id);
        return $this->sendResponse(new Resource($enterprise),'tous les devices du compte');
    }

    public function entreprise_appareil_attribuer($enterprise_id){
        $device = [];
        return  ets::where('id',$enterprise_id)->orWhere('principal_id', $enterprise_id)->with(['devices' => function($query) {
            $query->with('transactions');
        }])->get();
    }

    public function attributeDeviceToChild(Request $request, $enterprise_id, $enterprise_id_child){
        $input = $request->all();
        $validator = $this->validation($input, ['device_state' => 'required|in:attribuer,desattribuer']);
        if($validator->fails())
            return $this->sendError('Erreur de validation.', $validator->errors());
        $user_device = User_device::where('user_id', $enterprise_id)->first();
        if($user_device == null)
            $this->sendError('Echec de l\'opération l\'appareil a été attribué');
        User_device::hydratation($user_device->toArray());
        if($request->device_state == "attribuer"){
            $user_device->delete();
            $userdevice = User_device::prepareDataToSave();
            $userdevice['user_id'] = $enterprise_id_child;
            $user_device = User_device::createUserDevice($userdevice);
        }else{
            $user_device->delete();
            $userdevice = User_device::prepareDataToSave();
            $userdevice['user_id'] = $enterprise_id;
            $user_device = User_device::createUserDevice($userdevice);
        }
        return $this->sendResponse(new Resource($user_device),'le tpe est '.$request->device_state);
    }
}

