<?php
// required headers
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

if(! isset($_SESSION)){
    session_start();
}

$rs = $members->orders();

// $result_single = array(
//         "order_date" => $row['order_date'],
//         "order" => array(
//             "id_session" => $row['id_session'],
//             "seat_num" => '',
//             "seat_count" => 0,
//         ),
//     );
$session = [];
$result = [];

while($row = $rs->fetch_assoc()){    
    $seat_num = $row['seat'];
    // if(!isset($result[$row["id_session"]])){
        $result["session_".$row["id_session"]] .= "$seat_num,";
    // }

    // foreach($result as $value){
    //     if($value['order_date'] === $row['order_date']){
    //         if(!empty($value['order']["id_session"][$row["id_session"]])){
    //             $value['order']['seat_num'] .= "$seat_num,";
    //         }else{
    //             $value['order'] = array(
    //                 "id_session" => $row['id_session'],
    //                 "seat_num" => 0,
    //                 "seat_count" => 0,
    //             );
    //         }
    //     }else{
    //         $result_single = array(
    //             "order_date" => $row['order_date'],
    //         );
    //     }
    // }
    // array_push($result, array(
    //     "order_date" => $row['order_date'],
    //     "order" => array(
    //         "id_session" => $row['id_session'],
    //         "seat_num" => ,
    //         "seat_count" => ,
    //     ),
    // ));
    // $session[$row['id_session']] = array()
    // $result[0]['order_date']
};


// $result = $rs->fetch_all(MYSQLI_ASSOC);

echo json_encode($result);

?>