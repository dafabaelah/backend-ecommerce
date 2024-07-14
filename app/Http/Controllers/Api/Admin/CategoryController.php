<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
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

        // cek apakah ada query string q
        $cacheKey = 'categories_' . request()->q. '_page_' . request()->get('page', 1);
        // Cek apakah data ada di cache
        if (Redis::exists($cacheKey)) {
            // Ambil data dari cache
            $categories = json_decode(Redis::get($cacheKey));
        } else {
            // Jika tidak ada di cache, query ke database
            $categories = Category::when(request()->q, function($categories) {
                $categories = $categories->where('name', 'like', '%'. request()->q . '%');
            })->latest()->paginate(5);

            // Simpan hasil query ke cache dengan waktu kadaluwarsa (misalnya 60 detik)
            Redis::set($cacheKey, $categories->toJson());
            Redis::expire($cacheKey, 600);
        }

        // versi sebelum menggunakan cache

        // get categories
        // $categories = Category::when(request()->q, function($categories) {
        //     $categories = $categories->where('name', 'like', '%'. request()->q . '%');
        // })->latest()->paginate(5);

        // dd($categories);
        
        //return with Api Resource
        return new CategoryResource(true, 'List Data Categories', $categories);
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
            'image'    => 'required|image|mimes:jpeg,jpg,png|max:2000',
            'name'     => 'required|unique:categories',
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
            'folder' => 'category',
            'quality' => 'auto',
            'fetch_format' => 'auto',
        ]);

        $uploadedFileUrl = $uploadedFile->getSecurePath();
        // $uploadedFilePath = $uploadedFile->getPublicId();

        // dd($uploadedFileUrl, $uploadedFilePath);

        // //upload image
        // $image = $request->file('image');
        // $image->storeAs('public/categories', $image->hashName());

        //create category
        $category = Category::create([
            'image'=> $uploadedFileUrl,
            'name' => $request->name,
            'slug' => Str::slug($request->name, '-'),
        ]);

        if($category) {
            //return success with Api Resource
            return new CategoryResource(true, 'Data Category Berhasil Disimpan!', $category);
        }

        //return failed with Api Resource
        return new CategoryResource(false, 'Data Category Gagal Disimpan!', null);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = Category::whereId($id)->first();
        
        if($category) {
            //return success with Api Resource
            return new CategoryResource(true, 'Detail Data Category!', $category);
        }

        //return failed with Api Resource
        return new CategoryResource(false, 'Detail Data Category Tidak DItemukan!', null);
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
        $validator = Validator::make($request->all(), [
            'name'     => 'required|unique:categories,name,'.$category->id,
            'image'    => 'required|image|mimes:jpeg,jpg,png|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //check image update
        if ($request->file('image')) {

            // Remove old image from Cloudinary if exists
            if ($category->image) {
                // $publicId = pathinfo(parse_url($category->image, PHP_URL_PATH), PATHINFO_FILENAME);
                $publicId = 'category/' . pathinfo(parse_url($category->image, PHP_URL_PATH), PATHINFO_FILENAME);
                // dd($publicId);
                Cloudinary::destroy($publicId);
            }

            // to base64
            $image = $request->file('image')->getRealPath();
            $image_base64 = base64_encode(file_get_contents($image));

            // dd($image_base64);

            // upload to cloudinary
            $uploadedFile = Cloudinary::upload('data:image/png;base64,' . $image_base64, [
                'folder' => 'category',
                'quality' => 'auto',
                'fetch_format' => 'auto',
            ]);

            $uploadedFileUrl = $uploadedFile->getSecurePath();
            // $uploadedFilePath = $uploadedFile->getPublicId();

            // dd($uploadedFileUrl, $uploadedFilePath);

            //remove old image
            // Storage::disk('local')->delete('public/categories/'.basename($category->image));
        
            // //upload new image
            // $image = $request->file('image');
            // $image->storeAs('public/categories', $image->hashName());

            //update category with new image
            $category->update([
                'image'=> $uploadedFileUrl,
                'name' => $request->name,
                'slug' => Str::slug($request->name, '-'),
            ]);

        }

        //update category without image
        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name, '-'),
        ]);

        if($category) {
            //return success with Api Resource
            return new CategoryResource(true, 'Data Category Berhasil Diupdate!', $category);
        }

        //return failed with Api Resource
        return new CategoryResource(false, 'Data Category Gagal Diupdate!', null);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        // $publicId = pathinfo(parse_url($category->image, PHP_URL_PATH), PATHINFO_FILENAME);
        $publicId = 'category/' . pathinfo(parse_url($category->image, PHP_URL_PATH), PATHINFO_FILENAME);
        // dd($publicId);
        Cloudinary::destroy($publicId);

        // dd($del);
        
        // //remove image
        // Storage::disk('local')->delete('public/categories/'.basename($category->image));

        if($category->delete()) {
            //return success with Api Resource
            return new CategoryResource(true, 'Data Category Berhasil Dihapus!', null);
        }

        //return failed with Api Resource
        return new CategoryResource(false, 'Data Category Gagal Dihapus!', null);
    }
}
