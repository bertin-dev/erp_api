<?php

namespace App\Http\Controllers\Api;

use App\Models\Card;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GestionCompteController extends BaseResponseController
{
    /**
     * cette fonction permet dafficher les differentes informations du profile d'un utilisateur
     *
     */
    public function profile ($code_number){
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

        return   $this->sendResponse(' profile',' Numerode compte : '.$code_number.  '; tye de carte : '.$cardtype. '; date de debut: ' .$cardStarting_date.'; date de fin: '.$cardEnd_date.'; subscription: '.$subscription);
    }

    /**
     * cette fonction prends en parametre le type de solde a consulter et le numero de la carte de l'utilisateur
     * elle renvoi le solde de  lutilisateur en franc cfa ;
     */

    public function getUnity(Request $request ,$code_number){

        $input = $request->all();
        $validator=Validator::make($input, [ 'type'=> 'required|in:unity,deposit',
        ]);

        if($validator->fails()) {
            return $this->sendError('Erreur de validation.', $validator->errors());
        }
        $card = Card::where('code_number',$code_number)->first();
        $type_unity = $request->type ==='unity' ? $card->unity : $card->deposit;
        return $this->sendResponse('Credit:', 'Votre Credit ' .$request->type.' est de '.$type_unity. ' franc cfa !!!');
    }
    /**
     * cette fonction permet de modifier le numero de telephone et le mot de passe d'un utilisateur
     *
     */
    public function update (Request $request,User $user,$phone_number){

        $input = $request->all();
        $validator=Validator::make($input, [
            'phone'=> 'numeric|required',
            'password'=> 'size:5|required',
            'ancien_password'=> 'required|size:5'

        ]);
        if($validator->fails()) {
            return $this->sendError('Erreur de validation.', $validator->errors());
        }
        $user = User::where('phone',$phone_number)->first();

        if($user->count() > 0){

            if (Auth::attempt([ 'password' => $request->ancien_password,'phone' => $phone_number])){
                $user->update([
                    $user->phone = $request->phone,
                    $user->password = bcrypt($request->password),

                ]);
                if($user->save()){
                    return $this->sendResponse('Succes','lutilisateur a ete modifie avec succes');
                }else{
                    return $this->sendResponse('Erreur:','une erreur est survenue impossible de modifier ');
                }
            } else{
                return $this->sendResponse('Erreur:','l\'ancien mots de passe ne concorde pas reesayez svp' );
            }

        }else if($user->count() === 0){
            return $this->sendResponse('Erreur:','l\'utilisateur n\'existe pas');
        }

    }
}


