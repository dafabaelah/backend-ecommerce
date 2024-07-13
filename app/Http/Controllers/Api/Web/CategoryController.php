<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $categories = Redis::get('categories_web');

        if (!$categories) {
            // Jika data tidak ada di Redis, ambil dari database dan simpan di Redis
            $categories = Category::latest()->get();

            // Simpan data ke Redis dengan waktu expired 1 jam
            Redis::setex('categories_web', 3600, serialize($categories));
        } else {
            // Jika data ada di Redis, ambil dari cache dan deserialize data
            $categories = unserialize($categories);
        }

        //get categories
        // $categories, untuk mengambil data categories dengan menggunakan model Category, get() untuk mengambil semua data categories

        // $categories = Category::latest()->get();
        
        //return with Api Resource
        return new CategoryResource(true, 'List Data Categories', $categories);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        // $category, untuk mengambil data category dengan menggunakan model Category, where('slug', $slug) untuk mencari data category berdasarkan slug yang dikirimkan
        // $query->withCount('reviews'), untuk menghitung jumlah review yang dimiliki oleh product
        // $query->withAvg('reviews', 'rating'), untuk menghitung rata-rata rating yang dimiliki oleh product
        $category = Category::with('products.category')
            //get count review and average review
            ->with('products', function ($query) {
                $query->withCount('reviews');
                $query->withAvg('reviews', 'rating');
            })
            ->where('slug', $slug)->first();
        
        if($category) {
            //return success with Api Resource
            return new CategoryResource(true, 'Data Product By Category : '.$category->name.'', $category);
        }

        //return failed with Api Resource
        return new CategoryResource(false, 'Detail Data Category Tidak DItemukan!', null);
    }
}
