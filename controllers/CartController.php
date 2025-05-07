<?php

namespace App\Controllers;

use App\Core\BaseController;
use \RedBeanPHP\R as R;

class CartController extends BaseController
{
    public function addToCart(): void
    {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

        if (!$productId || !$quantity || $quantity < 1) {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz ürün veya miktar.'], 400);
            return;
        }

        // Sepeti oturumda sakla
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }

        $this->jsonResponse(['success' => true, 'message' => 'Ürün sepete eklendi.']);
    }

    public function viewCart(): void
    {
        $cart = $_SESSION['cart'] ?? [];
        $products = [];

        foreach ($cart as $productId => $quantity) {
            $product = R::load('product', $productId);
            if ($product->id) {
                $products[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'total' => $product->price * $quantity,
                ];
            }
        }

        $this->view('cart', ['products' => $products]);
    }
}