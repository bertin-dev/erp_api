<?php

namespace App\Models;

use App\Http\Controllers\API\MonetbilController as monetbil;
use App\Models\Transaction as transaction;
use App\Models\User as user;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compte extends Model
{
    use HasFactory;
    protected $table = "comptes";

    protected $fillable = [
        'id',
        'account_number',
        'company',
        'account_state',
        'amount',
        'principal_account_id'
    ];

    private static $dataItem = array(
        'account_number'=>'',
        'company'=>'SMOPAYE',
        'account_state'=>'activer',
        'amount'=> 0,
        'principal_account_id'=>null
    );

    private static $id;
    private static $account_number;
    private static $company ="SMOPAYE";
    private static $account_state;
    private static $amount = 0;
    private static $principal_account_id = null;
    private static $withDrawalAmount;
    private static $code_number;
    private static $phoneNumber;
    private static $amount_recharge;
    private static $paymentId;

    public function __construct($compte=[], $user_id=null){
        self::setAccount_number(self::generateNumericOTP(9));
    }

    public static function hydratation($data){
        foreach ($data as $key => $valeur){
            $method = "set".ucfirst($key);
            if(method_exists(__CLASS__,$method)){
                self::$method($valeur);
            }
        }
    }

    public static function prepareDataToSave(){
        $data = [];
        foreach (self::$dataItem as $key => $value) {
            $method = "get".ucfirst($key);
            if(method_exists(__CLASS__,$method) and self::$method() != null){
                $data[$key] = self::$method();
            }
        }
        return $data;
    }

    public static function afterDataToSave(){
        foreach (self::$dataItem as $key => $value) {
            $value = '';
            $method = "set".ucfirst($key);
            if(method_exists(__CLASS__,$method)){
                self::$method($value);
            }
        }
    }

    function generateNumericOTP($n) {
        $generator = "1357902468";
        $result = "";
        for ($i = 1; $i <= $n; $i++) {
            $result .= substr($generator, (rand()%(strlen($generator))), 1);
        }
        return $result;
    }

    public static function getId(){
        return self::$id;
    }

    public static function setId($id){
        self::$id = $id;
    }

    public static function getAccount_number(){
        return self::$account_number;
    }

    public static function setAccount_number($account_number){
        self::$account_number = $account_number;
    }

    public static function getCompany(){
        return self::$company;
    }

    public static function setCompany($company){
        self::$company = $company;
    }

    public static function getAccount_state(){
        return self::$account_state;
    }

    public static function setAccount_state($account_state){
        self::$account_state = $account_state;
    }

    public static function getAmount(){
        return self::$amount;
    }

    public static function setAmount($amount){
        self::$amount = $amount;
    }

    public static function getPrincipal_account_id(){
        return self::$principal_account_id;
    }

    public static function setPrincipal_account_id($principal_account_id){
        self::$principal_account_id = $principal_account_id;
    }

    public static function getWithDrawalAmount(){
        return self::$withDrawalAmount;
    }

    public static function setWithDrawalAmount($withDrawalAmount){
        self::$withDrawalAmount = $withDrawalAmount;
    }

    public static function getCode_number(){
        return self::$code_number;
    }

    public static function setCode_number($code_number){
        self::$code_number = $code_number;
    }

    public static function getPhoneNumber(){
        return self::$phoneNumber;
    }

    public static function setPhoneNumber($phoneNumber){
        self::$phoneNumber = $phoneNumber;
    }

    public static function getPaymentId(){
        return self::$paymentId;
    }

    public static function setPaymentId($paymentId){
        self::$paymentId = $paymentId;
    }

    public static function getAmountRecharge(){
        return self::$amount_recharge;
    }

    public static function setAmountRecharge($amount_recharge){
        self::$amount_recharge = $amount_recharge;
    }

    public static function updateAmount($compte){
        $compte->update(['amount'=>round(self::getAmount(), 2)]);
        return $compte->get()->toArray()[0];
    }

    /** <<<==== CREATEACCOUNT ====>>>
     * cette fonction permet la creation d'un compte
     * @param $compte_id
     * @return array
     */
    public static function createAccount($compte){
        $compte->account_number = self::getAccount_number();
        if($compte->save()){
            return $compte->id;
        }
        return [];
    }
    /**
     * cette fonction renvoie tous les comptes
     * @param $compte_id
     * @return array
     */
    public static function getAllAccount(){
        return self::all();
    }

    /**
     * cette fonction renvoie un compte
     * @param $compte_id
     * @return array
     */
    public static function showAccount($compte_id){
        $compte = self::find($compte_id);
        if($compte != null){
            self::hydratation($compte->toArray());
        }
        return $compte;
    }

    /**
     * cette fonction permet de modifier un compte
     * @param $compte_id
     * @return array
     */
    public static function updateAccount($account_number){
        $result = self::findAccount($account_number);
        if($result['status'] == 1){
            $compte = self::prepareDataToSave();
            return array('status' =>$result['object']->update($compte), 'compte' => $compte);
        }
        return $result;
    }

    /**
     * cette fonction permet de supprimer un compte
     * @param $compte
     * @return array
     */
    public static function deleteCompte($account_number){
        $result = self::findAccount($account_number);
        if($result['status'] == 1){
            return array('status' =>$result['object']->delete(), 'compte' => $result['account']);
        }
        return $result;
    }

    /**
     * @param $compte_id
     * @return array
     * cette fonction recupere un compte a partir de son numero de compte
     * ensuite modifie son etat par activer ou desactiver
     * puis la sauvegarde
     */
    public static function activationCompte($account_id){
        $account = self::find($account_id);
        switch (!empty($account)) {
            case true:
                $account->account_state = self::getAccount_state();
                $account->save();
                return array('status' => 1, 'account'=>$account->toArray());
                break;
            case false:
                return array('status' => -1, 'notif'=>'la compte n°'.$account_id.' n\'existe pas');
                break;
        }
    }
    /**
     * cette fonction permet de get un compte a partir
     * du numero du compte
     * @param $code_number
     * @return array
     */
    public static function findAccount($account_number, $id=null){
        if($id == null){
            $compteObject = self::where('account_number','=',$account_number)->with('user');
        }else{
            $compteObject = self::where('id','=',$id)->with('user');
        }
        $compte = $compteObject->get()->toArray();
        switch (!empty($compte)) {
            case true:
                if(self::getAccount_state() != 'desactiver'){
                    return array('status' => 1, 'account'=>$compte[0], 'object'=>$compteObject);
                }else{
                    return array('status' => 0, 'notif'=>'le compte '.$account_number.' n\'est pas active');
                }
                break;
            case false:
                return array('status' => -1, 'notif'=>'le compte '.$account_number.' n\'existe pas');
                break;
        }
    }



    public static function findUserCard($compte_id){
        return self::with('user')->find($compte_id)->toArray()['user'];
    }

    /**
     * cette fonction permet de initialiser le paiement via monetbil
     *
     * @return array
     */
    public static function placeRechargeAccount(){
        $monetbil = new monetbil(self::getPhoneNumber(), self::getAmount());
        return $monetbil::placePayment($monetbil::$placePayment);
    }

    public static function checkPayment($paymentId, $compte_id){
        transaction::setStarting_date(date('Y-m-d h:i:s', time()));
        $monetbilMsg = monetbil::validatePayment($paymentId);
        transaction::setAccount_id_sender($compte_id);
        transaction::setAccount_id_receiver($compte_id);
        transaction::setPaymentId($paymentId);
        transaction::setTransaction_type('RECHARGE_COMPTE_VIA_MONETBIL');
        if(is_array($monetbilMsg) and array_key_exists('transaction', $monetbilMsg))
        {
            transaction::setAmount($monetbilMsg['transaction']['amount']);
            transaction::setState($monetbilMsg['message']);
            transaction::setOperator($monetbilMsg['transaction']['mobile_operator_name_short']);
            transaction::setEnd_date(date('Y-m-d h:i:s', time()));
            transaction::createTransaction(transaction::prepareDataToSave());
            return $monetbilMsg['transaction'];
        }else{
            transaction::setAmount(self::getAmount());
            transaction::setState("ECHEC VALIDATION");
            transaction::setEnd_date(date('Y-m-d h:i:s', time()));
            transaction::createTransaction(transaction::prepareDataToSave());
            return $monetbilMsg;
        }
    }

    public static function setAccountWithPhone($phone, $amount){
        //dd($phone);
        $usercompte = user::where('phone', $phone)->with('compte')->first();
        $compte = self::where('id',$usercompte->compte->id)->first();
        $compte->amount = $compte->amount + $amount;
        $compte->save();
        return $compte->toArray();
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function transaction_comptes(){
        return $this->hasMany(Transaction::class, 'account_id_sender');
    }

    public function compte_subscriptions(){
        $subscriptions = $this->hasMany(Compte_subscription::class);
        $subscriptions->where('compte_subscriptions.subscription_type','<>','service');
        return $subscriptions;
    }

}
