<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\StatistiqueController;
use \App\Http\Controllers\Api\UserController;
use \App\Http\Controllers\Api\CompteController;
use \App\Http\Controllers\Api\DashboardController;
use \App\Http\Controllers\Api\reverseUserController;
use \App\Http\Controllers\Api\TransactionController;
use \App\Http\Controllers\Api\AutoRegistrationController;
use \App\Http\Controllers\Api\SubscriptionController;
use \App\Http\Controllers\Api\CardController;
use \App\Http\Controllers\Api\CategoryController;
use \App\Http\Controllers\Api\CampaignController;
use \App\Http\Controllers\Api\RoleController;
use \App\Http\Controllers\Api\PermissionController;
use \App\Http\Controllers\Api\DeviceController;
use \App\Http\Controllers\Api\TarifController;
use \App\Http\Controllers\Api\EnterpriseController;
use \App\Http\Controllers\Api\User_deviceController;
use \App\Http\Controllers\Api\ParticulierController;
use \App\Http\Controllers\Api\SubUserController;


header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Accept,Authorization, Content-Type, Origin, X-Requested-With,Access-Control-Allow-Headers, Access-Control-Request-Method,access-control-allow-origin");

/*

|--------------------------------------------------------------------------

| API Routes

|--------------------------------------------------------------------------

|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


/*Route::middleware('auth:api')->get('/user', function (Request $request) {

    return $request->user();

});*/

Route::get('statistique/count', [StatistiqueController::class, 'count']);
Route::post('auth/login',[UserController::class, 'login'])->name('LOGIN');
Route::put('account/{account_number}/checkpayment', [CompteController::class, 'validateRechargeAccount']);
Route::post('account/{account_number}/recharge', [CompteController::class, 'postRechargeAccount']);
Route::post('renitializePassword', [UserController::class, 'RenitializePassword']);
Route::get('userCard/{user_id}', [UserController::class, 'userCard'])->where('user_id', '[0-9]+');
Route::post('activationutilisateur/{id}',[AutoRegistrationController::class, 'checkout']);
Route::post('autoregister' ,[AutoRegistrationController::class, 'register']);
Route::post('StatisticalTransaction',[TransactionController::class, 'getStatisticalTransaction']);
Route::get('oldusers',[reverseUserController::class, 'convertion']);
Route::get('/date_create_user', [DashboardController::class, 'getAllMonths']);
Route::get('/get_monthly_users/{month}', [DashboardController::class, 'MonthlyUserCount']);
Route::middleware('auth:api')->group(function () {
    Route::get('/date_create_user', [DashboardController::class, 'getAllMonths']);
    Route::post('account/{account_id}/takeSubscription/',[SubscriptionController::class, 'takeSubscription']);
    Route::resource('card',CardController::class);
    Route::resource('categorie',CategoryController::class);
    Route::resource('campagne',CampaignController::class);
    Route::get('role/entreprise', [RoleController::class, 'entreprise']);
    Route::resource('role',RoleController::class)->middleware('role:administrateur,utilisateur');
    Route::resource('permission', PermissionController::class)->middleware('role:administrateur');
    Route::get('device/attribution/{type}',[DeviceController::class, 'attribution']);
    Route::resource('device',DeviceController::class);
    Route::resource('subscription',SubscriptionController::class);
    Route::resource('tarif',TarifController::class);
    Route::resource('user',UserController::class);
    Route::resource('enterprise',EnterpriseController::class);
    Route::post('user_device/desattribuer', [User_deviceController::class, 'desattribuer']);
    Route::resource('user_device',User_deviceController::class);
    Route::get('transaction/historique/utilisateur', [TransactionController::class, 'loadTransactionUser']);
    Route::get('transaction/{date}/{type_operation}/historique', [TransactionController::class, 'historiqueByTransaction']);
    Route::resource('transaction',TransactionController::class);
    Route::resource('transaction/card',CardController::class);
    Route::resource('transaction/account',CompteController::class);
    Route::resource('card/transaction', TransactionController::class);
    Route::resource('particulier', ParticulierController::class);
    Route::post('account/transaction/debit', [TransactionController::class, 'debit']);
    Route::post('account/transaction/payment', [TransactionController::class, 'paymentAccount']);
    Route::post('card/{card_id}/activation', [CardController::class, 'activation']);
    Route::delete('particulier/{particulier}/delete', [ParticulierController::class, 'destroy']);
    Route::post('card/{card_id}/toggleUnityDeposit',[CardController::class, 'toggleUnityDeposit']);
    Route::post('card/{card_id}/retrait', [CardController::class, 'retraitCarte']);
    Route::post('account/{account_number}/retrait', [CompteController::class, 'retraitCompte']);
    Route::post('account/{account_number}/rechargecarte', [CompteController::class, 'rechargeCarteViaCompte']);
    Route::post('account/{account_id}/activation', [CompteController::class, 'activation']);
    Route::post('enterprise/{enterprise_id}/{enterprise_id_child}/attribution', [EnterpriseController::class, 'attributeDeviceToChild']);
    Route::post('user/{user_id}/card/attribute', [UserController::class, 'addCardToUser']);
    Route::post('user/card/desattribute', [UserController::class, 'removeCardToUser']);
    Route::post('card/transaction/debit', [TransactionController::class, 'debit']);
    Route::post('card/transaction/payment', [TransactionController::class, 'paymenTransaction']);
    Route::post('card/transaction/remote', [TransactionController::class, 'remoteCollection']);
    Route::post('auth/register', [ParticulierController::class, 'register']);
    Route::post('auth/registerEnterprise', [EnterpriseController::class, 'register']);
    Route::post('transaction/filter/compte', [TransactionController::class, 'filterCompte']);
    Route::post('transaction/filter/carte', [TransactionController::class, 'filterCarte']);
    Route::post('resetPassword', [UserController::class, 'resetPassword']);
    Route::get('user/profile/{user_phone}', [UserController::class, 'profile']);
    Route::get('categorierole', [CategoryController::class, 'categorieWithRole']);
    route::post('subuser',[SubUserController::class, 'create']);
    Route::get('card/{card_id}/unity', [CardController::class, 'getUnity']);
    Route::get('card/{card_id}/deposit',[CardController::class, 'getDeposit']);
    Route::get('card/{code_number}/UserCard',[CardController::class, 'findUserCard']);
    Route::get('card/{card_id}/transaction', [TransactionController::class, 'showAllTransaction']);
    Route::get('daterange/transaction', [TransactionController::class, 'daterange']);
    Route::get('account/{account_id}/transaction', [TransactionController::class, 'showAllTransactionAccount']);
    Route::get('auth/logout', [UserController::class, 'logout']);
    Route::get('user/{user_id}/children', [UserController::class, 'getCardUserChild']);
    Route::get('account/user/{user_id}/children', [UserController::class, 'getAccountUserChild']);
    Route::get('enterprise/{enterprise_id}/device', [EnterpriseController::class, 'getAllDevice']);
    Route::get('particulier/{user_id}/children', [ParticulierController::class, 'getUserChild']);
    Route::get('roleWithPermission', [RoleController::class, 'roleWithPermission']);
    Route::get('particulierStaff', [ParticulierController::class, 'particulierStaff']);
    Route::get('particulierAgent', [ParticulierController::class, 'particulierAgent']);
    Route::get('carte_libre', [CardController::class, 'loadusenocard']);
    Route::get('carte_non_libre', [CardController::class, 'loadusecard']);
    Route::get('hasrole/{role}', [ParticulierController::class, 'hasRoleUser']);
    Route::get('recaputilatif', [CardController::class, 'recaputilatif']);
});
