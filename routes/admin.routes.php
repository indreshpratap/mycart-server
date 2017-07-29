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
   
    $data = R::getAll('select * from products where active=1');
  
    echoJSON($app, $data);
});



