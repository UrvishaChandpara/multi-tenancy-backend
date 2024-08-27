<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Stancl\Tenancy\Exceptions\DomainOccupiedByOtherTenantException;

class TenantController extends Controller
{
    /** create tenant */
    public function createTenant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'domain_name' => 'required|string|max:255|unique:domains,domain',
            'email' => 'required|email',
            'password' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message' => $validator->messages()
            ], 400);
        }
        try {
            $existingTenant = Tenant::where('email', $request->email)->first();

            if ($existingTenant) {
                return response()->json([
                    'status_code' => 400,
                    'message' => 'This email is already in use'
                ], 400);
            } else {
                $tenant = new Tenant();
                $tenant->name = $request->name;
                $tenant->email = $request->email;
                $tenant->password = Hash::make($request->password);
                $tenant->save();
                $tenant->domains()->create([
                    'domain' => $request->domain_name . '.' . config('app.domain')
                ]);
            }

            return response()->json([
                'status_code' => 200,
                'message' => 'Tenant created successfully'
            ], 200);
        } catch (DomainOccupiedByOtherTenantException $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 400,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to create tenant'
            ], 500);
        }
    }

    /** get all tenants */
    public function getAllTenants()
    {
        try {
            $tenants = Tenant::with('domains')->get()->makeHidden(['password', 'tenancy_db_name']);
            return response()->json([
                'status_code' => 200,
                'tenants' => $tenants,
                'message' => 'Tenants retrieved successfully'
            ], 200);
        } catch (DomainOccupiedByOtherTenantException $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to retrieve tenants'
            ], 500);
        }
    }

    /** tenant login */
    public function tenantLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message' => $validator->messages()
            ], 400);
        }
        try {
            $tenant = Tenant::where('email', $request->email)->first();
            if (!$tenant) {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'Invalid Credentials'
                ], 200);
            }
            if (Hash::check($request->password, $tenant->password)){
                // $token = $tenant->createToken('TenantToken', ['tenant'])->accessToken; //specify scope name
                $tenant->makeHidden(['password','tenancy_db_name']);
                return response()->json([
                    'status_code' => 200,
                    'data' => $tenant,
                    // 'token' => $token,
                    'message' => 'Login successfully'
                ], 200);
            } else {
                return response()->json([
                    'status_code' => 401,
                    'message' => 'Invalid password',
                ], 200);
            }

            return response()->json([
                'status_code' => 200,
                'message' => 'Logged in successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to login tenant'
            ], 500);
        }
    }
}
