<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaytrService;


class CartController extends Controller
{

    protected $paytrService;

    public function __construct(PaytrService $paytrService)
    {
        $this->paytrService = $paytrService;
    }

    public function pay(Request $request, PaytrService $paytrService)
    {

        $cartProducts = []; // ürünlerin listesi paytr formatında dizi olarak burada olmalı.

       /*  $user_basket = base64_encode(json_encode(array(
            array("Örnek ürün 1", "18.00", 1), // 1. ürün (Ürün Ad - Birim Fiyat - Adet )
            array("Örnek ürün 2", "33.25", 2), // 2. ürün (Ürün Ad - Birim Fiyat - Adet )
            array("Örnek ürün 3", "45.42", 1)  // 3. ürün (Ürün Ad - Birim Fiyat - Adet )
        ))); */

        $userInfo = [
            'email' => 'sercan@2sr.net',
            'name' => 'sercan',
            'address' => 'sercan@2sr.net',
            'phone' => '05555555555'
        ];

        try {
            $token = $this->paytrService->initiatePayment($request, $cartProducts, 1000, $userInfo);

            return view('cart.pay', compact('token'));
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }



    public function success() {
        
    }
    public function fail() {
        
    }
}
