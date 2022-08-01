<?php

namespace App\Http\Controllers\Api;

use App\Models\Card;
use App\Models\Compte;
use App\Models\Compte_subscription;
use App\Http\Controllers\Controller;
use App\Models\Particulier;
use App\Models\Subscription as subscription;
use App\Models\User;
use Illuminate\Http\Request;

class reverseUserController extends BaseController
{
    /**
     * instegration des anciens  utilisateurs dans la base de donnee e-zpass
     *
     *
     */
    public function create($request)
    {
        // creation du compte
        $compte = New Compte();
        $account_compt= User::generatepassword(9);
        $compte->account_number = $account_compt;
        //$compte->principal_account_id = Auth::guard('api')->user()->compte_id;
        $compte->save();
        // creation de l'utilisateur
        $user = new User();
        $user->phone = $request['phone'];
        $user->address = $request['address'];
        $user->password = bcrypt($request['password']);
        $user->category_id = $request['category_id'];
        $user->role_id = $request['role_id'];
        //$user->parent_id = Auth::guard('api')->user()->id;
        //$user->created_by = Auth::guard('api')->user()->id;
        $user->created_at = $request['created_at'];
        $user->compte_id = $compte->id;
        $user->save();

        $particulier = new Particulier();
        $particulier->firstname = $request['firstname'];
        $particulier->lastname = $request['lastname'];
        $particulier->gender = $request['gender'];
        $particulier->cni = $request['cni'];
        $particulier->user_id = $user->id;
        $particulier->save();

        // inserertion de l'abonnement service
        Compte_subscription::create(
            ['compte_id'=>$user->compte_id,
                'subscription_id'=> subscription::where('name','service')->first()->id,
                'starting_date'=>date("Y-m-d H:i:s"),
                'subscriptionCharge'=>'0',
                'transaction_number'=>'0',
                'subscription_type' =>'service',
                'end_date'=>'0000-00-00 00:00:00']);


        //recuperation de l'user enregistre
        $userSaved = User::where('phone', $request['phone'])->first();
        $id_user = $userSaved->id;

        //creation de la carte de l'user

        $card_number = $request['code_number'];
        $card_infos = Card::where('code_number', $card_number)->first();
        $id_card = $card_infos->id;
        // ici on met a jour l'id de l'utilisateur dans la table de sa carte
        $card = Card::where('id', $id_card)->update(['user_id' => $id_user]);
        // return $this->sendResponse(new Resource([]), $message);
        $token = $user->createToken('newToken')->accessToken;

    }

    public function convertion(){
        $data1 = array(
            array('NOM' => 'belane','PRENOM' => 'isabelle','GENRE' => 'FEMININ','Adresse' => 'soa','CNI' => 'CNI-pièce','pwd' => '50317','CARDN' => 'CCAFA547','IDCathegorie' => '42','id_session' => '1','TELEPHONE' => '676719569','created_at' => '2020-08-12 15:11:57'),
            array('NOM' => 'awono','PRENOM' => 'catherine','GENRE' => 'FEMININ','Adresse' => 'soa','CNI' => 'CNI-pièce','pwd' => '32551','CARDN' => 'FCAFD547','IDCathegorie' => '42','id_session' => '1','TELEPHONE' => '656671015','created_at' => '2020-08-12 15:18:43'),
            array('NOM' => 'mouzong','PRENOM' => 'auréole','GENRE' => 'MASCULIN','Adresse' => 'soa','CNI' => 'CNI-pièce','pwd' => '92088','CARDN' => 'BBDE5475','IDCathegorie' => '42','id_session' => '1','TELEPHONE' => '659476985','created_at' => '2020-08-12 15:24:10'),
            array('NOM' => 'nga ngono','PRENOM' => 'achille','GENRE' => 'MASCULIN','Adresse' => 'soa','CNI' => 'CNI-118838399','pwd' => '99726','CARDN' => 'C5189598','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '659039686','created_at' => '2020-08-12 18:17:43'),
            array('NOM' => 'gherapou','PRENOM' => 'hahichatou','GENRE' => 'FEMININ','Adresse' => 'ngousso','CNI' => 'CNI-piece','pwd' => '11294','CARDN' => 'BAFCFFDF','IDCathegorie' => '42','id_session' => '1','TELEPHONE' => '653150381','created_at' => '2020-08-13 09:51:22'),
            array('NOM' => 'ndjama','PRENOM' => 'eugene salvadore','GENRE' => 'MASCULIN','Adresse' => 'soa','CNI' => 'CNI-111364134','pwd' => '49166','CARDN' => 'BCDAC365','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '693743600','created_at' => '2020-08-13 13:32:53'),
            array('NOM' => 'minkoulou','PRENOM' => 'emery','GENRE' => 'MASCULIN','Adresse' => 'yaounde','CNI' => 'CNI-000480386','pwd' => '36322','CARDN' => 'B5201327','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '694410761','created_at' => '2020-08-13 20:53:11'),
            array('NOM' => 'bedzeme','PRENOM' => 'béatrice mireille','GENRE' => 'FEMININ','Adresse' => 'nsimelong','CNI' => 'CNI-pièce','pwd' => '93936','CARDN' => 'ECAAAABF','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '699103168','created_at' => '2020-08-14 07:42:20'),
            array('NOM' => 'bourbossima','PRENOM' => 'emmanuel','GENRE' => 'MASCULIN','Adresse' => 'soa','CNI' => 'recipissé-KIT 149','pwd' => '24104','CARDN' => 'FBADAE52','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '697110801','created_at' => '2020-08-15 17:11:04'),
            array('NOM' => 'bassa','PRENOM' => 'marie thérèse','GENRE' => 'FEMININ','Adresse' => 'barrière','CNI' => 'CNI-pièce','pwd' => '80634','CARDN' => 'FAFADACC','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '677737413','created_at' => '2020-08-17 16:06:00'),
            array('NOM' => 'mbang ossomba','PRENOM' => 'marie goretti','GENRE' => 'FEMININ','Adresse' => 'barrière','CNI' => 'CNI-pièce','pwd' => '23473','CARDN' => 'ECDEBC47','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '657928949','created_at' => '2020-08-17 16:14:09'),
            array('NOM' => 'zibi','PRENOM' => 'dylan','GENRE' => 'MASCULIN','Adresse' => 'barrière','CNI' => 'CNI-pièce','pwd' => '66356','CARDN' => 'ABCCFBBD','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '657190356','created_at' => '2020-08-17 16:16:05'),
            array('NOM' => 'ntsa nkouma','PRENOM' => 'jules alain','GENRE' => 'MASCULIN','Adresse' => 'nsam','CNI' => 'CNI-piece','pwd' => '73151','CARDN' => 'D5925192','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '694132709','created_at' => '2020-08-19 07:10:05'),
            array('NOM' => 'melingui','PRENOM' => 'marie','GENRE' => 'MASCULIN','Adresse' => 'eleveur','CNI' => 'CNI-Cni','pwd' => '40317','CARDN' => 'FCB46739','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '695572379','created_at' => '2020-08-19 07:32:42'),
            array('NOM' => 'yaka epanlo','PRENOM' => 'patricia stéphanie','GENRE' => 'FEMININ','Adresse' => 'biyemassi','CNI' => 'CNI-100022484','pwd' => '97043','CARDN' => 'BCBFB467','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '693218452','created_at' => '2020-08-19 09:38:20'),
            array('NOM' => 'boyomo','PRENOM' => 'junior franck jordan','GENRE' => 'MASCULIN','Adresse' => 'nsam','CNI' => 'CNI-pièce','pwd' => '82094','CARDN' => 'FDEFB495','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '655269417','created_at' => '2020-08-19 12:44:12'),
            array('NOM' => 'tabi','PRENOM' => 'pascaline stéphane','GENRE' => 'FEMININ','Adresse' => 'damas','CNI' => 'CNI-pièce','pwd' => '49040','CARDN' => 'CCB47848','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '659295301','created_at' => '2020-08-20 19:25:35'),
            array('NOM' => 'anaba','PRENOM' => 'ulrich','GENRE' => 'MASCULIN','Adresse' => 'soa','CNI' => 'CNI-piece','pwd' => '38777','CARDN' => 'ACDDCA48','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '698837195','created_at' => '2020-08-21 10:54:25'),
            array('NOM' => 'engama enama','PRENOM' => 'rodrigue   lazare','GENRE' => 'MASCULIN','Adresse' => 'soa','CNI' => 'recipissé-KIT282','pwd' => '47780','CARDN' => 'BFB43727','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '665258160','created_at' => '2020-08-21 10:56:05'),
            array('NOM' => 'kandem','PRENOM' => 'gaelle','GENRE' => 'FEMININ','Adresse' => 'soa','CNI' => 'CNI-piece','pwd' => '72552','CARDN' => 'DAEC5930','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '699674372','created_at' => '2020-08-21 14:36:53'),
            array('NOM' => 'assiang','PRENOM' => 'rodrigue','GENRE' => 'MASCULIN','Adresse' => 'soa','CNI' => 'CNI-piece','pwd' => '62035','CARDN' => 'E4473796','IDCathegorie' => '42','id_session' => '1','TELEPHONE' => '693556777','created_at' => '2020-08-21 15:40:13'),
            array('NOM' => 'kana','PRENOM' => 'christian','GENRE' => 'MASCULIN','Adresse' => 'soa','CNI' => 'CNI-piece','pwd' => '89481','CARDN' => 'BBEECED4','IDCathegorie' => '42','id_session' => '1','TELEPHONE' => '690440346','created_at' => '2020-08-21 16:10:58'),
            array('NOM' => 'mpok','PRENOM' => 'remy serge','GENRE' => 'MASCULIN','Adresse' => 'soa','CNI' => 'CNI-115024638','pwd' => '32469','CARDN' => 'BACFDBE5','IDCathegorie' => '42','id_session' => '1','TELEPHONE' => '691159290','created_at' => '2020-08-24 14:16:12'),
            array('NOM' => 'nyala nke','PRENOM' => 'therese nancy','GENRE' => 'FEMININ','Adresse' => 'soa','CNI' => 'CNI-100370038','pwd' => '32835','CARDN' => 'BBDBAB56','IDCathegorie' => '42','id_session' => '1','TELEPHONE' => '656903741','created_at' => '2020-08-24 15:57:36'),
            array('NOM' => 'mbengue','PRENOM' => 'joseph maxime','GENRE' => 'MASCULIN','Adresse' => 'soa','CNI' => 'passeport-PS 0793022','pwd' => '47050','CARDN' => 'ACCF4679','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '690261678','created_at' => '2020-08-25 09:08:40'),
            array('NOM' => 'biloa','PRENOM' => 'jean francois','GENRE' => 'MASCULIN','Adresse' => 'soa','CNI' => 'CNI-piece','pwd' => '24724','CARDN' => 'BCECCBF5','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '694937566','created_at' => '2020-08-25 09:12:57'),
            array('NOM' => 'zanga nsom','PRENOM' => 'samuel','GENRE' => 'MASCULIN','Adresse' => 'soa','CNI' => 'recipissé-kit149','pwd' => '63494','CARDN' => 'ABFDD510','IDCathegorie' => '42','id_session' => '1','TELEPHONE' => '696223503','created_at' => '2020-08-25 09:59:46'),
            array('NOM' => 'fokam tamko','PRENOM' => 'stephane','GENRE' => 'MASCULIN','Adresse' => 'melen','CNI' => 'CNI-114853928','pwd' => '88930','CARDN' => 'FBBBBDBE','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '695660689','created_at' => '2020-08-26 08:24:28'),
            array('NOM' => 'nzhiou','PRENOM' => 'etienne','GENRE' => 'MASCULIN','Adresse' => 'soa','CNI' => 'CNI-112607541','pwd' => '89533','CARDN' => 'ADCEAEDE','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '690961085','created_at' => '2020-08-31 12:59:49'),
            array('NOM' => 'minkoua','PRENOM' => 'pierrot','GENRE' => 'MASCULIN','Adresse' => 'soa','CNI' => 'CNI-116865274','pwd' => '46192','CARDN' => 'BBABEABB','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '691217933','created_at' => '2020-08-31 13:03:55'),
            array('NOM' => 'nkou mveng','PRENOM' => 'mirielle','GENRE' => 'FEMININ','Adresse' => 'soa','CNI' => 'CNI-Cni','pwd' => '48367','CARDN' => 'DCADEB42','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '694875440','created_at' => '2020-08-31 18:12:39'),
            array('NOM' => 'kinang','PRENOM' => 'derick','GENRE' => 'MASCULIN','Adresse' => 'soa','CNI' => 'CNI-100885614','pwd' => '10360','CARDN' => 'B3661824','IDCathegorie' => '41','id_session' => '1','TELEPHONE' => '676004401','created_at' => '2020-08-31 18:58:34'),
            array('NOM' => 'alo','PRENOM' => 'vincentdepaul','GENRE' => 'MASCULIN','Adresse' => 'soa','CNI' => 'CNI-cni','pwd' => '37513','CARDN' => 'FBCCB469','IDCathegorie' => '51','id_session' => '1','TELEPHONE' => '699005525','created_at' => '2020-09-04 08:53:16')
        );

        $data2 = [];
        for ($i=0; $i <count($data1) ; $i++) {
            foreach ($data1[$i] as $key => $value) {
                switch ($key) {
                    case 'NOM':
                        $data2['lastname'] = $value;
                        break;

                    case 'PRENOM':
                        $data2['firstname'] = $value;
                        break;

                    case 'GENRE':
                        $data2['gender'] = $value;
                        break;

                    case 'Adresse':
                        $data2['address'] = $value;
                        break;

                    case 'CNI':
                        $data2['cni'] = $value;
                        break;

                    case 'pwd':
                        $data2['password'] = $value;
                        break;

                    case 'CARDN':
                        $data2['code_number'] = $value;
                        break;

                    case 'IDCathegorie':
                        $data2['category_id'] = $value;
                        break;

                    case 'id_session':
                        $data2['role_id'] = $value;
                        break;

                    case 'TELEPHONE':
                        $data2['phone'] = $value;
                        break;
                    case 'created_at':
                        $data2['created_at'] = $value;
                        break;
                }
            }
            $this->create($data2);
        }
    }

}

