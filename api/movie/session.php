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

$rs = $movie->session();

$session_arr = [];

while ($row = $rs->fetch_assoc()){
    array_push($session_arr, $row);
}
echo json_encode($session_arr);
?>