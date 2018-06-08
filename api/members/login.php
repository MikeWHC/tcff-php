<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
// header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Connection, User-Agent, Cookie");

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
    // if(! isset($_SESSION)){
    //     session_start();
    // }
    $json = file_get_contents('php://input');
    $obj = json_decode($json, true);
    // echo "please post data";
    // echo json_encode($obj);
    $result = $members->login($obj['email'], $obj['password']);

    // $result = array("success" => $success);

    // if(isset($_SESSION['user'])){
    //     $result["username"] = $_SESSION['user']['username'];
    //     $result["sessionId"] = session_id();
    // }
    // setcookie("test","cookie",time()+60*60);

    echo json_encode($result);

}