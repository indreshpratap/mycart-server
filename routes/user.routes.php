<?php


$app->get('/get-all-products', function() use ($app) {
   
    $data = R::getAll('select * from products where active = 1');
  
    echoJSON($app, $data);
});


$app->post('/add-to-cart', function() use ($app) {
   $data = getBody($app);
   $cart = R::dispense("cart");
   $cart->username = getUsername();
   $cart->product_id = $data["id"];
   R::store($cart);

   ok($app);
});

$app->get("/remove-from-cart/:cartid", function($cartid) use ($app) {
   
    R::exec("delete from cart where username= ? and id= ?", [getUsername(),$cartid]);
   
    ok($app);
});

$app->get('/cart-items', function() use ($app) {
  
    $data = R::getAll("select c.id,p.name,p.price from cart c,products p where p.id = c.product_id and c.username = ? ", [getUsername()]);
   
    echoJSON($app, $data);
});
