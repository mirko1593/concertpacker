<?php

namespace App\Http\Controllers;

use App\Order;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function show($confirmation_number)
    {
        $order = Order::findByConfirmationNumber($confirmation_number);
        
        return view('orders.show', compact('order'));        
    }    
}
