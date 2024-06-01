<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; 
use Illuminate\Support\Facades\File; 

class ProductController extends Controller
{
    // show product page 
    public function index()
    {
        $products = Product::orderBy('created_at', 'DESC')->get();
        return view('products.list',[
            'products' => $products
        ]);
    }

    // show create product page 
    public function create(){
        return view('products.create');
    }

    // store a product in DB 
    public function store(Request $request){
        $rules = [
            'name' => 'required|min:5',
            'sku' => 'required|min:3',
            'price' => 'required|numeric'
        ];

        if($request->image != ""){
            $rules['image'] = 'image';
        }

        $validator = Validator::make($request->all(),$rules);

        if($validator->fails()){
            return redirect()->route('products.create')->withInput()->withErrors($validator);
        }

        // here we will insert product in DB
        $product = new Product();
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->save();

        if($request->image != ""){
                // here we will sotre image 
            $image = $request->image;
            $ext = $image->getClientOriginalExtension();
            $imageName = time().'.'.$ext; //unique image name 

            // save image to products directory
            $image->move(public_path('uploads/products'),$imageName);

            // save image name in database
            $product->image = $imageName;
            $product->save();
        }

        return redirect()->route('products.index')->with('success', 'Product added successfully');
    }

    // show edit product page 
    public function edit($id){
        $product = Product::findorFail($id);
        return view('products.edit',[
            'product' => $product
        ]);
    }

    // update a product page 
    public function update($id, Request $request){

        $product = Product::findorFail($id);

        $rules = [
            'name' => 'required|min:5',
            'sku' => 'required|min:3',
            'price' => 'required|numeric'
        ];

        if($request->image != ""){
            $rules['image'] = 'image';
        }

        $validator = Validator::make($request->all(),$rules);

        if($validator->fails()){
            return redirect()->route('products.edit', $product->id )->withInput()->withErrors($validator);
        }

        // here we will update product in DB
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->save();

        if($request->image != ""){

            // delete old image
            File::delete(public_path('uploads/products/'.$product->image));

            // here we will sotre image 
            $image = $request->image;
            $ext = $image->getClientOriginalExtension();
            $imageName = time().'.'.$ext; //unique image name 

            // save image to products directory
            $image->move(public_path('uploads/products'),$imageName);

            // save image name in database
            $product->image = $imageName;
            $product->save();
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully');

    }


    // delete a product 
    public function destroy($id){
        $product = Product::findorFail($id);

        // delete image
        File::delete(public_path('uploads/products/'.$product->image));

        // delete product from database 
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully');
    }




}
