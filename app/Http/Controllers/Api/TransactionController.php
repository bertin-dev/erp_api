<?php

namespace App\Http\Controllers\Api;

use App\Models\Card as card;
use App\Models\Compte as compte;
use App\Models\Compte_subscription as CompteSubscription;
use App\Models\Device as device;
use App\Http\Controllers\Controller;
use App\Http\Resources\Index as Resource;
use App\Models\Tarif as tarif;
use App\Models\Transaction as transaction;
use App\Models\User as user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use DateTime;

class TransactionController extends BaseController{
    /**

     * cette fonction permet de renvoyer toues les transaction

     * avec succes

     * Display a listing of the resource.

     *

     * @return \Illuminate\Http\Response

     */

    public function index(){
        $transaction = transaction::getAllTransaction();
        return $this->sendResponse(Resource::collection($transaction), 'les transactions ont été renvoyer avec succés.');
    }

    public function daterange(Request $request){

        if($request()->ajax()){
            if(!empty($request->from_date)){
                $data = DB::table('transaction')
                    ->whereBetween('created_at', array($request->from_date, $request->to_date))
                    ->get();
            }
        }else{
            $data = DB::table('transaction')->get();
        }
        return json_encode($data);
    }



    public function showAllTransaction($card_id){
        $transaction = [];
        $nbreTransfertDEPOTUNITE = 0;
        $amountTransfertDEPOTUNITE = 0;
        $nbreTransfertUNITEDEPOT = 0;
        $amountTransfertUNITEDEPOT = 0;
        $nbreTransfertCarte = 0;
        $nbreTransfertCompte = 0;
        $amountTransfertCompte = 0;
        $amountTransfertCarte = 0;
        $nbreQrcode = 0;
        $amountQrcode = 0;
        $nbreDebit = 0;
        $amountDebit = 0;
        $nbreRechargeOrange = 0;
        $amountRechargeOrange = 0;
        $nbreRechargeMtn = 0;
        $amountRechargeMtn = 0;
        $nbreRetraitMtn = 0;
        $amountRetraitMtn = 0;
        $nbreRetraitOrange = 0;
        $amountRetraitOrange = 0;
        $nbreRetraitCarteMtn = 0;
        $amountRetraitCarteMtn = 0;
        $nbreRetraitCarteOrange = 0;
        $amountRetraitCarteOrange = 0;
        $user = Auth::guard('api')->user();
        $res= transaction::where('card_id_sender',$card_id)->orWhere('card_id_receiver',$card_id)->get();
        for ($i=0; $i < count($res) ; $i++) {
            switch ($res[$i]['transaction_type']) {
                case 'TRANSFERT_CARTE_A_CARTE':
                    $nbreTransfertCarte = $nbreTransfertCarte + 1;
                    $amountTransfertCarte = $amountTransfertCarte + $res[$i]['amount'];
                    break;
                case 'TRANSFERT_COMPTE_A_COMPTE':
                    $nbreTransfertCompte = $nbreTransfertCompte + 1;
                    $amountTransfertCompte +=$res[$i]['amount'];
                    break;
                case 'PAYEMENT_VIA_QRCODE':
                    $nbreQrcode = $nbreQrcode + 1;
                    $amountQrcode = $amountQrcode + $res[$i]['amount'];
                    break;
                case 'DEBIT_CARTE':
                    $nbreDebit = $nbreDebit + 1;
                    $amountDebit = $amountDebit + $res[$i]['amount'];
                    break;
                case 'TRANSFERT_DEPOT_UNITE':
                    $nbreTransfertUNITEDEPOT = $nbreTransfertUNITEDEPOT + 1;
                    $amountTransfertUNITEDEPOT = $amountTransfertUNITEDEPOT + $res[$i]['amount'];
                    break;

                case 'TRANSFERT_UNITE_DEPOT':
                    $nbreTransfertDEPOTUNITE = $nbreTransfertDEPOTUNITE + 1;
                    $amountTransfertDEPOTUNITE = $amountTransfertDEPOTUNITE + $res[$i]['amount'];
                    break;

                case 'RECHARGE_COMPTE_VIA_MONETBIL':
                    switch ($res[$i]['operator']) {
                        case 'MTN':
                            $nbreRechargeMtn += 1;
                            $amountRechargeMtn += $res[$i]['amount'];
                            break;

                        case 'Orange':
                            $nbreRechargeOrange += 1;
                            $amountRechargeOrange += $res[$i]['amount'];
                            break;
                    }
                    break;

                case 'RETRAIT_COMPTE_VIA_MONETBIL':
                    switch ($res[$i]['operator']) {
                        case 'CM_MTNMOBILEMONEY':
                            $nbreRetraitMtn += 1;
                            $amountRetraitMtn += $res[$i]['amount'];
                            break;

                        case 'CM_ORANGEMONEY':
                            $nbreRetraitOrange += 1;
                            $amountRetraitOrange += $res[$i]['amount'];
                            break;
                    }
                    break;

                case 'RETRAIT_CARTE_VIA_MONETBIL':
                    switch ($res[$i]['operator']) {
                        case 'CM_MTNMOBILEMONEY':
                            $nbreRetraitCarteMtn += 1;
                            $amountRetraitCarteMtn += $res[$i]['amount'];
                            break;

                        case 'CM_ORANGEMONEY':
                            $nbreRetraitCarteOrange += 1;
                            $amountRetraitCarteOrange += $res[$i]['amount'];
                            break;
                    }
                    break;

                case 'DEBIT_CARTE':
                    $nbreDebit = $nbreDebit + 1;
                    $amountDebit = $amountDebit + $res[$i]['amount'];
                    break;
            }
            if($res[$i]['card_id_sender'] || $res[$i]['card_id_receiver'] || $res[$i]['account_id_receiver'] || $res[$i]['account_id_sender']){

                $transaction[$i]['Date'] = date('d/m/Y H:i:s', strtotime($res[$i]['starting_date']));

                $transaction[$i]['id'] = $res[$i]['transaction_number'];

                $transaction[$i]['Status'] = $res[$i]['state'];

                $transaction[$i]['Operation'] = $res[$i]['transaction_type'];

                $transaction[$i]['Montant'] = $res[$i]['amount'];

                $transaction[$i]['Frais'] = $res[$i]['servicecharge'];



                if($res[$i]['card_id_sender']){

                    $card = $res[$i]['card_id_sender'];

                    $userCardSender =  card::where('id', $card)->with(['user'=>function($query){$query->with(['particulier','enterprise']);}])->first()->user;;
                    if(array_key_exists(0,$userCardSender->toArray()['particulier'])){
                        $transaction[$i]['user']['emetteur'] = $userCardSender->toArray()['particulier'][0];
                    }
                    $transaction[$i]['user']['emetteur']['type'] = 'emetteur';

                    $transaction[$i]['user']['emetteur']['phone'] = $userCardSender->toArray()['phone'];

                    if(!empty($userCardSender->toArray()['cards']))

                        $transaction[$i]['user']['emetteur']['entite'] = 'carte n°: '.$userCardSender->toArray()['cards'][0]['code_number'];

                }



                if($res[$i]['card_id_receiver']){

                    $card = $res[$i]['card_id_receiver'];

                    $userCardReceiver = card::where('id', $card)->with(['user'=>function($query){$query->with(['particulier','enterprise']);}])->first()->user;
                    if(array_key_exists(0,$userCardReceiver->toArray()['particulier'])){
                        $transaction[$i]['user']['destinataire'] = $userCardReceiver->toArray()['particulier'][0];
                    }
                    $transaction[$i]['user']['destinataire']['type'] = 'destinataire';

                    $transaction[$i]['user']['emetteur']['phone'] = $userCardReceiver->toArray()['phone'];

                    if(!empty($userCardReceiver->toArray()['cards']))

                        $transaction[$i]['user']['destinataire']['entite'] = 'carte n°: '.$userCardReceiver->toArray()['cards'][0]['code_number'];

                }



                if($res[$i]['account_id_receiver']){

                    $card = $res[$i]['account_id_receiver'];

                    $userCardReceiver = user::where('compte_id', $card)->with(['particulier','enterprise'])->first();

                    $transaction[$i]['user']['destinataire'] = !empty($userCardReceiver->toArray()['particulier']) ? $userCardReceiver->toArray()['particulier'][0]: $userCardReceiver->toArray()['enterprise'][0];
                    $transaction[$i]['user']['emetteur']['phone'] = $userCardReceiver->toArray()['phone'];

                }





                if($res[$i]['account_id_sender']){

                    $card = $res[$i]['account_id_sender'];

                    $userCardReceiver = user::where('compte_id', $card)->with(['particulier','enterprise'])->first();

                    $transaction[$i]['user']['emetteur'] = !empty($userCardReceiver->toArray()['particulier']) ? $userCardReceiver->toArray()['particulier'][0]: $userCardReceiver->toArray()['enterprise'][0];

                    $transaction[$i]['user']['emetteur']['type'] = $userCardReceiver;

                    $transaction[$i]['user']['emetteur']['phone'] = $userCardReceiver->toArray()['phone'];

                }

            }
            switch ($res[$i]) {

                case $res[$i]['account_id_sender'] == $res[$i]['account_id_receiver']:

                    $transaction[$i]['statusColor'] = 'warning';

                    $transaction[$i]['statusMsg'] = 'self';

                    break;

                case $res[$i]['account_id_sender'] == $user->compte_id && $res[$i]['account_id_sender'] != $res[$i]['account_id_receiver']:

                    $transaction[$i]['statusColor'] = 'danger';

                    $transaction[$i]['statusMsg'] = 'sortant';

                    break;

                case $res[$i]['account_id_receiver'] == $user->compte_id && $res[$i]['account_id_sender'] != $res[$i]['account_id_receiver']:

                    $transaction[$i]['statusColor'] = 'success';

                    $transaction[$i]['statusMsg'] = 'entrant';

                    break;

            }

        }
        $data['nbreTransfert'] = $nbreTransfertCarte + $nbreTransfertCompte; //$nbreTransfertDEPOTUNITE + $nbreTransfertUNITEDEPOT;
        $data['nbreQrcode'] = $nbreQrcode;
        $data['nbreDebit'] = $nbreDebit;
        $data['transaction'] = $transaction;
        return $this->sendResponse(new Resource($data), 'les transactions ont été renvoyer avec succés.');
    }



    public function showAllTransactionAccount($account_id){
        $transaction = transaction::where('account_id_sender',$account_id)->orWhere('account_id_receiver',$account_id)->orderBy('created_at','desc')->get();
        return $this->sendResponse(Resource::collection($transaction),'toutes les transaction du compte '.$account_id);
    }



    public function filterCompte(Request $request){

        $global = [];

        $transactions = transaction::all();

        foreach ($transactions as $transaction) {

            $transaction->user = null;

            $transaction->compte = compte::where('id', $transaction->account_id_sender)->orWhere('id', $transaction->account_id_receiver)->first();

            if(!empty($transaction->compte)){

                $transaction->user = user::where('compte_id', $transaction->compte->id)->first();

            }

            $global[] = $transaction;

        }



        switch ($request) {

            case !is_null($request->starting_date) && !is_null($request->end_date) && !is_null($request->phone) && !is_null($request->type) && !is_null($request->compte):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(($request->starting_date >= date('Y-m-d', strtotime($global[$i]->starting_date))) && (date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->transaction_type == $request->type) && ($global[$i]->user->phone == $request->phone) && ($global[$i]->compte->account_number == $request->compte)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;

            case !is_null($request->end_date) && !is_null($request->phone) && !is_null($request->type) && !is_null($request->compte):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if((date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->compte->account_number == $request->compte) && ($global[$i]->user->phone == $request->phone) && ($global[$i]->transaction_type == $request->type)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->starting_date) && !is_null($request->end_date) && !is_null($request->compte) && !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(($request->starting_date >= date('Y-m-d', strtotime($global[$i]->starting_date))) && (date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->compte->account_number == $request->compte) && ($global[$i]->transaction_type == $request->type)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->starting_date) && !is_null($request->end_date) && !is_null($request->compte) && !is_null($request->phone):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(($request->starting_date >= date('Y-m-d', strtotime($global[$i]->starting_date))) && (date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->compte->account_number == $request->compte) && ($global[$i]->user->phone == $request->phone)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->starting_date) && !is_null($request->end_date) && !is_null($request->phone) && !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(($request->starting_date >= date('Y-m-d', strtotime($global[$i]->starting_date))) && (date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->transaction_type == $request->type) && ($global[$i]->user->phone == $request->phone)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;

            case !is_null($request->end_date) && !is_null($request->compte) && !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(!is_null($global[$i]->compte) && (date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->compte->account_number == $request->compte) && ($global[$i]->transaction_type == $request->type)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->starting_date) && !is_null($request->compte) && !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(!is_null($global[$i]->compte) && (date('Y-m-d', strtotime($global[$i]->starting_date)) == $request->starting_date) && ($global[$i]->compte->account_number == $request->compte) && ($global[$i]->transaction_type == $request->type)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->end_date) && !is_null($request->compte) && !is_null($request->phone):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if((date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->compte->account_number == $request->compte) && ($global[$i]->user->phone == $request->phone)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->end_date) && !is_null($request->phone) && !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if((date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->user->phone == $request->phone) && ($global[$i]->user->phone == $request->phone)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->phone) && !is_null($request->type) && !is_null($request->compte):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(($global[$i]->compte->account_number == $request->compte) && ($global[$i]->user->phone == $request->phone) && ($global[$i]->transaction_type == $request->type)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->starting_date) && !is_null($request->end_date) && !is_null($request->compte):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(($request->starting_date >= date('Y-m-d', strtotime($global[$i]->starting_date))) && (date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->compte->account_number == $request->compte)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->starting_date) && !is_null($request->end_date) && !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(($request->starting_date <= date('Y-m-d', strtotime($global[$i]->starting_date))) && (date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->transaction_type == $request->type)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;



                break;



            case !is_null($request->starting_date) && !is_null($request->end_date) && !is_null($request->phone):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(($request->starting_date >= date('Y-m-d', strtotime($global[$i]->starting_date))) && (date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->user->phone == $request->phone)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->starting_date) && !is_null($request->compte):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if((date('Y-m-d', strtotime($global[$i]->starting_date)) == $request->starting_date) && ($global[$i]->compte->account_number == $request->compte)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->end_date) && is_null($request->starting_date):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;

            case !is_null($request->compte) && !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(!is_null($global[$i]->compte) && ($global[$i]->compte->account_number == $request->compte) && ($global[$i]->transaction_type == $request->type)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;



                break;



            case !is_null($request->compte) && !is_null($request->phone):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(($global[$i]->compte->account_number == $request->compte) && ($global[$i]->user->phone == $request->phone)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->phone) && !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(!is_null($global[$i]->user) && ($global[$i]->user->phone == $request->phone) && ($global[$i]->transaction_type == $request->type)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->starting_date) && !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if((date('Y-m-d', strtotime($global[$i]->starting_date)) == $request->starting_date) && ($global[$i]->transaction_type == $request->type)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->starting_date) && !is_null($request->end_date):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(($request->starting_date <= date('Y-m-d', strtotime($global[$i]->starting_date))) && (date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->end_date) && !is_null($request->compte):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(!is_null($global[$i]->compte) && (date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->compte->account_number == $request->compte)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->end_date) && !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if((date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->transaction_type == $request->type)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->end_date) && !is_null($request->phone):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if((date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->user->phone == $request->phone)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->starting_date):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(date('Y-m-d', strtotime($global[$i]->starting_date)) == $request->starting_date){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->compte):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(!is_null($global[$i]->compte) && ($global[$i]->compte->account_number == $request->compte)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if($global[$i]->transaction_type == $request->type){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->phone):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(!is_null($global[$i]->user) && ($global[$i]->user->phone == $request->phone)){

                        $globalFil[] = $global[$i]->user->phone;

                    }

                }

                $global = $globalFil;

                break;



            default:

                $global = [];

                break;

        }



        return $this->sendResponse(new Resource($global), 'la transaction numero ');

    }



    public function filterCarte(Request $request){

        $global = [];

        $transactions = transaction::orderBy('created_at','desc')->get();
        foreach ($transactions as $transaction) {
            $transaction->carte = card::where('id', $transaction->card_id_sender)->orWhere('id', $transaction->card_id_receiver)->first();
            if($transaction->card_id_sender){

                $card = $transaction->card_id_sender;

                $userCardSender =  card::where('id', $card)->with(['user'=>function($query){$query->with(['particulier','enterprise']);}])->first()->user;

                if(array_key_exists(0,$userCardSender->toArray()['particulier'])){
                    $transaction->emetteur = $userCardSender->toArray()['particulier'][0];
                }
            }

            if($transaction->card_id_receiver){

                $card = $transaction->card_id_receiver;

                $userCardReceiver = card::where('id', $card)->with(['user'=>function($query){$query->with(['particulier','enterprise']);}])->first()->user;

                if(array_key_exists(0,$userCardReceiver->toArray()['particulier'])){
                    $transaction->destinataire = $userCardReceiver->toArray()['particulier'][0];
                }
            }

            if($transaction->account_id_receiver){

                $card = $transaction->account_id_receiver;

                $userCardReceiver = user::where('compte_id', $card)->with(['particulier','enterprise'])->first();

                $transaction->destinataire = !empty($userCardReceiver->toArray()['particulier']) ? $userCardReceiver->toArray()['particulier'][0]: $userCardReceiver->toArray()['enterprise'][0];
            }

            if($transaction->account_id_sender){
                $card = $transaction->account_id_sender;
                $userCardReceiver = user::where('compte_id', $card)->with(['particulier','enterprise'])->first();
                $transaction->emetteur = !empty($userCardReceiver->toArray()['particulier']) ? $userCardReceiver->toArray()['particulier'][0]: $userCardReceiver->toArray()['enterprise'][0];
            }

            $global[] = $transaction;

        }



        switch ($request) {

            case !is_null($request->starting_date) && !is_null($request->end_date) && !is_null($request->phone) && !is_null($request->type) && !is_null($request->carte):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(($request->starting_date >= date('Y-m-d', strtotime($global[$i]->starting_date))) && (date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->transaction_type == $request->type) && ($global[$i]->user->phone == $request->phone) && ($global[$i]->carte->code_number == $request->carte)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;

            case !is_null($request->end_date) && !is_null($request->phone) && !is_null($request->type) && !is_null($request->carte):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if((date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->carte->code_number == $request->carte) && ($global[$i]->user->phone == $request->phone) && ($global[$i]->transaction_type == $request->type)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->starting_date) && !is_null($request->end_date) && !is_null($request->carte) && !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(($request->starting_date >= date('Y-m-d', strtotime($global[$i]->starting_date))) && (date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->carte->code_number == $request->carte) && ($global[$i]->transaction_type == $request->type)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->starting_date) && !is_null($request->end_date) && !is_null($request->carte) && !is_null($request->phone):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(($request->starting_date >= date('Y-m-d', strtotime($global[$i]->starting_date))) && (date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->carte->code_number == $request->carte) && ($global[$i]->user->phone == $request->phone)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->starting_date) && !is_null($request->end_date) && !is_null($request->phone) && !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(($request->starting_date >= date('Y-m-d', strtotime($global[$i]->starting_date))) && (date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->transaction_type == $request->type) && ($global[$i]->user->phone == $request->phone)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;

            case !is_null($request->end_date) && !is_null($request->carte) && !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(!is_null($global[$i]->carte) && (date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->carte->code_number == $request->carte) && ($global[$i]->transaction_type == $request->type)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->starting_date) && !is_null($request->carte) && !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(!is_null($global[$i]->carte) && (date('Y-m-d', strtotime($global[$i]->starting_date)) == $request->starting_date) && ($global[$i]->carte->code_number == $request->carte) && ($global[$i]->transaction_type == $request->type)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->end_date) && !is_null($request->carte) && !is_null($request->phone):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if((date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->carte->code_number == $request->carte) && ($global[$i]->user->phone == $request->phone)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->end_date) && !is_null($request->phone) && !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if((date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->user->phone == $request->phone) && ($global[$i]->user->phone == $request->phone)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->phone) && !is_null($request->type) && !is_null($request->carte):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(($global[$i]->carte->code_number == $request->carte) && ($global[$i]->user->phone == $request->phone) && ($global[$i]->transaction_type == $request->type)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->starting_date) && !is_null($request->end_date) && !is_null($request->carte):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(($request->starting_date >= date('Y-m-d', strtotime($global[$i]->starting_date))) && (date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->carte->code_number == $request->carte)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->starting_date) && !is_null($request->end_date) && !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(($request->starting_date <= date('Y-m-d', strtotime($global[$i]->starting_date))) && (date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->transaction_type == $request->type)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;



                break;



            case !is_null($request->starting_date) && !is_null($request->end_date) && !is_null($request->phone):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(($request->starting_date >= date('Y-m-d', strtotime($global[$i]->starting_date))) && (date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->user->phone == $request->phone)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->starting_date) && !is_null($request->carte):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if((date('Y-m-d', strtotime($global[$i]->starting_date)) == $request->starting_date) && ($global[$i]->carte->code_number == $request->carte)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->end_date) && is_null($request->starting_date):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;

            case !is_null($request->carte) && !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(!is_null($global[$i]->carte) && ($global[$i]->carte->code_number == $request->carte) && ($global[$i]->transaction_type == $request->type)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;



                break;



            case !is_null($request->carte) && !is_null($request->phone):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(($global[$i]->carte->code_number == $request->carte) && ($global[$i]->user->phone == $request->phone)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->phone) && !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(!is_null($global[$i]->user) && ($global[$i]->user->phone == $request->phone) && ($global[$i]->transaction_type == $request->type)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->starting_date) && !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if((date('Y-m-d', strtotime($global[$i]->starting_date)) == $request->starting_date) && ($global[$i]->transaction_type == $request->type)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->starting_date) && !is_null($request->end_date):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(($request->starting_date <= date('Y-m-d', strtotime($global[$i]->starting_date))) && (date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->end_date) && !is_null($request->carte):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(!is_null($global[$i]->carte) && (date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->carte->code_number == $request->carte)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->end_date) && !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if((date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->transaction_type == $request->type)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->end_date) && !is_null($request->phone):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if((date('Y-m-d', strtotime($global[$i]->starting_date)) <= $request->end_date) && ($global[$i]->user->phone == $request->phone)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->starting_date):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(date('Y-m-d', strtotime($global[$i]->starting_date)) == $request->starting_date){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->carte):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(!is_null($global[$i]->carte) && ($global[$i]->carte->code_number == $request->carte)){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->type):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if($global[$i]->transaction_type == $request->type){

                        $globalFil[] = $global[$i];

                    }

                }

                $global = $globalFil;

                break;



            case !is_null($request->phone):

                $globalFil = [];

                for ($i=0; $i < count($global); $i++) {

                    if(!is_null($global[$i]->user) && ($global[$i]->user->phone == $request->phone)){

                        $globalFil[] = $global[$i]->user->phone;

                    }

                }

                $global = $globalFil;

                break;



            default:

                $global = [];

                break;

        }



        return $this->sendResponse(new Resource($global), 'la transaction numero ');

    }


    public function recap_transaction($transactionArray, $transaction){
        if(array_key_exists('Date', $transactionArray)){
            for ($i=0; $i < count($transactionArray['Date']); $i++) {
                if($transactionArray[$i]['Date'] == $transaction['created_at']){
                    switch ($transaction['transaction_type']) {
                        case 'TRANSFERT_CARTE_A_CARTE':
                            if(array_key_exists('TRANSFERT_CARTE_A_CARTE', $transactionArray[$i])){
                                $transactionArray[$i]['TRANSFERT_CARTE_A_CARTE'] += 1;
                            }else{
                                $transactionArray[$i]['TRANSFERT_CARTE_A_CARTE'] = 1;
                            }
                            break;
                    }
                }else{
                    $transactionArray[$i]['Date'] = $transaction['created_at'];
                }
            }
        }else{
            $transactionArray["Date"][0] = $transaction['created_at'];
        }
        return $transactionArray;
    }

    public function show($transaction_id){
        $nbreTransfertDEPOTUNITE = 0;
        $amountTransfertDEPOTUNITE = 0;
        $nbreTransfertUNITEDEPOT = 0;
        $amountTransfertUNITEDEPOT = 0;
        $nbreTransfertCarte = 0;
        $nbreTransfertCompte = 0;
        $amountTransfertCompte = 0;
        $amountTransfertCarte = 0;
        $nbreQrcode = 0;
        $amountQrcode = 0;
        $nbreDebit = 0;
        $amountDebit = 0;
        $nbreRechargeOrange = 0;
        $amountRechargeOrange = 0;
        $nbreRechargeMtn = 0;
        $amountRechargeMtn = 0;
        $nbreRetraitMtn = 0;
        $amountRetraitMtn = 0;
        $nbreRetraitOrange = 0;
        $amountRetraitOrange = 0;
        $nbreRetraitCarteMtn = 0;
        $amountRetraitCarteMtn = 0;
        $nbreRetraitCarteOrange = 0;
        $amountRetraitCarteOrange = 0;
        $countTransaction = [];
        $res = transaction::all();
        for ($i=0; $i < count($res) ; $i++) {
            //$countTransaction[] = $this->recap_transaction($countTransaction, $res[$i]);
            switch ($res[$i]['transaction_type']) {
                case 'TRANSFERT_CARTE_A_CARTE':
                    $nbreTransfertCarte = $nbreTransfertCarte + 1;
                    $amountTransfertCarte = $amountTransfertCarte + $res[$i]['amount'];
                    break;
                case 'TRANSFERT_COMPTE_A_COMPTE':
                    $nbreTransfertCompte = $nbreTransfertCompte + 1;
                    $amountTransfertCompte +=$res[$i]['amount'];
                    break;
                case 'PAYEMENT_VIA_QRCODE':
                    $nbreQrcode = $nbreQrcode + 1;
                    $amountQrcode = $amountQrcode + $res[$i]['amount'];
                    break;
                case 'DEBIT_CARTE':
                    $nbreDebit = $nbreDebit + 1;
                    $amountDebit = $amountDebit + $res[$i]['amount'];
                    break;
                case 'TRANSFERT_DEPOT_UNITE':
                    $nbreTransfertUNITEDEPOT = $nbreTransfertUNITEDEPOT + 1;
                    $amountTransfertUNITEDEPOT = $amountTransfertUNITEDEPOT + $res[$i]['amount'];
                    break;

                case 'TRANSFERT_UNITE_DEPOT':
                    $nbreTransfertDEPOTUNITE = $nbreTransfertDEPOTUNITE + 1;
                    $amountTransfertDEPOTUNITE = $amountTransfertDEPOTUNITE + $res[$i]['amount'];
                    break;

                case 'RECHARGE_COMPTE_VIA_MONETBIL':
                    switch ($res[$i]['operator']) {
                        case 'MTN':
                            $nbreRechargeMtn += 1;
                            $amountRechargeMtn += $res[$i]['amount'];
                            break;

                        case 'Orange':
                            $nbreRechargeOrange += 1;
                            $amountRechargeOrange += $res[$i]['amount'];
                            break;
                    }
                    break;

                case 'RETRAIT_COMPTE_VIA_MONETBIL':
                    switch ($res[$i]['operator']) {
                        case 'CM_MTNMOBILEMONEY':
                            $nbreRetraitMtn += 1;
                            $amountRetraitMtn += $res[$i]['amount'];
                            break;

                        case 'CM_ORANGEMONEY':
                            $nbreRetraitOrange += 1;
                            $amountRetraitOrange += $res[$i]['amount'];
                            break;
                    }
                    break;

                case 'RETRAIT_CARTE_VIA_MONETBIL':
                    switch ($res[$i]['operator']) {
                        case 'CM_MTNMOBILEMONEY':
                            $nbreRetraitCarteMtn += 1;
                            $amountRetraitCarteMtn += $res[$i]['amount'];
                            break;

                        case 'CM_ORANGEMONEY':
                            $nbreRetraitCarteOrange += 1;
                            $amountRetraitCarteOrange += $res[$i]['amount'];
                            break;
                    }
                    break;

                case 'DEBIT_CARTE':
                    $nbreDebit = $nbreDebit + 1;
                    $amountDebit = $amountDebit + $res[$i]['amount'];
                    break;
            }
            if($res[$i]['card_id_sender'] || $res[$i]['card_id_receiver'] || $res[$i]['account_id_receiver'] || $res[$i]['account_id_sender']){

                $transaction[$i]['Date'] = date('d/m/Y H:i:s', strtotime($res[$i]['starting_date']));

                $transaction[$i]['id'] = $res[$i]['transaction_number'];

                $transaction[$i]['Status'] = $res[$i]['state'];

                $transaction[$i]['Operation'] = $res[$i]['transaction_type'];

                $transaction[$i]['Montant'] = $res[$i]['amount'];

                $transaction[$i]['Frais'] = $res[$i]['servicecharge'];



                if($res[$i]['card_id_sender']){
                    $card = $res[$i]['card_id_sender'];
                    $userCardSender =  card::where('id', $card)->with(['user'=>function($query){$query->with(['particulier','enterprise']);}])->first()->user;
                    if(!is_null($userCardSender)){
                        if(array_key_exists(0,$userCardSender->toArray()['particulier'])){
                            $transaction[$i]['user']['emetteur'] = $userCardSender->toArray()['particulier'][0];
                        }
                        $transaction[$i]['user']['emetteur']['type'] = 'emetteur';

                        $transaction[$i]['user']['emetteur']['phone'] = $userCardSender->toArray()['phone'];

                        if(!empty($userCardSender->toArray()['cards']))

                            $transaction[$i]['user']['emetteur']['entite'] = 'carte n°: '.$userCardSender->toArray()['cards'][0]['code_number'];

                    }}



                if($res[$i]['card_id_receiver']){

                    $card = $res[$i]['card_id_receiver'];

                    $userCardReceiver = card::where('id', $card)->with(['user'=>function($query){$query->with(['particulier','enterprise']);}])->first()->user;

                    if(!is_null($userCardReceiver)){
                        if(array_key_exists(0,$userCardReceiver->toArray()['particulier'])){
                            $transaction[$i]['user']['destinataire'] = $userCardReceiver->toArray()['particulier'][0];
                        }
                        $transaction[$i]['user']['destinataire']['type'] = 'destinataire';

                        $transaction[$i]['user']['emetteur']['phone'] = $userCardReceiver->toArray()['phone'];

                        if(!empty($userCardReceiver->toArray()['cards']))

                            $transaction[$i]['user']['destinataire']['entite'] = 'carte n°: '.$userCardReceiver->toArray()['cards'][0]['code_number'];

                    }
                }



                if($res[$i]['account_id_receiver']){

                    $card = $res[$i]['account_id_receiver'];

                    $userCardReceiver = user::where('compte_id', $card)->with(['particulier','enterprise'])->first();
                    if(!is_null($userCardReceiver)){
                        $transaction[$i]['user']['destinataire'] = !empty($userCardReceiver->toArray()['particulier']) ? $userCardReceiver->toArray()['particulier'][0]: $userCardReceiver->toArray()['enterprise'][0];
                        $transaction[$i]['user']['emetteur']['phone'] = $userCardReceiver->toArray()['phone'];
                    }

                }





                if($res[$i]['account_id_sender']){

                    $card = $res[$i]['account_id_sender'];

                    $userCardReceiver = user::where('compte_id', $card)->with(['particulier','enterprise'])->first();
                    if(!is_null($userCardReceiver)){
                        $transaction[$i]['user']['emetteur'] = !empty($userCardReceiver->toArray()['particulier']) ? $userCardReceiver->toArray()['particulier'][0]: $userCardReceiver->toArray()['enterprise'];

                        $transaction[$i]['user']['emetteur']['type'] = $userCardReceiver;

                        $transaction[$i]['user']['emetteur']['phone'] = $userCardReceiver->toArray()['phone'];

                    }

                }

            }
        }
        $transaction['index'] = $transaction;
        $transaction['transaction_count'] = DB::select('call getTransactionsCount()');
        $transaction['recapitulatif'] = $countTransaction;
        $transaction['nbreTransfertCarte'] = $nbreTransfertCarte;
        $transaction['amountTransfertCarte'] = $amountTransfertCarte;
        $transaction['nbreTransfertCompte'] = $nbreTransfertCompte;
        $transaction['amountTransfertCompte'] = $amountTransfertCompte;
        $transaction['nbreRechargeOrange'] = $nbreRechargeOrange;
        $transaction['amountRechargeOrange'] = $amountRechargeOrange;
        $transaction['nbreRechargeMtn'] = $nbreRechargeMtn;
        $transaction['amountRechargeMtn'] = $amountRechargeMtn;
        $transaction['nbreRetraitOrange'] = $nbreRetraitOrange;
        $transaction['amountRetraitOrange'] = $amountRetraitOrange;
        $transaction['nbreRetraitMtn'] = $nbreRetraitMtn;
        $transaction['amountRetraitMtn'] = $amountRetraitMtn;
        $transaction['nbreRetraitCarteOrange'] = $nbreRetraitCarteOrange;
        $transaction['amountRetraitCarteOrange'] = $amountRetraitCarteOrange;
        $transaction['nbreRetraitCarteMtn'] = $nbreRetraitCarteMtn;
        $transaction['amountRetraitCarteMtn'] = $amountRetraitCarteMtn;
        $transaction['nbreTransfertUNITEDEPOT'] = $nbreTransfertUNITEDEPOT;
        $transaction['amountTransfertUNITEDEPOT'] = $amountTransfertUNITEDEPOT;
        $transaction['nbreTransfertDEPOTUNITE'] = $nbreTransfertDEPOTUNITE;
        $transaction['amountTransfertDEPOTUNITE'] = $amountTransfertDEPOTUNITE;
        return $this->sendResponse(new Resource($transaction), 'la transaction numero '.$transaction_id.' a été trouver avec succés.');
    }



    /**







     * <<=== //// DEBIT CARTE //// ===>>







     * cette fonction permet de faire un retrait ou debit sur une carte







     * @param Request $request







     * @return Response







     */







    /* public function debit(Request $request){

         transaction::setStarting_date(date('Y-m-d h:i:s', time()));

         transaction::setTransaction_number('SMP'.date('ymd').strtotime('now'));

         transaction::setId('SMP'.date('ymd').strtotime('now'));

         $input = $request->all();

         $validator = $this->validation($input, [



             'amount'=>'required|numeric',

             'code_number_sender' => 'required',

             'serial_number_device' => 'required'







         ]);







         if($validator->fails()) {







             return $this->sendError('Erreur de validation.', $validator->errors(),400);







         }else{







             transaction::hydratation($input);







             $card_sender = card::findCodeNumberCard(transaction::getCode_number_sender());







             $device = device::findSerialNumberDevice(transaction::getSerial_number_device());







             if($device != null){







                 switch ($card_sender['status']) {







                     case 1:







                         $response = transaction::debitCarte($card_sender, $device);







                         transaction::setState($response['status'] == 1 ? "SUCCESS" : "FAILED");







                         transaction::setEnd_date(date('Y-m-d h:i:s a', time()));







                         $transaction = $response['status'] == 1 ? transaction::createTransaction(transaction::prepareDataToSave()) :[];







                         $status = $response['status'] == 1 ? 200 : 404;







                         $success = $response['status'] == 1 ? true : false;







                         return $this->sendResponse(new Resource($transaction), $response, $status, $success);







                         break;







                     case 0:







                         return $this->sendError($card_sender['msg'], 404);







                         break;







                     case -1:







                         return $this->sendError($card_sender['msg'], 404);







                         break;







                 }







             }else{







                 return $this->sendResponse('désolé aucun device trouvé', 404);







             }







         }







     }*/



    public function remoteCollection(Request $request){







        transaction::setStarting_date(date('Y-m-d h:i:s a', time()));

        transaction::setTransaction_number('SMP'.date('ymd').strtotime('now'));

        transaction::setId('SMP'.date('ymd').strtotime('now'));





        $validator = Validator::make($input, [







            'amount'=>'required|numeric',







            'code_number_receiver' => 'required',







            'serial_number_device' => 'required'







        ]);



        if($validator->fails()) {







            return $this->sendError('Erreur de validation.', $validator->errors());







        }else{







            transaction::hydratation($input);







            $card_receiver = card::findCodeNumberCard(transaction::getCode_number_receiver());







            $device = device::findSerialNumberDevice(transaction::getSerial_number_device());







            if($device != null){







                switch ($card_receiver['status']) {







                    case 1:







                        $response = transaction::startRemoteCollectionTransaction($card_receiver, $device);







                        transaction::setState($response['status'] == 1 ? "SUCCESS" : "FAILED");







                        transaction::setEnd_date(date('Y-m-d h:i:s a', time()));







                        $transaction = $response['status'] == 1 ? transaction::createTransaction(transaction::prepareDataToSave()) :[];







                        $status = $response['status'] == 1 ? 200 : 404;







                        $success = $response['status'] == 1 ? true : false;







                        return $this->sendResponse(new Resource($transaction), $response, $status, $success);







                        break;







                    case 0 or -1:







                        return $this->sendError($card_sender['msg'], 404);







                        break;







                }







            }else{







                return $this->sendResponse('désolé aucun device trouvé', 404);







            }







        }







    }



    /**







     * @param Request $request







     * @return \Illuminate\Http\Response







     * cette fonction permet de faire les paiements







     * - transfert compte à compte







     * - depot chez un compte agré







     * - qrcode







     * - payer facture







     */








    /*    public function paymentAccount(Request $request){
            transaction::setStarting_date(date('Y-m-d h:i:s', time()));

            transaction::setTransaction_number('SMP'.date('ymd').strtotime('now'));

            transaction::setId('SMP'.date('ymd').strtotime('now'));

            $input = $request->all();

            $validator = $this->validation($input, [

                'amount'=>'required',

                'account_number_sender' => 'required',

                'account_number_receiver' => 'required',

                'transaction_type' => 'required|in:TRANSFERT_COMPTE_A_COMPTE'

            ]);

            if($validator->fails())

                return $this->sendError('Erreur de validation.', $validator->errors(), 400);

            if($input['account_number_sender']  == $input['account_number_receiver']){

                return $this->sendError('Echec de l\'opération.', $validator->errors(), 400);

            }

            //transaction::hydratation($input);
            transaction::setTransaction_type($input['transaction_type']);
            transaction::setAmount($input['amount']);
            $account_sender = compte::findAccount($input['account_number_sender']);
            if(array_key_exists('status', $account_sender) and $account_sender['status'] == -1)
                return $this->sendError($account_sender['notif']);

            $account_receiver = compte::findAccount($input['account_number_receiver']);

            if(array_key_exists('status', $account_receiver) and $account_receiver['status'] == -1)

                return $this->sendError($account_receiver['notif']);

                switch ($account_receiver['status']) {

                    case 1:

                        switch ($account_sender['status']) {

                            case 1:

                                $response = transaction::placeStartTransactionTransfert($account_sender, $account_receiver, $input['amount'], 'compte');

                                transaction::setState($response['status'] == 1 ? "SUCCESS" : "FAILED");

                                transaction::setEnd_date(date('Y-m-d h:i:s', time()));

                                if($response['status'] == 1){
                                    $transaction = transaction::createTransaction(transaction::prepareDataToSave());

                                    compte::afterDataToSave();

                                }else{

                                    $transaction = [];

                                    }

                                if($response['status'] != 1)

                                    return $this->sendError($response);

                                return $this->sendResponse(new Resource($transaction), $response);

                                break;

                            case 0 || -1:

                                return $this->sendError($account_sender['notif'],'',400);

                                break;

                        }

                        break;

                    case 0 || -1:

                        return $this->sendError($account_receiver['notif'],'',400);

                        break;

                }

        } */


    public function paymenTransaction(Request $request){

        transaction::setStarting_date(date('Y-m-d h:i:s'));

        $amountWithDraw = "";

        transaction::setTransaction_number('SMP'.date('ymd').strtotime('now'));

        transaction::setId('SMP'.date('ymd').strtotime('now'));

        $input = $request->all();

        $validator = $this->validation($input, [

            'amount'=>'required',

            'code_number_sender' => 'required',

            'code_number_receiver' => 'required',

            'serial_number_device' => '',

            'transaction_type' => 'required|in:TRANSFERT_CARTE_A_CARTE,DEPOT,PAYEMENT_VIA_QRCODE,PAYEMENT_FACTURE,RETRAIT_SMOPAYE,DEBIT_CARTE,TRANSFERT_COMPTE_A_COMPTE,DEBIT_ACHAT'

        ]);



        if($validator->fails())

            return $this->sendError('Erreur de validation.', $validator->errors(), 400);



        if($input['code_number_sender']  == $input['code_number_receiver']){

            return $this->sendError('Echec de l\'opération.', $validator->errors(), 400);

        }

        transaction::hydratation($input);

        $card_sender = card::findCodeNumberCard(transaction::getCode_number_sender());
        if(array_key_exists('status', $card_sender) and ($card_sender['status'] == -1 || $card_sender['status'] == 0))

            return $this->sendError($card_sender['notif']);

        $card_receiver = card::findCodeNumberCard(transaction::getCode_number_receiver());

        if(array_key_exists('status', $card_receiver) and ($card_receiver['status'] == -1 || $card_receiver['status'] == 0))

            return $this->sendError($card_receiver['notif']);

        $user = card::findUserCard($card_sender['card']['id']);
        $compte = compte::findAccount(null, $user['compte_id']);

        switch ($compte['status']) {

            case 1:
                switch(transaction::getTransaction_type()){
                    case "TRANSFERT_CARTE_A_CARTE":
                        tarif::setServiceCharge(0);
                        $amountWithDraw = transaction::getAmount();
                        break;

                    case "TRANSFERT_COMPTE_A_COMPTE":
                        tarif::setServiceCharge(0);
                        $amountWithDraw = transaction::getAmount();
                        break;

                    case 'DEBIT_CARTE':
                        $result = transaction::checkRemise($user);
                        if(!is_null($result)){
                            transaction::setAmount($result);
                            $amountWithDraw = CompteSubscription::getAmountSubcription($result, $user);
                        }else{
                            $amountWithDraw = CompteSubscription::getAmountSubcription(transaction::getAmount(), $user);
                        }
                        break;


                    default:
                        $amountWithDraw = CompteSubscription::getAmountSubcription(transaction::getAmount(), $user);
                        break;
                }

                switch ($card_receiver['status']) {
                    case 1:

                        switch ($card_sender['status']) {

                            case 1:

                                $response = transaction::startTransaction($card_sender, $card_receiver, $amountWithDraw, $this->getSmopaye_phone());

                                transaction::setState($response['status'] == 1 ? "SUCCESS" : "FAILED");

                                transaction::setEnd_date(date('Y-m-d h:i:s', time()));

                                if($response['status'] == 1){

                                    if(tarif::getServiceCharge() != 0){

                                        $sender = compte::setAccountWithPhone('673003170',tarif::getServiceCharge());

                                        $sender['notif'] = 'le compte de SMOPAYE vient d\'être crébité d\'un montant de '.tarif::getServiceCharge(). 'fcfa suite a une opération de '.transaction::getTransaction_type().' de la carte n° '.card::getCode_number().' et votre nouveau solde est de '.$sender['amount'].' fcfa';

                                        tarif::setServiceCharge(0);

                                    }

                                    $transaction = transaction::createTransaction(transaction::prepareDataToSave());
                                    if((transaction::getTransaction_type() == "PAYEMENT_VIA_QRCODE") || (transaction::getTransaction_type() == "DEBIT_CARTE")){

                                        $user = user::where('id',$card_sender['card']['user_id'])->with(['particulier'])->first();
                                        transaction::checkBonus($user, $card_sender, $this->getSmopaye_phone(), new transaction());

                                    }





                                }else{







                                    $transaction = [];







                                }







                                if($response['status'] != 1){







                                    return $this->sendError($response['notif'],'', 400);







                                }







                                if(tarif::getServiceCharge() != 0){







                                    $response['service'] = $sender;







                                }







                                return $this->sendResponse(new Resource($transaction), $response);







                                break;







                            case 0 || -1:







                                return $this->sendError($card_sender['notif'],'',400);







                                break;







                        }

                        break;

                    case 0 || -1:

                        return $this->sendError($card_receiver['notif'],'',400);

                        break;

                }

                break;

            default:

                return $this->sendError($compte['notif'],'',400);

                break;

        }

    }





    public function getStatisticalTransaction(Request $request){

        $input = $request->all();

        $transaction = array();

        $validator = $this->validation($input, [

            'date'=>'',

            'type_operation'=>'required'

        ]);

        if($validator->fails())

            return $this->sendError('Erreur de validation.', $validator->errors(), 400);

        $user = Auth::guard('api')->user();

        $card = card::where('user_id',$user->id)->get()->first();

        $res = transaction::where([['account_id_sender', $user->compte_id], ['transaction_type', $input['type_operation']]])

            ->orWhere([['account_id_receiver', $user->compte_id], ['transaction_type', $input['type_operation']]])

            ->orWhere([['card_id_sender', $card->id], ['transaction_type', $input['type_operation']]])

            ->orWhere([['card_id_receiver', $card->id], ['transaction_type', $input['type_operation']]])

            ->selectRaw('starting_date')

            ->groupBy('starting_date')

            ->get()->toArray();

    }




    public function loadTransactionUser(){
        $user = Auth::guard('api')->user();
        $card = card::where('user_id',$user->id)->get()->first();
        //return json_encode($card);
        $userCardReceiver = null; $userCardSender = null; $userCompteReceiver = null; $userCompteSender = null; $transaction = array();
        $res = transaction::where('account_id_sender', $user->compte_id)->orWhere('account_id_receiver', $user->compte_id)->orWhere('card_id_receiver', $card->id)->orWhere('card_id_sender', $card->id)->get()->toArray();
        for ($i=0; $i < count($res) ; $i++) {

            $transaction[$i]['Date'] = date('d/m/Y H:i:s', strtotime($res[$i]['starting_date']));
            $transaction[$i]['id'] = $res[$i]['transaction_number'];
            $transaction[$i]['Status'] = $res[$i]['state'];
            $transaction[$i]['Operation'] = $res[$i]['transaction_type'];
            $transaction[$i]['Montant'] = $res[$i]['amount'];
            $transaction[$i]['Frais'] = $res[$i]['servicecharge'];

            if($res[$i]['account_id_sender']){

                $userCompteSender = user::where('compte_id', $res[$i]['account_id_sender'])->with(['enterprise','particulier', 'compte'])->first();

                if(!empty($userCompteSender ->toArray()['particulier'][0])){

                    $transaction[$i]['user'][0] = $userCompteSender->toArray()['particulier'][0];

                }else{

                    $transaction[$i]['user'][0] = $userCompteSender->toArray()['enterprise'][0];

                }

                $transaction[$i]['user'][0]['type'] = 'emetteur';

                $transaction[$i]['user'][0]['entite'] = 'compte n°: '.$userCompteSender->toArray()['compte']['account_number'];

            }

            if($res[$i]['account_id_receiver']){

                $userCompteReceiver = user::where('compte_id', $res[$i]['account_id_receiver'])->with(['enterprise','particulier', 'compte'])->first();

                if(!empty($userCompteReceiver->toArray()['particulier'][0])){

                    $transaction[$i]['user'][1] = $userCompteReceiver->toArray()['particulier'][0];

                }else{

                    $transaction[$i]['user'][1] = $userCompteReceiver->toArray()['enterprise'][0];

                }

                $transaction[$i]['user'][1]['type'] = 'destinataire';

                $transaction[$i]['user'][1]['entite'] = 'compte n°: '.$userCompteReceiver->toArray()['compte']['account_number'];

            }



            if($res[$i]['card_id_sender']){

                $card = card::where('id', $res[$i]['card_id_sender'])->first()->toArray();

                $userCardSender = user::where('id', $card['user_id'])->with(['enterprise','particulier'])->first();

                if(!empty($userCardSender->toArray()['particulier'][0])){

                    $transaction[$i]['user'][0] = $userCardSender->toArray()['particulier'][0];

                }else{

                    $transaction[$i]['user'][0] = $userCardSender->toArray()['enterprise'][0];

                }

                $transaction[$i]['user'][0]['type'] = 'emetteur';

                if(!empty($card))

                    $transaction[$i]['user'][0]['entite'] = 'carte n°: '.$card['code_number'];

            }



            if($res[$i]['card_id_receiver']){

                $card = card::where('id', $res[$i]['card_id_receiver'])->first()->toArray();

                $userCardReceiver = user::where('id', $card['user_id'])->with(['enterprise','particulier'])->first();

                if(!empty($userCardReceiver->toArray()['particulier'][0])){

                    $transaction[$i]['user'][1] = $userCardReceiver->toArray()['particulier'][0];

                }else{

                    $transaction[$i]['user'][1] = $userCardReceiver->toArray()['enterprise'][0];

                }

                $transaction[$i]['user'][1]['type'] = 'destinataire';

                if(!empty($card))

                    $transaction[$i]['user'][1]['entite'] = 'carte n°: '.$card['code_number'];

            }



            switch ($res[$i]) {

                case $res[$i]['account_id_sender'] == $res[$i]['account_id_receiver']:

                    $transaction[$i]['statusColor'] = 'warning';

                    $transaction[$i]['statusMsg'] = 'self';

                    break;

                case $res[$i]['account_id_sender'] == $user->compte_id && $res[$i]['account_id_sender'] != $res[$i]['account_id_receiver']:

                    $transaction[$i]['statusColor'] = 'danger';

                    $transaction[$i]['statusMsg'] = 'sortant';

                    break;

                case $res[$i]['account_id_receiver'] == $user->compte_id && $res[$i]['account_id_sender'] != $res[$i]['account_id_receiver']:

                    $transaction[$i]['statusColor'] = 'success';

                    $transaction[$i]['statusMsg'] = 'entrant';

                    break;

            }

        }



        return $this->sendResponse(new Resource($transaction), "historique renvoyée avec succés");
    }


    public function historiqueByTransaction($date, $type_operation){
        $user = Auth::guard('api')->user();
        $card = card::where('user_id',$user->id)->get()->first();

        $userCardReceiver = null; $userCardSender = null; $userCompteReceiver = null; $userCompteSender = null; $transaction = array();

        if($date == '1' and $type_operation == "compte"){

            if(!empty($card)){

                $res = transaction::where([['account_id_sender', $user->compte_id], ['account_id_receiver', $user->compte_id]])->orWhere('account_id_sender', $user->compte_id)->orWhere('account_id_receiver', $user->compte_id)->orWhere([['card_id_sender', $card->id], ['account_id_receiver', $user->compte_id]])->orWhere([['account_id_sender', $user->compte_id], ['card_id_receiver', $card->id]])->get()->toArray();

            }else{

                $res = transaction::where([['account_id_sender', $user->compte_id], ['account_id_receiver', $user->compte_id]])->orWhere('account_id_sender', $user->compte_id)->orWhere('account_id_receiver', $user->compte_id)->get()->toArray();

            }



        }else{

            $res = transaction::where([['account_id_sender', $user->compte_id], ['transaction_type', $type_operation]])->orWhere([['account_id_receiver', $user->compte_id], ['transaction_type', $type_operation]])->orWhere([['card_id_receiver', $card->id], ['transaction_type', $type_operation]])->orWhere([['card_id_sender', $card->id], ['transaction_type', $type_operation]])->get()->toArray();

        }



        for ($i=0; $i < count($res) ; $i++) {

            $dt = new DateTime($res[$i]['starting_date']);

            if($date == $dt->format('Y-m-d')){

                $transaction[$i]['Date'] = date('d/m/Y H:i:s', strtotime($res[$i]['starting_date']));

                $transaction[$i]['id'] = $res[$i]['transaction_number'];

                $transaction[$i]['Status'] = $res[$i]['state'];

                $transaction[$i]['Operation'] = $res[$i]['transaction_type'];

                $transaction[$i]['Montant'] = $res[$i]['amount'];

                $transaction[$i]['Frais'] = $res[$i]['servicecharge'];



                if($res[$i]['account_id_sender']){

                    $userCompteSender = user::where('compte_id', $res[$i]['account_id_sender'])->with(['enterprise','particulier', 'compte'])->first();

                    if(!empty($userCompteSender->toArray()['particulier'][0])){
                        $transaction[$i]['user']['entreprise'] = $userCompteSender->toArray()['particulier'][0];
                    }else{
                        $transaction[$i]['user']['entreprise'] = $userCompteSender->toArray()['enterprise'][0];
                    }

                    $transaction[$i]['user']['entreprise']['type'] = 'emetteur';
                    $transaction[$i]['user']['entreprise']['entite'] = 'compte n°: '.$userCompteSender->toArray()['compte']['account_number'];

                }

                if($res[$i]['account_id_receiver']){
                    $userCompteReceiver = user::where('compte_id', $res[$i]['account_id_receiver'])->with(['enterprise','particulier', 'compte'])->first();
                    if(!empty($userCompteReceiver->toArray()['particulier'][0])){
                        $transaction[$i]['user']['entreprise'] = $userCompteReceiver->toArray()['particulier'][0];
                    }else{
                        $transaction[$i]['user']['entreprise'] = $userCompteReceiver->toArray()['enterprise'][0];
                    }
                    $transaction[$i]['user']['entreprise']['type'] = 'destinataire';
                    $transaction[$i]['user']['entreprise']['entite'] = 'compte n°: '.$userCompteReceiver->toArray()['compte']['account_number'];
                }



                if($res[$i]['card_id_sender']){

                    $card = card::where('id', $res[$i]['card_id_sender'])->first()->toArray();

                    $userCardSender = user::where('id', $card['user_id'])->with(['enterprise','particulier'])->first();

                    if(!empty($userCardSender->toArray()['particulier'][0])){

                        $transaction[$i]['user']['particulier'] = $userCardSender->toArray()['particulier'][0];

                    }else{

                        $transaction[$i]['user']['particulier'] = $userCardSender->toArray()['enterprise'][0];

                    }

                    $transaction[$i]['user']['particulier']['type'] = 'emetteur';

                    if(!empty($card))

                        $transaction[$i]['user']['particulier']['entite'] = 'carte n°: '.$card['code_number'];

                }



                if($res[$i]['card_id_receiver']){

                    $card = card::where('id', $res[$i]['card_id_receiver'])->first()->toArray();

                    $userCardReceiver = user::where('id', $card['user_id'])->with(['enterprise','particulier'])->first();

                    if(!empty($userCardReceiver->toArray()['particulier'][0])){

                        $transaction[$i]['user']['particulier'] = $userCardReceiver->toArray()['particulier'][0];

                    }else{

                        $transaction[$i]['user']['particulier'] = $userCardReceiver->toArray()['enterprise'][0];

                    }

                    $transaction[$i]['user']['particulier']['type'] = 'destinataire';

                    if(!empty($card))

                        $transaction[$i]['user']['particulier']['entite'] = 'carte n°: '.$card['code_number'];

                }



                switch ($res[$i]) {

                    case $res[$i]['account_id_sender'] == $res[$i]['account_id_receiver']:

                        $transaction[$i]['statusColor'] = 'warning';

                        $transaction[$i]['statusMsg'] = 'self';

                        break;

                    case $res[$i]['account_id_sender'] == $user->compte_id && $res[$i]['account_id_sender'] != $res[$i]['account_id_receiver']:

                        $transaction[$i]['statusColor'] = 'danger';

                        $transaction[$i]['statusMsg'] = 'sortant';

                        break;

                    case $res[$i]['account_id_receiver'] == $user->compte_id && $res[$i]['account_id_sender'] != $res[$i]['account_id_receiver']:

                        $transaction[$i]['statusColor'] = 'success';

                        $transaction[$i]['statusMsg'] = 'entrant';

                        break;

                }

            }

        }



        return $this->sendResponse(new Resource($transaction), "historique renvoyée avec succés");



    }







}



