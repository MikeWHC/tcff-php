<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
 
// include database and object files
include_once '../config/database.php';
include_once '../pages/cast.php';

// instantiate database and product object
$database = new Database();
$db = $database->getConnection();
 
// initialize object
$cart = new Cart($db);

if(! isset($_SESSION)){
    session_start();
}

$rs = $cast->collection();

$result = $rs->fetch_all(MYSQLI_ASSOC);

echo json_encode($result);

?>