<?php

namespace App\Http\Controllers\Api;

use App\Models\Card;
use App\Models\Compte;
use App\Http\Controllers\Controller;
use App\Models\Particulier;
use App\Models\User;
use App\Models\UserAutoRegistration;
use Illuminate\Http\Request;

class AutoRegistrationController extends BaseController
{
    public function register(Request $request)
    {
        $input = $request->all();

        $rules = [
            'username' => 'required,unique:Userlogin,username',
            'password' => 'required',
            'email'    => 'required,unique:Userlogin,email'
        ];

        /*$messages = [
          'required'  => 'The :attribute field is required.',
          'unique'    => ':attribute is already used'
        ];*/
        /*  $rules = [
            'firstname' => 'required',
            'lastname' => 'required|email',
            'gender' => 'required|max:250',
        ];

        $customMessages = [
            'firstname.required' => 'The :attribute field is requiredddddddddddd.'
        ];

        $this->validate($request, $rules, $customMessages);*/


        $rules = array(
            'firstname' => 'max:255',
            'lastname' => 'required|max:255',
            'gender' => 'required|in:MASCULIN,FEMININ',
            'address'=>'required',
            'category_id'=>'required',
            //'created_by'=>'required',
            'role_id'=>'required',
            'cni'=>'required',
            'phone'=>'required|unique:user_registration|size:9',
            'nom_img_recto' =>'required',
        );
        $messages = array(

            'phone.unique'=>'Ce numero de telephone existe deja dans notre base',
        );
        $validator = Validator::make( $request->all(), $rules, $messages );

        if ( $validator->fails() )
        {
            return [
                'success' => false,
                'message' => $validator->errors()->first()
            ];
        }



        /*  $input = $request->all()
         $valid = Validator::make($request->all(), [
             'firstname'=>'max:255',
             'lastname' => 'required|max:255',
             'gender' => 'required',
             'address'=>'required',
             'category_id'=>'required',
             //'created_by'=>'required',
             'role_id'=>'required',
             'cni'=>'required',
             'phone'=>'required|unique:users|size:9',
             'nom_img_recto' =>'required',
             ],
              [
               'phone' => 'Phone already exists!', // <---- pass a message for your custom validator
         ] );



         $existphone = User::where('phone',$input['phone'])->first();
         //var_dump($existcard->user_id); die();

         if ($existphone)
           return $this->sendError('Ce numero de telephone est deja utilise',400,400);*/




// recuperation des champs des images pour le recto et pour le verso
        if($request->piece_recto) {

            $imageData_recto = $input['piece_recto'];
            $imageName_recto= $input['nom_img_recto'];
            file_put_contents(public_path("images/$imageName_recto"), base64_decode($imageData_recto));
        }
        else if ($input['piece_verso']){

            $imageData_verso =  $input['piece_verso'];
            $imageName_verso =$input['nom_img_verso'];
//decodage de la base 64 des differentes images
            file_put_contents(public_path("images/$imageName_verso"), base64_decode($imageData_verso));

        }else
        {
            return $this->sendError('Erreur Chargement Image.', 'Veuillez renseigner le recto svp ');
        }

        $user = UserAutoRegistration::create($input);
        $success['lastname'] =  $user->lastname;
        return $this->sendResponse($success, 'Vous été enregistré avec success.');
    }


    public function checkout ($id){
        // generation de mot de passe
        $non_crypte_mdp = User::generatepassword(5);
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
                $compte = Compte::createAccount(new Compte());
                // on cree l'utlisateur
                $user =  User::create([
                    'address'=> $user_for_update->address,
                    'category_id'=> $user_for_update->category_id,
                    'password'=>$crypte_mdp,
                    'parent_id'=>$user_for_update->parent_id,
                    'created_by'=>1,
                    'role_id'=>$user_for_update->role_id,
                    'state'=>'activer',
                    'cni'=> $user_for_update->cni,
                    'phone'=> $user_for_update->phone,
                    'compte_id' => $compte,
                ]);

                // on cree le particulier correspondant

                $particulier =  Particulier::create([
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

