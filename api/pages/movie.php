<?php
class Movie{
 
    // database connection and table name
    private $conn;
    // private $table_name = "films";
 
    // // object properties
    // public $id;
    // public $name;
    // public $description;
    // public $price;
    // public $category_id;
    // public $category_name;
    // public $created;
 
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    
    // read movie list
    public function read(){ 
        // return $_SERVER['PATH_INFO'];
        // return $_GET['cf'];
        // if($_GET['cf']):
        if(!empty($_GET['cf'])) $cf = $_GET['cf']=="true" ? 1 : 0;
        $whereStr = empty($_GET['cf']) ? 1 : "`cf`=$cf";
        // return $cf;
        // select all query
        $sql = "SELECT m.id AS id_movie, m.name_zhtw, m.name_en, m.release_year, m.theme, m.cf, d.name_zhtw AS director_name
            FROM movie m
            INNER JOIN (movie_director md
                INNER JOIN director d
                ON md.id_director = d.id
            ) ON md.id_movie = m.id
            WHERE $whereStr
            ORDER BY id_movie";

        // echo $sql;
    
        // prepare query statement
        $rs = $this->conn->query($sql);
        // echo $rs;
        
        // $datas = $rs->fetch_all(MYSQLI_ASSOC);
        // echo $datas;
        
        
        return $rs;
    }

    // read movie details
    public function readOne($id){ 
        // select one query
        $sql = "SELECT m.*, d.id AS id_director, d.name_zhtw AS director_name_zhtw, d.name_en AS director_name_en, d.description AS director_description, 
        c.id AS id_cast, c.name_zhtw AS cast_name_zhtw, c.name_en AS cast_name_en, c.description AS cast_description, 
        s.id AS id_session, s.date, s.day, s.time, s.auditorium

                from movie m 

                JOIN movie_director md ON m.id = md.id_movie

                JOIN director d ON md.id_director = d.id

                JOIN movie_cast mc ON mc.id_movie = m.id

                JOIN cast c ON mc.id_cast = c.id

                JOIN session s ON s.id_movie = m.id

                WHERE m.id = $id";

        // echo $sql;
    
        // prepare query statement
        $rs = $this->conn->query($sql);
        // echo $rs;
        
        // $datas = $rs->fetch_all(MYSQLI_ASSOC);
        // echo $datas;
        
        
        return $rs;
    }

    public function readOneCF($id){ 
        // select all query
        $sql = "SELECT m.*, d.id AS id_director, d.name_zhtw AS director_name_zhtw, d.name_en AS director_name_en, d.description AS director_description, 
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

    public function session(){ 
        // select all query
        $sql = "SELECT s.*, m.name_zhtw, m.running_time 
                FROM `session` s 
                JOIN movie m ON m.id = s.id_movie 
                WHERE 1";

        // echo $sql;
    
        // prepare query statement
        $rs = $this->conn->query($sql);
        // echo $rs;
        
        // $datas = $rs->fetch_all(MYSQLI_ASSOC);
        // echo $datas;
        
        
        return $rs;
    }
    public function cfRead(){
        $sql = "SELECT o.id_movie, o.quantity
                FROM `orders` o
                WHERE `cf`=1";
        $rs = $this->conn->query($sql);
        $goal = ceil(108 * 0.7);
        $datas = $rs->fetch_all(MYSQLI_ASSOC);
        // return $datas;
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
    public function cfReadOne(){
        
    }

}