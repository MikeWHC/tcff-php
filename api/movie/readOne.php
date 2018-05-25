<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
 
// include database and object files
include_once '../config/database.php';
include_once '../pages/movie.php';

// echo $_SERVER['REQUEST_METHOD'];
// echo print_r($_GET);
if(!isset($_GET['id'])){
    echo "error: no movie_id";
    exit;
}elseif(!isset($_GET['cf'])){

    $id = $_GET['id'];
    // echo $id;
    
    // instantiate database and product object
    $database = new Database();
    $db = $database->getConnection();
    
    // initialize object
    $movie = new Movie($db);
    
    // query products
    // $stmt = $product->read();
    // $num = $stmt->rowCount();

    $rs = $movie->readOne($id);

    // echo json_encode($datas);
    // check if more than 0 record found
    // if($num>0){
    
    //     // products array
        $director = [];
        $cast = [];
        // $movie_list_arr["records"]=array();
    
        // retrieve our table contents
        // fetch() is faster than fetchAll()
        // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
        $i = 0;
        while ($row = $rs->fetch_assoc()){
            // extract row
            // this will make $row['name'] to
            // just $name only
            // extract($row);
    
        //     "id": "7",
        // "name_zhtw": "101忠狗",
        // "name_en": "101 Dalmations",
        // "country": "美國",
        // "release_year": "1961",
        // "running_time": "79",
        // "rating": "普遍級",
        // "synopsis": "彭哥（Pongo）是一隻大麥町狗，牠和他的單身漢作曲家主人羅傑（Roger）居住在英國倫敦的一間小公寓中。彭哥對牠單身的生活感到無趣，於是就開始為自己和主人尋找一對異性伴侶。從窗戶的觀察中，彭哥看到了一對完美的組合：一位名叫安妮塔（Anita）的女士以及一隻名叫白佩蒂（Perdita）的大麥町狗。於是，彭哥很快的把羅傑帶出了公寓，並把他拉到公園中，想要讓他們互相見面。彭哥意外的讓羅傑和安妮塔兩人摔到了池塘中，但這卻奇妙地起了效用，他們兩人認識後就相愛了。後來，這對情侶和大麥町狗都結婚了。 之後，白佩蒂產下了十五隻小狗。當晚，庫伊拉（Cruella）前來拜訪羅傑他們，她是安妮塔以前一位很有錢的同學。庫伊拉想要以很高的價錢買下白佩蒂產下的所有小狗，但是羅傑堅持不肯將牠們賣出去。故事內容主要就是敘述小狗被庫伊拉的手下偷走後所發生的冒險事件。",
        // "award": "《101忠狗》是1961年美國迪士尼推出第17部經典動畫長片，被認為是父母最樂意推薦給小朋友觀賞的電影之一。本片採用了當時十分創新的技術來繪製動畫，當時迪士尼為了畫出本片群狗齊聚的畫面，採用一套全新的 XWEOX 動像攝影技術，使本片出現到處都是大麥町的畫面，當時在大銀幕呈現的確是十分令人稱奇。這部動畫片也在1996年改為真人拍攝的電影。",
        // "trailer": "https://www.youtube.com/watch?v=-jzPZjvPWqw",
        // "director_name_zhtw": "克萊德·傑洛尼米",
        // "director_name_en": "Clyde Geronimi",
        // "director_description": "",
        // "cast_name_zhtw": "羅德·泰勒",
        // "cast_name_en": "Rod Taylor",
        // "cast_description": "",
        // "id_session": "31",
        // "date": "2018-07-08",
        // "day": "Sun",
        // "time": "10:00:00",
        // "auditorium": "台北光點"
        // echo print_r($row);
        // echo $row['id'];
            $movie_detail_arr=array(
                "id" => $row['id'],
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

                // "description" => html_entity_decode($description),
                // "price" => $price,
                // "category_id" => $category_id,
                // "category_name" => $category_name
            );
            $row_director=array(
                "id_director" => $row['id_director'],
                "director_name_zhtw" => $row['director_name_zhtw'],
                "director_name_en" => $row['director_name_en'],
                "director_description" => $row['director_description'],
            );
            $row_cast=array(
                "id_cast" => $row['id_cast'],
                "cast_name_zhtw" => $row['cast_name_zhtw'],
                "cast_name_en" => $row['cast_name_en'],
                "cast_description" => $row['cast_description'],
            );
            // echo print_r($row_director);
            // echo print_r($row_cast);
            // if($i % 3 == 0){
            //     // if($row_director['director_name_zhtw'] !== $director[$i-1]['director_name_zhtw']){
            //         // $director[$i] = [];
            //         // array_push($director[$i], $row_director);
            //     // }else{
            //         $cast[$i] = [];
            //         array_push($cast[$i], $row_cast);
            //     }
            // // }else{
            //     if($i<3){
            //     $director[$i] = [];
            //     // $cast[$i] = [];
            //     array_push($director[$i], $row_director);
            //     // array_push($cast[$i], $row_cast);
            // }
            // // if()
            // // array_push($movie_detail_arr, $row);
            if($i === 0){
                // $director[$i] = array();
                // $cast[$i] = array();
                array_push($director, $row_director);
                array_push($cast, $row_cast);
            }
            // foreach ($director as $value) {
                // echo $key;
                // echo print_r($value);
                if($director[sizeof($director)-1]['id_director'] !== $row_director['id_director']){
                    // $director[$i] = [];
                    array_push($director, $row_director);
                    // break;
                }
            // }
            // foreach ($cast as $value) {
                if($cast[sizeof($cast)-1]['id_cast'] !== $row_cast['id_cast']){
                    // $cast[$i] = [];
                    array_push($cast, $row_cast);
                    // break;
                }
            // }
            $i++;
        }
        
        $movie_detail_arr["director"] = $director;
        $movie_detail_arr["cast"] = $cast;
    
        // $movie_detail_arr.each
        echo json_encode($movie_detail_arr);
    // }
    
    // else{
    //     echo json_encode(
    //         array("message" => "No products found.")
    //     );
    // }

}elseif($_GET['cf']){
    $id = $_GET['id'];
    $database = new Database();
    $db = $database->getConnection();
    $movie = new Movie($db);
    $rs = $movie->readOneCF($id);
    $director = [];
    $cast = [];
    $i = 0;
    while ($row = $rs->fetch_assoc()){
        $movie_detail_arr=array(
            "id" => $row['id'],
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
        $row_director=array(
            "id_director" => $row['id_director'],
            "director_name_zhtw" => $row['director_name_zhtw'],
            "director_name_en" => $row['director_name_en'],
            "director_description" => $row['director_description'],
        );
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