<?php

namespace App\Http\Controllers\Api;

use App\Models\Card;
use App\Models\Compte;
use App\Models\Compte_subscription;
use App\Models\Enterprise;
use App\Http\Controllers\Controller;
use App\Models\Particulier;
use App\Models\Subscription as subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource as Resource;
use Illuminate\Support\Facades\Auth;

class SubUserController extends BaseController
{
    /**
     * Creation des utilisateur e-zpass
     *
     *
     */
    public function create(Request $request)
    {
        $input = $request->all();
        $idConnectedUser = Auth::guard('api')->user()->id;
        $password = User::generatepassword(5);
        $particulierConnected = Particulier::with('user')->where('user_id', $idConnectedUser)->first();
        $EnterpriseConnected = Enterprise::with('user')->where('user_id', $idConnectedUser)->first();
        $connectedUserAccount=Auth::guard('api')->user()->compte_id;
        $accountConnected = Compte::with('user')->where('id', $connectedUserAccount)->first();








        if ($particulierConnected) {
            $valid = $this->validation($input,
                [
                    'firstname' => 'filled|max:255',
                    'lastname' => 'required|max:255',
                    'gender' => 'required|in:masculin,feminin',
                    'address' => 'required',
                    'category_id' => 'required',
                    'cni' => 'filled|max:255',
                    'role_id' => 'required',
                    'phone' => 'required|size:9',
                    'card_number' => '',
                ]);
            if ($valid->fails())
                return $this->sendError('Erreur de validation.', $valid->errors(),400);

            $existcard = Card::where('code_number',$request->input('card_number'))->first();
            $existphone = User::where('phone',$input['phone'])->first();
            //var_dump($existcard->user_id); die();

            if ($existphone)
                return $this->sendError('Ce numero de telephone est deja utilise',400);

            if (!$existcard){

                return $this->sendError('Cette carte n\'existe pas' ,400);
            }
            elseif($existcard->user_id)

            {
                return $this->sendError('Cette carte est deja utilisee' ,400);
            }

            // creation de l'utlisateur
            $user = new User();
            $user->phone = $request->input('phone');
            $user->address = $request->input('address');
            $user->password = bcrypt($password);
            //$user->cni = $request->input('cni');
            $user->category_id = $request->input('category_id');
            $user->role_id = $request->input('role_id');
            $user->parent_id = Auth::guard('api')->user()->id;
            $user->created_by = Auth::guard('api')->user()->id;
            $user->compte_id = $connectedUserAccount;
            $user->save();

            $token = $user->createToken('newToken')->accessToken;
            $particulier = new Particulier();
            $particulier->firstname = $request->input('firstname');
            $particulier->lastname = $request->input('lastname');
            $particulier->gender = $request->input('gender');
            $particulier->cni = $request->input('cni');
            $particulier->user_id = $user->id;
            $particulier->save();
        } elseif ($EnterpriseConnected) {
            $valid = $this->validation($input,
                [
                    'raison_social' => 'required|max:255',
                    'phone' => 'required',
                    'address' => 'required',
                    'category_id' => 'required',
                    'cni' => 'filled|max:255',
                    'role_id' => 'required',
                    'phone' => 'required|size:9',
                    'card_number' => '',
                ]);
            if ($valid->fails())
                return $this->sendError('Erreur de validation.', $valid->errors(),400);
            // creation de l'entreprise

            $compte = New Compte();
            $account_compt= User::generatepassword(9);
            $compte->account_number = $account_compt;
            $compte->principal_account_id = Auth::guard('api')->user()->compte_id;
            $compte->save();

            $user = new User();
            $user->phone = $request->input('phone');
            $user->address = $request->input('address');
            $user->password = bcrypt($password);
            $user->category_id = $request->input('category_id');
            $user->role_id = $request->input('role_id');
            $user->parent_id = Auth::guard('api')->user()->id;
            $user->created_by = Auth::guard('api')->user()->id;
            $user->compte_id = $compte->id;
            $user->save();

            $token = $user->createToken('newToken')->accessToken;
            $enterprise = new Enterprise();
            $enterprise->raison_social = $request->input('raison_social');
            $enterprise->principal_id = Auth::guard('api')->user()->id;
            $enterprise->user_id = $user->id;
            $enterprise->save();

            Compte_subscription::create(
                ['compte_id'=>$user->compte_id,
                    'subscription_id'=> subscription::where('name','service')->first()->id,
                    'starting_date'=>date("Y-m-d H:i:s"),
                    'subscriptionCharge'=>'0',
                    'transaction_number'=>'0',
                    'end_date'=>'0000-00-00 00:00:00']);
        }
        //recuperation de l'user connecte
        $userSaved = User::where('phone', $request->phone)->first();
        $id_user = $userSaved->id;

        //creation de la caret de l'user si elle en a une
        if ($request->input('card_number') != null) {
            $card_number = $request->input('card_number');
            $card_infos = Card::where('code_number', $card_number)->first();
            $id_card = $card_infos->id;
            // ici on met a jour l'id de l'utilisateur dans la table de sa carte
            $card = Card::where('id', $id_card)->update(['user_id' => $id_user]);
            if ($particulierConnected) {
                $msg = "la carte rattachée à votre compte est " . $card_number;
                $message = 'Mr/Mme ' . '' . $particulier::getLastname() . ' ' . $request->input('lastname'). ' vous avez été enregistré(e) avec succès votre login est ' .  $request->input('phone') . ' mot de passe est ' . $user::getPassword() . ' et votre numéro de compte est ' . $accountConnected->account_number . ' ' . $msg;
                $this->API_AVS($user::getPhone(), $message);
                return $this->sendResponse(new Resource([]), $message);
            } elseif ($EnterpriseConnected) {
                $msg = "la carte rattachée à votre compte est " .$card_number;
                $message = 'le compte dont la raison sociale fait l\'objet ' . $request->input('raison_social') . ' a été enregistrer avec succés. votre login est ' .  $request->input('phone'). ', mot de passe est ' . $user::getPassword() . ' et votre numéro de compte est ' .$account_compt. ' ' . $msg;;
                $this->API_AVS($user::getPhone(), $message);
                $this->API_AVS(user::getPhone(), $message);
                return $this->sendResponse(new Resource([]), $message);
            }
        } elseif ($particulierConnected) {

            $message = 'Mr/Mme ' . '' . $particulier::getLastname() . ' ' . $request->input('lastname'). ' vous avez été enregistré(e) avec succès votre login est ' .  $request->input('phone'). ' mot de passe est ' . $user::getPassword() . ' et votre numéro de compte est ' . $accountConnected->account_number;
            $this->API_AVS($user::getPhone(), $message);
            return $this->sendResponse(new Resource([]), $message);
        } else {

            $message = 'le compte dont la raison sociale fait l\'objet ' . $request->input('raison_social') . ' a été enregistrer avec succés. votre login est ' .  $request->input('phone'). ', mot de passe est ' . $user::getPassword() . ' et votre numéro de compte est ' . $account_compt;
            $this->API_AVS($user::getPhone(), $message);
            $this->API_AVS(user::getPhone(), $message);
            return $this->sendResponse(new Resource([]), $message);
        }

        $token = $user->createToken('newToken')->accessToken;


    }
}

