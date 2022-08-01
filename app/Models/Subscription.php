<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'subscription_fees',
        'starting_date',
        'end_date',
        'periode_abon',
    ];

    public static function hydratation($data){
        foreach ($data as $key => $valeur){
            $method = "set".ucfirst($key);
            if(method_exists(__CLASS__,$method)){
                self::$method($valeur);
            }
        }
    }

    private static $id;
    private static $subscription_fees;
    private static $starting_date;
    private static $end_date;
    private static $periode_abon;


    public static function getId(){
        return self::$action;
    }
    public static function getName(){
        return self::$name;
    }
    public static function getStarting_date(){
        return self::$starting_date;
    }
    public static function getEnd_date(){
        return self::$end_date;
    }
    public static function getPeriode_abon(){
        return self::$periode_abon;
    }
    public static function setPeriode_abon($periode_abon){
        self::$periode_abon = $periode_abon;
    }
    public static function setStarting_date($starting_date){
        self::$starting_date= $starting_date;
    }
    public static function setEnd_date($end_date){
        self::$end_date = $end_date;
    }

    public static function setName($name){
        self::$name = $name;
    }
    public static function getSubscription_fees(){
        return self::$subscription_fees;
    }
    public static function setSubscription_fees($subscription_fees){
        self::$subscription_fees = $subscription_fees ;
    }
    public function cards (){
        return $this->belongsToMany(Card::class,'card_subscription')->withPivot('starting_date', 'end_date')->withTimestamps()->using(Card_subscription::class);

    }
    public function card_subscriptions(){
        return $this->hasMany(Card_subscription::class);
    }
    public static function createSubscription($subscription){
        return self::create($subscription);
    }
    public static function showSubscription($name){
        $subscription = self::where('name',$name);
        self::hydratation($subscription->toArray());
        return $subscription;
    }

}
