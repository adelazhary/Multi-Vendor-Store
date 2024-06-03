<?php

namespace App\Repositories\Cart;

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

        if ($item) {
            $this->update($product, $quantity);
            return $item->increment('quantity', $quantity);
        }
        if (!$item) {
            $cart = Cart::Create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'quantity' => $quantity,
                'cookie_id' => $this->getCookieId(),
            ]);
            $this->get()->push($cart);
            return $cart;
        }
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
    }
    public function total(): float
    {
        return $this->get()->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });
    }
    public function storeOrder($request)
    {
        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => order::getNextOrderNumberAttribute(),
                'payment_method' => $request->payment_method,
                'store_id' => $request->store_id,
                'payment_status' => 'unpaid',
            ]);
            foreach ($this->get() as $item) {
                OrderItem::create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                    'product_name' => $item->product->name,
                    'order_id' => $order->id,
                ]);
            }
            foreach ($request->post('address') as $type => $address) {
                $order->addresses()->create([
                    'type' => $type,
                ]);
            }
            DB::commit();
            return $order;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

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
