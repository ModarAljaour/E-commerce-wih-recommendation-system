<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repository\CartRepository;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Laravel\Sanctum\HasApiTokens;

class OrderController extends Controller
{
    use HasApiTokens;
    use GeneralTrait;

    protected $cart;

    public function __construct(CartRepository $cart)
    {
        $this->cart = $cart;
    }

    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
