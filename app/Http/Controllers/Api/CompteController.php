<?php

namespace App\Http\Controllers\Api;

use App\Models\Card as card;
use App\Models\Compte as compte;
use App\Http\Controllers\Controller;
use App\Http\Resources\Index as Resource;
use App\Models\Tarif as tarif;
use App\Models\Transaction as transaction;
use App\Models\User as user;
use Illuminate\Http\Request;

class CompteController extends BaseController{



    /**



     * Display a listing of the resource.



     *



     * @return Response



     */



    public function index()



    {



        $compte = compte::getAllAccount();



        return $this->sendResponse(Resource::collection($compte), 'les comptes ont été renvoyer avec succés.');



    }







    /**



     * Display the specified resource.



     *



     * @param  int  $id



     * @return Response



     */



    public function show($account_id){
        $compte = compte::showAccount($account_id);
        if (is_null($compte))
            return $this->sendError('la compte n\'existe pas.');
        return $this->sendResponse(new Resource($compte), 'le compte a été trouvée avec succés.');
    }







    /**



     * Update the specified resource in storage.



     *



     */



    public function update(Request $request)



    {



        $input = $request->all();



        $validator = $this->validation($input, [



            'account_number' => 'required'



        ]);







        if($validator->fails())



            return $this->sendError('Erreur de validation.', $validator->errors());







        compte::hydratation((array) $input);



        $compte = compte::updateAccount($account_number);



        if($compte['status'] == 1){



            return $this->sendResponse(new Resource($compte['compte']), 'votre compte a été modifier avec succés.');



        }else{



            return $this->sendError($compte['notif']);



        }



    }







    /**



     * cette fonction permet de supprimer la resource dans la BD.



     */



    public function destroy($account_number)



    {



        $compte = compte::deleteCompte($account_number);



        if($compte['status'] == 1){



            return $this->sendResponse($compte['compte'], 'la compte a été supprimer avec succés.');



        }else{



            return $this->sendError($compte['notif']);



        }



    }











    /**



     * <<=== //// RECHARGE CARTE VIA COMPTE //// ===>>



     * cette fonction permet de faire un retrait ou debit sur son compte pour recharger la carte



     * @param Request $request



     * @return Response



     */



    public function rechargeCarteViaCompte(Request $request, Compte $compte, $compte_number){

        transaction::setStarting_date(date('Y-m-d h:i:s', time()));

        transaction::setTransaction_number('SMP'.date('ymd').strtotime('now'));

        transaction::setId('SMP'.date('ymd').strtotime('now'));

        $compte = compte::findAccount($compte_number);



        compte::hydratation($compte['account']);



        switch ($compte['status']) {



            case 1:



                $input = $request->all();



                $valid = $this->validation($input, [



                    'withDrawalAmount' => 'required|numeric',



                    'code_number'=>'required',



                ]);



                if($valid->fails())



                    return $this->sendError('Erreur de validation.', $valid->errors());



                compte::hydratation($input);



                $card = card::findCodeNumberCard(compte::getCode_number());



                if(array_key_exists('status', $card) and $card['status'] != 1)



                    return $this->sendError($card['notif']);



                switch (compte::getWithDrawalAmount() <= compte::getAmount()) {



                    case true:



                        compte::setAmount(compte::getAmount() - compte::getWithDrawalAmount());



                        $compte = compte::updateAmount($compte['object']);



                        $sender = compte::setAccountWithPhone('673003170',tarif::getServiceCharge());



                        $sender['notif'] = 'le compte de SMOPAYE vient d\'être crébité d\'un montant de '.tarif::getServiceCharge(). 'fcfa suite a une opération de debit du compte n° '.$compte['account_number'].' et votre nouveau solde est de '.$sender['amount'].' fcfa';



                        card::setUnity(card::getUnity() + compte::getWithDrawalAmount());



                        $card = card::updateUnity(card::find(card::getId()));



                        $compte['notif'] = 'votre compte vient d\'être débité d\'un montant de '.compte::getWithDrawalAmount().' fcfa avec des frais de service de '.tarif::getServiceCharge().' fcfa et le nouveau solde du compte est de '.$compte['amount'].' fcfa';



                        $card['notif'] = 'succés de l\'opération, le solde depot de la carte n° '.$card['code_number'].' a été crédité d\'un montant de '.compte::getWithDrawalAmount().' fcfa';



                        transaction::setAccount_id_sender(compte::getId());

                        transaction::setAccount_id_receiver(compte::getId());

                        transaction::setCard_id_receiver(card::getId());



                        transaction::setTransaction_type('RECHARGE_CARTE_VIA_COMPTE');



                        transaction::setAmount(compte::getWithDrawalAmount());



                        transaction::setState("SUCCESS");



                        transaction::setServicecharge(tarif::getServiceCharge());



                        transaction::setEnd_date(date('Y-m-d h:i:s', time()));



                        transaction::createTransaction(transaction::prepareDataToSave());



                        transaction::afterDataToSave();



                        compte::afterDataToSave();



                        return $this->sendResponse(new Resource(['receiver'=>$sender,'sender'=>$compte]),$card);



                        break;



                    case false:



                        compte::afterDataToSave();



                        return $this->sendError('le solde de compte est insuffisant pour effectuer cette transaction');



                        break;



                }



                break;



            default:



                return $this->sendError($compte['notif']);



                break;



        }



    }







    /**



     * * <<=== //// POST RECHERGE ACCOUNT //// ===>>



     * cette fonction permet de recharge le compte a partir du compte monetbil



     * l'entreprise smopaye



     * @param Request $request



     * @param card $compte



     * @param $compte_id



     * @return Response



     */



    public function postRechargeAccount(Request $request, Compte $compte, $compte_number){



        transaction::setStarting_date(date('Y-m-d h:i:s', time()));

        transaction::setTransaction_number('SMP'.date('ymd').strtotime('now'));

        transaction::setId('SMP'.date('ymd').strtotime('now'));

        $input = $request->all();



        $compte = compte::findAccount($compte_number);



        if($compte['status'] == 1){



            $validator = $this->validation($request->all(),[



                'amount' => 'required|numeric',



                'phoneNumber'=>'required',



            ]);



            if($validator->fails())



                return $this->sendError('Erreur de validation.', $validator->errors());



            compte::hydratation((array) $input);



            transaction::setAccount_id_sender($compte['account']['id']);

            transaction::setAccount_id_receiver($compte['account']['id']);



            transaction::setTransaction_type('RECHARGE_COMPTE_VIA_MONETBIL');



            transaction::setAmount(compte::getAmount());



            $monetbilMsg = compte::placeRechargeAccount();



            if($monetbilMsg != null){



                transaction::setState($monetbilMsg['message']);



            }else{



                transaction::setState("ECHEC_MONETBIL_INDISPONIBLE");



            }



            transaction::setEnd_date(date('Y-m-d h:i:s', time()));



            transaction::createTransaction(transaction::prepareDataToSave());



            if(!$monetbilMsg)



                return $this->sendError("Echec de l'opération l'operateur est indisponible");



            return $this->sendResponse(new Resource($compte['account']), $monetbilMsg);



        }else{



            return $this->sendError($compte['notif']);



        }



    }







    /**



     * @param Request $request



     * @param card $compte



     * @param $compte_id



     * @return Response



     */



    public function validateRechargeAccount(Request $request, Card $compte, $compte_number){



        transaction::setStarting_date(date('Y-m-d h:i:s', time()));

        transaction::setTransaction_number('SMP'.date('ymd').strtotime('now'));

        transaction::setId('SMP'.date('ymd').strtotime('now'));

        $input = $request->all();

        $compte = compte::findAccount($compte_number);

        if($compte['status'] != 1)

            return $this->sendError($compte['notif']);



        $validator = $this->validation($input, [



            'paymentId' => 'required|numeric'



        ]);



        if($validator->fails())



            return $this->sendError('Erreur de validation.', $validator->errors());



        compte::hydratation($input);

        $ckeck = compte::checkPayment(compte::getPaymentId(), $compte['account']['id']);

        if(!array_key_exists('status', $ckeck))

            return $this->sendError($ckeck['message']);



        switch ($ckeck['status']) {

            case 1:



                compte::setAmount($compte['account']['amount'] + $ckeck['amount']);



                $compte = compte::updateAmount($compte['object']);







                if($compte){



                    //$this->API_AVS($compte['account']['user']['phone'],'Chers Mr/Mss '.''.card::findUserCard()['lastname'].''.' votre compte SMOPAYE a été credité d\'un montant de '.$ckeck['amount'].' et votre nouveau solde est '.card::getDeposit().' fcfa.');



                    return $this->sendResponse(new Resource($compte), $ckeck['message'].', votre nouveau solde est de '.compte::getAmount().' fcfa');



                }else{



                    return $this->sendResponse(new Resource($compte), 'Erreur serveur!!! contacter le service client pour entrer en possession de votre recharge');



                }



                break;







            case 0:



                return $this->sendResponse(new Resource($compte), $ckeck['message']);



                break;







            default:



                return $this->sendError($ckeck['message']);



                break;



        }



    }







    /**



     * cette fonction permet de modifier le champs etat carte de la table user card de la base de donnée.



     * son but est remplacer activer par desactiver



     *



     * @param  int  $compte_id



     * user_card correspond l'identifiant de la carte



     * @return Response



     */



    public function activation(Request $request, $account_id)



    {



        /** @var array $request */



        $input = $request->all();



        $validator = $this->validation($input, [



            'account_state' => 'required|in:activer,desactiver'



        ]);



        if($validator->fails()){



            return $this->sendError('Erreur de validation.', $validator->errors());



        }



        compte::hydratation($input);



        $response = compte::activationCompte($account_id);



        switch ($response['status']) {



            case 1:



                return $this->sendResponse(new Resource($response['account']), 'votre compte est '.compte::getAccount_state());



                break;







            default:



                return $this->sendError($response['notif']);



                break;



        }



    }







    /**



     * <<=== //// RETRAIT MONETBIL ET RECHARGE CARTE //// ===>>



     * cette fonction permet de faire un retrait ou debit sur une carte



     * @param Request $request



     * @return Response



     */



    public function retraitCompte(Request $request, $account_number){



        transaction::setStarting_date(date('Y-m-d h:i:s', time()));

        transaction::setTransaction_number('SMP'.date('ymd').strtotime('now'));

        transaction::setId('SMP'.date('ymd').strtotime('now'));



        $compte = compte::findAccount($account_number);
        switch ($compte['status']) {



            case 1:



                $input = $request->all();



                $valid = $this->validation($input, [



                    'withDrawalAmount' => 'required|numeric',



                    'phoneNumber'=>'required',



                ]);



                if($valid->fails()){
                    return $this->sendError('Erreur de validation.', $valid->errors());
                }
                compte::hydratation($compte['account']);
                compte::hydratation((array) $input);
                $sender = user::where('compte_id', compte::getId())->first();
                //$amountWithDraw = CompteSubscription::getAmountSubcription(compte::getWithDrawalAmount(), $sender);
                $tarif_charge =  (compte::getWithDrawalAmount() * 2)/100;
                $amountWithDraw =  compte::getWithDrawalAmount() + $tarif_charge;
                tarif::setServiceCharge($tarif_charge);
                switch ($amountWithDraw != false and $amountWithDraw < compte::getAmount()){



                    case true:



                        $monetbilMsg = card::cardWithDrawal(compte::getWithDrawalAmount(), compte::getPhoneNumber());

                        if($monetbilMsg['success']){



                            compte::setAmount(compte::getAmount() - $amountWithDraw);



                            $compte = compte::updateAmount($compte['object']);



                            $compte['notif'] = 'succés de l\'opération, le solde du compte n° '.$compte['account_number'].' a été débité d\'un montant de '.$amountWithDraw.' fcfa';







                            if(tarif::getServiceCharge() != 0){



                                $sender = compte::setAccountWithPhone('673003170',tarif::getServiceCharge());



                                $sender['notif'] = 'le compte de SMOPAYE vient d\'être crébité d\'un montant de '.tarif::getServiceCharge(). 'fcfa suite a une opération de RETRAIT VIA OPERATEUR SUR LA CARTE n° '.card::getCode_number().' et votre nouveau solde est de '.$sender['amount'].' fcfa';



                            }



                            transaction::setAccount_id_sender(compte::getId());



                            transaction::setAccount_id_receiver(compte::getId());



                            transaction::setTransaction_type('RETRAIT_COMPTE_VIA_MONETBIL');



                            transaction::setAmount(compte::getWithDrawalAmount());



                            transaction::setState("SUCCESS");



                            if(tarif::getServiceCharge() != 0)



                                transaction::setServicecharge(tarif::getServiceCharge());



                            transaction::setEnd_date(date('Y-m-d h:i:s', time()));



                            transaction::createTransaction(transaction::prepareDataToSave());



                            transaction::afterDataToSave();



                            compte::afterDataToSave();



                            return $this->sendResponse(new Resource($sender),['sender'=>$compte,'notif'=>'succes de l\'opération']);



                        }else{

                            transaction::setAccount_id_sender(compte::getId());



                            transaction::setAccount_id_receiver(compte::getId());



                            transaction::setTransaction_type('RETRAIT_COMPTE_VIA_MONETBIL');



                            transaction::setAmount(compte::getAmount());



                            transaction::setState("Echec");



                            if(tarif::getServiceCharge() != 0)



                                transaction::setServicecharge(tarif::getServiceCharge());



                            transaction::setEnd_date(date('Y-m-d h:i:s', time()));



                            transaction::createTransaction(transaction::prepareDataToSave());



                            transaction::afterDataToSave();

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







}

