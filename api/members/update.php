<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
 
// include database and object files
include_once '../config/database.php';
include_once '../pages/members.php';

// echo print_r($_GET);
// $id = $_GET['id'];
// echo $id;
 
// instantiate database and product object
$database = new Database();
$db = $database->getConnection();
 
// initialize object
$members = new Members($db);

session_start();

$method = $_SERVER['REQUEST_METHOD'];
if($method === "GET"){
    echo json_encode($_SESSION["user"]);
}elseif($method === "POST"){
    $message = $members->update();
    $result = ["message"=>$message];
    if($message==="success"){
        $result['user'] = $_SESSION['user'];
        echo json_encode($result);
    }else{
        echo json_encode($result);
    }
}
 
?>