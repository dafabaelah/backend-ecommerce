<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SliderResource;
use App\Models\Slider;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SliderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //get sliders
        $sliders = Slider::latest()->paginate(5);
        
        //return with Api Resource
        return new SliderResource(true, 'List Data Sliders', $sliders);
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
            'folder' => 'slider',
            'quality' => 'auto',
            'fetch_format' => 'auto',
        ]);

        $uploadedFileUrl = $uploadedFile->getSecurePath();

        // //upload image
        // $image = $request->file('image');
        // $image->storeAs('public/sliders', $image->hashName());

        //create slider
        $slider = Slider::create([
            'image'=> $uploadedFileUrl,
            'link' => $request->link,
        ]);

        if($slider) {
            //return success with Api Resource
            return new SliderResource(true, 'Data Slider Berhasil Disimpan!', $slider);
        }

        //return failed with Api Resource
        return new SliderResource(false, 'Data Slider Gagal Disimpan!', null);
    }

        /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Slider $slider)
    {
        $publicId = 'slider/' . pathinfo(parse_url($slider->image, PHP_URL_PATH), PATHINFO_FILENAME);
        // dd($publicId);
        Cloudinary::destroy($publicId);
        
        //remove image
        // Storage::disk('local')->delete('public/sliders/'.basename($slider->image));

        if($slider->delete()) {
            //return success with Api Resource
            return new SliderResource(true, 'Data Slider Berhasil Dihapus!', null);
        }

        //return failed with Api Resource
        return new SliderResource(false, 'Data Slider Gagal Dihapus!', null);
    }
}
