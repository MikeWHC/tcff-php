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
 
$rs = $movie->read();

$movie_list_arr=array();
    
while ($row = $rs->fetch_assoc()){        
    array_push($movie_list_arr, $row);
}
 
echo json_encode($movie_list_arr);
    
?>