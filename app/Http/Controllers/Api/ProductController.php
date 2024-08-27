<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\product;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Traits\ImageHandleTrait;

class ProductController extends Controller
{
    use ImageHandleTrait;

    /** add product */
    public function addProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required', //image should be in base64
            'price' => 'required|numeric|min:1',
            'tenant_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message' => $validator->messages()
            ], 400);
        }
        DB::beginTransaction();
        try {
            $tenant=Tenant::find($request->tenant_id);
            if(!$tenant)
            {
                return response()->json([
                    'status_code'=>404,
                    'message'=>'Tenant not found'
                ],400);
            }
            $product=new product();
            $product->name=$request->name;
            $product->description=$request->description;
            $product->price=$request->price;
            $product->save();
            $image = $this->decodeBase64Image($request->image);
            $imageName = 'product_' . $product->id . '.' . $image['extension'];
            $imagePath = 'tenant' . $request->tenant_id . '/product/' . $imageName;
            Storage::put($imagePath, $image['data']);
            $product->image = 'storage/tenant' . $request->tenant_id . '/' . $imageName;
            // $product->image = 'storage/tenant' . $request->tenant_id . '/public/sproduct/' . $imageName;
            $product->save();
            DB::commit();

            return response()->json([
                'status_code' => 200,
                'message' => 'Product added successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to add product'
            ], 500);
        }
    }

    /** get products */
    public function getAllProducts()
    {
        try {
           $products=product::all();
            return response()->json([
                'status_code' => 200,
                'data' => $products,
                'message' => 'Products retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to retrieve products'
            ], 500);
        }
    }
}
