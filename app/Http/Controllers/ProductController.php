<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::all();
        return response()->json([
            "success" => true,
            "message" => "Product List",
            "data" => $products
        ]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'price' => 'required',
            'category_id' => 'required',
            'avatar' => 'required|mimes:jpeg,jpg,png|max:1024',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        if ($file = $request->file('avatar')) {

            $imageName = time() . '.' . $file->extension();
            $file_path = 'products/' . $imageName;
            
            DB::beginTransaction();
            try {
                $product = Product::create([
                    'name' => $request->name,
                    'user_id' => auth()->user()->id,
                    'category_id' => $request->category_id,
                    'price' => $request->price,
                    'avatar' => $file_path,
                    'description' => $request->description
                ]);
                
                DB::commit();
                $file->move(public_path('products'), $imageName);
                return response()->json([
                    "success" => true,
                    "message" => "Product created successfully.",
                    "data" => $product
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                return redirect()->back()->with('error', "Exception: " . $e->getMessage());
            }
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::find($id);
        if (is_null($product)) {
            return response()->json(['error' => 'Product not found.'], 401);
        }
        return response()->json([
            "success" => true,
            "message" => "Product retrieved successfully.",
            "data" => $product
        ]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'price' => 'required',
            'category_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        DB::beginTransaction();
        try {
            if ($file = $request->file('avatar')) {
                $imageName = time() . '.' . $file->extension();
                $file_path = 'products/' . $imageName;
                $file->move(public_path('products'), $imageName);
                $product->avatar = $file_path;
            }
            $product->name = $input['name'];
            $product->price = $input['price'];
            $product->category_id = $input['category_id'];
            $product->description = $input['description'];
            $product->save();

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Product updated successfully.",
                "data" => $product
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', "Exception: " . $e->getMessage());
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            $product->delete();
            DB::commit();
            return response()->json([
                "success" => true,
                "message" => "Product deleted successfully.",
                "data" => $product
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', "Exception: " . $e->getMessage());
        }
    }
}
