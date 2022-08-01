<?php

namespace App\Models;

use App\Models\Transaction as transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    use HasFactory;

    public static $amountWithDraw;
    private static $serviceCharge = 0;
    private static $id;
    private static $tranche_min;
    private static $tranche_max;
    private static $tarif_night;
    private static $tarif_day;
    private static $categorie_id;
    protected $table = "tarif_grids";
    protected $fillable = [
        'tranche_min',
        'tranche_max',
        'tarif_night',
        'tarif_day',
        'type_tarif',
        'categorie_id',
        'role_id'
    ];

    public static function hydratation($data)
    {
        foreach ($data as $key => $valeur) {
            $method = "set" . ucfirst($key);
            if (method_exists(__CLASS__, $method)) {
                self::$method($valeur);
            }
        }
    }


    public static function getAmountWithDraw()
    {
        return self::$amountWithDraw;
    }

    public static function setAmountWithDraw($amountWithDraw)
    {
        self::$amountWithDraw = $amountWithDraw;
    }

    public static function getId()
    {
        return self::$id;
    }

    public static function setId($id)
    {
        self::$id = $id;
    }

    public static function getTranche_min()
    {
        return self::$tranche_min;
    }

    public static function setTranche_max($tranche_max)
    {
        self::$tranche_max = $tranche_max;
    }

    public static function getTarif_night()
    {
        return self::$tarif_night;
    }

    public static function setTarif_night($tarif_night)
    {
        self::$tarif_night = $tarif_night;
    }

    public static function getTarif_day()
    {
        return self::$tarif_day;
    }

    public static function setTarif_day($tarif_day)
    {
        self::$tarif_day = $tarif_day;
    }

    public static function getServiceCharge()
    {
        return self::$serviceCharge;
    }

    public static function setServiceCharge($serviceCharge)
    {
        self::$serviceCharge = $serviceCharge;
    }

    public static function showTarif($tarif_id)
    {
        return self::find($tarif_id);
    }

    public static function amountWithDraw($amountWithDrawal, $categorie_id, $role_id)
    {
        $serviceCharge = self::getTarif($amountWithDrawal, $categorie_id, $role_id);
        if (!empty($serviceCharge) and !is_null($serviceCharge['montant']) and !is_null($serviceCharge['type'])) {
            switch ($serviceCharge['type']) {
                case '%':
                    self::$serviceCharge = ($amountWithDrawal * $serviceCharge['montant']) / 100;
                    break;

                default:
                    self::$serviceCharge = $serviceCharge['montant'];
                    break;
            }
            return ($amountWithDrawal + self::$serviceCharge);
        } else {
            return false;
        }
    }

    public static function getTarif($amountWithDrawal, $categorie_id, $role_id)
    {
        $data = self::getAllTarif()->toArray();
        $serviceCharge = [];
        switch (!empty($data)) {
            case true:
                for ($i = 0; $i < count($data); $i++) {
                    if (($data[$i]['tranche_min'] <= $amountWithDrawal) and ($data[$i]['tranche_max'] >= $amountWithDrawal) and ($data[$i]['categorie_id'] == $categorie_id) and ($data[$i]['role_id'] == $role_id)) {
                        transaction::setTarif_grid_id($data[0]['id']);
                        switch ((int)date('G') >= 6 and (int)date('G') <= 22) {
                            case true:
                                $serviceCharge['montant'] = $data[$i]['tarif_day'];
                                $serviceCharge['type'] = $data[$i]['type_tarif'];
                                break;

                            case false:
                                $serviceCharge['montant'] = $data[$i]['tarif_night'];
                                $serviceCharge['type'] = $data[$i]['type_tarif'];
                                break;
                        }
                    }
                }
                break;
        }
        return $serviceCharge;
    }

    public static function getAllTarif()
    {
        return self::with(['categorie', 'role'])->orderBy('created_at', 'desc')->get();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function categorie()
    {
        return $this->belongsTo(Category::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
