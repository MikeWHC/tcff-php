<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
 
// include database and object files
include_once '../config/database.php';
include_once '../pages/movie.php';

//沒有電影id
if(!isset($_GET['id'])){
    echo "error: no movie_id";
    exit;
}elseif(!isset($_GET['cf'])){ //確映(沒給cf)

    $id = $_GET['id']; //電影id
    
    // instantiate database and product object
    $database = new Database();
    $db = $database->getConnection();
    
    // initialize object
    $movie = new Movie($db);
    
    $rs = $movie->readOne($id);

    $director = [];
    $cast = [];

    $i = 0;
    
    while ($row = $rs->fetch_assoc()){
        //格式
        $movie_detail_arr=array(
            "id_movie" => $row['id_movie'],
            "name_zhtw" => $row['name_zhtw'],
            "name_en" => $row['name_en'],
            "country" => $row['country'],
            "release_year" => $row['release_year'],
            "running_time" => $row['running_time'],
            "rating" => $row['rating'],
            "synopsis" => $row['synopsis'],
            "award" => $row['award'],
            "trailer" => $row['trailer'],
            "id_session" => $row['id_session'],
            "date" => $row['date'],
            "day" => $row['day'],
            "time" => $row['time'],
            "auditorium" => $row['auditorium'],
        );
        //導演
        $row_director=array(
            "id_director" => $row['id_director'],
            "director_name_zhtw" => $row['director_name_zhtw'],
            "director_name_en" => $row['director_name_en'],
            "director_description" => $row['director_description'],
        );
        //演員
        $row_cast=array(
            "id_cast" => $row['id_cast'],
            "cast_name_zhtw" => $row['cast_name_zhtw'],
            "cast_name_en" => $row['cast_name_en'],
            "cast_description" => $row['cast_description'],
        );
        //第一次push
        if($i === 0){
            array_push($director, $row_director);
            array_push($cast, $row_cast);
        }
        //查最後一個，id不重複就push
        if($director[sizeof($director)-1]['id_director'] !== $row_director['id_director']){
            array_push($director, $row_director);
        }
        //查最後一個，id不重複就push
        if($cast[sizeof($cast)-1]['id_cast'] !== $row_cast['id_cast']){
            array_push($cast, $row_cast);
        }

        $i++;
    }
        
    $movie_detail_arr["director"] = $director;
    $movie_detail_arr["cast"] = $cast;

    echo json_encode($movie_detail_arr);

}elseif($_GET['cf']=="true"){ //募資(有給cf=true)
    //電影id
    $id = $_GET['id'];
    $database = new Database();
    $db = $database->getConnection();
    $movie = new Movie($db);

    $rs = $movie->readOneCF($id);

    $director = [];
    $cast = [];
    $i = 0;

    while ($row = $rs->fetch_assoc()){
        //格式
        $movie_detail_arr=array(
            "id_movie" => $row['id_movie'],
            "name_zhtw" => $row['name_zhtw'],
            "name_en" => $row['name_en'],
            "country" => $row['country'],
            "release_year" => $row['release_year'],
            "running_time" => $row['running_time'],
            "rating" => $row['rating'],
            "synopsis" => $row['synopsis'],
            "award" => $row['award'],
            "trailer" => $row['trailer']
        );
        //導演
        $row_director=array(
            "id_director" => $row['id_director'],
            "director_name_zhtw" => $row['director_name_zhtw'],
            "director_name_en" => $row['director_name_en'],
            "director_description" => $row['director_description'],
        );
        //演員
        $row_cast=array(
            "id_cast" => $row['id_cast'],
            "cast_name_zhtw" => $row['cast_name_zhtw'],
            "cast_name_en" => $row['cast_name_en'],
            "cast_description" => $row['cast_description'],
        );
        
        if($i === 0){
            array_push($director, $row_director);
            array_push($cast, $row_cast);
        }
        if($director[sizeof($director)-1]['id_director'] !== $row_director['id_director']){
            array_push($director, $row_director);
        }
        if($cast[sizeof($cast)-1]['id_cast'] !== $row_cast['id_cast']){
            array_push($cast, $row_cast);
        }
        $i++;
    }
    $movie_detail_arr["director"] = $director;
    $movie_detail_arr["cast"] = $cast;
 
    echo json_encode($movie_detail_arr);
}
?>