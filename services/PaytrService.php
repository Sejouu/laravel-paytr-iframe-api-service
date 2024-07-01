<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class PaytrService
{
    protected $merchantId;
    protected $merchantKey;
    protected $merchantSalt;
    protected $email;
    protected $merchantOid;
    protected $userName;
    protected $userAddress;
    protected $userPhone;
    protected $merchantOkUrl;
    protected $merchantFailUrl;
    protected $currency;
    protected $timeoutLimit;
    protected $debugOn;
    protected $testMode;

    public function __construct()
    {
        $this->merchantId = Config::get('paytr.merchant_id');
        $this->merchantKey = Config::get('paytr.merchant_key');
        $this->merchantSalt = Config::get('paytr.merchant_salt');
        $this->email = "sercan@2sr.net"; // Müşteri e-posta
        $this->userName = "sercan"; // Müşteri adı soyadı
        $this->userAddress = "sercan@2sr.net"; // Müşteri adresi
        $this->userPhone = "05555555555"; // Müşteri telefonu
        $this->merchantOkUrl = route('cart.pay.success'); // Başarılı ödeme sonrası yönlendirme URL
        $this->merchantFailUrl = route('cart.pay.fail'); // Hata durumunda yönlendirme URL
        $this->currency = "TL"; // Ödeme para birimi
        $this->timeoutLimit = "30"; // İşlem zaman aşımı süresi
        $this->debugOn = 1; // Hata mesajlarını göstermek için 1, prodüksiyon için 0
        $this->testMode = 0; // Canlı mod için 0, test modu için 1
    }

    public function initiatePayment(Request $request, $cartProducts, $totalPrice)
    {
        $merchant_oid = uniqid(); // Benzersiz sipariş numarası

        $user_basket = base64_encode(json_encode(array_map(function($item) {
            return [$item['title'], $item['price'], $item['quantity']];
        }, $cartProducts)));

        $user_ip = $request->ip(); // Müşteri IP adresi

        $no_installment = 0; // Taksit seçeneği, tek çekim için 1
        $max_installment = 0; // En fazla taksit adedi, sınırsız ise 0

        $hash_str = $this->merchantId . $user_ip . $merchant_oid . $this->email . $totalPrice . $user_basket . $no_installment . $max_installment . $this->currency . $this->testMode;
        $paytr_token = base64_encode(hash_hmac('sha256', $hash_str . $this->merchantSalt, $this->merchantKey, true));

        $post_vals = [
            'merchant_id' => $this->merchantId,
            'user_ip' => $user_ip,
            'merchant_oid' => $merchant_oid,
            'email' => $this->email,
            'payment_amount' => $totalPrice,
            'paytr_token' => $paytr_token,
            'user_basket' => $user_basket,
            'debug_on' => $this->debugOn,
            'no_installment' => $no_installment,
            'max_installment' => $max_installment,
            'user_name' => $this->userName,
            'user_address' => $this->userAddress,
            'user_phone' => $this->userPhone,
            'merchant_ok_url' => $this->merchantOkUrl,
            'merchant_fail_url' => $this->merchantFailUrl,
            'timeout_limit' => $this->timeoutLimit,
            'currency' => $this->currency,
            'test_mode' => $this->testMode
        ];

        $result = $this->sendRequest($post_vals);

        if ($result['status'] == 'success') {
            $token = $result['token'];

            return $token;
        } else {
            throw new \Exception("PAYTR IFRAME başarısız oldu. Sebep: " . $result['reason']);
        }
    }

    protected function sendRequest($post_vals)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception("PAYTR IFRAME bağlantı hatası. Hata: " . curl_error($ch));
        }

        curl_close($ch);

        return json_decode($result, true);
    }
}
