<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        //check review already
        $check_review = Review::where('order_id', $request->order_id)->where('product_id', $request->product_id)->first();

        // pengkondisian jika review sudah ada, maka akan memberikan response 409, yang artinya konflik
        if($check_review) {
            return response()->json($check_review, 409);
        }

        $review = Review::create([
            'rating'        => $request->rating,
            'review'        => $request->review,
            'product_id'    => $request->product_id,
            'order_id'      => $request->order_id,
            'customer_id'   => auth()->guard('api_customer')->user()->id
        ]);

        if($review) {
            //return success with Api Resource
            return new ReviewResource(true, 'Data Review Berhasil Disimpan!', $review);
        }

        //return failed with Api Resource
        return new ReviewResource(false, 'Data Review Gagal Disimpan!', null);
    }
}
