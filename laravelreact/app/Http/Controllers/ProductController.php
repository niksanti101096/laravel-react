<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;



class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        return response()->json(array('status' => 200, 'products' => $products));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * @var \Illuminate\Filesystem\FilesystemAdapter
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'image' => 'required|image',
        ]);

        try {
            $imagePath = Str::random() . '.' . $request->image->getClientOriginalExtension();

            Storage::putFileAs("/public/products/image", $request->image, $imagePath, 'public');
            Product::create($request->post() + ["image" => $imagePath]);

            return response()->json([
                "message" => "Product created successfully!"
            ]);

        } catch (Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                "message" => "Error while creating a product!"
            ]);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {

        // $product = Product::find($product);
        return response()->json(['product' => $product]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'image' => 'nullable',
        ]);

        try {
            $product->fill($request->post())->update();

            if ($request->hasFile('image')) {
                // Remove old image
                if ($product->image) {
                    $oldImage = Storage::disk('public')->exists("/public/products/image/{$product->image}");
                    
                    if ($oldImage) {
                        Storage::disk('public')->delete("/products/image/{$product->image}");
                    }
                }
                $imagePath = Str::random() . '.' . $request->image->getClientOriginalExtension();

                Storage::putFileAs("/public/products/image", $request->image, $imagePath, 'public');
                $product->image = $imagePath;
                $product->save();
            }

            return response()->json([
                "message" => "Product updated successfully!"
            ]);

        } catch (Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                "message" => "Error while creating a product!"
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            if ($product->image) {
                $oldImage = Storage::disk('public')->exists("/public/products/image/{$product->image}");
                if ($oldImage) {
                    Storage::disk('public')->delete("/public/products/image/{$product->image}");
                }
            }
            $product->delete();
            return response()->json(['message', 'Product deleleted successfully']);

        } catch (Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                "message" => "Error while deleting a product!"
            ]);
        }
    }
}