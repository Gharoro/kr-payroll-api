<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\AdminController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

/** Auth Routes */
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => 'auth:api', 'prefix' => 'employee'], function () {
    Route::post('/add', [EmployeeController::class, 'add_employee']);
    Route::get('/all', [EmployeeController::class, 'all_employees']);
    // Route::get('/{id}', [EmployeeController::class, 'single_employee']);
    Route::get('/report/all', [EmployeeController::class, 'employee_reports']);
    Route::put('/{id}/update', [EmployeeController::class, 'edit_employee']);
    Route::get('/user/profile', [EmployeeController::class, 'employee_profile']);
    Route::get('/salary', [EmployeeController::class, 'salary']);
    Route::get('/payslip', [EmployeeController::class, 'payslip']);
});

Route::group(['middleware' => 'auth:api', 'prefix' => 'admin'], function () {
    Route::post('/{id}/payroll', [AdminController::class, 'create_payroll']);
    Route::get('/{id}/employee_payroll', [AdminController::class, 'employee_payroll']);
    Route::get('/{id}/generate_payslip', [AdminController::class, 'payslip']);
    Route::post('/send_payslip', [AdminController::class, 'send_payslip']);
    Route::post('/{id}/remittance', [AdminController::class, 'remittances']);
});
