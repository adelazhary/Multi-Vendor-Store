<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartStoreRequest;
use App\Http\Requests\CartUpdateRequest;
use App\Models\product;
use App\Repositories\Cart\CartModelRepository;

class CartController extends Controller
{
    protected $cartModelRepository;
    public function __construct(CartModelRepository $cartModelRepository)
    {
        $this->cartModelRepository = $cartModelRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('front.cart.index')->with([
            'cart' => $this->cartModelRepository,
            'total' => $this->cartModelRepository->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CartStoreRequest $request, product $product)
    {
        $this->cartModelRepository->add($product, $request->quantity);
        return to_route('cart.index')->with('success','Product added to cart');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CartUpdateRequest $cartUpdateRequest , product $product)
    {
        dd($this->cartModelRepository->update($product, $cartUpdateRequest->quantity));
        // $product->update(['quantity' => $cartUpdateRequest->quantity]);
        // return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(product $product)
    {
        $this->cartModelRepository->delete($product);
        return back();
    }
}
