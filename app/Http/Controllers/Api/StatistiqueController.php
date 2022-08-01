<?php

namespace App\Http\Controllers\Api;

use App\Models\Card;
use App\Models\Category;
use App\Models\Enterprise;
use App\Http\Controllers\Controller;
use App\Models\Particulier;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Resources\Index as Resource;

class StatistiqueController extends BaseController {
    public function count(){
        $statistique = [];
        $nbreTransfertDEPOTUNITE = 0;
        $amountTransfertDEPOTUNITE = 0;
        $nbreTransfertUNITEDEPOT = 0;
        $amountTransfertUNITEDEPOT = 0;
        $nbreTransfertCarte = 0;
        $nbreTransfertCompte = 0;
        $amountTransfertCompte = 0;
        $amountTransfertCarte = 0;
        $nbreQrcode = 0;
        $amountQrcode = 0;
        $nbreDebit = 0;
        $amountDebit = 0;
        $nbreFacture = 0;
        $amountFacture = 0;
        $nbreRechargeOrange = 0;
        $amountRechargeOrange = 0;
        $nbreRechargeMtn = 0;
        $amountRechargeMtn = 0;
        $nbreRetraitMtn = 0;
        $amountRetraitMtn = 0;
        $nbreRetraitOrange = 0;
        $amountRetraitOrange = 0;
        $nbreRetraitCarteMtn = 0;
        $amountRetraitCarteMtn = 0;
        $nbreRetraitCarteOrange = 0;
        $amountRetraitCarteOrange = 0;
        $res = Transaction::all();
        for ($i=0; $i < count($res) ; $i++) {
            switch ($res[$i]['transaction_type']) {
                case 'TRANSFERT_CARTE_A_CARTE':
                    $nbreTransfertCarte = $nbreTransfertCarte + 1;
                    $amountTransfertCarte = $amountTransfertCarte + $res[$i]['amount'];
                    break;
                case 'TRANSFERT_COMPTE_A_COMPTE':
                    $nbreTransfertCompte = $nbreTransfertCompte + 1;
                    $amountTransfertCompte +=$res[$i]['amount'];
                    break;
                case 'PAYEMENT_VIA_QRCODE':
                    $nbreQrcode = $nbreQrcode + 1;
                    $amountQrcode = $amountQrcode + $res[$i]['amount'];
                    break;
                case 'DEBIT_CARTE':
                    $nbreDebit = $nbreDebit + 1;
                    $amountDebit = $amountDebit + $res[$i]['amount'];
                    break;

                case 'PAYEMENT_FACTURE':
                    $nbreFacture = $nbreFacture + 1;
                    $amountFacture = $amountFacture + $res[$i]['amount'];
                    break;
                case 'TRANSFERT_DEPOT_UNITE':
                    $nbreTransfertUNITEDEPOT = $nbreTransfertUNITEDEPOT + 1;
                    $amountTransfertUNITEDEPOT = $amountTransfertUNITEDEPOT + $res[$i]['amount'];
                    break;

                case 'TRANSFERT_UNITE_DEPOT':
                    $nbreTransfertDEPOTUNITE = $nbreTransfertDEPOTUNITE + 1;
                    $amountTransfertDEPOTUNITE = $amountTransfertDEPOTUNITE + $res[$i]['amount'];
                    break;

                case 'RECHARGE_COMPTE_VIA_MONETBIL':
                    switch ($res[$i]['operator']) {
                        case 'MTN':
                            $nbreRechargeMtn += 1;
                            $amountRechargeMtn += $res[$i]['amount'];
                            break;

                        case 'Orange':
                            $nbreRechargeOrange += 1;
                            $amountRechargeOrange += $res[$i]['amount'];
                            break;
                    }
                    break;

                case 'RETRAIT_COMPTE_VIA_MONETBIL':
                    switch ($res[$i]['operator']) {
                        case 'CM_MTNMOBILEMONEY':
                            $nbreRetraitMtn += 1;
                            $amountRetraitMtn += $res[$i]['amount'];
                            break;

                        case 'CM_ORANGEMONEY':
                            $nbreRetraitOrange += 1;
                            $amountRetraitOrange += $res[$i]['amount'];
                            break;
                    }
                    break;

                case 'RETRAIT_CARTE_VIA_MONETBIL':
                    switch ($res[$i]['operator']) {
                        case 'CM_MTNMOBILEMONEY':
                            $nbreRetraitCarteMtn += 1;
                            $amountRetraitCarteMtn += $res[$i]['amount'];
                            break;

                        case 'CM_ORANGEMONEY':
                            $nbreRetraitCarteOrange += 1;
                            $amountRetraitCarteOrange += $res[$i]['amount'];
                            break;
                    }
                    break;

                case 'DEBIT_CARTE':
                    $nbreDebit = $nbreDebit + 1;
                    $amountDebit = $amountDebit + $res[$i]['amount'];
                    break;
            }
        }

        $categories  = Category::where('name','smopaye');
        $statistique['nbreEnterprises'] = Enterprise::count();
        $statistique['nbreParticuliers'] = Particulier::count();
        $statistique['nbrePersonnels'] = User::where('category_id', $categories->first()->id)->orWhere('category_id', $categories->latest()->first())->count();
        $statistique['nbreCartes'] = Card::count();
        $statistique['nbreDebits'] = Transaction::where('transaction_type','DEBIT_CARTE')->orWhere('transaction_type','DEBIT_CARTE')->count();
        $statistique['nbreRecharges'] = Transaction::where('transaction_type','RECHARGE_CARTE_VIA_COMPTE')->orWhere('transaction_type','RECHARGE_COMPTE_VIA_MONETBIL')->count();
        $users = User::select('id', 'created_at')
            ->get()
            ->groupBy(function($date) {
                //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
                return Carbon::parse($date->created_at)->format('m'); // grouping by months
            });

        $usermcount = [];
        $userArr = [];

        foreach ($users as $key => $value) {
            $usermcount[(int)$key] = count($value);
        }

        for($i = 1; $i <= 12; $i++){
            if(!empty($usermcount[$i])){
                $userArr[$i] = $usermcount[$i];
            }else{
                $userArr[$i] = 0;
            }
        }
        $agents = User::select('id', 'created_at')->whereHas(
            'role', function($q){
            $q->where('name', 'Agent');
        }
        )->get()
            ->groupBy(function($date) {
                //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
                return Carbon::parse($date->created_at)->format('m'); // grouping by months
            });

        $accepteurs = User::select('id', 'created_at')->whereHas(
            'role', function($q){
            $q->where('name', 'Accepteur');
        }
        )->get()
            ->groupBy(function($date) {
                //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
                return Carbon::parse($date->created_at)->format('m'); // grouping by months
            });

        $momos = Transaction::select('id', 'created_at')->where([['transaction_type','RECHARGE_COMPTE_VIA_MONETBIL'],['operator','MTN']])->get()
            ->groupBy(function($date) {
                //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
                return Carbon::parse($date->created_at)->format('m'); // grouping by months
            });
        $oms = Transaction::select('id', 'created_at')->where([['transaction_type','RECHARGE_COMPTE_VIA_MONETBIL'],['operator','Orange']])->get()
            ->groupBy(function($date) {
                //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
                return Carbon::parse($date->created_at)->format('m'); // grouping by months
            });

        $momors = Transaction::select('id', 'created_at')->where([['transaction_type','RETRAIT_COMPTE_VIA_MONETBIL'],['operator','CM_MTNMOBILEMONEY']])->get()
            ->groupBy(function($date) {
                //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
                return Carbon::parse($date->created_at)->format('m'); // grouping by months
            });
        $omrs = Transaction::select('id', 'created_at')->where([['transaction_type','RETRAIT_COMPTE_VIA_MONETBIL'],['operator','CM_ORANGEMONEY']])->get()
            ->groupBy(function($date) {
                //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
                return Carbon::parse($date->created_at)->format('m'); // grouping by months
            });
        $comptes = Transaction::select('id', 'created_at')->where('transaction_type','TRANSFERT_COMPTE_A_COMPTE')->get()
            ->groupBy(function($date) {
                //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
                return Carbon::parse($date->created_at)->format('m'); // grouping by months
            });
        $cartes = Transaction::select('id', 'created_at')->where('transaction_type','TRANSFERT_CARTE_A_CARTE')->get()
            ->groupBy(function($date) {
                //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
                return Carbon::parse($date->created_at)->format('m'); // grouping by months
            });
        $depotunites = Transaction::select('id', 'created_at')->where('transaction_type','TRANSFERT_DEPOT_UNITE')->get()
            ->groupBy(function($date) {
                //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
                return Carbon::parse($date->created_at)->format('m'); // grouping by months
            });
        $unitedepots = Transaction::select('id', 'created_at')->where('transaction_type','TRANSFERT_UNITE_DEPOT')->get()
            ->groupBy(function($date) {
                //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
                return Carbon::parse($date->created_at)->format('m'); // grouping by months
            });
        $accepteurmcount = [];
        $accepteurArr = [];

        foreach ($accepteurs as $key => $value) {
            $accepteurmcount[(int)$key] = count($value);
        }

        for($i = 1; $i <= 12; $i++){
            if(!empty($accepteurmcount[$i])){
                $accepteurArr[$i] = $accepteurmcount[$i];
            }else{
                $accepteurArr[$i] = 0;
            }
        }

        $agentmcount = [];
        $agentArr = [];

        foreach ($agents as $key => $value) {
            $agentmcount[(int)$key] = count($value);
        }

        for($i = 1; $i <= 12; $i++){
            if(!empty($agentmcount[$i])){
                $agentArr[$i] = $agentmcount[$i];
            }else{
                $agentArr[$i] = 0;
            }
        }

        $momomcount = [];
        $momoArr = [];

        foreach ($momos as $key => $value) {
            $momomcount[(int)$key] = count($value);
        }

        for($i = 1; $i <= 12; $i++){
            if(!empty($momomcount[$i])){
                $momoArr[$i] = $momomcount[$i];
            }else{
                $momoArr[$i] = 0;
            }
        }

        $omcount = [];
        $omArr = [];

        foreach ($oms as $key => $value) {
            $omcount[(int)$key] = count($value);
        }

        for($i = 1; $i <= 12; $i++){
            if(!empty($omcount[$i])){
                $omArr[$i] = $omcount[$i];
            }else{
                $omArr[$i] = 0;
            }
        }


        $momormcount = [];
        $momorArr = [];

        foreach ($momors as $key => $value) {
            $momormcount[(int)$key] = count($value);
        }

        for($i = 1; $i <= 12; $i++){
            if(!empty($momormcount[$i])){
                $momorArr[$i] = $momormcount[$i];
            }else{
                $momorArr[$i] = 0;
            }
        }

        $omrcount = [];
        $omrArr = [];

        foreach ($omrs as $key => $value) {
            $omrcount[(int)$key] = count($value);
        }

        for($i = 1; $i <= 12; $i++){
            if(!empty($omrcount[$i])){
                $omrArr[$i] = $omrcount[$i];
            }else{
                $omrArr[$i] = 0;
            }
        }

        $comptecount = [];
        $compte = [];

        foreach ($comptes as $key => $value) {
            $comptecount[(int)$key] = count($value);
        }

        for($i = 1; $i <= 12; $i++){
            if(!empty($comptecount[$i])){
                $compte[$i] = $comptecount[$i];
            }else{
                $compte[$i] = 0;
            }
        }

        $cartecount = [];
        $carte = [];

        foreach ($cartes as $key => $value) {
            $cartecount[(int)$key] = count($value);
        }

        for($i = 1; $i <= 12; $i++){
            if(!empty($cartecount[$i])){
                $carte[$i] = $cartecount[$i];
            }else{
                $carte[$i] = 0;
            }
        }

        $depotunitecount = [];
        $depotunite = [];

        foreach ($depotunites as $key => $value) {
            $depotunitecount[(int)$key] = count($value);
        }

        for($i = 1; $i <= 12; $i++){
            if(!empty($depotunitecount[$i])){
                $depotunite[$i] = $depotunitecount[$i];
            }else{
                $depotunite[$i] = 0;
            }
        }

        $unitedepotcount = [];
        $unitedepot = [];

        foreach ($unitedepots as $key => $value) {
            $unitedepotcount[(int)$key] = count($value);
        }

        for($i = 1; $i <= 12; $i++){
            if(!empty($unitedepotcount[$i])){
                $unitedepot[$i] = $unitedepotcount[$i];
            }else{
                $unitedepot[$i] = 0;
            }
        }

        $statistique['usermcount'] = $usermcount;
        $statistique['userArr'] = $userArr;
        $statistique['accepteurmcount'] = $accepteurmcount;
        $statistique['accepteurArr'] = $accepteurArr;
        $statistique['agentmcount'] = $agentmcount;
        $statistique['agentArr'] = $agentArr;
        $statistique['momoArr'] = $momoArr;
        $statistique['omArr'] = $omArr;
        $statistique['momorArr'] = $momorArr;
        $statistique['omrArr'] = $omrArr;
        $statistique['compte'] = $compte;
        $statistique['carte'] = $carte;
        $statistique['unitedepot'] = $unitedepot;
        $statistique['depotunite'] = $depotunite;
        $statistique['nbreRechargeOrange'] = $nbreRechargeOrange;
        $statistique['nbreQrcode'] = $nbreQrcode;
        $statistique['nbreFacture'] = $nbreFacture;
        $statistique['nbreTransfertDEPOTUNITE'] = $nbreTransfertDEPOTUNITE;
        $statistique['nbreTransfertUNITEDEPOT'] = $nbreTransfertUNITEDEPOT;
        $statistique['nbreTransfertCarte'] = $nbreTransfertCarte;
        $statistique['nbreTransfertCompte'] = $nbreTransfertCompte;

        return $this->sendResponse(new Resource($statistique), 'statistique.');
    }
}


