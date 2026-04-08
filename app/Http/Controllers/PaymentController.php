<?php


namespace App\Http\Controllers;


use App\Models\IaSubscription;
use App\Models\IaTransaction;
use App\Models\Transaction;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function show($reference, Request $request)
    {
        $payment = IaTransaction::query()->firstWhere(['reference'=>$reference]);


        return view('payment.pay', compact('payment'));
    }

    public function success(IaTransaction $order)
    {
        $order->update([
            'status' => 'success'
        ]);

        return view('payment.success');
    }

    public function cancel(IaTransaction $order)
    {
        $order->update([
            'status' => 'failed'
        ]);
        return view('payment.cancel');
    }
}
