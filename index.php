<?php
session_start();
require 'libs/Slim/Slim.php';

require 'libs/rb.php';

// use app\Table;


R::setup('sqlite:dbfile.db');
R::setAutoResolve(TRUE);        //Recommended as of version 4.2

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

/* Utility function */
function toJSON($res) {
    return json_encode($res, JSON_NUMERIC_CHECK);
}

function echoJSON($app, $data) {
    $app->response->headers->set("Content-Type", "application/json");
    echo toJSON($data);
}

function ok($app) {
    echoJSON($app,["status"=>true]);
}

function fail($app) {
    echoJSON($app,["status"=>false]);
}
function getBody($app) {
    return json_decode($app->request()->getBody(), true);
}

function clearSession() {
    $_SESSION["username"] = null;
    $_SESSION["role"] = null;
}

function getUsername(){
    if(isset($_SESSION["username"])){
        return $_SESSION["username"];
    }else {
        return "demo";
    }
}

/* End of utility functions */

$app->get("/",function() use ($app){
    $app->render("index.html");

});
$app->group("/api",function() use ($app) {

    $app->get("/initial-setup",function() use ($app) {
        // clear users table
        R::exec('delete from users');
        R::exec('delete from products');
        
        // insert user 
        $user =   R::dispense("users");
        $user->role = "user";
        $user->name = "Test User";
        $user->username = "demo";
        $user->password = "demo123";
        R::store($user);

        // insert admin
        $admin =   R::dispense("users");
        $admin->role = "admin";
        $admin->name = "Test Admin";
        $admin->username = "admin";
        $admin->password = "admin123";
        R::store($admin);

        echo "Setup completed";

    }); 

    $app->post('/dologin', function() use ($app) {

        clearSession();
        $body = getBody($app);

        $info  = R::getRow("select name,role,username from users where username= ? and password = ?",[$body["username"],$body["password"]]);
    
        if(isset($info) && isset($info["username"])){
            
            $_SESSION["username"] = $info["username"];
            $_SESSION["role"] = $info["role"];

            $info["status"] = true;
            
            echoJSON($app,$info);

        } else {
          fail($app);
        }
    });

$app->get('/user-info',function() use ($app){
    if(isset( $_SESSION["username"]) ){
        $info = R::getRow("select name,role,username from users where username= ?",[getUsername()]);
        if(isset($info)){
            $info["status"]= true;
            echoJSON($app,$info);
        }else {
            fail($app);
        }
    }else {
            fail($app);
        }
});

$app->get('/logout',function() use ($app){
    $_SESSION["username"]=null;
    $_SESSION["role"]=null;
    session_destroy();
    ok($app);
        
});

$app->group("/user", function() use ($app) {
    require 'routes/user.routes.php';
});

$app->group("/admin", function() use ($app) {
    require 'routes/admin.routes.php';
});

      
});

/* *
 * Step 4: Run the Slim application
 *
 * This method should be called last. This executes the Slim application
 * and returns the HTTP response to the HTTP client.
 */
$app->run();
