<?php

namespace App\Models;

use App\Models\Campaign as campaign;
use App\Models\Card as card;
use App\Models\Category as categorie;
use App\Models\Compte;
use App\Models\Compte_subscription as CompteSubscription;
use App\Models\Device as device;
use App\Http\Controllers\API\BaseController as br;
use App\Models\Role;
use App\Models\Tarif as tarif;
use App\Models\User;
use App\Models\User_device as user_device;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    private static $id;
    private static $starting_date;
    private static $end_date;
    private static $card_id_sender;
    private static $card_id_receiver;
    private static $account_number_sender;
    private static $account_number_receiver;
    private static $account_id_sender;
    private static $account_id_receiver;
    private static $transaction_type;
    private static $transaction_number;
    private static $amount;
    private static $amountDiscount = null;
    private static $device_id;
    private static $state;
    private static $operator;
    private static $paymentId;
    private static $tarif_grid_id;
    private static $code_number_sender;
    private static $code_number_receiver;
    private static $serial_number_device;
    private static $servicecharge;
    private static $remise = null;
    private static $arrayCardSubscription;
    private static $dataItem = array(


        'id' => '',


        'starting_date' => '',


        'end_date' => '',


        'card_id_sender' => '',


        'card_id_receiver' => '',


        'account_id_sender' => '',


        'account_id_receiver' => '',


        'transaction_type' => '',


        'transaction_number' => '',


        'amount' => '',


        'device_id' => '',


        'state' => '',


        'operator' => '',


        'paymentId' => '',


        'tarif_grid_id' => '',


        'servicecharge' => ''


    );
    protected $fillable =


        [


            'id',


            'starting_date',


            'transaction_number',


            'end_date',


            'card_id_sender',


            'card_id_receiver',


            'account_id_sender',


            'account_id_receiver',


            'transaction_type',


            'operator',


            'amount',


            'device_id',


            'state',


            'tarif_grid_id',


            'servicecharge'


        ];

    public static function hydratation($data)
    {


        foreach ($data as $key => $valeur) {


            $method = "set" . ucfirst($key);


            if (method_exists(__CLASS__, $method)) {


                self::$method($valeur);


            }


        }


    }

    public static function prepareDataToSave()
    {


        $data = [];


        foreach (self::$dataItem as $key => $value) {


            $method = "get" . ucfirst($key);


            if (method_exists(__CLASS__, $method)) {


                $data[$key] = self::$method();


            }


        }


        return $data;


    }

    public static function getId()
    {


        return self::$id;


    }

    public static function setId($id)
    {


        self::$id = $id;


    }

    public static function getStarting_date()
    {


        return self::$starting_date;


    }

    public static function setStarting_date($starting_date)
    {


        if (is_null(self::getStarting_date())) {


            self::$starting_date = $starting_date;


        }


    }

    public static function getEnd_date()
    {


        return self::$end_date;


    }

    public static function setEnd_date($end_date)
    {


        self::$end_date = $end_date;


    }

    public static function getCard_id_sender()
    {


        return self::$card_id_sender;


    }

    public static function setCard_id_sender($card_id_sender)
    {


        self::$card_id_sender = $card_id_sender;

    }

    public static function getCard_id_receiver()
    {


        return self::$card_id_receiver;


    }

    public static function setCard_id_receiver($card_id_receiver)
    {


        self::$card_id_receiver = $card_id_receiver;


    }

    public static function getAccount_number_sender()
    {


        return self::$account_number_sender;


    }

    public static function setAccount_number_sender($account_number_sender)
    {


        self::$account_number_sender = $account_number_sender;


    }

    public static function getAccount_id_sender()
    {


        return self::$account_id_sender;


    }

    public static function setAccount_id_sender($account_id_sender)
    {


        self::$account_id_sender = $account_id_sender;


    }

    public static function getAccount_id_receiver()
    {


        return self::$account_id_receiver;


    }

    public static function setAccount_id_receiver($account_id_receiver)
    {


        self::$account_id_receiver = $account_id_receiver;


    }

    public static function getAccount_number_receiver()
    {


        return self::$account_number_receiver;


    }

    public static function setAccount_number_receiver($account_number_receiver)
    {


        self::$account_number_receiver = $account_number_receiver;


    }

    public static function getCode_number_sender()
    {


        return self::$code_number_sender;


    }

    public static function setCode_number_sender($code_number_sender)
    {


        self::$code_number_sender = $code_number_sender;


    }

    public static function getCode_number_receiver()
    {


        return self::$code_number_receiver;


    }

    public static function setCode_number_receiver($code_number_receiver)
    {


        self::$code_number_receiver = $code_number_receiver;


    }

    public static function getServicecharge()
    {


        return self::$servicecharge;


    }

    public static function setServicecharge($servicecharge)
    {


        self::$servicecharge = $servicecharge;


    }

    public static function getDevice_id()
    {


        return self::$device_id;


    }

    public static function setDevice_id($device_id)
    {


        self::$device_id = $device_id;


    }

    public static function getState()
    {


        return self::$state;


    }

    public static function setState($state)
    {


        self::$state = $state;


    }

    public static function getTarif_grid_id()
    {


        return self::$tarif_grid_id;


    }

    public static function setTarif_grid_id($tarif_grid_id)
    {


        self::$tarif_grid_id = $tarif_grid_id;


    }

    public static function getOperator()
    {


        return self::$operator;


    }

    public static function setOperator($operator)
    {


        self::$operator = $operator;


    }

    public static function getPaymentId()
    {


        return self::$paymentId;


    }

    public static function setPaymentId($paymentId)
    {


        self::$paymentId = $paymentId;


    }

    public static function getAllTransaction()
    {


        return self::all();


    }

    public static function createTransaction($transaction)
    {


        $transaction = self::create($transaction);


        if (!empty(self::$arrayCardSubscription)) {


            CompteSubscription::updateTransaction_number();


        }


        self::afterDataToSave();


        return $transaction;


    }

    public static function afterDataToSave()
    {


        foreach (self::$dataItem as $key => $value) {


            $value = '';


            $method = "set" . ucfirst($key);


            if (method_exists(__CLASS__, $method)) {


                self::$method($value);


            }


        }


    }

    public static function showTransaction($transaction_id)
    {


        return self::find($transaction_id);


    }

    public static function startRemoteCollectionTransaction($card, $device, $amountWithDraw)
    {


        $br = new br();


        card::hydratation($card['card']);


        device::hydratation($device);


        $possession = user_device::currentPossessionDevice(now(), card::getUser_id(), device::getId());


        if ($amountWithDraw != false and !empty($possession)) {


            self::setCard_id_sender(card::getId());


            self::setCard_id_receiver(card::getId());


            card::setDeposit(card::getDeposit() + (self::getAmount() - tarif::getServiceCharge()));


            self::setServicecharge(tarif::getServiceCharge());


            self::setDevice_id(device::getId());


            self::setTransaction_type('telecollecte');


            $br->API_AVS(card::findUserCard(card::getId())['phone'], 'votre compte SMOPAYE a été credité d\'un montant de ' . self::getAmount() . ' et votre nouveau solde est ' . card::getDeposit() . ' fcfa, suite a une transaction de' . self::getTransaction_type());


            return array('device' => $device, 'card_receiver' => $card['card'], 'status' => 1, 'notif' => 'transaction effectuée', 'service_charge' => tarif::getServiceCharge(), 'success' => card::updateDeposit(card::find(card::getId())));


        } else {


            return array('card' => $card['card'], 'device' => $device, 'status' => 0, 'notif' => $amountWithDraw ? 'votre solde unité est insuffisant pour effectué cette transaction' : 'aucun device disponible');


        }


    }

    public static function getAmount()
    {


        return self::$amount;


    }

    public static function setAmount($amount)
    {


        self::$amount = $amount;


    }

    public static function getTransaction_type()
    {


        return self::$transaction_type;


    }

    public static function setTransaction_type($transaction_type)
    {


        self::$transaction_type = $transaction_type;


    }

    /**
     * @param $sender
     * @param $receiver
     * @return array
     */


    public static function startTransaction($sender, $receiver, $amountWithDraw)
    {


        $user_id = $receiver['card']['user_id'];


        $compte = user::where('id', $user_id)->with(['compte', 'enterprise'])->first();


        if ($compte == null)


            return array('notif' => 'le compte recepteur n\'existe pas', 'status' => 0);


        $compteWithPhone = $compte->toArray();


        card::hydratation($sender['card']);


        self::setCard_id_sender(card::getId());


        switch (self::getTransaction_type()) {


            case 'DEPOT':

                if ($receiver['card']['user_id'] != null and !empty(Role::getRoleUserCard($receiver['card']['user_id'], 'point agrée'))) {

                    return self::placeStartTransaction($sender['card'], $receiver['card'], $amountWithDraw, new br());

                } else {

                    return array('card' => $receiver['card'], 'status' => 0, 'notif' => 'désolé le bénéficiare doit être un point agrée');

                }


                break;


            case 'DEBIT_ACHAT':

                $user = card::findUserCard($sender['card']['user_id']);

                $response = self::currentStudent_Check($user, $compte);

                if ($response["response"]) {

                    device::hydratation($response['res']);

                    tarif::setServiceCharge(15);

                    return self::placeStartTransaction($sender['card'], $compteWithPhone, $amountWithDraw, new br());

                } else {


                    tarif::setServiceCharge(0);

                    self::setAmount(0);


                    return self::placeStartTransaction($sender['card'], $compteWithPhone, 0, new br());

                }

                break;


            case 'DEBIT_CARTE':
                $res = device::findSerialNumberDevice(Transaction::getSerial_number_device());
                self::setDevice_id($res['id']);
                //dd(empty($compte->enterprise->toArray()));

                //if(empty($compte->enterprise->toArray()))

                //return array('notif'=>'Vous n\'êtes pas autorisé à utiliser cet appareil', 'status'=> 0);
                if (is_null($res))
                    return array('notif' => 'Terminal introuvable', 'status' => 0);

                $user_device = user_device::where([['user_id', $compte->enterprise[0]->id], ['device_id', $res['id']]])->first();
                if (is_null($user_device))
                    return array('notif' => 'Echec de paiement, ce terminal ne vous appartient pas.', 'status' => 0);
                device::hydratation($res);
                return self::placeStartTransaction($sender['card'], $compteWithPhone, $amountWithDraw, new br());
                break;


            case 'TRANSFERT_CARTE_A_CARTE':


                return self::placeStartTransactionTransfert($sender['card'], $receiver['card'], self::getAmount());


                break;


            case 'RETRAIT_SMOPAYE':


                return self::placeStartTransaction($sender['card'], $compteWithPhone, $amountWithDraw, new br(), 'Deposit', 'Deposit');


                break;


            default:


                return self::placeStartTransaction($sender['card'], $compteWithPhone, $amountWithDraw, new br());


                break;


        }


    }

    /**
     * @param $sender
     * @param $receiver
     * @param $amountWithDraw
     * @param $br instance de VBS
     * @param string $compte
     * @param string $compteSender
     * @return array
     */


    public static function placeStartTransaction($sender, $receiver, $amountWithDraw, $br, $compte = 'Deposit', $compteSender = 'Unity')
    {


        $set = 'set' . $compte;
        $get = 'get' . $compte;
        $update = 'update' . $compte;
        $setSender = 'set' . $compteSender;
        $getSender = 'get' . $compteSender;
        $updateSender = 'update' . $compteSender;


        if (($amountWithDraw != false and card::$getSender() > $amountWithDraw) || (self::getTransaction_type() and card::$getSender() > $amountWithDraw)) {


            card::$setSender(card::$getSender() - $amountWithDraw);


            switch (card::$updateSender(card::find(card::getId()))) {


                case true:


                    $msg = "";


                    if (tarif::getServiceCharge() != 0)


                        $msg = "frais de service: " . tarif::getServiceCharge() . " fcfa,";


                    if (self::getTransaction_type() == "DEBIT_CARTE" && self::getTransaction_type() == "PAYEMENT_VIA_QRCODE")
                        $br->API_AVS(card::findUserCard(card::getId())['phone'], 'Opération effectué avec succés, ' . self::getTransaction_type() . ':' . self::getAmount() . ' fcfa ' . $msg . ' Nouveau Solde: ' . card::$getSender() . 'fcfa ' . 'IDTransaction: ' . self::getTransaction_number() . ' Merci de faire confiance à SMOPAYE');


                    $sender = card::$update(card::find(card::getId()));


                    if (!is_null(self::getRemise())) {
                        $sender['remise'] = self::getRemise();
                    }

                    $sender['notif'] = 'Opération effectué avec succés, MONTANT:' . self::getAmount() . ' fcfa ' . self::getTransaction_type() . ' ' . $msg . ' Nouveau Solde: ' . card::$getSender() . 'fcfa ' . 'IDTransaction: ' . self::getTransaction_number() . ' Merci de faire confiance à SMOPAYE';


                    compte::hydratation($receiver['compte']);


                    self::setAccount_id_receiver(compte::getId());


                    compte::setAmount(compte::getAmount() + self::getAmount());


                    $receiver['compte'] = compte::updateAccount(compte::getAccount_number())['compte'];


                    if (self::getTransaction_type() == "DEBIT_CARTE" && self::getTransaction_type() == "PAYEMENT_VIA_QRCODE")


                        $br->API_AVS($receiver['phone'], self::getTransaction_type() . ' de la carte ' . $sender['code_number'] . ' reçu avec succés, MONTANT:' . self::getAmount() . ' fcfa, ' . ' Nouveau Solde: ' . compte::getAmount() . ' fcfa, IDTransaction: xxxxxx  Merci de faire confiance à SMOPAYE');


                    if (self::getTransaction_type() == "DEBIT_CARTE")


                        self::setDevice_id(device::getId());


                    $receiver['notif'] = self::getTransaction_type() . ' de la carte ' . $sender['code_number'] . ' reçu avec succés, MONTANT:' . self::getAmount() . ' fcfa, ' . ' Nouveau Solde: ' . compte::getAmount() . ' fcfa, IDTransaction: ' . self::getTransaction_number() . ', Merci de faire confiance à SMOPAYE';


                    self::setServicecharge(tarif::getServiceCharge());


                    return array('card_sender' => $sender, 'card_receiver' => $receiver, 'status' => 1);


                    break;


                case false:


                    return array('card_sender' => $sender, 'status' => 0, 'notif' => 'echec de la mise à jour du compte unité');


                    break;


            }


        } else {

            return array('card_sender' => $sender, 'status' => 0, 'notif' => ($amountWithDraw != false || $amountWithDraw == 0) ? 'votre solde unité est insuffisant pour effectué cette transaction' : 'aucune tarification disponible pour votre categorie, appellez le service client.');

        }


    }

    public static function getTransaction_number()
    {


        return self::$transaction_number;


    }

    public static function setTransaction_number($transaction_number)
    {


        self::$transaction_number = $transaction_number;


    }

    public static function getRemise()
    {

        return self::$remise;

    }


    /*







        public static function debitCarte ($card, $device){















            $br = new br();















            card::hydratation($card['card']);















            device::hydratation($device);







            $amountWithDraw = CompteSubscription::getAmountSubcription(compte::getWithDrawalAmount(), );















            if($amountWithDraw != false and card::$getSender() > $amountWithDraw) {















                card::$setSender(card::$getSender() - $amountWithDraw);















                switch (card::updateUnity(card::find(card::getId()))) {















                    case true:















                        //$br->API_AVS(card::findUserCard(card::getId())['phone'],'votre compte SMOPAYE a été debité d\'un montant de '.$amountWithDraw.' et votre nouveau solde est '.card::$getSender().' fcfa.');















                        self::setTransaction_type('debit carte');























                        self::setCard_id_sender(card::getId());















                        self::setCard_id_receiver(card::getId());















                        self::setServicecharge(tarif::getServiceCharge());















                        self::setDevice_id(device::getId());















                        return array('card'=>$card['card'], 'status' => 1, 'notif'=>'succés de la transaction');















                    break;































                    case false:















                         return array('card'=>$card['card'], 'status' => 0, 'notif'=>'echec de la mise à jour du compte unité');















                    break;















                }















            }else{















                return array('card'=>$card['card'], 'status' => 0,'notif'=> $amountWithDraw != false ? 'votre solde unité est insuffisant pour effectué cette transaction':'aucun forfait disponible.');















            }















        }*/

    public static function setRemise($remise)
    {

        self::$remise = $remise;

    }

    public static function currentStudent_Check($user, $compte)
    {

        $array_serial_number = ['1908G90043700363', '1908G90043700351', '1901G90002200006'];

        if (in_array(self::getSerial_number_device(), $array_serial_number)) {

            $res = device::findSerialNumberDevice(transaction::getSerial_number_device());

            self::setDevice_id($res['id']);

            //if(empty($compte->enterprise->toArray()))

            //return array('notif'=>'Vous n\'êtes pas autorisé à utiliser cet appareil', 'status'=> 0);

            if (is_null($res))

                return array('notif' => 'Terminal introuvable', 'status' => 0);

            $card = card::where('user_id', $user['id'])->first();

            self::setCard_id_sender($card->id);

            $transactions = self::whereDate('created_at', Carbon::today())->where('device_id', $res['id'])->where('card_id_sender', $card->id)->first();
            dd($transactions);
            if (date('d/m/Y', strtotime(Carbon::today())) == date('d/m/Y', strtotime($transactions['created_at']))) {

                $transaction_exists = true;

            } else {

                $transaction_exists = false;

            }

            return array('res' => $res, 'response' => $transaction_exists);

        } else {


        }

    }

    public static function getSerial_number_device()
    {


        return self::$serial_number_device;


    }

    public static function setSerial_number_device($serial_number_device)
    {


        self::$serial_number_device = $serial_number_device;


    }

    public static function placeStartTransactionTransfert($sender, $receiver, $amount, $type = "carte")
    {


        switch ($type) {


            case 'carte':


                if (card::getUnity() >= $amount) {


                    self::setCard_id_sender(card::getId());


                    card::setUnity(card::getUnity() - $amount);


                    $sender = card::updateUnity(card::find(card::getId()));


                    switch ($sender) {


                        case true:


                            $sender['notif'] = self::getTransaction_type() . ' de la carte ' . $sender['code_number'] . ' envoyer avec succés, MONTANT:' . $amount . ' fcfa, ' . ' Nouveau Solde: ' . card::getUnity() . ' fcfa, IDTransaction: xxxxxx  Merci de faire confiance à SMOPAYE';


                            card::hydratation($receiver);


                            self::setCard_id_receiver(card::getId());


                            card::setUnity(card::getUnity() + $amount);


                            $receiver = card::updateUnity(card::find(card::getId()));


                            $receiver['notif'] = self::getTransaction_type() . ' de la carte ' . $sender['code_number'] . ' reçu avec succés, MONTANT:' . $amount . ' fcfa, ' . ' Nouveau Solde: ' . card::getUnity() . ' fcfa, IDTransaction: xxxxxx  Merci de faire confiance à SMOPAYE';


                            return array('card_sender' => $sender, 'card_receiver' => $receiver, 'status' => 1);


                            break;


                        case false:


                            return array('card_sender' => $sender, 'status' => 0, 'notif' => 'echec de la mise à jour du compte unité');


                            break;


                    }


                } else {


                    return array('card_sender' => $sender, 'status' => 0, 'notif' => 'votre solde unité est insuffisant pour effectué cette transaction');


                }


                break;


            default:


                compte::hydratation($sender['account']);


                if (compte::getAmount() < self::getAmount())


                    return array('compte' => $sender, 'status' => -1, 'notif' => 'solde insuffisant');


                self::setAccount_id_sender(compte::getId());


                compte::setAmount(compte::getAmount() - self::getAmount());


                $sender = compte::updateAmount($sender['object']);


                switch ($sender) {


                    case true:


                        $senderMsg = 'votre compte SMOPAYE a été debité d\'un montant de ' . self::getAmount() . ' et votre nouveau solde est ' . compte::getAmount() . ' fcfa, suite a une transaction TRANSFERT VIA COMPTE';


                        $sender['notif'] = $senderMsg;


                        compte::hydratation($receiver['account']);


                        compte::setAmount(compte::getAmount() + self::getAmount());


                        self::setAccount_id_receiver(compte::getId());


                        $receiver = compte::updateAmount($receiver['object']);


                        $receiver['notif'] = 'votre compte SMOPAYE a été credité d\'un montant de ' . self::getAmount() . ' et votre nouveau solde est ' . compte::getAmount() . ' fcfa, suite a une transaction ' . self::getTransaction_type();


                        return array('sender' => $sender, 'compte_receiver' => $receiver, 'status' => 1, 'notif' => 'succès de l\'opération');


                        break;


                    case false:


                        return array('compte' => $sender, 'status' => 0, 'notif' => 'echec de la mise à jour du compte unité');


                        break;


                }


                break;


        }


    }

    public static function checkRemise($user)
    {

        $campagne = campaign::where('discount', '<>', 0)->first();


        if (empty($campagne)) {


            return null;


        } else {

            self::setRemise($campagne->discount);

            $amount = self::getAmount() - (self::getAmount() * $campagne->discount) / 100;

            return $amount;


        }


    }

    public static function checkBonus($user, $sender, $smopaye_phone, $transaction)
    {


        $transaction->starting_date = date('Y-m-d h:i:s');


        $amountWithDraw = "";


        $transaction->transaction_number = 'SMP' . date('ymd') . strtotime('now');


        $transaction->id = 'SMP' . date('ymd') . strtotime('now');


        $br = new br();


        $bonus_history = new Bonus_history();


        $categorie = categorie::find($user->category_id);


        $current_point = $user->bonus;


        $nbre_de_bonus = $bonus_history::where('user_id', $user->id)->count();


        if ($current_point > 0 && ($current_point % $categorie->bonus_point == 0) || (intdiv($current_point, $categorie->bonus_point) > $nbre_de_bonus)) {


            $transaction->card_id_receiver = $sender['card']['id'];


            $sender['object']->update(['unity' => ($sender['card']['unity'] + 250)]);


            $smopaye_user = user::where('phone', $smopaye_phone)->first();


            $smopaye = compte::where('id', $smopaye_user->compte_id)->first();


            $transaction->account_id_sender = $smopaye->id;


            $transaction->transaction_type = "BONUS";


            if ($smopaye->amount > 250) {


                $transaction->amount = 250;


                $smopaye->amount = $smopaye->amount - 250;


                $smopaye->save();


                $transaction->state = "SUCCESS";


                $transaction->end_date = date('Y-m-d h:i:s', time());


                $transaction->save();


                $bonus_history->state = 1;


            } else {


                $bonus_history->state = 0;


            }


            $bonus_history->message = 'Bravo Mr/Mme ' . $user->particulier[0]->firstname . ' ' . $user->particulier[0]->lastname . ' vous venez de gagner un voyage gratuit, SMOPAYE vous remercie et vous encourage a utiliser notre solution davantage';


            $bonus_history->user_id = $user->id;


            $bonus_history->amount = 250;


            $bonus_history->save();


            $user->bonus = $current_point % $categorie->bonus_point;


            $user->save();


            $br->API_AVS($user->phone, 'Bravo Mr/Mme.' . $user->particulier[0]->firstname . ' ' . $user->particulier[0]->lastname . ' vous venez de gagner un voyage gratuit, SMOPAYE vous remercie et vous encourage a utiliser notre solution davantage');


        }


    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */


    public function sender()
    {


        return $this->belongsTo(Card::class, 'card_id_sender');


    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */


    public function card_receiver()
    {


        return $this->belongsTo(Card::class, 'card_id_receiver');


    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */


    public function device()
    {


        return $this->belongsTo(Device::class);


    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */


    public function tarif()
    {


        return $this->belongsTo(Tarif::class);


    }

}
