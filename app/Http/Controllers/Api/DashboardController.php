<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{

    public function  getAllMonths(){
        $users = user::orderBy('created_at','ASC')->pluck('created_at');
        //return $this->sendResponse(new Resource($users), 'succés.');
        return$datecreated=json_decode($users);//$this->sendResponse(new Resource($users), 'les date de ceration ont ete renvoyees avec sucess.');
        //return response()->json($users);

    }
    public function  getUser(){

    }

    public function MonthlyUserCount(Request $request, $month){
        //$month=06;
        var_dump($month); die();
        $monthly_post_count=User::whereMonth('created_at',$month)->get()->count();
        return $monthly_post_count;
    }
}

