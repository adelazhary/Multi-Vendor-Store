<?php

namespace App\Repositories\Cart;

use App\Events\OrderEvent;
use App\Models\cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\product;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartModelRepository implements CartsRepository
{
    protected $items;
    public function __construct()
    {
        $this->items = collect([]);
    }
    public function get()
    {
        if (!$this->items->count()) {
            $this->items = cart::with('product')->Cookie($this->getCookieId())->get();
        }
        return $this->items;
    }
    public function delete(product $product)
    {
        return Cart::Cookie($this->getCookieId())->where('product_id', $product->id)
            ->delete();
    }
    public function add(product $product, $quantity = 1)
    {
        $item = cart::Cookie($this->getCookieId())
            ->where('product_id', $product->id)
            ->where('user_id', auth()->id())
            ->first();
        dd($item);
        // if ($item) {
        //     $this->update($product, $quantity);
        //     return $item->increment('quantity', $quantity);
        // }
        // if (!$item) {
        //     $cart = Cart::Create([
        //         'user_id' => auth()->id(),
        //         'product_id' => $product->id,
        //         'quantity' => $quantity,
        //         'cookie_id' => $this->getCookieId(),
        //     ]);
        //     $this->get()->push($cart);
        //     return $cart;
        // }
    }
    public function update(product $product, $quantity = 1)
    {
        return Cart::Cookie($this->getCookieId())->where('product_id', $product->id)
            ->update([
                'quantity' => request()->quantity,
            ]);
    }
    public function empty()
    {
        return Cart::Cookie($this->getCookieId())->delete();
    }
    public function total(): float
    {
        return $this->get()->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });
    }
    public function storeOrder($request)
    {
        $items = $this->get()->groupBy('store_id');

        DB::beginTransaction();
        try {
            foreach ($items as $store_id => $carts) {
                $order = Order::create([
                    'payment_method' => 'stripe',
                    'store_id' => 1
                ]);
                foreach ($carts as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'price' => $item->product->price,
                        'quantity' => $item->quantity,
                    ]);
                }

                foreach ($request->post('address') as $type => $address) {
                    $order->addresses()->create([
                        'type' => $type,
                        'first_name' => $address['first_name'],
                        'last_name' => $address['last_name'],
                        'street_address' => $address['street_address'],
                        'city' => 'Dhaka',
                        'state' => 'Dhaka',
                        'country' => $address['country'],
                        'email' => $address['email'],
                        'postal_code' => $address['postal_code'],
                        'phone_number' => $address['phone_number'],
                    ]);
                }
            }

            DB::commit();
            event(new OrderEvent($order));
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return $order;
    }

    public function getCookieId()
    {
        $cookie_id = Cookie::get('cart_id');
        if (!$cookie_id) {
            $cookie_id = str::uuid();
            Cookie::queue(Cookie::make('cart_id', $cookie_id, 60 * 24 * 7));
        }
        return $cookie_id;
    }
}
