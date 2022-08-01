<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card_subscription extends Model
{
    use HasFactory;

    protected $table = "card_subscription";
    public $fillable = [
        'id',
        'subscription_id',
        'card_id',
        'starting_date',
        'end_date',
        'validate',
        'transaction_number',
        'created_at',
        'updated_at' ];

    private static $id;
    private static $subscription_id;
    private static $card_subscription_id;
    private static $starting_date;
    private static $end_date;
    private static $validate;
    private static $transaction_number;
    private static $arrayCardSubscription;

    public static function hydratation($data){
        foreach ($data as $key => $valeur){
            $method = "set".ucfirst($key);
            if(method_exists(__CLASS__,$method)){
                self::$method($valeur);
            }
        }
    }

    public static function getId(){
        return self::$id;
    }

    public static function setId($id){
        self::$id = $id;
    }

    public static function getSubscription_id(){
        return self::$subscription_id;
    }

    public static function setSubscription_id($subscription_id){
        self::$subscription_id = $subscription_id;
    }

    public static function getCard_id(){
        return self::$card_subscription_id;
    }

    public static function setCard_id($card_subscription_id){
        self::$card_subscription_id = $card_subscription_id;
    }

    public static function getStarting_date(){
        return self::$starting_date;
    }

    public static function setStarting_date($starting_date){
        self::$starting_date = $starting_date;
    }

    public static function getEnd_date(){
        return self::$end_date;
    }

    public static function setEnd_date($end_date){
        self::$end_date = $end_date;
    }

    public static function getValidate(){
        return self::$validate;
    }

    public static function setValidate($validate){
        self::$validate = $validate;
    }

    public static function getTransaction_number(){
        return self::$transaction_number;
    }

    public static function setTransaction_number($transaction_number){
        self::$transaction_number = $transaction_number;
    }

    public static function updateTransaction_number(){
        $card_subscription = self::find(self::getId());
        $number = self::getTransaction_number()-1;
        $card_subscription->transaction_number = $number;
        if($number == 0 || self::getEnd_date() == date("Y-m-d")){
            $card_subscription->validate = 'deactive';
        }
        return $card_subscription->save();
    }

    public function subscription(){
        return $this->belongsTo(Subscription::class);
    }
}
