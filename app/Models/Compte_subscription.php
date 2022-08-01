<?php

namespace App\Models;

use App\Models\Compte as compte;
use App\Models\Tarif as tarif;
use App\Models\User as user;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compte_subscription extends Model
{
    use HasFactory;

    private static $id;
    private static $subscription_id;
    private static $compte_id;
    private static $starting_date;
    private static $end_date;
    private static $validate;
    private static $transaction_number;
    private static $arrayCompteSubscription;
    protected $table = "compte_subscriptions";
    protected $fillable = [
        'id',
        'subscriptionCharge',
        'subscription_id',
        'subscription_type',
        'compte_id',
        'starting_date',
        'end_date',
        'validate',
        'transaction_number'
    ];

    public static function getSubscription_id()
    {
        return self::$subscription_id;
    }

    public static function setSubscription_id($subscription_id)
    {
        self::$subscription_id = $subscription_id;
    }

    public static function getCompte_id()
    {
        return self::$compte_id;
    }

    public static function setCompte_id($compte_id)
    {
        self::$compte_id = $compte_id;
    }

    public static function getStarting_date()
    {
        return self::$starting_date;
    }

    public static function setStarting_date($starting_date)
    {
        self::$starting_date = $starting_date;
    }

    public static function getValidate()
    {
        return self::$validate;
    }

    public static function setValidate($validate)
    {
        self::$validate = $validate;
    }

    public static function getAmountSubcription($amount, $user)
    {
        self::$arrayCompteSubscription = self::getCurrentSubscription($user['id']);
        if (empty(self::$arrayCompteSubscription)) {
            return tarif::amountWithDraw($amount, $user['category_id'], $user['role_id']);
        } else {
            tarif::setServiceCharge(0);
            self::hydratation(self::$arrayCompteSubscription);
            self::updateTransaction_number();
            return $amount;
        }
    }

    public static function getCurrentSubscription($user_id)
    {
        $compte = new compte();
        if (!is_null($user_id)) {
            $user = user::showUser($user_id);
            $currentaccount = $compte::where('id', user::getCompte_id())->with('compte_subscriptions')->first();
            if (is_null($currentaccount))
                return [];
            $subscriptions = $currentaccount->compte_subscriptions->toArray();
            $compte::hydratation($subscriptions);
            if (empty($subscriptions))
                return [];
            foreach ($subscriptions as $key) {
                switch ($key['validate'] == 'active' && $key['transaction_number'] > 0 && key_exists('subscription_type', $key)) {
                    case true:
                        if ($key['subscription_type'] == 'semaine') {
                            $subscriptions = $key;
                            break;
                        } elseif ($key['subscription_type'] == 'mensuel') {
                            $subscriptions = $key;
                            break;
                        } else {
                            $subscriptions = [];
                        }
                        break;

                    case false:
                        $subscriptions = [];
                        break;
                }
            }
            return $subscriptions;
        }
        return [];
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

    public static function updateTransaction_number()
    {
        $compte_subscription = self::find(self::getId());
        $number = self::getTransaction_number() - 1;
        $compte_subscription->transaction_number = $number;
        if ($number == 0 || self::getEnd_date() == date("Y-m-d")) {
            $compte_subscription->validate = 'deactive';
        }
        return $compte_subscription->save();
    }

    public static function getId()
    {
        return self::$id;
    }

    public static function setId($id)
    {
        self::$id = $id;
    }

    public static function getTransaction_number()
    {
        return self::$transaction_number;
    }

    public static function setTransaction_number($transaction_number)
    {
        self::$transaction_number = $transaction_number;
    }

    public static function getEnd_date()
    {
        return self::$end_date;
    }

    public static function setEnd_date($end_date)
    {
        self::$end_date = $end_date;
    }

    public function subscriptions()
    {
        return $this->belongs(Subscription::class);
    }

    public function comptes()
    {
        return $this->belongs(Compte::class);
    }

}
