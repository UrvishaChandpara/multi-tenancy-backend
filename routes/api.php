<?php

use App\Http\Controllers\Api\SuperAdmin;
use App\Http\Controllers\Api\TenantController;
use Illuminate\Support\Facades\Route;

//super admin login
Route::post('admin/login', [SuperAdmin::class, 'adminLogin']);

Route::prefix('admin')->group(function () {
// Route::prefix('admin')->middleware(['auth:admin', 'scope:admin'])->group(function () {
    //Tenant
    Route::post('tenant/create',[TenantController::class,'createTenant']);
    Route::post('tenant/getall',[TenantController::class,'getAllTenants']);
});