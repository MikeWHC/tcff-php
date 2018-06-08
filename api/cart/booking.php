<?php
// required headers
if($_SERVER['REQUEST_METHOD'] === 'OPTIONS'):
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: X-PINGOTHER, Content-Type");

else:
    header("Access-Control-Allow-Origin: *");
    // header("Content-Type: *; charset=UTF-8");
    header("Content-Type: application/json; charset=UTF-8");
    
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
    $result = $cart->booking();
    echo json_encode($result);
endif;

// $result = $rs->fetch_all(MYSQLI_ASSOC);

// echo var_dump($result);
// echo print_r($result);
// echo $result;
// echo json_encode($result);
// echo parse_str($result);
// echo var_dump($result);
// echo $result;

?>