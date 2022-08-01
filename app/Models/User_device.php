<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_device extends Model
{
    use HasFactory;

    protected $fillable =

        [

            'id',
            'starting_possession',

            'end_possession',

            'user_id',

            'device_id'

        ];


    private static $id;

    private static $starting_possession;

    private static $end_possession;

    private static $user_id;

    private static $device_id;

    private static $dataItem = array(
        'starting_possession' => '',

        'end_possession' => '',

        'user_id' => '',

        'device_id' => '',
    );


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


    public static function getStarting_possession()
    {

        return self::$starting_possession;

    }


    public static function setStarting_possession($starting_possession)
    {

        self::$starting_possession = $starting_possession;

    }


    public static function getEnd_possession()
    {
        return self::$end_possession;
    }

    public static function setEnd_possession($end_possession)
    {
        self::$end_possession = $end_possession;
    }

    public static function getUser_id()
    {
        return self::$user_id;
    }

    public static function setUser_id($user_id)
    {
        self::$user_id = $user_id;
    }

    public static function getDevice_id()
    {
        return self::$device_id;
    }

    public static function setDevice_id($device_id)
    {
        self::$device_id = $device_id;
    }


    public static function createUserDevice($user_device)
    {

        return self::create($user_device);

    }


    public static function currentPossessionDevice($date, $user_id, $device_id)
    {

        return self::where('starting_possession', '<=', $date)
            ->where('end_possession', '>=', $date)
            ->where('user_id', $user_id)
            ->where('device_id', $device_id)
            ->get()
            ->toArray();

    }


    public function devices()

    {

        return $this->hasMany(Device::class);

    }


    public function enterprises()

    {

        return $this->hasMany(Enterprise::class);

    }
}
