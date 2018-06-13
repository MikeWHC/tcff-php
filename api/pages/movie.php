<?php
class Movie{
 
    // database connection and table name
    private $conn;
 
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    
    // 影片列表(所有、確映、募資)
    public function read(){ 
        //有給query string
        if(!empty($_GET['cf'])) $cf = $_GET['cf']=="true" ? 1 : 0;
        //查詢條件
        $whereStr = empty($_GET['cf']) ? 1 : "`cf`=$cf";  //沒給query string就查全部

        //查詢列表
        $sql = "SELECT m.id AS id_movie, m.name_zhtw, m.name_en, m.release_year, m.theme, m.cf, d.name_zhtw AS director_name
            FROM movie m
            INNER JOIN (movie_director md
                INNER JOIN director d
                ON md.id_director = d.id
            ) ON md.id_movie = m.id
            WHERE $whereStr
            ORDER BY id_movie";

        $rs = $this->conn->query($sql);       
        
        return $rs;
    }

    // 確映影片細節
    public function readOne($id){ 

        $sql = "SELECT m.id AS id_movie, m.name_zhtw, m.name_en, m.release_year, m.rating, m.synopsis, m.award, m.running_time, m.country, m.trailer, d.id AS id_director, d.name_zhtw AS director_name_zhtw, d.name_en AS director_name_en, d.description AS director_description, 
        c.id AS id_cast, c.name_zhtw AS cast_name_zhtw, c.name_en AS cast_name_en, c.description AS cast_description, 
        s.id AS id_session, s.date, s.day, s.time, s.auditorium

                from movie m 

                JOIN movie_director md ON m.id = md.id_movie

                JOIN director d ON md.id_director = d.id

                JOIN movie_cast mc ON mc.id_movie = m.id

                JOIN cast c ON mc.id_cast = c.id

                JOIN session s ON s.id_movie = m.id

                WHERE m.id = $id";

        $rs = $this->conn->query($sql);        
        
        return $rs;
    }
    // (X) 募資影片細節
    public function readOneCF($id){ 
        // select all query
        $sql = "SELECT m.id AS id_movie, m.name_zhtw, m.name_en, m.release_year, m.rating, m.synopsis, m.award, m.running_time, m.country, m.trailer, d.id AS id_director, d.name_zhtw AS director_name_zhtw, d.name_en AS director_name_en, d.description AS director_description, 
        c.id AS id_cast, c.name_zhtw AS cast_name_zhtw, c.name_en AS cast_name_en, c.description AS cast_description

                from movie m 

                JOIN movie_director md ON m.id = md.id_movie

                JOIN director d ON md.id_director = d.id

                JOIN movie_cast mc ON mc.id_movie = m.id

                JOIN cast c ON mc.id_cast = c.id

                WHERE m.id = $id";

        // echo $sql;
    
        // prepare query statement
        $rs = $this->conn->query($sql);
        // echo $rs;
        
        // $datas = $rs->fetch_all(MYSQLI_ASSOC);
        // echo $datas;
        
        
        return $rs;
    }
    // 場次表
    public function session(){ 

        $sql = "SELECT s.*, m.name_zhtw, m.running_time 
                FROM `session` s 
                JOIN movie m ON m.id = s.id_movie 
                WHERE 1";

        $rs = $this->conn->query($sql);        
        
        return $rs;
    }
    // 所有募資進度
    public function cfRead(){
        $sql = "SELECT o.id_movie, o.quantity
                FROM `orders` o
                WHERE `cf`=1";

        $rs = $this->conn->query($sql);

        //募資目標(總座位數七成)
        $goal = ceil(108 * 0.7);
        $datas = $rs->fetch_all(MYSQLI_ASSOC);

        $result = [];

        foreach ($datas as $key => $value) {
            $result[$value['id_movie']] = isset($result[$value['id_movie']]) ? $result[$value['id_movie']] : 0;
            $result[$value['id_movie']] += $value['quantity'];
        }
        foreach ($result as $key => &$value) {
            $value = round(($value / $goal), 2);
        }

        return $result;

    }

}