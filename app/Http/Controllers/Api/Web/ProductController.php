<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // withAvg('reviews', 'rating') -> mengambil rata-rata rating dari review dari tabel reviews
        // withCount('reviews') -> menghitung jumlah review dari tabel reviews
        //get products
        $products = Product::with('category')
        //count and average
        ->withAvg('reviews', 'rating')
        ->withCount('reviews')
        //search
        ->when(request()->q, function($products) {
            $products = $products->where('title', 'like', '%'. request()->q . '%');
        })->latest()->paginate(8);
        
        //return with Api Resource
        return new ProductResource(true, 'List Data Products', $products);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        // query menggunakan eager loading,eager loading adalah cara untuk mengambil data terkait dari model yang berelasi
        $product = Product::with('category', 'reviews.customer')
        //count and average
        ->withAvg('reviews', 'rating')
        ->withCount('reviews')
        ->where('slug', $slug)->first();
        
        if($product) {
            //return success with Api Resource
            return new ProductResource(true, 'Detail Data Product!', $product);
        }

        //return failed with Api Resource
        return new ProductResource(false, 'Detail Data Product Tidak Ditemukan!', null);
    }
}
