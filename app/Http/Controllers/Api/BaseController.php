<?php

namespace App\Http\Controllers\Api;

use App\Models\Card;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BaseController extends Controller
{


    public function getSmopaye_phone(){
        return '673003170';
    }

    public function validation($request, $array, $message = []){
        if(!empty($message))
            return Validator::make($request, $array, $message);
        return Validator::make($request, $array);
    }
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];


        return response()->json($response, 200);
    }
    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];


        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }
        return response()->json($response, $code);
    }
    // on verifi que le numero de carte existe en bd et quil nappartient a aucuns users
    public function checkCard ($card_number) {
        // ->where('user_id',0)
        $card = Card::where('code_number',$card_number)->where('user_id',null)->count();
        return $card;
    }
// fonction qui envoi les sms
    public static function API_AVS($telnumber, $msg){
        $time = time();

        $url = "54.37.231.5:8090/bulksms";
        $data = array(
            'id'=>27,
            'timestamp'=>time(),
            'phonenumber'=>  '237'.$telnumber,
            'sms'=>$msg,
            'signature'=>hash_hmac('sha1',"qzfrgtyefth".$time,"drytgrfrfegtgh"),
        );

        $data_string = json_encode($data);

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

        $result = curl_exec($curl);

        curl_close($curl);

        $retourdata =  json_decode($result);
    }

}

