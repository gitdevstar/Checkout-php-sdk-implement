<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Exception;

use App\Models\Payment;

use Checkout\CheckoutApi;
use Checkout\Models\Tokens\Card;
use Checkout\Models\Payments\TokenSource;

class CheckoutApiController extends Controller
{
    protected static $secretKey = "sk_test_bcb8ffa7-8597-4eb2-9f7b-ad6cc0073f65";
    protected static $publicKey = "pk_test_9309fb5b-9fe1-4a45-a2a5-e594e14c9fe8";
    protected static $checkout;
    protected $curl;
    protected $base_url = 'https://api.sandbox.checkout.com/';
    protected $version = '1.0.15';

    public function __construct()
    {
        self::$checkout = new CheckoutApi(self::$secretKey);
    }

    public function addCard(Request $request)
    {
        $number = $request->number;
        $month = $request->month;
        $year = $request->year;
        $cvv = $request->cvv;
        $holder = $request->holder;

        try {
            $card = new Card($number, $month, $year);

            return $card;

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function payment(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'amount' => 'required',
            'currency' => 'required'
        ]);
        $token = $request->token;
        $amount = $request->amount;
        $currency = $request->currency;
        try {
            $method = new TokenSource($token);
            $payment = new \Checkout\Models\Payments\Payment($method, $currency); // GBP
            $payment->amount = $amount;
            $result = $this->checkout->payments()->request($payment);

            Payment::create([
                'name' => 'test name',
                'amount' => $amount,
                'currency' => $currency,
                'email' => 'test email',
                'payment_id' => $result->id,
                'status' => $result->status,
                'source' => $result->source->type
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json($th->getMessage(), 500);
        }
    }

    public function getPaymentsList(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $limit = $request->limit;

        $params = array();

        if(isset($from))
            $params["from"] = $from;
        if(isset($to))
            $params["to"] = $to;
        if(isset($limit))
            $params["limit"] = $limit;

        try {
            $result = $this->request('get', 'reporting/payments', $params);
            // foreach($result as $payment) {
            //     Payment::create([
            //         'amount' => $payment->actions[0]->breakdown->payout_currency_amount,
            //         'currency' => $payment->payout_currency,
            //         'payment_id' => $payment->id,
            //         'source' => $payment->payment_method,
            //     ]);
            // }
            return response()->json($result);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json($th->getMessage(), 500);
        }
    }

    private function request($method, $path, $params=NULL)
    {
        try {

            $url = $this->base_url.$path;

            $this->curl = curl_init();

             curl_setopt_array($this->curl, array(
                //  CURLOPT_USERAGENT => 'Checkout PHP API Agent',
                 CURLOPT_POST => $method == 'post'? true: false,
                 CURLOPT_RETURNTRANSFER => true)
             );
            if(isset($params))
                $postdata = http_build_query($params);

       //   make request
            $headers = array(
                'Authorization: ' . self::$secretKey,
                'User-Agent: checkout-sdk-php/' . $this->version
            );

             curl_setopt($this->curl, CURLOPT_URL, $url);
            //  if(isset($params))
            //     curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($postdata));
             curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
             $result = curl_exec($this->curl);
			 $httpcode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

             if($httpcode != 200 && $httpcode != 202)
                 throw new Exception('CURL error: ' . curl_error($this->curl));

            //  decode results
			if($result == '')
				return true;

            $result = json_decode($result);
             // if(!is_array($result))
                 // throw new Exception('JSON decode error');

             return $result;
        } catch (Exception $exception) {
            throw new Exception("HTTP request failed: {$url} " . $exception->getMessage(), null, $exception);
        }
    }


}
