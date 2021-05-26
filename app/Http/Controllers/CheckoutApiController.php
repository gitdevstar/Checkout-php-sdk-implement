<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Payment;

use Checkout\CheckoutApi;
use Checkout\Models\Tokens\Card;
use Checkout\Models\Payments\TokenSource;

class CheckoutApiController extends Controller
{
    protected static $secretKey = "sk_test_bcb8ffa7-8597-4eb2-9f7b-ad6cc0073f65";
    protected static $publicKey = "pk_test_9309fb5b-9fe1-4a45-a2a5-e594e14c9fe8";
    protected static $checkout;

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
            $payment = new Chekcout\Models\Payments\Payment($method, $currency); // GBP
            $payment->amount = $amount;
            $result = $this->checkout->payments()->request($payment);

            Payment::create([
                'name' => 'test name',
                'amount' => $amount,
                'currency' => $currency,
                'email' => 'test email',
                'payment_id' => $result["id"],
                'status' => $result["status"],
                'source' => $result["source"]["type"]
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json($th->getMessage(), 500);
        }
    }


}
