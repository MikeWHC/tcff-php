<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json; charset=UTF-8");
 
// include database and object files
include_once './config/database.php';
include_once './pages/cart.php';

// instantiate database and product object
$database = new Database();
$db = $database->getConnection();
 
// initialize object
$cart = new Cart($db);

$json = file_get_contents('php://input');
// $obj = json_decode($json, true);
// echo json_encode($obj, true);
// echo $json;
session_id($json);

// echo json_encode(session_id());
echo session_status();
session_start("8gvqboaohuinulpg1dv7s535c6");
// echo json_encode($_SESSION);
// if(! isset($_SESSION)){
//     session_start();
// }

// // $result = $cart->booking();
// // echo isset($_SESSION['user']) ? json_encode($_SESSION['user']) : json_encode("error");
// echo isset($_SESSION) ? json_encode($_SESSION) : json_encode("error");
// echo print_r($_SESSION['user']);

?>