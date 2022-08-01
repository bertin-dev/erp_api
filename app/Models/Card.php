<?php

namespace App\Models;

use App\Http\Controllers\API\MonetbilController as monetbil;
use App\Models\Tarif as tarif;
use App\Models\Transaction as transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    public static $code_number_sender;
    public static $code_number_receiver;
    private static $id;
    private static $code_number;
    private static $serial_number;
    private static $user_id;
    private static $type;
    private static $company;
    private static $unity;
    private static $deposit;
    private static $starting_date;
    private static $end_date;
    private static $card_state;
    private static $user_created;
    private static $phoneNumber;
    private static $amount_recharge;
    private static $withDrawalAmount;
    private static $paymentId;
    private static $amountWithDraw;
    private static $monetbil;
    private static $action;
    private static $dataItem = array(

        'code_number' => '',

        'serial_number' => '',

        'user_id' => '',

        'type' => '',

        'company' => '',

        'unity' => '',

        'deposit' => '',

        'starting_date' => '',

        'end_date' => '',

        'card_state' => '',

        'code_number_sender' => '',

        'code_number_receiver' => '',

        'action' => '',

        'monetbil' => '',

        'amountWithDraw' => '',

        'paymentId' => '',

        'phoneNumber' => '',

        'amount_recharge' => '',

        'user_created' => ''

    );
    public $fillable = [

        'code_number',

        'serial_number',

        'user_id',

        'type',

        'company',

        'unity',

        'deposit',

        'starting_date',

        'end_date',

        'card_state',

        'user_created'

    ];

    public static function getAmountWithDraw()
    {

        return self::$amountWithDraw;

    }

    public static function setAmountWithDraw($amountWithDraw)
    {

        self::$amountWithDraw = $amountWithDraw;

    }

    public static function getPaymentId()
    {

        return self::$paymentId;

    }

    public static function setPaymentId($paymentId)
    {

        self::$paymentId = $paymentId;

    }

    public static function getCode_number()
    {

        return self::$code_number;

    }

    public static function setCode_number($code_number)
    {

        self::$code_number = $code_number;

    }

    public static function getSerial_number()
    {

        return self::$serial_number;

    }

    public static function setSerial_number($serial_number)
    {

        self::$serial_number = $serial_number;

    }

    public static function getUser_id()
    {

        return self::$user_id;

    }

    public static function setUser_id($user_id)
    {

        self::$user_id = $user_id;

    }

    public static function getType()
    {

        return self::$type;

    }

    public static function setType($type)
    {

        self::$type = $type;

    }

    public static function getCompany()
    {

        return self::$company;

    }

    public static function setCompany($company)
    {

        self::$company = $company;

    }

    public static function getStarting_date()
    {

        return self::$starting_date;

    }

    public static function setStarting_date($starting_date)
    {

        self::$starting_date = $starting_date;

    }

    public static function getEnd_date()
    {

        return self::$end_date;

    }

    public static function setEnd_date($end_date)
    {

        self::$end_date = $end_date;

    }

    public static function getUser_created()
    {

        return self::$user_created;

    }

    public static function setUser_created($user_created)
    {

        self::$user_created = $user_created;

    }

    public static function getAllCard()
    {

        return self::all();

    }

    public static function createCard($card)
    {

        return self::create($card);

    }

    public static function showCard($card_id)
    {

        $card = self::where('id', $card_id)->with(['user' => function ($query) {
            $query->with(['enterprise', 'particulier']);
        }])->get();

        if ($card != null) {

            self::hydratation($card->toArray()[0]);

        }

        return $card;

    }

    public static function hydratation($data)
    {

        foreach ($data as $key => $valeur) {

            $method = "set" . ucfirst($key);

            if (method_exists(__CLASS__, $method)) {

                self::$method($valeur);

            }

        }

    }

    public static function updateCard($card)
    {

        $card->code_number = self::$code_number;

        $card->serial_number = self::$serial_number;

        $card->type = self::$type;

        $card->company = self::$company;

        $card->starting_date = self::$starting_date;

        $card->end_date = self::$end_date;

        $card->card_state = self::$card_state;

        return array('result' => $card->save(), 'card' => $card);

    }

    public static function deleteCard($card)
    {

        return $card->delete();

    }

    /**
     * @param $card_id
     * @return array
     * cette fonction recupere une carte a partir de son id
     * ensuite modifie son etat par activer ou desactiver
     * puis la sauvegarde
     */

    public static function activationCard($card_id)
    {

        $card = self::find($card_id);

        switch (!empty($card)) {

            case true:

                $card->card_state = self::getCard_state();

                $card->save();

                return array('status' => 1, 'card' => $card->toArray());

                break;

            case false:

                return array('status' => -1, 'notif' => 'la carte n°' . $card_id . ' n\'existe pas');

                break;

        }

    }

    public static function getCard_state()
    {

        return self::$card_state;

    }

    public static function setCard_state($card_state)
    {

        self::$card_state = $card_state;

    }

    /**
     * @param $code_number
     * @return array
     */

    public static function findCodeNumberCard($code_number)
    {

        $cardObject = self::where('code_number', '=', $code_number);

        $card = $cardObject->get()->toArray();

        switch (!empty($card)) {

            case true:

                self::hydratation($card[0]);

                if (self::getCard_state() != 'desactiver') {

                    return array('status' => 1, 'card' => $card[0], 'object' => $cardObject);

                } else {

                    $res = array('status' => 0, 'notif' => 'carte ' . $code_number . ' est désactiver');

                    self::afterDataToSave();

                    return $res;

                }

                break;

            case false:

                $res = array('status' => -1, 'notif' => 'la carte ' . $code_number . ' n\'existe pas');

                self::afterDataToSave();

                return $res;

                break;

        }

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

    public static function findUserCard($card_id = null, $card_number = null)
    {

        if ($card_number == null) {

            $user = self::with('user')->find($card_id);

        } else {

            $user = self::where('code_number', $card_number)->with(['user' => function ($query) {
                $query->with('particulier');
            }])->get();

            return $user->toArray();

        }

        if ($user == null)

            return [];

        return $user->toArray()['user'];

    }

    public static function placeRechargeCard()
    {

        $monetbil = new monetbil(self::getPhoneNumber(), self::getAmountRecharge());

        $monetbilMsg = $monetbil::placePayment($monetbil::$placePayment);

        return $monetbilMsg;

    }

    public static function getPhoneNumber()
    {

        return self::$phoneNumber;

    }

    public static function setPhoneNumber($phoneNumber)
    {

        self::$phoneNumber = $phoneNumber;

    }

    public static function getAmountRecharge()
    {

        return self::$amount_recharge;

    }

    public static function setAmountRecharge($amount_recharge)
    {

        self::$amount_recharge = $amount_recharge;

    }

    public static function checkPayment($paymentId)
    {

        transaction::setStarting_date(date('Y-m-d h:i:s', time()));

        $monetbilMsg = monetbil::validatePayment($paymentId);

        transaction::setCard_id_sender(self::getId());

        transaction::setCard_id_receiver(self::getId());

        transaction::setTransaction_type('RECHARGE_CARTE_VIA_MONETBIL');

        transaction::setAmount($monetbilMsg['transaction']['amount']);

        transaction::setState($monetbilMsg['transaction']['message']);

        transaction::setEnd_date(date('Y-m-d h:i:s', time()));

        transaction::createTransaction(transaction::prepareDataToSave());

        if (is_array($monetbilMsg) and array_key_exists('transaction', $monetbilMsg)) {

            return $monetbilMsg['transaction'];

        } else {

            return $monetbilMsg;

        }

    }

    public static function getId()
    {

        return self::$id;

    }

    public static function setId($id)
    {

        self::$id = $id;

    }

    public static function cardWithDrawal($amountWithDraw, $phone = null)
    {
        if ($phone == null) {
            $phone = self::getPhoneNumber();
        }
        self::$monetbil = new monetbil($phone, $amountWithDraw);

        $monetbilMsg = self::$monetbil::payouts();

        transaction::setCard_id_sender(self::getId());

        transaction::setCard_id_receiver(self::getId());

        transaction::setAmount($amountWithDraw);

        transaction::setTransaction_type('RETRAIT_CARTE_VIA_MONETBIL');

        transaction::setOperator("SMOPAYE");

        transaction::setState($monetbilMsg['message']);

        transaction::setEnd_date(date('Y-m-d h:i:s', time()));


        return $monetbilMsg;

    }

    public static function rechargeAccount($card, $amountWithDraw)
    {

        $response = false;

        transaction::setStarting_date(date('Y-m-d h:i:s', time()));

        transaction::setTransaction_number('SMP' . date('ymd') . strtotime('now'));

        transaction::setId('SMP' . date('ymd') . strtotime('now'));

        switch (self::getAction()) {

            case 'unity':

                self::setDeposit(self::getDeposit() - $amountWithDraw);

                self::updateDeposit($card);

                self::setUnity(self::getUnity() + self::getWithDrawalAmount());

                $response = self::updateUnity($card);

                transaction::setTransaction_type('TRANSFERT_UNITE_DEPOT');

                break;


            case 'deposit':

                self::setUnity(self::getUnity() - $amountWithDraw);

                self::setDeposit(self::getDeposit() + self::getWithDrawalAmount());

                $response = self::updateDeposit($card);

                transaction::setTransaction_type('TRANSFERT_DEPOT_UNITE');

                break;

        }

        transaction::setCard_id_sender(self::getId());

        transaction::setCard_id_receiver(self::getId());

        transaction::setAmount(self::getWithDrawalAmount());

        transaction::setState($response == true ? "SUCCESS" : "FAILED");

        transaction::setServicecharge(tarif::getServiceCharge());

        transaction::setEnd_date(date('Y-m-d h:i:s', time()));

        transaction::createTransaction(transaction::prepareDataToSave());

        return $response;

    }

    public static function getAction()
    {

        return self::$action;

    }

    public static function setAction($action)
    {

        self::$action = $action;

    }

    public static function getDeposit()
    {

        return self::$deposit;

    }

    public static function setDeposit($deposit)
    {

        self::$deposit = $deposit;

    }

    public static function updateDeposit($card)
    {

        $card->deposit = round(self::getDeposit(), 2);

        $card->save();

        return $card->toArray();

    }

    public static function getUnity()
    {

        return self::$unity;

    }

    public static function setUnity($unity)
    {

        self::$unity = $unity;

    }

    public static function getWithDrawalAmount()
    {

        return self::$withDrawalAmount;

    }

    public static function setWithDrawalAmount($withDrawalAmount)
    {

        self::$withDrawalAmount = $withDrawalAmount;

    }

    public static function updateUnity($card)
    {

        $card->unity = round(self::getUnity(), 2);

        $card->save();

        return $card->toArray();

    }

    public function user()
    {

        return $this->belongsTo(User::class);

    }


    public function transactions()
    {

        return $this->hasMany(Transaction::class);

    }


    public function card_subscriptions()
    {

        return $this->hasMany(Card_subscription::class);

    }


    public function subscriptions()
    {

        return $this->belongsToMany(Subscription::class);

    }

}
