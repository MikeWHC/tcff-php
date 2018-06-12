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

$result = [
    'success' => true,
];

if(isset($_POST['email'])){
    // 檢查各欄位值是否符合要求

    if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === false){
        $result['success'] = false;
        $result['message'] = 'email wrong format';
    }

    if(mb_strlen($_POST['username'], 'UTF-8')<2){ //mb_strlen第二個參數是編碼
        $result['success'] = false;
        $result['message'] = 'username wrong format';
    }
    if(strlen($_POST['password'])<6){
        $result['success'] = false;
        $result['message'] = 'password wrong format';
    }

    if($result['success']){
        extract($_POST);
        $member_arr = [
            "email" => $email,
            "password" => $password,
            "username" => $username,
        ];

        $affected_rows = $members->create($member_arr);
        
        if($affected_rows===-1){
            $result['success'] = false;
            $result['message'] = 'email used';
        }elseif($affected_rows===0){
            $result['success'] = false;
            $result['message'] = 'unknown bug';
        }    
    }

} else {
    $json = file_get_contents('php://input');
    $obj = json_decode($json, true);
    
    if(filter_var($obj['email'], FILTER_VALIDATE_EMAIL) === false){
        $result['success'] = false;
        $result['message'] = 'email wrong format';
    }

    // if(mb_strlen($obj['username'], 'UTF-8')<2){ //mb_strlen第二個參數是編碼
    //     $result['success'] = false;
    //     $result['message'] = 'username wrong format';
    // }
    if(strlen($obj['password'])<6){
        $result['success'] = false;
        $result['message'] = 'password wrong format';
    }

    if($result['success']){

        $affected_rows = $members->create($obj);
        
        if($affected_rows===-1){
            $result['success'] = false;
            $result['message'] = 'email used';
        }elseif($affected_rows===0){
            $result['success'] = false;
            $result['message'] = 'unknown bug';
        }    
    }
    // $result['success'] = true;
    // $result['success'] = false;
    // $result['message'] = 'no email';
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);