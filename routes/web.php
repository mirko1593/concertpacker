<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('concerts/{id}', 'ConcertsConctroller@show');

Route::post('concerts/{id}/orders', 'ConcertOrdersController@store');

Route::get('orders/{id}', 'OrdersController@show');

// Route::get('orders/mockup', function () {
//     $concert = factory(App\Concert::class)->states('published')->create();
//     $order = factory(App\Order::class)->create([
//         'confirmation_number' => 'ORDERCONFIRMATION1234', 
//         'card_last_four' => '1234'
//     ]);
//     $ticket = factory(App\Ticket::class)->create([
//         'code' => 'TICKETCODE1', 
//         'concert_id' => $concert->id, 
//         'order_id' => $order->id
//     ]);
//     return view('orders.show', compact('order'));
// });
