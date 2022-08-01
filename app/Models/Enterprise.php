<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enterprise extends User
{
    use HasFactory;

    private static $raison_social;
    private static $status;
    private static $rccm;
    private static $user_id;
    private static $principal_id;
    private static $dataItem = array(
        'raison_social' => '',
        'status' => '',
        'rccm' => '',
        'user_id' => '',
        'principal_id' => null
    );
    protected $table = "enterprises";
    protected $primaryKey = "id";
    protected $fillable = [
        'raison_social',
        'rccm',
        'status',
        'user_id',
        'principal_id'
    ];

    public function __construct($user = 0)
    {
        parent::__construct('EP', $user);
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

    public static function setEts_principal_id($principal_id)
    {
        self::$principal_id = $principal_id;
    }

    public static function createEnterprise($enterprise)
    {
        $enterprise->user_id = self::getUser_id();
        $enterprise->principal_id = self::getEts_principal_id();
        $enterprise->raison_social = self::getRaison_social();
        $enterprise->status = self::getstatus();
        $enterprise->rccm = self::getRccm();
        return $enterprise->save();
    }

    public static function getUser_id()
    {
        return self::$user_id;
    }

    public static function setUser_id($user_id)
    {
        self::$user_id = $user_id;
    }

    public static function getEts_principal_id()
    {
        return self::$principal_id;
    }

    public static function getRaison_social()
    {
        return self::$raison_social;
    }

    public static function setRaison_social($raison_social)
    {
        self::$raison_social = $raison_social;
    }

    public static function getStatus()
    {
        return self::$status;
    }

    public static function setStatus($status)
    {
        self::$status = $status;
    }

    public static function getRccm()
    {
        return self::$rccm;
    }

    public static function setRccm($rccm)
    {
        self::$rccm = $rccm;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function devices()
    {
        return $this->belongsToMany(Device::class, 'user_devices', 'user_id');
    }

    public function user_device()
    {
        return $this->hasMany(User_device::class, 'user_id');
    }
}
