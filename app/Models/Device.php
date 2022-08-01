<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [

        'device_type',

        'serial_number',

        'passerel',

        'mobile',

        'manifacturer',

        'designation',

        'branch',

        'provider'

    ];


    private static $id;

    private static $device_type;

    private static $serial_number;

    private static $passerel;

    private static $mobile;

    private static $manifacturer;

    private static $branch;

    private static $designation;

    private static $provider;


    public static function hydratation($data)
    {

        foreach ($data as $key => $valeur) {

            $method = "set" . ucfirst($key);

            if (method_exists(__CLASS__, $method)) {

                self::$method($valeur);

            }

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


    public static function getDevice_type()
    {

        return self::$device_type;

    }


    public static function setDevice_type($device_type)
    {

        self::$device_type = $device_type;

    }

    public static function getDesignation()
    {

        return self::$designation;

    }

    public static function setDesignation($designation)
    {

        self::$designation = $designation;

    }


    public static function getserial_number()
    {

        return self::$serial_number;

    }


    public static function setserial_number($serial_number)
    {

        self::$serial_number = $serial_number;

    }


    public static function getPasserel()
    {

        return self::$passerel;

    }


    public static function setPasserel($passerel)
    {

        self::$passerel = $passerel;

    }


    public static function getMobile()
    {

        return self::$mobile;

    }


    public static function setMobile($mobile)
    {

        self::$mobile = $mobile;

    }


    public static function getManifacturer()
    {

        return self::$manifacturer;

    }


    public static function setManifacturer($manifacturer)
    {

        self::$manifacturer = $manifacturer;

    }


    public static function getBranch()
    {

        return self::$branch;

    }


    public static function setBranch($branch)
    {

        self::$branch = $branch;

    }


    public static function getProvider()
    {

        return self::$provider;

    }


    public static function setProvider($provider)
    {

        self::$provider = $provider;

    }


    public static function getAllDevice()
    {

        return self::with('entreprises')->get();

    }


    public static function showDevice($device_id)
    {

        return self::where('id', $device_id)->with(['entreprises', 'user_device'])->first();

    }


    public static function createDevice($device)
    {

        return self::create($device);

    }


    public static function updateDevice($device)
    {

        $device->device_type = self::$device_type;

        $device->serial_number = self::$serial_number;

        $device->passerel = self::$passerel;

        $device->mobile = self::$mobile;

        $device->designation = self::$designation;

        $device->manifacturer = self::$manifacturer;

        $device->branch = self::$branch;

        $device->provider = self::$provider;

        return array('result' => $device->save(), 'device' => $device);

    }


    public static function deleteDevice($device)
    {

        return $device->delele();

    }


    public static function findSerialNumberDevice($serial_number)
    {

        $device = self::where('serial_number', '=', $serial_number)->get()->toArray();
        if (!empty($device)) {
            self::hydratation($device[0]);
            return $device[0];
        } else {
            return null;
        }

    }

    public function entreprises()
    {
        return $this->belongsToMany(Enterprise::class, 'user_devices', 'device_id', 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function user_device()
    {
        return $this->hasMany(User_device::class);
    }
}
