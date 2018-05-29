<?php
class Cart{
 
    // database connection and table name
    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    
    // read collection
    public function collection(){
        $id = $_SESSION['user']['id'] ? $_SESSION['user']['id'] : 1;
        if($_SERVER['REQUEST_METHOD'] === 'GET'){
            $sql = "SELECT m.id AS id_movie, m.name_zhtw, m.name_en, m.release_year FROM `collection` c JOIN `movie` m ON m.id=c.id_movie WHERE `id_member`=$id";
            $rs = $this->conn->query($sql);
            $result = $rs->fetch_all(MYSQLI_ASSOC);
            return $result;
        }elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){
            $result = [];
            // $id_movie=json_decode(file_get_contents('php://input'),true);
            // $id_movie=file_get_contents('php://input');
            // $request = trim($_SERVER['PATH_INFO'],'/');
            $id_movie = trim($_SERVER['PATH_INFO'],'/');
            // $request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
            // $request = $_SERVER['PATH_INFO'];
            // return json_decode($id_movie);
            // return $request;
            $sql = "DELETE FROM `collection` WHERE id_member=? AND id_movie=?";
            $stmt = $this->conn->prepare($sql);

            if($this->conn->errno){
                echo $this->conn->error;
                exit;
            }

            $stmt->bind_param('ss',
                $_SESSION['user']['id'],
                $id_movie
            );

            $stmt->execute();

            $affected_rows = $stmt->affected_rows;

            // //將修改後的資料update到session
            if($affected_rows==1){
                $result['message'] = "delete 1 data";
            }elseif($affected_rows==0){
                $result['message'] = "something wrong";
            }
            return $result;
            $stmt->close();
        }elseif($_SERVER['REQUEST_METHOD'] === 'POST'){
            $sql_select = "SELECT c.id_movie FROM `collection` c WHERE `id_member`=$id";
            $rs = $this->conn->query($sql_select);
            // $collection = $rs->fetch_all(MYSQLI_ASSOC);
            $collection = [];
            while($row = $rs->fetch_assoc()){
                array_push($collection, $row['id_movie']);
            }
            // return $collection;
            $result = [];
            $id_movie = trim($_SERVER['PATH_INFO'],'/');

            foreach($collection as $value){
                if($value === $id_movie){
                    $result['message'] = 'already in your collection';
                    return $result;
                    // exit;
                }
            }
            $sql_insert = "INSERT INTO `collection`(`id_member`, `id_movie`) VALUES (?, ?)";
            $stmt = $this->conn->prepare($sql_insert);

            if($this->conn->errno){
                echo $this->conn->error;
                exit;
            }

            $stmt->bind_param('ss',
                $_SESSION['user']['id'],
                $id_movie
            );

            $stmt->execute();

            $affected_rows = $stmt->affected_rows;

            // //將修改後的資料update到session
            if($affected_rows==1){
                $result['message'] = "add 1 collection";
            }elseif($affected_rows==0){
                $result['message'] = "something wrong";
            }
            return $result;
            $stmt->close();
        }
    }

    public function booking(){
        $id = $_SESSION['user']['id'] ? $_SESSION['user']['id'] : 1;
        
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $id_session = trim($_SERVER['PATH_INFO'],'/');
            $sql_select = "SELECT o.seat FROM `orders` o WHERE `id_member`=$id AND `id_session`=$id_session";
            $rs = $this->conn->query($sql_select);
            // $result = $rs->fetch_all(MYSQLI_ASSOC);
            $occupied_seats = [];
            while($row = $rs->fetch_assoc()){
                array_push($occupied_seats, $row['seat']);
            }
            $post_seats = explode('_', $_POST['seat']);
            $result = [];
            foreach($post_seats as $value){
                foreach($occupied_seats as $v){
                    if($value === $v){
                        $result['message'] = "seat $v occupied";
                        return $result;
                    }
                }
            }
            // $result['message'] = "could book";
            // return $result;
            $sql_insert = "INSERT INTO `orders`(`id_session`, `seat`, `id_member`, `order_date`) VALUES ";
            foreach($post_seats as $value){
                $sql_insert .= "($id_session, $value, $id, NOW()),";
            }
            $sql_insert = chop($sql_insert, ',');

            $stmt = $this->conn->prepare($sql_insert);

            if($this->conn->errno){
                echo $this->conn->error;
                exit;
            }

            $stmt->execute();

            $affected_rows = $stmt->affected_rows;

            // //將修改後的資料update到session
            if($affected_rows>0){
                $result['message'] = "booking success";
            }elseif($affected_rows==0){
                $result['message'] = "something wrong";
            }
            return $result;
            $stmt->close();
            // return $sql_insert;

            // return $seats;
            // return $_POST['seat'];

        }elseif($_SERVER['REQUEST_METHOD'] === 'GET'){
            $id_session = trim($_SERVER['PATH_INFO'],'/');
            $sql_select = "SELECT o.seat FROM `orders` o WHERE `id_session`=$id_session ORDER BY `seat`";
            $rs = $this->conn->query($sql_select);
            $occupied_seats = [];
            while($row = $rs->fetch_assoc()){
                array_push($occupied_seats, $row['seat']);
            }
            $count = count($occupied_seats);
            $result = array(
                "occupied_seats_num" => $occupied_seats,
                "occupied_seats_count" => count($occupied_seats),
                "bookable_seats_count" => 170 - count($occupied_seats)
            );
            return $result;
        }
    }


}