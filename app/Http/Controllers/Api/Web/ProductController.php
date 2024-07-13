<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // cek apakah ada query string q
        $cacheKey = 'products_web' . request()->q;
        // Cek apakah data ada di cache
        if (Redis::exists($cacheKey)) {
            // Ambil data dari cache
            $products = json_decode(Redis::get($cacheKey));
        } else {
            // Jika tidak ada di cache, query ke database
            $products = Product::with('category')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when(request()->q, function($products) {
                $products = $products->where('title', 'like', '%'. request()->q . '%');
            })->latest()->paginate(8);

            // Simpan hasil query ke cache dengan waktu kadaluwarsa (misalnya 60 detik)
            Redis::set($cacheKey, $products->toJson());
            Redis::expire($cacheKey, 3600);
        }



        // withAvg('reviews', 'rating') -> mengambil rata-rata rating dari review dari tabel reviews
        // withCount('reviews') -> menghitung jumlah review dari tabel reviews
        //get products
        // $products = Product::with('category')
        // //count and average
        // ->withAvg('reviews', 'rating')
        // ->withCount('reviews')
        // //search
        // ->when(request()->q, function($products) {
        //     $products = $products->where('title', 'like', '%'. request()->q . '%');
        // })->latest()->paginate(8);
        
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
