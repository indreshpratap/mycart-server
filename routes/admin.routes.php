<?php

$app->post('/save-product', function() use ($app) {

    $form = getBody($app);
    
    $product = R::dispense("products");
    $product->import($form);
    $product->active = 1;
    R::store($product);
    
    ok($app);
});

$app->get('/get-products', function() use ($app) {
   
    $data = R::getAll('select * from products');
  
    echoJSON($app, $data);
});
$app->get('/get-product-by-id/:id', function($id) use ($app) {
   
    $data = R::getRow('select * from products where id=?',[$id]);
  
    echoJSON($app, $data);
});



