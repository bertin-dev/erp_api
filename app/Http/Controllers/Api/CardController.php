<?php

namespace App\Http\Controllers\Api;

use App\Models\Card as card;
use App\Models\Compte as compte;
use App\Http\Controllers\Controller;
use App\Models\Recap_tab;
use App\Models\Tarif as tarif;
use App\Models\Transaction as transaction;
use App\Models\User as user;
use Illuminate\Http\Request;
use App\Http\Resources\Index as Resource;
use Illuminate\Support\Facades\Auth;

class CardController extends BaseController

{

    public function recaputilatif(){

        $data = Recap_tab::all();

        return $this->sendResponse(new Resource($data),'recaputilatif cartes');

    }



    public function loadusenocard(){

        $cards = card::where('user_id', null)->get();

        return $this->sendResponse(Resource::collection($cards),'liste des cartes libre');

    }



    public function loadusecard(){

        $cards = card::where('user_id','<>', null)->with(['user'=> function($query){$query->with(['particulier','enterprise']);}])->get();

        return $this->sendResponse(Resource::collection($cards),'liste des cartes utilisée');

    }

    /**

     * Display a listing of the resource.

     *

     * @return \Illuminate\Http\Response

     */

    public function index()

    {

        $card = card::getAllCard();

        return $this->sendResponse(Resource::collection($card), 'les cartes d\'utilisateur ont été renvoyer avec succés.');

    }



    public function edit(Card $card){

        return $this->sendResponse(new Resource($card), 'les cartes d\'utilisateur ont été renvoyer avec succés.');

    }



    /**

     * Store a newly created resource in storage.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function store(Request $request)

    {

        $input = $request->all();



        $validator = $this->validation($input, [

            'code_number' => 'required|size:8',

            'serial_number' => 'required',

            'end_date' => 'required',

        ]);

        if($validator->fails())

            return $this->sendError('Erreur de validation.', $validator->errors());


        $existcard = Card::where('code_number',$request->input('code_number'))->first();
        if ($existcard){
            return response()->json([
                'success' => false,
                'message' => 'cette carte existe deja'
            ], 400);

        }


        $input['created_by'] = Auth::guard('api')->user()->id;

        $card = card::createCard($input);

        return $this->sendResponse(new Resource($card), 'la carte a été créée avec succés.');}



    /**

     * Display the specified resource.

     *

     * @param  int  $id

     * @return \Illuminate\Http\Response

     */

    public function show($id){

        $card = card::showCard($id);

        if (is_null($card))

            return $this->sendError('la carte n\'existe pas.');

        return $this->sendResponse(new Resource($card[0]), 'la carte a été trouvée avec succés.');

    }



    /**

     * Update the specified resource in storage.

     *

     * @param  \Illuminate\Http\Request $request

     * @param Card $card

     * @param $card_id

     * @return \Illuminate\Http\Response

     * @internal param int $id

     */

    public function update(Request $request, Card $card)

    {

        $input = $request->all();

        $validator = $this->validation($input, [

            'code_number' => 'required',

            'serial_number' => 'required',

            'end_date' => 'required'

        ]);



        if($validator->fails()){

            return $this->sendError('Erreur de validation.', $validator->errors());

        }

        $card->code_number = $request->code_number;

        $card->serial_number = $request->serial_number;

        $card->end_date = $request->end_date;



        if($card->save()){

            return $this->sendResponse(new Resource($card), 'la carte a été modifier avec succés.');

        }else{

            return $this->sendError('Erreur!!!, les informations n\'ont pas été mises à jour');

        }

    }



    /**

     * Display the specified resource.

     *

     * @param  int  $id

     * @return \Illuminate\Http\Response

     */

    public function getDeposit($id){

        $card = card::find($id);

        if (is_null($card)) {

            return $this->sendError('la carte n\'existe pas.');

        }else{

            return $this->sendResponse(new Resource(['solde'=>$card->deposit]),'votre solde dépôt est '.$card->deposit.' fcfa');

        }

    }



    /**

     * Display the specified resource.

     *

     * @param  int  $id

     * @return \Illuminate\Http\Response

     */

    public function getUnity($id){

        $card = card::find($id);

        if (is_null($card)) {

            return $this->sendError('la carte n\'existe pas.');

        }else{

            return $this->sendResponse(new Resource(['solde'=>$card->unity]), 'votre solde unité est '.$card->unity.' fcfa');

        }

    }

    /**

     * Remove the specified resource from storage.

     *

     * @param Card $card

     * @return \Illuminate\Http\Response

     * @throws \Exception

     * @internal param Card $card

     * @internal param int $id

     */

    public function destroy(Card $card)

    {

        if(card::deleteCard($card)){

            return $this->sendResponse([], 'la carte a été supprimer avec succés.');

        }else{

            return $this->sendError([], 'Erreur !!!, la carte n\'as pas été supprimer.');

        }

    }





    /**

     * <<=== //// RETRAIT MONETBIL ET RECHARGE CARTE //// ===>>

     * cette fonction permet de faire un retrait ou debit sur une carte

     * @param Request $request

     * @return Response

     */

    public function retraitCarte(Request $request, $card_id){

        transaction::setStarting_date(date('Y-m-d h:i:s', time()));

        transaction::setTransaction_number('SMP'.date('ymd').strtotime('now'));

        transaction::setId('SMP'.date('ymd').strtotime('now'));

        $card = card::find($card_id);

        $user = card::findUserCard($card_id);

        if(empty($user)){

            return $this->sendError('Echec de l\'opération');

        }

        $compte = compte::findAccount(null, $user['compte_id']);

        switch ($compte['status']) {

            case 1:

                $input = $request->all();

                $valid = $this->validation($input, [

                    'withDrawalAmount' => 'required|numeric',

                    'phoneNumber'=>'required',

                ]);

                if($valid->fails())

                    return $this->sendError('Erreur de validation.', $valid->errors());

                compte::hydratation($compte['account']);

                card::hydratation((array) $card->toArray());

                card::hydratation((array) $input);

                $sender = user::where('compte_id',$user['compte_id'])->first();

                //$amountWithDraw =  CompteSubscription::getAmountSubcription(card::getWithDrawalAmount(), $sender);
                $amountWithDraw =  card::getWithDrawalAmount() + (card::getWithDrawalAmount() * 2)/100;
                switch ($amountWithDraw != false and $amountWithDraw < card::getDeposit()){

                    case true:

                        $monetbilMsg = card::cardWithDrawal(card::getWithDrawalAmount());

                        if($monetbilMsg['success']){

                            card::setDeposit(card::getDeposit() - $amountWithDraw);

                            $card = card::updateDeposit($card);

                            $card['notif'] = 'succés de l\'opération, le solde depot de la carte n° '.$card['code_number'].' a été débité d\'un montant de '.card::getWithDrawalAmount().' fcfa';



                            if(tarif::getServiceCharge() != 0){

                                $sender = compte::setAccountWithPhone('673003170',tarif::getServiceCharge());

                                $sender['notif'] = 'le compte de SMOPAYE vient d\'être crébité d\'un montant de '.tarif::getServiceCharge(). 'fcfa suite a une opération de RETRAIT VIA OPERATEUR SUR LA CARTE n° '.card::getCode_number().' et votre nouveau solde est de '.$sender['amount'].' fcfa';

                            }

                            transaction::setCard_id_sender(card::getId());
                            transaction::setCard_id_receiver(card::getId());
                            transaction::setTransaction_type('RETRAIT_CARTE_VIA_MONETBIL');
                            transaction::setOperator($monetbilMsg['operator']);
                            transaction::setAmount(compte::getAmount());
                            transaction::setState("SUCCESS");
                            if(tarif::getServiceCharge() != 0)
                                transaction::setServicecharge(tarif::getServiceCharge());
                            transaction::setEnd_date(date('Y-m-d h:i:s', time()));
                            transaction::createTransaction(transaction::prepareDataToSave());
                            transaction::afterDataToSave();

                            compte::afterDataToSave();

                            return $this->sendResponse(new Resource($sender),['sender'=>$card,'notif'=>'succes de l\'opération']);

                        }else{

                            compte::afterDataToSave();

                            return $this->sendError('Echec de l\'opération');

                        }

                        break;

                    case false:

                        compte::afterDataToSave();

                        return $this->sendError('votre solde est insuffisant pour effectuer cette transaction');

                        break;

                }

                break;

            default:

                return $this->sendError($compte['notif']);

                break;

        }

    }



    public function postRechargeCard(Request $request, Card $card, $card_id){

        transaction::setStarting_date(date('Y-m-d h:i:s', time()));

        transaction::setTransaction_number('SMP'.date('ymd').strtotime('now'));

        transaction::setId('SMP'.date('ymd').strtotime('now'));

        $card = card::showCard($card_id);

        $input = $request->all();

        $valid = $this->validation($input, [

            'amountRecharge' => 'required|numeric',

            'phoneNumber'=>'required',

        ]);

        if($valid->fails())

            return $this->sendError('Erreur de validation.', $valid->errors());

        card::hydratation((array) $input);

        $monetbilMsg = card::placeRechargeCard();

        transaction::setCard_id_sender(card::getId());

        transaction::setCard_id_receiver(card::getId());

        transaction::setTransaction_type('RECHARGE_CARTE_VIA_MONETBIL');

        transaction::setAmount(card::getAmountRecharge());

        transaction::setOperator($monetbilMsg['channel_name']);

        transaction::setState($monetbilMsg['message']);

        transaction::setEnd_date(date('Y-m-d h:i:s', time()));

        transaction::createTransaction(transaction::prepareDataToSave());

        return $this->sendResponse(new Resource($card), $monetbilMsg);

    }



    public function validateRechargeCard(Request $request, Card $card, $card_id){

        $card = card::showCard($card_id);

        $input = $request->all();

        $validator = $this->validation($input, [

            'paymentId' => 'required|numeric'

        ]);

        if($validator->fails()){

            return $this->sendError('Erreur de validation.', $validator->errors());

        }

        card::hydratation($input);

        $ckeck = card::checkPayment(card::getPaymentId());

        switch ($ckeck['status']) {

            case 1:

                card::setDeposit(card::getDeposit() + $ckeck['amount']);

                if(card::updateDeposit($card)){

                    $this->API_AVS(card::findUserCard(card::getId())['phone'],' votre compte SMOPAYE a été credité d\'un montant de '.$ckeck['amount'].' et votre nouveau solde est '.card::getDeposit().' fcfa.');

                    return $this->sendResponse(new Resource($card), $ckeck['message'].', votre nouveau solde depot est de '.card::getDeposit());

                }else{

                    return $this->sendResponse(new Resource($card), 'Erreur serveur!!! contacter le service client pour entrer en possession de votre recharge');

                }

                break;



            case 0:

                return $this->sendResponse(new Resource($card), $ckeck['message']);

                break;



            case -1:

                return $this->sendResponse(new Resource($card), $ckeck['message']);

                break;



            default:

                return $this->sendResponse(new Resource($card), $ckeck['message']);

                break;

        }

    }



    /**

     * cette fonction permet de modifier le champs etat carte de la table user card de la base de donnée.

     * son but est remplacer activer par desactiver

     *

     * @param  int  $card_id

     * user_card correspond l'identifiant de la carte

     * @return \Illuminate\Http\Response

     */

    public function activation(Request $request, $card_id)

    {

        $input = $request->all();

        $validator = $this->validation($input, [

            'card_state' => 'required|in:activer,desactiver'

        ]);

        if($validator->fails()){

            return $this->sendError('Erreur de validation.', $validator->errors());

        }

        card::hydratation($input);

        $response = card::activationCard($card_id);

        switch ($response['status']) {

            case 1:

                return $this->sendResponse(new Resource($response['card']), 'votre carte est '.card::getCard_state());

                break;



            default:

                return $this->sendError($response['notif']);

                break;

        }

    }





    public function toggleUnityDeposit(Request $request, $card_id){

        $card = card::showCard($card_id);

        $user = card::findUserCard($card_id);

        $compte = compte::findAccount(null, $user['compte_id']);

        switch ($compte['status']) {

            case 1:

                $input = $request->all();

                $validator = $this->validation($input, [

                    'withDrawalAmount' => 'required',

                    'action' => 'required|in:unity,deposit'

                ]);

                if($validator->fails())

                    return $this->sendError('Erreur de validation.', $validator->errors());
                card::hydratation((array) $input);
                //$amountWithDraw = CompteSubscription::getAmountSubcription(card::getWithDrawalAmount(), $user);
                $amountWithDraw = card::getWithDrawalAmount();
                switch ($amountWithDraw != false and card::getCard_state() != "desactiver") {
                    case true:
                        $method = 'get'.ucfirst(card::getAction() == "unity"? "deposit":"unity");
                        if($amountWithDraw < card::$method()){
                            $card = card::rechargeAccount(card::find($card_id), $amountWithDraw);
                            $sender = [];
                            if(tarif::getServiceCharge() != 0){
                                $sender = compte::setAccountWithPhone('673003170',tarif::getServiceCharge());
                                $sender['notif'] = 'le compte de SMOPAYE vient d\'être crébité d\'un montant de '.tarif::getServiceCharge(). 'fcfa suite a une opération de TOGGLE UNITY-DEPOSIT n° '.card::getCode_number().' et votre nouveau solde est de '.$sender['amount'].' fcfa';
                                $card["notif"] = 'votre compte '.card::getAction().' a été crediter de '.card::getWithDrawalAmount().' avec pour frais de service de '.tarif::getServiceCharge().' fcfa';
                            }else{
                                $card["notif"] = 'votre compte '.card::getAction().' a été crediter de '.card::getWithDrawalAmount();
                            }
                            return $this->sendResponse(new Resource($sender), $card);

                        }else{

                            return $this->sendError('votre solde '.card::getAction().' Insuffisant !!!');

                        }

                        break;



                    case false:

                        if(card::getCard_state() == 'activer'){

                            return $this->sendError('vous n\'avez soucrit aucun forfait');

                        }else{

                            return $this->sendError('votre carte est désactiver');

                        }

                        break;

                }

                break;

            default:

                return $this->sendError($compte['notif']);

                break;

        }

    }



    public function findUserCard($code_number){

        $card = card::findUserCard(null,$code_number);

        if (empty($card))

            return $this->sendError('la carte n\'existe pas.');

        return $this->sendResponse(new Resource($card), 'la carte a été trouver avec succés.');

    }

}
