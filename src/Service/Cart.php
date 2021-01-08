<?php


namespace App\Service;


use App\Repository\ProductsRepository;
use PhpParser\Node\Expr\Array_;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Cart
{

    public function retrieveCartItems(SessionInterface $session, ProductsRepository $productsRepository): array
    {

        //This function accesses the session variable and retrieves the items in the cart
        //( cart is implemented via an associative array)

        //will hold the chosen products by the client
        $chosenProducts = array();

        //Total price amount of the cart
        $totalPrice = null;

        //Total amount of items
        $totalQuantity = null;

        //Retrieving the cart in case it's defined
        $cart = $session->get('cart');
        //Ensuring it's defined
        if(isset($cart)) {

            //Array made of the ID's of the products
            $keys = array_keys($cart);
            foreach ($keys as $id)
            {
                //Retrieve the item with id = $id
                $product = $productsRepository->find($id);
                //Using unmapped Product data member(amount) to hold the product quantity
                $product->setAmount($cart[$id]['quantity']);
                //Push the array product into the chosenProducts array
                array_push($chosenProducts, $product);

                $totalPrice += $product->getAmount() * $product->getPrice();
                $totalQuantity += $product->getAmount();
            }
        }

        //INDEX 0 -> PRODUCTS IN THE CART
        //INDEX 1 -> TOTAL PRICE OF THE PRODUCTS IN THE CART
        //INDEX 2 -> TOTAL QUANTITY OF THE PRODUCTS IN THE CART
        return array($chosenProducts, $totalPrice, $totalQuantity);
    }




}