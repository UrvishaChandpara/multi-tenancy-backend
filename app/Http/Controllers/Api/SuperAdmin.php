<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\super_admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SuperAdmin extends Controller
{
    /**
     * Super Admin Login.
     */
    public function adminLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message' => $validator->messages()
            ], 400);
        }
        DB::beginTransaction();
        try {
            $user = super_admin::where('username', $request->username)->first();
            if (!$user) {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'User not found'
                ], 200);
            }
            if ($request->password === $user->password) {
                $token = $user->createToken('AdminToken', ['admin'])->accessToken; //specify scope name
                return response()->json([
                    'status_code' => 200,
                    'data' => $user,
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
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to Login.'
            ], 500);
        }
    }
}
