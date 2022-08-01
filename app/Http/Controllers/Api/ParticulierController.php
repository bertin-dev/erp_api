<?php

namespace App\Http\Controllers\Api;

use App\Models\Card as card;
use App\Models\Category as categorie;
use App\Models\Compte as compte;
use App\Models\Compte_subscription;
use App\Http\Controllers\Controller;
use App\Models\Particulier as particulier;
use App\Models\Role;
use App\Models\Transaction as transaction;
use App\Models\User as user;
use Illuminate\Http\Request;
use App\Http\Resources\Index as Resource;

class ParticulierController extends UserController
{
    public function particulierStaff(){
        $categories  = categorie::where('name','smopaye');
        $particulierStaff =  user::where('category_id', $categories->first()->id)
            ->orWhere('category_id', $categories->latest()->first())
            ->with(['particulier'=>function($q){ $q->with('permissions','roles');}])
            ->get();
        return $this->sendResponse(new Resource($particulierStaff), 'staff succès');
    }



    public function particulierAgent(){

        $particulierAgent = particulier::with(['user'=> function($query){$query->with(['categorie'=> function($query){$query->where('name','agent');}]);},'roles','permissions'])->get();

        return $this->sendResponse(new Resource($particulierAgent), 'staff agent succès');

    }



    public function register(Request $request){
        $params = [
            'firstname'=>'',
            'lastname' => 'required|string',
            'gender' => 'required|in:masculin,feminin',
            'cni'=>'required',
            'fonction'=> '',
            'email'=>''
        ];
        $authc = new UserController();
        $result = $authc->signup($request, $params);
        if($result['valid']->fails())
            return $this->sendError('erreur de validation', $result['valid']->errors(), 400);
        $particulier = new particulier($result['input']);
        if(user::getCard_number() != null){
            $card = card::findCodeNumberCard(user::getCard_number());
            if($card['status'] !=  1)
                return $this->sendError('la carte n\'existe pas');
        }
        $carte= user::getCard_number();
        $existcard = Card::where('code_number',$carte)->first();
        if($existcard->user_id)

        {
            return $this->sendError('Cette carte est deja utilisee' ,400);
        }
        user::getCompte_id() == null?user::setCompte_id(user::$compte->createAccount(user::$compte)):user::setCompte_id(user::getCompte_id());
        $result = user::createUser(new User(), 'fils');
        if(empty($result))
            return $this->sendError('une erreur c\'est produite lors de la création du compte utilisateur');
        $particulier::setUser_id($result);
        if(!$particulier::createParticulier($particulier))
            return $this->sendError('une erreur c\'est produite lors de la création du compte entreprise');
        if($request->role_id != null){
            $particulier->roles()->attach($request->role_id);
            $particulier->save();
        }else{
            $particulier->roles()->attach(user::getRole_id());
            $particulier->save();
        }
        if($request->permissions != null){
            foreach($request->permissions as $permission){
                $particulier->permissions()->attach($permission);
                $particulier->save();
            }
        }else{
            $role_permissions = Role::where('id', user::getRole_id())->with('permissions')->get()->toArray()[0];
            foreach($role_permissions["permissions"] as $permission){
                $particulier->permissions()->attach($permission['id']);
                $particulier->save();
            }
        }
        $msg='';
        if(user::getCard_number() != null){
            card::where('code_number',user::getCard_number())->update(['user_id' =>$particulier::getUser_id()]);
            $msg = "la carte rattachée à votre compte est ".user::getCard_number();
        }
        //user::setCard_number(null);
        $message = 'Mr/Mme '.''.$particulier::getLastname().' '.$particulier::getFirstname().' vous avez été enregistré(e) avec succès votre login est '.user::getPhone().' mot de passe est '. user::getPassword() .' et votre numéro de compte est '. user::$compte::getAccount_number().' '.$msg;
        $this->API_AVS(user::getPhone(),$message);
        return $this->sendResponse(new Resource([]),$message);
    }



    public function getUserChild(User $user, $user_id){



        return $this->sendResponse(new Resource($user->where('parent_id', $user_id)->with(['cards','particulier'])->get()->toArray()), 'success');



    }



    public function create(Request $request){

        if($request->role_id != null){

            $roles = Role::where('id', $request->role_id)->with('permissions')->first();

            $permissions = $roles->permissions;

            return $permissions;

        }

        $categories = categorie::all();

        $roles = Role::all();

        return $this->sendResponse(new Resource(['roles'=>$roles, 'categories'=>$categories]), 'creation d\'un particulier');

    }



    public function index(){
        $users = particulier::with(['user'=>function($query){$query->with('cards','categorie','role','compte');}])->get();
        return $this->sendResponse(new Resource($users),'toutes les utilisateurs ezpass');

    }





    public function edit(Particulier $particulier){

        $rolePermission = null;

        $roles = Role::get();

        $categories = categorie::get();

        $particulierRole = $particulier->roles->first();

        if($rolePermission != null)

            $rolePermission = $particulierRole->allRolePermissions;

        $particulierPermissions = $particulier->permissions;



        return $this->sendResponse(new Resource(

            [

                'user'=>$particulier->user,

                'particulier'=>$particulier,

                'categories'=>$categories,

                'roles'=>$roles,

                'particulierRole'=>$particulierRole,

                'rolePermission'=>$rolePermission,

                'particulierPermissions'=>$particulierPermissions

            ]), 'edition du particulier');

    }



    public function show($user_id){

        $transactionsCarte = [];

        $userCreate = [];

        $transactionsCompte = [];

        $particulierStaff = particulier::where('id',$user_id)->with(['user'=>function($query){$query->with(['compte', 'cards']);},'roles','permissions'])->first();

        $particulierStaff->categorie = categorie::find($particulierStaff->user->category_id)->name;

        if(isset($particulierStaff->user->cards[0]))

            $transactionsCarte = $this->transaction('carte', $particulierStaff->user->cards[0]->id);

        if(!is_null($particulierStaff->user->created_by)){

            $createUser = user::where('id',$particulierStaff->user->created_by)->with('particulier')->first();

            $createUser->categorie = categorie::find($createUser->category_id)->name;

            $userCreate["by"] = $createUser;

        }else{

            $userCreate["by"] = null;

        }

        $userCreate["create"] = $this->chercherTousLesUtilisateurscreer($particulierStaff->user->id);



        if($particulierStaff->user->compte)

            $transactionsCompte =$this->transaction('compte', $particulierStaff->user->compte->id);

        //$transactionsCompte['userCreate'] = $this->chercherTousLesUtilisateurscreer($particulierStaff->user->create_by);

        return $this->sendResponse(new Resource(['particulierStaff'=>$particulierStaff, 'transactionsCarte'=>$transactionsCarte, 'transactionsCompte'=>$transactionsCompte, 'utilisateurs'=>$userCreate]), 'staff succès');

    }



    public function chercherTousLesUtilisateurscreer($created_by) {

        $user = user::where('created_by',$created_by)->with(['particulier'=>function($query){$query->with(['roles','permissions']);},'compte', 'cards'])->get();

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


    //suppression d'un utilisateur
    public function destroy(Particulier $particulier){
        $user = user::find($particulier->user_id);
        foreach($user->cards() as $card){
            $card->transactions()->delete();
        }
        $user->cards()->delete();

        $compte = compte::find($user->compte_id);
        $compte->transaction_comptes()->delete();
        $souscriptions = Compte_subscription::where("compte_id", $compte->id)->get();
        foreach ($souscriptions as $souscription) {
            $souscription->delete();
        }
        $particulier = particulier::find($particulier->id);
        $particulier->permissions()->detach();
        $particulier->roles()->detach();
        $particulier->delete();
        $user->delete();
        $compte->delete();

    }



    public function update(Request $request, $particulier){

        $particulier = Particulier::where('id', $particulier)->with('user')->first();

        $input = $request->all();

        $params = [



            'firstname'=>'',



            'lastname' => 'required|string',



            'phone' => 'required',



            'gender' => 'required|in:masculin,feminin',



            'cni'=>'required',



            'fonction'=> '',



            'email'=>''



        ];



        $result = $this->validation($input, $params);

        if($result->fails())

            return $this->sendError('erreur de validation', $result->errors(), 400);



        $particulier->firstname = $request->firstname;

        $particulier->lastname = $request->lastname;

        $particulier->gender = $request->gender;

        $particulier->cni = $request->cni;

        $particulier->fonction = $request->fonction;

        $particulier->email = $request->email;



        $particulier->user->address = $request->address;

        $particulier->user->phone = $request->phone;

        $particulier->user->category_id = $request->categorie;

        $particulier->user->role_id = $request->role;



        $particulier->save();

        $particulier->roles()->detach();

        $particulier->permissions()->detach();



        if($request->role != null){

            $particulier->roles()->attach($request->role);

            $particulier->save();

        }



        if($request->permissions != null){

            foreach ($request->permissions as $permission) {

                $particulier->permissions()->attach($permission);

                $particulier->save();

            }

        }



        return $this->sendResponse(new Resource($particulier), 'modification terminé');

    }



    public function hasRoleUser($role){

        $data = auth()->user()->particulier()->first()->hasRole($role);

        return json_encode($data);

    }



}


