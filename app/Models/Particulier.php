<?php

namespace App\Models;

use App\Traits\HasRolesAndPermissions;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Particulier extends User
{
    use HasFactory, HasRolesAndPermissions;

    private static $firstname;
    private static $lastname;
    private static $gender;
    private static $cni;
    private static $user_id;
    private static $fonction;
    private static $email;
    protected $table = "particuliers";
    protected $primaryKey = "id";
    protected $fillable = [

        'lastname',

        'firstname',

        'gender',

        'cni',

        'user_id',

        'fonction',

        'email'

    ];

    public function __construct($user = [])
    {

        parent::__construct('PA', $user);

        $this->hydratation((array)$user);

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

    public static function createParticulier($particulier)
    {

        $particulier->user_id = self::getUser_id();

        $particulier->lastname = self::getLastname();

        $particulier->firstname = self::getFirstname();

        $particulier->gender = self::getGender();

        $particulier->cni = self::getCni();

        $particulier->email = self::getEmail();

        $particulier->fonction = self::getFonction();

        return $particulier->save();

    }

    public static function getUser_id()
    {

        return self::$user_id;

    }

    public static function setUser_id($user_id)
    {

        self::$user_id = $user_id;

    }

    public static function getLastname()
    {

        return self::$lastname;

    }

    public static function setLastname($lastname)
    {

        self::$lastname = $lastname;

    }

    public static function getFirstname()
    {

        return self::$firstname;

    }

    public static function setFirstname($firstname)
    {

        self::$firstname = $firstname;

    }

    public static function getGender()
    {

        return self::$gender;

    }

    public static function setGender($gender)
    {

        self::$gender = $gender;

    }

    public static function getCni()
    {

        return self::$cni;

    }

    public static function setCni($cni)
    {

        self::$cni = $cni;

    }

    public static function getEmail()
    {

        return self::$email;

    }

    public static function setEmail($email)
    {

        self::$email = $email;

    }

    public static function getFonction()
    {

        return self::$fonction;

    }

    public static function setFonction($fonction)
    {

        self::$fonction = $fonction;

    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
