<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MonetbilController
{
    public static $placePayment = 'https://api.monetbil.com/payment/v1/placePayment';
    public static $checkPayment = 'https://api.monetbil.com/payment/v1/checkPayment';
    public static $payOut = 'https://api.monetbil.com/v1/payouts/withdrawal';

    private static $service = 'EGVL96bVrRD1ug8Mtj05Fz25HvRdqfZG';
    private static $serviceSecret = "xadTRmFnC4zTlXXHidCPckfr6CM3W1z6gdtSoYD2eG7Xluq95XXA9hcojwjPqlVj";
    private static $phoneNumber;
    private static $amount;
    private static $ArrayData;

    public static $placePayement;

    public function __construct($phoneNumber, $amount){
        self::setAmount($amount);
        self::setPhone($phoneNumber);
    }

    /**
     * getAmount
     *
     * @return string
     */
    public static function getAmount()
    {
        return self::$amount;
    }

    /**
     * setAmount
     *
     * @param string $amount
     * @return string
     */
    public static function setAmount($amount)
    {
        self::$amount = $amount;
    }

    /**
     * getPhone
     *
     * @return string
     */
    public static function getPhone()
    {
        return self::$phoneNumber;
    }

    /**
     * setPhone
     *
     * @param string $phoneNumber
     * @return string
     */
    public static function setPhone($phoneNumber)
    {
        self::$phoneNumber = $phoneNumber;
    }

    /**
     * getArrayData
     *
     * @return jsonData
     */
    public static function getArrayData()
    {
        return self::$ArrayData;
    }

    /**
     * setArrayData
     *
     * @param string $ArrayData
     * @return ArrayData
     */
    public static function setArrayData($ArrayData)
    {
        self::$ArrayData = $ArrayData;
    }

    /**
     * placePayement
     * @return array
     */

    public static function placePayment($urlPayment){
        $ch =  curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlPayment);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('service' => self::$service,'phonenumber' => self::getPhone(),'amount' => self::getAmount())));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $json = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return json_decode($json, true);
    }

    public static function validatePayment($paymentId){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$checkPayment);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('paymentId' => $paymentId), '', '&'));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $json = curl_exec($ch);
        return json_decode($json, true);
    }

    public static function payouts(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$payOut);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('service_key' => self::$service,'service_secret' => self::$serviceSecret,'phonenumber' => '237'.self::getPhone(),'amount' => self::getAmount())));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $json = curl_exec($ch);
        return json_decode($json, true);
    }

}
