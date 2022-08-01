<?php

namespace App\Http\Controllers\Api;

use App\Models\Card;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RegisterLoginController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // ici je dois encore ajouter des champs pour la validation
        $validator = Validator::make($request->all(), [
            'firstname'=>'max:255',
            'lastname' => 'required|max:255',
            'gender' => 'required|in:male,female',
            'address'=>'required',
            'category_id'=>'required',
            'created_by'=>'required',
            'role_id'=>'required',
            'cni'=>'required',
            'phone'=>'required|size:9',
            'card_number'=>'required|size:8',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $card_number = $request->card_number;

        if( $card_number)
        {
            $existcard = Card::where('code_number', $card_number)->first();
            if($existcard->user_id)

            {
                return $this->sendError('Cette carte est deja utilisee' ,400);
            }


            if ($this->checkCard($card_number) === 0 || $this->checkCard($card_number) === null) {
                return $this->sendError('Validation Error.', 'Aucunes Cartes Trouve');
            }

            $input = $request->all();
            $mdp_non_crypte = $this->genererChaineAleatoire(5);
            // echo $mdp_non_crypte;
            $input['password'] =  bcrypt($mdp_non_crypte);
            $user = User::create($input);
// on recupere lidee de l'utilisation quon enregistre
            $user_infos=User::where('phone',$request->phone)->first();
            $id_user = $user_infos->id;

// on recupere id de la carte courante
            $card_infos = Card::where('code_number',$card_number)->first();
            $id_card = $card_infos->id ;
            // ici on met a jour l'id de l'utilisateur dans la table de sa carte
            $card = Card::where('id',$id_card)->update(['user_id' =>$id_user]);
            $user->save();
            $success['token'] =  $user->createToken('MyApp')->accessToken;
            $success['lastname'] =  $user->lastname;

            $this->API_AVS($request->phone,'Chers Mr/Mss '.''.$request->lastname .''.' vous avez ete enregistre avec succes votre login est '. $request->phone .' mot de passe est '.$mdp_non_crypte.' et votre numéro de compte est '. $request->card_number);

            return $this->sendResponse($success, 'User register successfully.');
        }
    }
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {

        // if (Auth::attempt(array('phone' => $request->phone, 'password' => $request->password))){
        if (Auth::attempt(['phone' => $request->phone, 'password' => $request->password,'state'=>'actif'])){
            $user = Auth::user();
            // $success['token'] =  $user->createToken('MyApp')->accessToken;
            $success['lastname'] =  $user->lastname;

            return $this->sendResponse($success, 'User login successfully.');
        }
        else{

            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised '.$request->phone. "  pwd "
                .$request->password]);
        }
    }
}
// php artisan passport:client --personal pour creer mon client quand je vais pull du bureau


// http://api.vassarl.com:9501/api?action=sendmessage&username=SMOPAYE@2019&password=SMOPAYE&recipient=237697846892&m
// essagetype=SMS:TEXT&messagedata=Hello+World
