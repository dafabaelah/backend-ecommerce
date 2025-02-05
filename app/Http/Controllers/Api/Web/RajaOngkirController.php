<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\RajaOngkirResource;
use App\Models\City;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

class RajaOngkirController extends Controller
{
    /**
     * getProvinces
     *
     * @return void
     */
    public function getProvinces()
    {

        $provinces = Redis::get('provinces_web');

        if (!$provinces) {
            // Jika data tidak ada di Redis, ambil dari database dan simpan di Redis
            $provinces = Province::all();

            // Simpan data ke Redis dengan waktu expired 1 jam
            Redis::setex('provinces_web', 3600, serialize($provinces));
        } else {
            // Jika data ada di Redis, ambil dari cache dan deserialize data
            $provinces = unserialize($provinces);
        }

        //get all provinces
        // $provinces = Province::all();

        //return with Api Resource
        return new RajaOngkirResource(true, 'List Data Provinces', $provinces);
    }
    
    /**
     * getCities
     *
     * @param  mixed $request
     * @return void
     */
    public function getCities(Request $request)
    {   

        $cacheKey = 'cities_province_' . $request->province_id;

        // cek apakah data ada di cache
        if (Redis::exists($cacheKey)) {
            // ambil data dari cache
            $cities = json_decode(Redis::get($cacheKey));
            $province = Province::where('province_id', $request->province_id)->first();
            // dd($province);
        } else {
            // jika tidak ada di cache, query ke database
            $province = Province::where('province_id', $request->province_id)->first();

            if (!$province) {
                // Rreturn error message if province not found
                return new RajaOngkirResource(false, 'Province not found', null);
            }

            $cities = City::where('province_id', $request->province_id)->get();

            // Store query result in cache
            Redis::set($cacheKey, $cities->toJson());
            Redis::expire($cacheKey, 600);
        }

        // //get province name
        // $province = Province::where('province_id', $request->province_id)->first();

        // //get cities by province
        // $cities = City::where('province_id', $request->province_id)->get();

        //return with Api Resource
        return new RajaOngkirResource(true, 'List Data City By Province : '.$province->name.'', $cities);
    }
    
    /**
     * checkOngkir
     *
     * @param  mixed $request
     * @return void
     */
    public function checkOngkir(Request $request)
    {
        //Fetch Rest API
        $response = Http::withHeaders([
            //api key rajaongkir
            'key'          => config('services.rajaongkir.key')
        ])->post('https://api.rajaongkir.com/starter/cost', [

            //send data
            'origin'      => 149, // ID kota Demak
            'destination' => $request->destination,
            'weight'      => $request->weight,
            'courier'     => $request->courier    
        ]);

        //return with Api Resource
        return new RajaOngkirResource(true, 'List Data Biaya Ongkos Kirim : '.$request->courier.'', $response['rajaongkir']['results'][0]['costs']);
    }
}
