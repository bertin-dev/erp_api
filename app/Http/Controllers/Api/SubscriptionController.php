<?php

namespace App\Http\Controllers\Api;

use App\Models\Compte;
use App\Models\Compte_subscription;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Http\Resources\Index as Resource;

class SubscriptionController extends BaseController {







    /**



     * Display a listing of the resource.



     *



     * @return \Illuminate\Http\Response



     */



    public function index() {



        return Subscription::all();



    }







    /**



     * Store a newly created resource in storage.



     *



     * @param  \Illuminate\Http\Request  $request



     * @return \Illuminate\Http\Response



     */



    public function store(Request $request) {



        $input=$request->all();











        $validator=Validator::make($input, [ 'name'=> 'required',



            'subscription_fees'=> 'required|numeric',



            'periode_abon'=>'required|numeric',



        ]);







        if($validator->fails()) {



            return $this->sendError('Erreur de validation.', $validator->errors());



        }







        else {



            $subscription=Subscription::createSubscription($input);



            return $this->sendResponse(new SubscriptionResource($subscription), 'la soubscription a été créer avec succés.');



        }



    }







    /**



     * Display the specified resource.



     *



     * @param  int  $id



     * @return \Illuminate\Http\Response



     */



    public function show($id) {



        //



    }







    /**



     * Update the specified resource in storage.



     *



     * @param  \Illuminate\Http\Request  $request



     * @param  int  $id



     * @return \Illuminate\Http\Response



     */



    public function update(Request $request, $id) {



        //



    }







    /**



     * Remove the specified resource from storage.



     *



     * @param  int  $id



     * @return \Illuminate\Http\Response



     */



    public function destroy($id) {}







    public function takeSubscription (Request $request,$compte_id) {

        $input=$request->all();

        $valid =  $this->validation($input, ['type'=> 'required|in:semaine,mensuel,service']);

        if($valid->fails()) {

            return $this->sendError('Erreur de validation.', $valid->errors());

        }

        $compte = Compte::showAccount($compte_id);
        if($compte){

            $type_abons = Subscription::where('name',$request->type)->get();

            foreach ($type_abons as $type_abon) {

                $abon_id = $type_abon->id;

                $abon_type = $type_abon->name;

                $abon_fees = $type_abon->subscription_fees;

                $periode_abon =$type_abon->periode_abon;

                $subcription_charge =$type_abon->subscription_fees;

            }

            $nombre_transMonth = 60;

            $nombre_transweek = 15;

            if($abon_type == $request->type && $request->type !='service'){

                if(Compte::getAmount() >=$abon_fees ) {

                    $dateNow=date("Y-m-d H:i:s");

                    $objetabon=new DateTime($dateNow);

                    $objetabon->add(new DateInterval('P'.$periode_abon.'D'));

                    $endDate=$objetabon->format('Y-m-d H:i:s');

                    Compte::setAmount(Compte::getAmount() - $abon_fees);

                    Compte::updateAmount($compte);

                    $transaction_number = $request->type  ==='mensuel' ? $nombre_transMonth : $nombre_transweek;

                    $subscription =Compte_subscription::create([

                        'compte_id'=>$compte_id,

                        'subscription_id'=>$abon_id,

                        'starting_date'=>$dateNow,

                        'end_date'=>$endDate,

                        'subscription_type'=> $abon_type,

                        'transaction_number' => $transaction_number,

                        'subscriptionCharge'=>  $subcription_charge,

                    ]);

                    if($subscription->save()){
                        Compte::setAccountWithPhone($this->getSmopaye_phone(),$subcription_charge);
                        return $this->sendResponse(new Resource($compte), 'vous avez souscrit à l\'abonnement  '.$request->type. ' avec succes !!!');

                    }

                }else {

                    return $this->sendError('Erreur:', 'votre solde est Insuffisant !!!');

                }

            }else if ($abon_type==='service'){

                $dateNow=date("Y-m-d H:i:s");

                $subscription=Compte_subscription::create([ 'compte_id'=>$compte_id,

                    'subscription_id'=>$abon_id,

                    'starting_date'=>$dateNow,

                    'end_date'=>'0000-00-00 00:00:00',

                ]);



                return $this->sendResponse(new Resource($compte), 'vous avez souscrit à l\'abonnement ' . $request->type. ' avec succes !!!');

            }

        }else{

            return $this->sendError('compte non trouvé','Erreur:');

        }

    }



}




