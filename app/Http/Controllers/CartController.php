<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $carts = Cart::all();
        return response()->json([
            "success" => true,
            "message" => "Cart List",
            "data" => $carts
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
            'user_id' => 'required',
            'product_id' => 'required',
            'qty' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        DB::beginTransaction();
        try {
            session_start();
            $cart = Cart::create([
                'user_id' => auth()->user()->id,
                'product_id' => $request->product_id,
                'qty' => $request->qty,
                'session_id' => session_id(),
            ]);
            DB::commit();
            return response()->json([
                "success" => true,
                "message" => "Cart created successfully.",
                "data" => $cart
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', "Exception: " . $e->getMessage());
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
        $cart = Cart::find($id);
        if (is_null($cart)) {
            return $this->sendError('Cart not found.');
        }
        return response()->json([
            "success" => true,
            "message" => "Cart retrieved successfully.",
            "data" => $cart
        ]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cart $cart)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'qty' => 'required',
            'product_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        DB::beginTransaction();
        try {
            $cart->qty = $input['qty'];
            $cart->product_id = $input['product_id'];
            $cart->save();
            DB::commit();
            return response()->json([
                "success" => true,
                "message" => "Cart updated successfully.",
                "data" => $cart
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
    public function destroy(Cart $cart)
    {
        DB::beginTransaction();
        try {
            $cart->delete();
            DB::commit();
            return response()->json([
                "success" => true,
                "message" => "Cart deleted successfully.",
                "data" => $cart
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', "Exception: " . $e->getMessage());
        }
    }
}
