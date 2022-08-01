<?php

namespace App\Models;

use App\Models\Compte;
use App\Models\Compte_subscription;
use App\Models\Subscription as subscription;
use App\Traits\HasRolesAndPermissions;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRolesAndPermissions;

    protected $table ="users";
    protected $primaryKey = "id";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'phone',
        'address',
        'category_id',
        'password',
        'created_by',
        'role_id',
        'state',
        'compte_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    private static $id;
    private static $phone;
    private static $address;
    private static $category_id;
    private static $password;
    private static $parent_id;
    private static $role_id;
    private static $state = "activer";
    private static $compte_id = null;
    private static $card_number = null;
    public static $compte;
    public static $card;

    private static $dataItem = array(
        'phone'=>'',
        'address'=>'',
        'category_id'=>'',
        'parent_id'=>'',
        'password'=>'',
        'role_id'=>'',
        'state'=>'desactiver',
        'compte_id'=>null
    );

    public function __construct($type=null, $user=[]){
        self::$compte = new Compte([], self::getId());
        self::setPassword($this->generateNumericOTP(5));
        !empty($user)?$this->hydratation((Array) $user):'';
    }

    public static function hydratation($data){
        foreach ($data as $key => $valeur){
            $method = "set".ucfirst($key);
            if(method_exists(__CLASS__,$method)){
                self::$method($valeur);
            }
        }
    }

    public function generateNumericOTP($n) {
        $generator = "1357902468";
        $result = "";
        for ($i = 1; $i <= $n; $i++) {
            $result .= substr($generator, (rand()%(strlen($generator))), 1);
        }
        return $result;
    }


    public static function prepareDataToSave(){
        $data = [];
        foreach (self::$dataItem as $key => $value) {
            $method = "get".ucfirst($key);
            if(method_exists(__CLASS__,$method)){
                $data[$key] = self::$method();
            }
        }
        return $data;
    }

    public static function getId(){
        return self::$id;
    }

    public static function setId($id){
        self::$id = $id;
    }

    public static function getPhone(){
        return self::$phone;
    }

    public static function setPhone($phone){
        self::$phone = $phone;
    }

    public static function getAddress(){
        return self::$address;
    }

    public static function setAddress($address){
        self::$address = $address;
    }

    public static function getCategory_id(){
        return self::$category_id;
    }

    public static function setCategory_id($category_id){
        self::$category_id = $category_id;
    }

    public static function getPassword(){
        return self::$password;
    }

    public static function setPassword($password){
        self::$password = $password;
    }

    public static function getParent_id(){
        return self::$parent_id;
    }

    public static function setParent_id($parent_id){
        self::$parent_id = $parent_id;
    }

    public static function getRole_id(){
        return self::$role_id;
    }

    public static function setRole_id($role_id){
        self::$role_id = $role_id;
    }

    public static function getState(){
        return self::$state;
    }

    public static function setState($state){
        self::$state = $state;
    }

    public static function getCard_number(){
        return self::$card_number;
    }

    public static function setCard_number($card_number){
        self::$card_number = $card_number;
    }

    public static function getCompte_id(){
        return self::$compte_id;
    }

    public static function setCompte_id($compte_id){
        self::$compte_id = $compte_id;
    }

    public static function getAllUser(){
        return self::with(['particulier','enterprise', 'cards'=>function($query){$query->where('user_id', NULL);}])->get();
    }

    /**
     * <<=== //// CREATEUSER //// ===>>
     * cette fonction permet de créer un utilisateur
     * @param Request $request
     * @return Response
     */
    public static function createUser(User $user, $fils=null){
        foreach (self::prepareDataToSave() as $key => $value) {
            $user->$key = $value;
        }
        $user->password = bcrypt(self::getPassword());
        $user->created_by = Auth::guard('api')->user()->id;
        if($user->save()){
            if($fils == null){
                Compte_subscription::create(
                    ['compte_id'=>$user->compte_id,
                        'subscription_id'=> subscription::where('name','service')->first()->id,
                        'starting_date'=>date("Y-m-d H:i:s"),
                        'subscriptionCharge'=>'0',
                        'subscription_type' => 'service',
                        'transaction_number'=>'0',
                        'end_date'=>'0000-00-00 00:00:00']);
            }
            return $user->id;
        }
        return [];
    }

    public static function showUser($user_id){
        $user = self::find($user_id);
        if($user != null){
            self::hydratation($user->toArray());
        }
        return $user;
    }
    /**
     * <<=== //// ADD CART TO USER //// ===>>
     * cette fonction permet d'attribuer une carte a un utilisateur
     * @param Request $request
     * @return Response
     */
    public static function attribCard($card){
        $carte = $card::findCodeNumberCard(self::getCard_number());
        if($carte['status'] == 1 and $carte['card']['user_id'] == null){
            if($carte['object']->update(['user_id'=> self::getId()])){
                return ['status'=> 1];
            }
            return ['notif'=> 'Echec de l\opération'];
        }
        return ['status'=> -1, 'notif'=> array_key_exists('notif', $carte) ? $carte['notif'] : ['notif'=> 'Echec de l\'opération: la carte est indisponible']];
    }

    public static function desattribCard($card){
        $carte = $card::findCodeNumberCard(self::getCard_number());
        if($carte['status'] == 1 and $carte['card']['user_id'] != null){
            if($carte['object']->update(['user_id'=> null])){
                return ['status'=> 1];
            }
            return ['notif'=> 'Echec de l\opération'];
        }
        return ['status'=> -1, 'notif'=> array_key_exists('notif', $carte) ? $carte['notif'] : ['notif'=> 'la carte est libre']];
    }


    public static function generatepassword($n) {
        $generator = "1357902468";
        $result = "";
        for ($i = 1; $i <= $n; $i++) {
            $result .= substr($generator, (rand()%(strlen($generator))), 1);
        }
        return $result;
    }

    public function compte(){
        return $this->belongsTo(Compte::class);
    }

    public function enterprise(){
        return $this->hasMany(Enterprise::class);
    }

    public function particulier(){
        return $this->hasMany(Particulier::class);
    }

    public function cards(){
        return $this->hasMany(Card::class);
    }

    public function Bonus_history(){
        return $this->hasMany(Bonus_history::class);
    }

    public function role() {
        return $this->belongsTo(Role::class);
    }

    public function categorie(){
        return $this->belongsTo(Category::class);
    }

    public function findForPassport($phone)
    {
        return $this->where('phone', $phone)->first();
    }

    public static function updateUser($user){
        self::$phone = $user->phone;
        //    $pass =  bcrypt($user->password)
        $user->password = self::$password;
        return array('result' => $user->save(), 'user' => $user);
    }
    public static function deleteUser($user){
        return $user->delete();
    }
}
