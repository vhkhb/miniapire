<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoanDetailsController;

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

Route::post('login', [AuthController::class, 'authenticate'])->name('api.login');

Route::post('register', [AuthController::class, 'createUser'])->name('api.register');

Route::group(['middleware' => ['auth:sanctum']], function(){
    Route::apiResource('loan', LoanDetailsController::class);
    Route::post('loan-status', [LoanDetailsController::class, 'loanStatusChange'])->name('loan.status-change');
    Route::post('repayment', [LoanDetailsController::class, 'repaymentLoan'])->name('loan.repayment');
});
