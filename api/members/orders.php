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

// if(! isset($_SESSION)){
//     session_start();
// }

$rs = $members->orders();

// $result_single = array(
//         "order_date" => $row['order_date'],
//         "order" => array(
//             "id_session" => $row['id_session'],
//             "seat_num" => '',
//             "seat_count" => 0,
//         ),
//     );
// $session = [];
$result = [];

while($row = $rs->fetch_assoc()){    
    $seat_num = $row['seat'];
    $result[] = $row;
    // if(!isset($result[$row["id_session"]])){
        // $result["session_".$row["id_session"]] .= "$seat_num,";
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

echo json_encode($result);
exit;
// $result = $rs->fetch_all(MYSQLI_ASSOC);
// echo var_dump(array_count_values($result));

$session = [];
$date = [];
$seat = [];
$prefix = array(&$session, &$date, &$seat);

function splitThree($value, $key, &$prefix){
    $vseat = $value["seat"];
    $vsession = $value["id_session"];
    $vdate = $value["order_date"];
    // echo $vseat;
    array_push($prefix[2], $vseat);
    array_push($prefix[0], $vsession);
    array_push($prefix[1], $vdate);
    // $seat[] = $vseat;
    // $session[] = $vsession;
    // $date[] = $vdate;
}
array_walk($result, "splitThree", $prefix);
$times = array_count_values($date);
// echo "order_times: <br/>";
// echo print_r($times);
$index = [];
for($i=0;$i<count($times);$i++){
    $index[] = array_sum(array_slice($times, 0, $i+1));
}
// $index = array(
//     $times[0]
// )
$orders = [];
for($i=0;$i<count($result);$i++){
    for($j=0;$j<count($index);$j++){
        if(empty($orders[$j])){
                $orders[$j] = [];
            }
        if($i<$index[$j]){
            // echo '1,';
            array_push($orders[$j], $result[$i]);
            break;
        }
    }
    // if($i<$index[0]){
    //     // echo '1,';
    //     array_push($orders[0], $result[$i]);
    // }elseif($i<$index[1]){
    //     // echo '2,';
    //     array_push($orders[1], $result[$i]);
    // }elseif($i<$index[2]){
    //     // echo '3,';
    //     array_push($orders[2], $result[$i]);
    // }elseif($i<$index[3]){
    //     // echo '4,';
    //     array_push($orders[3], $result[$i]);
    // }elseif($i<$index[4]){
    //     // echo '5,';
    //     array_push($orders[4], $result[$i]);
    // }elseif($i<$index[5]){
    //     // echo '6,';
    //     array_push($orders[5], $result[$i]);
    // }
}
// echo var_dump($orders);

$orders2 = [[],[],[],[],[],[]];
// echo count($orders);
// echo "    ";
// echo count($orders[0]);
for($a=0;$a<count($orders);$a++){
    for($b=0;$b<count($orders[$a]);$b++){
        // echo $a;
        // echo ',  ';
        // echo $b;
        // echo ',  ';
        if(empty($orders2[$a])){
            // echo $orders[$a][$b]["order_date"];
            // echo "empty";
            $orders2[$a] = array(
                "order_date" => $orders[$a][$b]["order_date"],
                "session" => array($orders[$a][$b]['id_session']),
                "seat" => array($orders[$a][$b]['seat']),
            );
        }else{
            // echo "push";
            $orders2[$a]["session"][] = $orders[$a][$b]['id_session'];
            $orders2[$a]["seat"][] = $orders[$a][$b]['seat'];
        }
    }
}
// echo print_r($orders2);

// $index2 = 
for($c=0;$c<count($orders2);$c++){
    $index = array_unique($orders2[$c]['session']);
    $times = array_count_values($orders2[$c]['session']);
    $times_accumulate = [];
    for($i=0;$i<count($times);$i++){
        $times_accumulate[] = array_sum(array_slice($times, 0, $i+1));
    }
    // echo print_r($times);
    // echo print_r($times_accumulate);
    // echo print_r($index);


    $orders2[$c]["order"] = [];

    for($e=0;$e<count($orders2[$c]['seat']);$e++){
        for($j=0;$j<count($times_accumulate);$j++){
            if(empty($orders2[$c]["order"][$index[$j]])){
                $orders2[$c]["order"][$index[$j]] = [];
            }
            if($e<$times_accumulate[$j]){
                // echo '1,';
                array_push($orders2[$c]["order"][$index[$j]], $orders2[$c]['seat'][$e]);
                // break;
            }
        }
        // $value[$index] = 
        // $order[$e] = [];
    }
    // $orders2[$c]['session'] = array(
    //     $orders2[$c]['session']
    // )
    // for($d=0;$d<count($orders2[$c]['session']);$d++){
    //     // echo $a;
    //     // echo ',  ';
    //     // echo $b;
    //     // echo ',  ';
    //     if(empty($orders2[$a])){
    //         // echo $orders[$a][$b]["order_date"];
    //         // echo "empty";
    //         $orders2[$a] = array(
    //             "order_date" => $orders[$a][$b]["order_date"],
    //             "session" => array($orders[$a][$b]['id_session']),
    //             "seat" => array($orders[$a][$b]['seat']),
    //         );
    //     }else{
    //         // echo "push";
    //         $orders2[$a]["session"][] = $orders[$a][$b]['id_session'];
    //         $orders2[$a]["seat"][] = $orders[$a][$b]['seat'];
    //     }
    // }
}
echo print_r($orders2);
// echo print_r($times_accumulate);
// echo print_r(array_merge_recursive($orders2[0]["session"], $orders2[0]["seat"]));

// foreach($result as $key=>$value){
//     splitThree($value, $key, $prefix);
//     echo $key;
//     echo print_r($value);
// }
// echo $result[0]['seat'];
// echo "session<br/>";
// echo print_r($session);
// echo "date<br/>";
// echo print_r($date);
// echo "seat<br/>";
// echo print_r($seat);
// echo "prefix<br/>";
// echo var_dump($prefix);
// echo "result<br/>";
// echo json_encode($result);

?>