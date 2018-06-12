<?php
// required headers
if($_SERVER['REQUEST_METHOD'] === 'OPTIONS'):
    header("Access-Control-Allow-Origin: * ");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
    header("Access-Control-Allow-Methods: GET,POST,PUT,OPTIONS,DELETE");
    // header('Access-Control-Request-Method', '*');

else:
    header("Access-Control-Allow-Origin: *");
    // header("Content-Type: *; charset=UTF-8");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET,POST,PUT,OPTIONS,DELETE");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
    
    // include database and object files
    include_once '../config/database.php';
    include_once '../pages/cart.php';

    // instantiate database and product object
    $database = new Database();
    $db = $database->getConnection();
    
    // initialize object
    $cart = new Cart($db);

    // if(! isset($_SESSION)){
    //     session_start();
    // }
    $result = $cart->collection();
    echo json_encode($result);
endif;
// header("Access-Control-Allow-Origin: *");
// header("Content-Type: application/json; charset=UTF-8");
 
// // include database and object files
// include_once '../config/database.php';
// include_once '../pages/cart.php';

// // instantiate database and product object
// $database = new Database();
// $db = $database->getConnection();
 
// // initialize object
// $cart = new Cart($db);

// // if(! isset($_SESSION)){
// //     session_start();
// // }

// $result = $cart->collection();

// // $result = $rs->fetch_all(MYSQLI_ASSOC);

// echo json_encode($result);
// echo parse_str($result);
// echo var_dump($result);
// echo $result;

?>