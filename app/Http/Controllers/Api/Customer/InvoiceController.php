<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $invoices = Invoice::latest()->when(request()->q, function($invoices) {
            $invoices = $invoices->where('invoice', 'like', '%'. request()->q . '%');
        })->where('customer_id', auth()->guard('api_customer')->user()->id)->paginate(5);

        // menggunakan when() untuk mencari data berdasarkan invoice berdasarkan request()->q
        // menggunakan paginate() untuk menampilkan data dengan pagination
        // menggunakan latest() untuk mengurutkan data dari yang terbaru

        // menggunakan where() untuk mencari data berdasarkan customer_id yang sedang login (auth()->guard('api_customer')->user()->id

        //return with Api Resource
        return new InvoiceResource(true, 'List Data Invoices : '.auth()->guard('api_customer')->user()->name.'', $invoices);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($snap_token)
    {
        $invoice = Invoice::with('orders.product', 'customer', 'city', 'province')->where('customer_id', auth()->guard('api_customer')->user()->id)->where('snap_token', $snap_token)->first();

        // menggunakan with() untuk mengambil relasi orders, customer, city, province dengan eager loading
        // menggunakan where() untuk mencari data berdasarkan customer_id yang sedang login (auth()->guard('api_customer')->user()->id
        // snap_token adalah variabel yang token spesfik yang sedang di cari
        // first() untuk mengambil data pertama yang ditemukan
        
        if($invoice) {
            //return success with Api Resource
            return new InvoiceResource(true, 'Detail Data Invoice : '.$invoice->snap_token.'', $invoice);
        }

        //return failed with Api Resource
        return new InvoiceResource(false, 'Detail Data Invoice Tidak DItemukan!', null);
    }
}
