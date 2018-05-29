<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
 
// include database and object files
include_once '../config/database.php';
include_once '../pages/members.php';
 
// instantiate database and product object
$database = new Database();
$db = $database->getConnection();
 
// initialize object
$members = new Members($db);

if(isset($_POST['email'])){
    if(! isset($_SESSION)){
        session_start();
    }

    $success = $members->login($_POST['email'], $_POST['password']);

    $result = array("success" => $success);

    if(isset($_SESSION['user'])){
        $result["username"] = $_SESSION['user']['username'];
    }

    echo json_encode($result);
}else{
    echo "please post data";
}