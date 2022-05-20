<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MercadoPago;

class PaymentController extends Controller
{
    public function __construct()
    {
        MercadoPago\SDK::setAccessToken(config('services.mercadopago.access_token'));
    }

    public function store(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'token' => 'required',
                'paymentMethodId' => 'required',
                'amount' => 'required',
                'installments' => 'required',
                'userEmail' => 'required',
            ]);

            if($validator->fails()) {
                return response()->json(['message' => $validator->errors()->all()], 422);
            }
            $payment = new MercadoPago\Payment();

            $payment->transaction_amount = $request->amount;
            $payment->token = $request->token;
            $payment->description = "Compra";
            $payment->installments = $request->installments;
            $payment->payment_method_id = $request->paymentMethodId;
            $payment->payer = array(
                "email" => $request->userEmail
            );

            $payment->binary_mode = true;

            $payment->save();

            return response()->json([
                "id" => $payment->id,
                "status" => $payment->status,
            ], 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
