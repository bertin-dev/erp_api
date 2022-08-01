<?php

namespace App\Http\Controllers\Api;

use App\Models\Bonus_history;
use App\Models\Category as category;
use App\Models\Compte as compte;
use App\Http\Controllers\Controller;
use App\Models\Particulier;
use App\Models\Transaction as transaction;
use App\Models\User as user;
use App\Models\Card as card;
use Illuminate\Http\Request;
use App\Http\Resources\Index as Resource;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Client as OClient;

class UserController extends BaseController
{

    public function index()
    {
        $users = user::all();
        return $this->sendResponse(new Resource($users), 'les Utilisateurs ont été renvoyer avec succés.');
    }

    public function show($user_id){
        $user = user::showUser($user_id);
        if (is_null($user)) {
            return $this->sendError('l\'utilisateur n\'existe pas.');
        }else{
            return $this->sendResponse(new Resource($user), 'l\'utilisateur numero '.$user_id.' a été renvoyé avec succés.');
        }
    }

    public function signup($request, $param){
        $input = $request->all();
        $paramU = [
            'phone' => 'required|unique:users|size:9',
            'address' => 'required',
            'category_id' => 'required',
            'role_id' => 'required',
            'parent_id' => '',
            'compte_id'=>'',
            'card_number' => 'exists:cards,code_number|size:8'
        ];

        $message = [
            'phone.unique' => 'Ce numero de telephone existe deja',
            'card_number.exists'=>'Cette carte n\'existe pas',
            'card_number.size'=>'le numero de carte doit être 8 caractéres',
        ];

        $params = array_merge($param, $paramU);
        $valid = $this->validation($input, $params, $message);
        return array('valid'=>$valid, 'input'=>$input);
    }

    /**
     * Login user and get token
     */
    public function login(Request $request){
        $credentials = $request->all();
        $valid = $this->validation($credentials, [
            'phone' => 'required|integer',
            'password' => 'required|string',
            'secret' => 'required'
        ]);

        if($valid->fails())
            return $this->sendError('erreur de validation', $valid->errors());
        if(Auth::attempt(['phone'=>$credentials['phone'], 'password'=>$credentials['password']])){
            $oClientSecret = OClient::where('name',$credentials['secret'])->get()->first();
            if($oClientSecret == null)
                return $this->sendError(new Resource([]),['error'=>'Client is not authentificated'], 401);
            if($oClientSecret){
                return $this->getTokenAndRefreshToken(OClient::where('password_client',1)->first(), $credentials['phone'], $credentials['password']);
            }else{
                return $this->sendError(new Resource([]),['error'=>'Client is not authentificated'], 401);
            }
        }
        return $this->sendError('login ou mot de passe incorrect',[], 401);
    }

    public function getTokenAndRefreshToken(OClient $oClient, $phone, $password){
        $data = [
            'grant_type' => 'password',
            'client_id' => $oClient->id,
            'client_secret' => $oClient->secret,
            'username' => $phone,
            'password' => $password,
            'scope' => '*',
        ];
        $tokenRequest = Request::create('/oauth/token', 'post', $data);
        return app()->handle($tokenRequest);
    }

    /**
     * deconnecter un utilisateur (Rejecter le token)
     *
     * @return [string] message
     */
    public function logout(){
        auth()->user()->tokens->each(function ($token, $key){
            $token->delete();
        });
        return response()->json('logged out successfully', 200);
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Get the authenticated User
     * @param card_number numero de la carte
     * @param id utilisateur
     * @return [json] user object
     */
    public function addCardToUser(Request $request, User $user)
    {
        $input = $request->all();
        $params = [
            'id' => 'required',
            'card_number' => 'required|size:8'
        ];

        $valid = $this->validation($input, $params);
        if($valid->fails())
            return $this->sendError('Erreur de validation.', $valid->errors());
        user::hydratation($input);
        $userItem = user::find(user::getId());
        $response = $user::attribCard(new card());
        if($response['status'] == 1){
            $user = $this->profileResource($user, $userItem->phone);
            if(array_key_exists(0, $user['enterprise'])){
                $notif = $user['enterprise'][0]['raison_social'];
            }else{
                $notif = $user['particulier'][0]['lastname'].' '.$user['particulier'][0]['firstname'];
            }
            return $this->sendResponse(new Resource($user),'la carte numero '.user::getCard_number().' a été attribuer a '.$notif);
        }
        return $this->sendError($response['notif']);
    }

    public function removeCardToUser(Request $request, User $user)
    {
        $input = $request->all();
        $params = [
            'id' => 'required',
            'card_number' => 'required|size:8'
        ];

        $valid = $this->validation($input, $params);
        if($valid->fails())
            return $this->sendError('Erreur de validation.', $valid->errors());
        user::hydratation($input);
        $response = $user::desattribCard(new card());
        if($response['status'] == 1){
            $user = $this->profileResource($user, user::getPhone());
            var_dump($user);
            if($user != null and !empty($user['enterprise'])){
                $notif = $user['enterprise'][0]['raison_social'];
            }else{
                $notif = $user['particulier'][0]['lastname'].' '.$user['particulier'][0]['firstname'];
            }
            return $this->sendResponse(new Resource($user),'la carte numero '.user::getCard_number().' a été attribuer a '.$notif);
        }
        return $this->sendError($response['notif']);
    }

    public function profileResource($user, $user_number){


        if($user::where('phone', $user_number)->first() == null)
            return $this->sendError('l\'utilisateur n\'existe pas');
        $userItem = $user->with(['particulier','enterprise','role','compte'=> function ($query){
            $query->with('compte_subscriptions');
            },'cards'])->where('phone', $user_number)->first();
        $userItem['statusUser'] = array_key_exists(0, [$userItem->particulier]);
        $userItem['bonus_valider'] = Bonus_history::where([['user_id', $userItem->id],['state',1]])->count();
        $userItem['bonus_non_valider'] = Bonus_history::where([['user_id',$userItem->id],['state',0]])->count();
        $userItem['categorie'] = category::where('id',$userItem['category_id'])->first();
        return $userItem;
    }

    public function getCardUserChild(User $user, $user_id){
        return $this->sendResponse(new Resource($user->where('parent_id', $user_id)->with(['cards','particulier','enterprise'])->get()->toArray()), 'succes');
    }

    public function getAccountUserChild(User $user, $user_id){
        $resource = $user->where('parent_id', $user_id)->with(['cards','enterprise'=>function($query){$query->withCount('devices');},'compte'])->get()->toArray();
        for ($i=0; $i < count($resource); $i++) {
            $resource[$i]['transactions'] = transaction::where('account_id_sender',$resource[$i]['compte']['id'])->orWhere('account_id_receiver',$resource[$i]['compte']['id'])->orderBy('created_at','desc')->get()->toArray();
        }
        return $this->sendResponse(new Resource($resource), 'success');
    }

    public function profile(User $user, $user_phone){

        return $this->profileResource($user, $user_phone);
    }

    public function cardUserProfile($code_number){
        $cards = Card::with('subscriptions')->where('code_number',$code_number)->get();
        foreach ($cards as $card) {
            $cardtype = $card->type;
            $cardStarting_date = $card->starting_date;
            $cardEnd_date = $card->end_date;
            $subscriptions=  $card->subscriptions;
            $code_number = $card->code_number;
        }

        foreach ($subscriptions as $subscription) {
            $subscription=  $subscription->name;

        }

        return $this->sendResponse(' profile',' Numerode compte : '.$code_number.  '; tye de carte : '.$cardtype. '; date de debut: ' .$cardStarting_date.'; date de fin: '.$cardEnd_date.'; subscription: '.$subscription);
    }

    /**
     * Update user informations
     * @param id de l'user
     * @param id utilisateur
     * @return [json] user object
     */
    public function update(Request $request, $id)
    {
        //validation
        $validator = Validator::make($request->all(), [
            'firstname'=>'max:255',
            'lastname' => 'max:255',
            'gender' => 'in:feminin,masculin',
            'phone'=>'size:9',
        ]);

        $input = $request->all();



        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        /*   $existphone = User::where('phone',$input['phone'])->first();
        //var_dump($existcard->user_id); die();

        if ($existphone)
          return $this->sendError('Ce numero de téléphone est déjà utilisé',400);*/

        //recupreation de luser par son id
        $user = user::showUser($id);
        // recuperation du particulier par l'id de luser
        $particulier =Particulier::with('user')->where('user_id', $id)->first();
        //var_dump($particulier); die();
        if (!$particulier || ! $user) {
            return $this->sendError('l\'utilisateur avec id ' . $id . ' non trouvé','',400);
        }

        $updateParticulier = $particulier->fill($request->only(['firstname', 'lastname','gender','cni']))->save();
        $updateUser = $user->fill($request->only(['phone', 'address','role_id','category_id']))->save();

        if ($updateUser AND $updateParticulier )
        {
            return response()->json([
                'success' => true,
                'message' => 'votre profil utilisateurs a été modifié avec succès'
            ]);
        }
        else{
            return response()->json([
                'success' => false,
                'message' => 'Echec de modification de l\'utilisateur'
            ], 500);
        }
    }



    /**
     * renitialiser le mot de passe d'un user
     * @param card_number numero de la carte
     * @param id utilisateur
     * @return [json] user object
     */
    public function RenitializePassword(Request $request)
    {
        $input = $request->all();


        // User::updateUser($user);

        // return $input;
        $validator = Validator::make($input,   [
            'phone' => 'required',
            'cni' => 'required',
            //'password' => 'required|confirmed',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $exist = user::leftJoin('particuliers', function($join) {
            $join->on('users.id', '=', 'particuliers.user_id');
        })
            ->where('users.phone',$request->phone)
            ->where('particuliers.cni',$request->cni)
            ->first();
        //var_dump($user);die();
        $password =user::generatepassword(5);
        if (!$exist) {
            return response()->json([
                'success' => false,
                'message' => 'vos donnees sont incorrectes'
            ], 400);
        }else{
            $user = user::where([
                ['phone', $request->phone]
            ])->first();
            // var_dump($user->id) ; die();
            $card =Card::where('user_id',$user->id)->first();
            // var_dump('=£userWithCard); die();
            user::hydratation((array) $input);
            $user->update(
                ['password'=>bcrypt($password)
                    // $user->password=bcrypt($password),
                ]);

            $this->API_AVS($request->phone,'Votre mot de passe renitialisé avec succès.Le nouveau  mot de passe est ' .$password);

            return response()->json([
                'success' => true,
                'message' => 'Votre mot de passe renitialisé avec succès.Le nouveau  mot de passe est ' .$password
            ],200);
            //return $this->sendResponse(new Resource(CARD::find($card)),'Votre mot de passe renitialise avec succès.Le nouveau  mot de passe est ' .$password);
        }
    }





    function resetPassword(Request $request) {
        $data = $request->all();
        $user = Auth::guard('api')->user();
        // return $input;
        $validator = Validator::make($data, [
            'oldPassword' => 'required',
            'newPassword' => 'required',

        ]);
        if($validator->fails()){
            return $this->sendError('Erreur de validation.', $validator->errors());
        }
        //Changing the password only if is different of null
        if( isset($data['oldPassword']) && !empty($data['oldPassword']) && $data['oldPassword'] !== "" && $data['oldPassword'] !=='undefined') {
            //checking the old password first
            $check  = Auth::guard('web')->attempt([
                'phone' => $user->phone,
                'password' => $data['oldPassword']
            ]);
            if($check && isset($data['newPassword']) && !empty($data['newPassword']) && $data['newPassword'] !== "" && $data['newPassword'] !=='undefined') {
                $user->password = bcrypt($data['newPassword']);
                //$user->isFirstTime = false; //variable created by me to know if is the dummy password or generated by user.
                //$user->token()->revoke();
                // $token = $user->createToken('newToken')->accessToken;
                //Changing the type
                $user->save();
                return response()->json([
                    'success' => true,
                    'message' => 'mot de passe modifié avec succès'
                ],200);
                //return $this->sendResponse(new Resource(User::find($user)),'mot de passe  modifié avec succès!');
            }
            else {
                return response()->json([
                    'success' => false,
                    'message' => 'Ancien mot de passe incorrecte'
                ], 400);

            }
            return response()->json([
                'success' => false,
                'message' => 'Ancien mot de passe incorrecte'
            ], 400);
        }

    }public function CardWithUser($user, $user_id)
{
    if(user::where('id', $user_id)->first() == null)
    {
        return $this->sendError('l\'utilisateur n\'existe pas');
    }
    $carduser=  $user->with('particulier')->with('cards')->where('id', $user_id)->first();
    return $carduser;

}


    public function userCard(User $user, $user_id){
        return $this->CardWithUser($user, $user_id);
    }

    public function checkout ($id){
        // generation de mot de passe
        $non_crypte_mdp = $this->genererChaineAleatoire(5);
        $crypte_mdp = bcrypt($non_crypte_mdp);
        // recuperer user de la table userAutoRegistration  verifier que l'utilisateur n'existe pas en BD User
        $user_auto_register = UserAutoRegistration::where('id',$id)->count();
        //  si lid de l'utilisateur n'existe pas dans la table UserAutoRegistration on retourne la reponse
        if($user_auto_register===0){
            return $this->sendError('ActivationError.', 'Lutilisateur que vous souhaitez activer nexiste pas dans la table d auto enregistrement');
        }
        // si il existe pas dans la table User et quil existe dans la table  UserAutoRegistration le transfere dans la table User
        if($user_auto_register===1){
            $user_for_update =  UserAutoRegistration::where('id',$id)->first();
            $numero_auto_register = $user_for_update->phone;
            //  on verifi que lutilsateur nexiste pas encore dans la table User
            $user_exist = User::where('phone',$numero_auto_register)->count();
            if($user_exist === 0){
                // l'utilisateur n'existe pas encore on le cree
                // On cree son compte
                $compte = Compte::createAccount(new Compte(), $user_for_update->id);
                // on cree l'utlisateur
                $user = User::create([
                    'address'=> $user_for_update->address,
                    'category_id'=> $user_for_update->category_id,
                    'password'=>$crypte_mdp,
                    'parent_id'=>$user_for_update->parent_id,
                    'created_by'=>$user_for_update->created_by,
                    'role_id'=>$user_for_update->role_id,
                    'state'=>'activer',
                    'cni'=> $user_for_update->cni,
                    'phone'=> $user_for_update->phone,
                    'compte_id' => $compte_id,
                ]);

                // on cree le particulier correspondant

                $particulier = Particulier::create([
                    'lastname'=>$user_for_update->lastname,
                    'firstname' =>$user_for_update->firstname,
                    'cni'=> $user_for_update->cni,
                    'gender' => $user_for_update->gender,
                    'user_id'=>$user_for_update->id,
                ]);

                // on cree sa carte

                $card = Card::where('user_id',NULL)->first()
                    ->update(['user_id' =>$id_user]);


                $this->API_AVS($user_for_update->phone,' Mr/Ms '.$user_for_update->lastname.' votre compte vient detre activé avec succes loggin :'.$user_for_update->phone.' password '.$non_crypte_mdp);
                $success['token'] =  $user->createToken('MyApp')->accessToken;
                $success['token'] =  $user->createToken('MyApp')->refreshToken;
                $success['lastname'] =  $user->lastname;
                return $this->sendResponse($success, 'User activated successfully.');
            }
            // l'utilisateur existe on envoi le message
            return $this->sendError('ActivationError.', 'l\'utilisateur existe Deja ');
        }
    }

}

