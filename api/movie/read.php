<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
 
// include database and object files
include_once '../config/database.php';
include_once '../pages/movie.php';
 
// instantiate database and product object
$database = new Database();
$db = $database->getConnection();
 
// initialize object
$movie = new Movie($db);
 
// query products
// $stmt = $product->read();
// $num = $stmt->rowCount();

$rs = $movie->read();
// echo $rs;

// echo json_encode($datas);
// check if more than 0 record found
// if($num>0){
 
//     // products array
    $movie_list_arr=array();
    // $movie_list_arr["records"]=array();
 
    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $rs->fetch_assoc()){
        // extract row
        // this will make $row['name'] to
        // just $name only
        // extract($row);
 
        // $product_item=array(
        //     "id" => $id,
        //     "name" => $name,
        //     "description" => html_entity_decode($description),
        //     "price" => $price,
        //     "category_id" => $category_id,
        //     "category_name" => $category_name
        // );
 
        array_push($movie_list_arr, $row);
    }
 
    echo json_encode($movie_list_arr);
    // echo count($movie_list_arr); //75
// }
 
// else{
//     echo json_encode(
//         array("message" => "No products found.")
//     );
// }
?>