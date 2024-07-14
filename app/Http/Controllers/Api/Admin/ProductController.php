<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Http\Resources\ProductResource;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        // cek apakah ada query string q
        $cacheKey = 'products_' . request()->q.'_page_' . request()->get('page', 1);
        // Cek apakah data ada di cache
        if (Redis::exists($cacheKey)) {
            // Ambil data dari cache
            $products = json_decode(Redis::get($cacheKey));
        } else {
            // Jika tidak ada di cache, query ke database
            $products = Product::with('category')->when(request()->q, function($products) {
                $products = $products->where('title', 'like', '%'. request()->q . '%');
            })->latest()->paginate(5);

            // Simpan hasil query ke cache dengan waktu kadaluwarsa (misalnya 60 detik)
            Redis::set($cacheKey, $products->toJson());
            Redis::expire($cacheKey, 600);
        }

        //get products
        // $products = Product::with('category')->when(request()->q, function($products) {
        //     $products = $products->where('title', 'like', '%'. request()->q . '%');
        // })->latest()->paginate(5);

        // dd($products);
        
        //return with Api Resource
        return new ProductResource(true, 'List Data Products', $products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image'         => 'required|image|mimes:jpeg,jpg,png|max:2000',
            'title'         => 'required|unique:products',
            'category_id'   => 'required',
            'description'   => 'required',
            'weight'        => 'required',
            'price'         => 'required',
            'stock'         => 'required',
            'discount'      => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // to base64
        $image = $request->file('image')->getRealPath();
        $image_base64 = base64_encode(file_get_contents($image));

        // dd($image_base64);

        // upload to cloudinary
        $uploadedFile = Cloudinary::upload('data:image/png;base64,' . $image_base64, [
            'folder' => 'product',
            'quality' => 'auto',
            'fetch_format' => 'auto',
        ]);

        $uploadedFileUrl = $uploadedFile->getSecurePath();

        //upload image
        // $image = $request->file('image');
        // $image->storeAs('public/products', $image->hashName());

        //create product
        $product = Product::create([
            'image'         => $uploadedFileUrl,
            'title'         => $request->title,
            'slug'          => Str::slug($request->title, '-'),
            'category_id'   => $request->category_id,
            'user_id'       => auth()->guard('api_admin')->user()->id,
            'description'   => $request->description,
            'weight'        => $request->weight,
            'price'         => $request->price,
            'stock'         => $request->stock,
            'discount'      => $request->discount
        ]);

        if($product) {
            //return success with Api Resource
            return new ProductResource(true, 'Data Product Berhasil Disimpan!', $product);
        }

        //return failed with Api Resource
        return new ProductResource(false, 'Data Product Gagal Disimpan!', null);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::whereId($id)->first();
        
        if($product) {
            //return success with Api Resource
            return new ProductResource(true, 'Detail Data Product!', $product);
        }

        //return failed with Api Resource
        return new ProductResource(false, 'Detail Data Product Tidak Ditemukan!', null);
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
        $validator = Validator::make($request->all(), [
            'title'         => 'required|unique:products,title,'.$product->id,
            'category_id'   => 'required',
            'description'   => 'required',
            'weight'        => 'required',
            'price'         => 'required',
            'stock'         => 'required',
            'discount'      => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //check image update
        if ($request->file('image')) {

            // Remove old image from Cloudinary if exists
            if ($product->image) {
                // $publicId = pathinfo(parse_url($product->image, PHP_URL_PATH), PATHINFO_FILENAME);
                $publicId = 'product/' . pathinfo(parse_url($product->image, PHP_URL_PATH), PATHINFO_FILENAME);
                // dd($publicId);
                Cloudinary::destroy($publicId);
            }

            // to base64
            $image = $request->file('image')->getRealPath();
            $image_base64 = base64_encode(file_get_contents($image));

            // dd($image_base64);

            // upload to cloudinary
            $uploadedFile = Cloudinary::upload('data:image/png;base64,' . $image_base64, [
                'folder' => 'product',
                'quality' => 'auto',
                'fetch_format' => 'auto',
            ]);

            $uploadedFileUrl = $uploadedFile->getSecurePath();

            //remove old image
            // Storage::disk('local')->delete('public/products/'.basename($product->image));
        
            //upload new image
            // $image = $request->file('image');
            // $image->storeAs('public/products', $image->hashName());

            //update product with new image
            $product->update([
                'image'         => $uploadedFileUrl,
                'title'         => $request->title,
                'slug'          => Str::slug($request->title, '-'),
                'category_id'   => $request->category_id,
                'user_id'       => auth()->guard('api_admin')->user()->id,
                'description'   => $request->description,
                'weight'        => $request->weight,
                'price'         => $request->price,
                'stock'         => $request->stock,
                'discount'      => $request->discount
            ]);

        }

        //update product without image
        $product->update([
            'title'         => $request->title,
            'slug'          => Str::slug($request->title, '-'),
            'category_id'   => $request->category_id,
            'user_id'       => auth()->guard('api_admin')->user()->id,
            'description'   => $request->description,
            'weight'        => $request->weight,
            'price'         => $request->price,
            'stock'         => $request->stock,
            'discount'      => $request->discount
        ]);

        if($product) {
            //return success with Api Resource
            return new ProductResource(true, 'Data Product Berhasil Diupdate!', $product);
        }

        //return failed with Api Resource
        return new ProductResource(false, 'Data Product Gagal Diupdate!', null);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $publicId = 'product/' . pathinfo(parse_url($product->image, PHP_URL_PATH), PATHINFO_FILENAME);
        // dd($publicId);
        Cloudinary::destroy($publicId);
        //remove image
        // Storage::disk('local')->delete('public/products/'.basename($product->image));

        if($product->delete()) {
            //return success with Api Resource
            return new ProductResource(true, 'Data Product Berhasil Dihapus!', null);
        }

        //return failed with Api Resource
        return new ProductResource(false, 'Data Product Gagal Dihapus!', null);
    }
}
