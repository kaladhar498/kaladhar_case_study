<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categorys = Category::all();
        return response()->json([
            "success" => true,
            "message" => "Categories List",
            "data" => $categorys
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
            'description' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        DB::beginTransaction();
        try {
            $category = Category::create($input);
            DB::commit();
            return response()->json([
                "success" => true,
                "message" => "Category created successfully.",
                "data" => $category
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
        $category = Category::find($id);
        if (is_null($category)) {
            return response()->json(['error' => 'Category not found.'], 401);
        }
        return response()->json([
            "success" => true,
            "message" => "Category retrieved successfully.",
            "data" => $category
        ]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'description' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        DB::beginTransaction();
        try {
            $category->name = $input['name'];
            $category->detail = $input['description'];
            $category->save();
            DB::commit();
            return response()->json([
                "success" => true,
                "message" => "Category updated successfully.",
                "data" => $category
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
    public function destroy(Category $category)
    {
        DB::beginTransaction();
        try {
            $category->delete();
            DB::commit();
            return response()->json([
                "success" => true,
                "message" => "Category deleted successfully.",
                "data" => $category
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', "Exception: " . $e->getMessage());
        }
    }
}
