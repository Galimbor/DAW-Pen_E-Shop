<?php


namespace App\Controller;

use App\Service\Cart;
use App\Entity\OrderItems;
use App\Entity\Orders;
use App\Repository\CategoriesRepository;
use App\Repository\OrderItemsRepository;
use App\Repository\OrdersRepository;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class StoreController extends AbstractController
{
//TODO : fix the active tags in the navbar; | DONE - NO MORE TAGS
//TODO : add CRFC tokens in the checkout so that the ADD/DES/ELI only get from there; | gravidade = 0..
//TODO : fix the products filter in the product page, to retrieve the categories from the database | DONE
//TODO : make the "my orders" page more visible | DONE

    private $session;


    public function __construct( SessionInterface $session)
    {
        $this->session = $session;
    }


    /**
     * @Route("/eshop", name="index")
     * @param ProductsRepository $productsRepository
     * @param Cart $cart
     * @return Response
     */
    public function index(ProductsRepository $productsRepository, Cart $cart): Response
    {

        //Retrieving the products in the cart
        $chosenProducts = $cart->retrieveCartItems($this->session,$productsRepository);



        //Rendering the index template
        return $this->render('/index/index.html.twig', [ 'chosenProducts' =>
            $chosenProducts[0], 'totalCartPrice' => $chosenProducts[1], 'totalQuantity' => $chosenProducts[2],
        ]);

    }

    /**
     * @Route("/eshop/products", name="store")
     * @param ProductsRepository $productsRepository
     * @param Cart $cart
     * @return Response
     */
    public function store(ProductsRepository $productsRepository, Cart $cart, CategoriesRepository $categoriesRepository): Response
    {
        //Retrieving the products in the cart
        $chosenProducts = $cart->retrieveCartItems($this->session,$productsRepository);

        //Retrieving all the existent items
        $allProducts = $productsRepository->findAll();

        //Retrieving all the existing categories
        $allCategories = $categoriesRepository->findAll();

        //Rendering the products page
        return $this->render('shop/shop-grid.html.twig', [ 'products'=> $allProducts, 'chosenProducts' =>
            $chosenProducts[0], 'totalCartPrice' => $chosenProducts[1], 'totalQuantity' => $chosenProducts[2],
            'categories' => $allCategories,
        ]);

    }


    /**
     * @Route("/eshop/checkout", name="checkout")
     * @return Response
     */
    public function checkout(ProductsRepository $productsRepository, Cart $cart): Response
    {

        //Retrieving the items in the cart
        $chosenProducts = $cart->retrieveCartItems($this->session,$productsRepository);

        //Rendering the checkout page
        return $this->render('checkout/cart.html.twig', [ 'chosenProducts' =>
            $chosenProducts[0], 'totalCartPrice' => $chosenProducts[1], 'totalQuantity' => $chosenProducts[2],
        ]);

    }


    /**
     * @Route("/eshop/add_product/{id?}", name="addProduct")
     * @param $id
     * @param ProductsRepository $productsRepository
     * @return Response
     */
    public function addProductToCart($id, ProductsRepository $productsRepository): Response
    {
        //productId was defined and it corresponds to a product
        if($id and $productsRepository->find($id) )
        {
            //Retrieve the cart from the session variable
            $cart = $this->session->get('cart');

            //If the cart doesn't yet exist, create it.
            if($cart == null) $cart = array();

            //If the product wasn't yet added, start with quantity of 1.
            if(!isset($cart[$id]['quantity']))  $cart[$id]['quantity'] = 1;
            //Else just increment it.
            else $cart[$id]['quantity']++;

            //Updating the cart in the session variable
            $this->session->set('cart',$cart);

            //Rendering the products page
            return $this->redirectToRoute('store');

        }
        else{
            $this->addFlash('error', 'Something went wrong! Please try again.');
            return $this->redirectToRoute('store');
        }


    }

    /**
     * @Route("/eshop/placeOrder", name="placeOrder")
     * @param ProductsRepository $productsRepository
     * @param Cart $cart
     * @return Response
     */
    public function placeOrder( ProductsRepository $productsRepository, Cart $cart): Response
    {


        //Retrieving the products in the cart
        $chosenProducts = $cart->retrieveCartItems($this->session,$productsRepository);


        //If the user is logged-in and the cart is defined(If there're any products in the cart
        // there's a total price
        if($this->getUser() && isset($chosenProducts[1]))
        {

            //Creating a new Orders object.
            $order = new Orders();
            $order->setCustomer($this->getUser());
            $order->setCreatedAt(date("Y-m-d H:i:s"));
            $order->setStatus(true);
            $order->setTotal($chosenProducts[1]);


            //Inserting new Order in the database.
            $em = $this->getDoctrine()->getManager();
            $em->persist($order);
            $em->flush();


            foreach ($chosenProducts[0] as $product)
            {
                //Creating a new OrderItem object
                $order_id = new OrderItems();
                $order_id->setOrder($order);
                $order_id->setProduct($product);
                $order_id->setQuantity($product->getAmount());

                //Inserting the OrderItem object into the database
                $em = $this->getDoctrine()->getManager();
                $em->persist($order_id);
                $em->flush();
            }


            //Clearing the cart session variable
            $this->session->remove('cart');


            $this->addFlash('success', 'Order successfully completed! Thank you for your purchase.');
            return $this->redirectToRoute('store');

        }
        else{
            $this->addFlash('error', 'Permission denied. Please login or fill your cart.');
            return $this->redirectToRoute('store');
        }


    }


    /**
     * @Route("/eshop/myOrders", name="myOrders")
     * @param ProductsRepository $productsRepository
     * @param OrdersRepository $ordersRepository
     * @param OrderItemsRepository $orderItemsRepository
     * @param Cart $cart
     * @return Response
     */

    public function viewOrders( ProductsRepository $productsRepository, OrdersRepository $ordersRepository,
                                OrderItemsRepository $orderItemsRepository , Cart $cart): Response
    {


        //If the user is logged-in and the cart is defined(If there're any products in the cart
        // there's a total price
        if($this->getUser()  )
        {
            //Retrieving the products in the cart
            $chosenProducts = $cart->retrieveCartItems($this->session,$productsRepository);

            //Retrieving all orders made by the user
            $ordersMadeByUser = $ordersRepository->findBy(array('customer' => $this->getUser()->getId()));

            foreach ($ordersMadeByUser as $order)
            {
                //Saving in a auxiliary data member of the object Orders an orderItems object.
                //Essentially the Order object is holding the information of its products.
                $orderItems = $orderItemsRepository->findBy(array('order' => $order));
                $order->setOrderItems($orderItems);
            }

            return $this->render('orders/order.html.twig',['chosenProducts' =>
                $chosenProducts[0], 'totalCartPrice' => $chosenProducts[1], 'totalQuantity' => $chosenProducts[2],
                'orders' => $ordersMadeByUser]);
        }
        else{
            $this->addFlash('error', 'Permission denied. Please login to view your orders.');
            return $this->redirectToRoute('store');
        }


    }

    /**
     * @Route("/eshop/decreaseQuantity/{id?}", name="decreaseQuantity")
     * @param $id
     * @param ProductsRepository $productsRepository
     * @return Response
     */
    public function decreaseQuantity($id, ProductsRepository $productsRepository): Response
    {

        //This function is used to decrease the quantity of a certain product.
        //Used in the checkout.

        $cart = $this->session->get('cart');

        //productId was defined and it corresponds to a product. The cart is also defined
        // and contains the product inside of it
        if($id and $productsRepository->find($id) and isset($cart[$id]['quantity']) )
        {

            if (($cart[$id]['quantity']) == 1 )  $cart[$id]['quantity'] = 1;
            else $cart[$id]['quantity']--;
            $this->session->set('cart',$cart);
            return $this->redirectToRoute('checkout');

        }
        else{
            $this->addFlash('error', 'Something went wrong! Please try again.');
            return $this->redirectToRoute('checkout');
        }


    }

    /**
     * @Route("/eshop/increaseQuantity/{id?}", name="increaseQuantity")
     * @param $id
     * @param ProductsRepository $productsRepository
     * @return Response
     */
    public function increaseQuantity($id, ProductsRepository $productsRepository): Response
    {

        //This function is used to increase the quantity of a certain product.
        //Used in the checkout.

        $cart = $this->session->get('cart');

        //productId was defined and it corresponds to a product. The cart is also defined
        // and contains the product inside of it
        if($id and $productsRepository->find($id) and isset($cart[$id]['quantity']) )
        {

            if (($cart[$id]['quantity']) == 100 )  $cart[$id]['quantity'] = 100;
            else $cart[$id]['quantity']++;
            $this->session->set('cart',$cart);
            return $this->redirectToRoute('checkout');

        }
        else{
            $this->addFlash('error', 'Something went wrong! Please try again.');
            return $this->redirectToRoute('checkout');
        }


    }


    /**
     * @Route("/eshop/eliminateFromCart/{id?}", name="eliminateFromCart")
     * @param $id
     * @param ProductsRepository $productsRepository
     * @return Response
     */
    public function eliminateFromCart($id, ProductsRepository $productsRepository): Response
    {

            //This function is used to delete a certain product from the cart.
            //Used in the checkout.
            $cart = $this->session->get('cart');

            //productId was defined and it corresponds to a product. The cart is also defined
            // and contains the product inside of it
            if($id and $productsRepository->find($id) and isset($cart[$id]['quantity']) )
            {
                unset($cart[$id]);
                $this->session->set('cart',$cart);
                return $this->redirectToRoute('checkout');
            }
            else{
                $this->addFlash('error', 'Something went wrong! Please try again.');
                return $this->redirectToRoute('checkout');
            }


    }



}