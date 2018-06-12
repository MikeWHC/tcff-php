<?php
class Cart{
 
    // database connection and table name
    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    
    // read collection
    public function collection(){
        // $id = $_SESSION['user']['id'] ? $_SESSION['user']['id'] : 1;
        // $id = $_SESSION['user']['id'];
        
        if($_SERVER['REQUEST_METHOD'] === 'GET'){//進入我的片單(分會員/訪客)
            if(!empty($_GET)):
                $id = $_GET['id'];
                //我的片單 讀取會員收藏的片單
                // $sql = "SELECT m.id AS id_movie, m.name_zhtw, m.name_en, m.release_year, m.cf FROM `collection` c JOIN `movie` m ON m.id=c.id_movie WHERE `id_member`=$id";
                $sql = "SELECT c.id_movie FROM `collection` c WHERE `id_member`=$id";
                // $sql = "SELECT m.id AS id_movie, m.name_zhtw, m.name_en, m.release_year, m.cf, s.id AS id_session, s.day, s.date, s.auditorium, s.time FROM `collection` c JOIN `movie` m ON m.id=c.id_movie JOIN `session` s ON s.id_movie = m.id WHERE `id_member`=$id";
                $rs = $this->conn->query($sql);
                // $result = $rs->fetch_all(MYSQLI_NUM); //[["1"],["2"]]
                $id_movie = [];
                while($row = $rs->fetch_assoc()):
                array_push($id_movie,$row['id_movie']);
                endwhile;
                $id_movie = implode(',',$id_movie);
                // $sql = "SELECT o.seat FROM `orders` o WHERE `id_session`=$id_session OR `id_movie` IN ($id_movie) ORDER BY `seat`";
                $sql = "SELECT o.id_movie,o.seat,o.quantity FROM `orders` o WHERE `id_movie` IN ($id_movie)";
                $rs = $this->conn->query($sql);
                $seats = $rs->fetch_all(MYSQLI_ASSOC);
                function reduce($carry,$item){
                    if(!isset($carry[$item['id_movie']])){
                        $carry[$item['id_movie']] = [];
                        $carry[$item['id_movie']]['quantity'] = 0;
                        $carry[$item['id_movie']]['seats'] = [];
                    };
                    array_push($carry[$item['id_movie']]['seats'], $item['seat']);
                    $carry[$item['id_movie']]['quantity'] += $item['quantity'];
                    return $carry;
                }
                $seats = array_reduce($seats,"reduce",[]);
                $sql = "SELECT m.id AS id_movie, m.name_zhtw, m.name_en, m.release_year, m.cf, 
                        s.id AS id_session, s.day, s.date, s.auditorium, s.time 
                        FROM `collection` c 
                        JOIN `movie` m ON m.id=c.id_movie 
                        LEFT JOIN `session` s ON s.id_movie = m.id 
                        WHERE `id_member`=$id";
                $rs = $this->conn->query($sql);
                $result = $rs->fetch_all(MYSQLI_ASSOC);
                // function map(){

                // }
                // array_map($result,"map");
                foreach ($result as &$value) {
                    if($value['cf'] == 0){
                        $value['bookable_seats_count'] = 108;
                        $value['occupied'] = [];
                    }else{
                        $value['cf_progress'] = 0;
                    }
                    foreach ($seats as $k => $v) {
                        if($k == $value['id_movie']){
                            if($value['cf'] == 0){
                                $value['bookable_seats_count'] -= $v['quantity'];
                                $value['occupied'] = $v['seats'];
                            }else{
                                $value['cf_progress'] = round(($v['quantity']/108), 2);
                            }
                        }
                    }
                }
                //查詢每部片的募資進度及剩餘空位
                // $result.
                return $result;
            
            else:
                $id_movie = preg_replace('/_/',",",trim($_SERVER['PATH_INFO'],'/'));

                $sql = "SELECT o.id_movie,o.seat,o.quantity FROM `orders` o WHERE `id_movie` IN ($id_movie)";
                $rs = $this->conn->query($sql);
                $seats = $rs->fetch_all(MYSQLI_ASSOC);
                function reduce($carry,$item){
                    if(!isset($carry[$item['id_movie']])){
                        $carry[$item['id_movie']] = [];
                        $carry[$item['id_movie']]['quantity'] = 0;
                        $carry[$item['id_movie']]['seats'] = [];
                    };
                    array_push($carry[$item['id_movie']]['seats'], $item['seat']);
                    $carry[$item['id_movie']]['quantity'] += $item['quantity'];
                    return $carry;
                }
                $seats = array_reduce($seats,"reduce",[]);
                $sql = "SELECT m.id AS id_movie, m.name_zhtw, m.name_en, m.release_year, m.cf, 
                        s.id AS id_session, s.day, s.date, s.auditorium, s.time 
                        FROM `movie` m 
                        LEFT JOIN `session` s ON s.id_movie = m.id 
                        WHERE m.id IN ($id_movie)";
                $rs = $this->conn->query($sql);
                $result = $rs->fetch_all(MYSQLI_ASSOC);
                // function map(){

                // }
                // array_map($result,"map");
                $goal = ceil(108 * 0.7);
                foreach ($result as &$value) {
                    if($value['cf'] == 0){
                        $value['bookable_seats_count'] = 108;
                        $value['occupied'] = [];
                    }else{
                        $value['cf_progress'] = 0;
                    }
                    foreach ($seats as $k => $v) {
                        if($k == $value['id_movie']){
                            if($value['cf'] == 0){
                                $value['bookable_seats_count'] -= $v['quantity'];
                                $value['occupied'] = $v['seats'];
                            }else{
                                $value['cf_progress'] = round(($v['quantity']/$goal), 2);
                            }
                        }
                    }
                }
                //查詢每部片的募資進度及剩餘空位
                // $result.
                return $result;
            endif;

        }elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){//刪除一筆collection(細節頁、列表頁、我的片單頁)，刪多筆(結帳頁)
            $result = [];
            // $id_movie=json_decode(file_get_contents('php://input'),true);
            // $id_movie=file_get_contents('php://input');
            // $request = trim($_SERVER['PATH_INFO'],'/');
            // $id_movie = trim($_SERVER['PATH_INFO'],'/');
            // $request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
            // $request = $_SERVER['PATH_INFO'];
            // return json_decode($id_movie);
            // return $request;

            $info = explode('/',trim($_SERVER['PATH_INFO'],'/'));
            $id_movie = $info[0];
            $id_user = $info[1];
            // return $result;

            if(strpos($id_movie, "_") === false){


                $sql = "DELETE FROM `collection` WHERE id_member=? AND id_movie=?";
                $stmt = $this->conn->prepare($sql);

                if($this->conn->errno){
                    echo $this->conn->error;
                    exit;
                }

                $stmt->bind_param('ss',
                    $id_user,
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
            }else{
                $id_movie = preg_replace("/_/",",",$id_movie);
                // $id_movie = str_replace("_",",",$id_movie);
                $result["id_movie"] = $id_movie;
                $sql = "DELETE FROM `collection` WHERE id_member=$id_user AND id_movie IN ($id_movie)";
                $result["sql"] = $sql;
                $stmt = $this->conn->prepare($sql);

                if($this->conn->errno){
                    echo $this->conn->error;
                    exit;
                }

                // $stmt->bind_param('ss',
                //     $id_user,
                //     $id_movie
                // );

                $stmt->execute();

                $affected_rows = $stmt->affected_rows;

                // //將修改後的資料update到session
                if($affected_rows>0){
                    $result['message'] = "delete collections";
                }elseif($affected_rows==0){
                    $result['message'] = "something wrong";
                }
                return $result;
                $stmt->close();
            }
        }elseif($_SERVER['REQUEST_METHOD'] === 'POST'){//加一筆collection(細節頁、列表頁)，加多筆(我的片單頁)
            $json = file_get_contents('php://input');
            $obj = json_decode($json, true); 
            $id = $obj['id'];
            $sql_select = "SELECT c.id_movie FROM `collection` c WHERE `id_member`=$id";
            $rs = $this->conn->query($sql_select);
            // $collection = $rs->fetch_all(MYSQLI_ASSOC);
            $collection = [];
            while($row = $rs->fetch_assoc()){
                array_push($collection, $row['id_movie']);
            }
            // return $collection;
            $result = [];
            // $id_movie = trim($_SERVER['PATH_INFO'],'/');
            $id_movie = $obj['id_movie'];

            if(strpos($id_movie, "_") === false):

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
                    $id,
                    $id_movie
                );

                $stmt->execute();

                $affected_rows = $stmt->affected_rows;

                // //將修改後的資料update到session
                if($affected_rows==1):
                    $result['message'] = "add 1 collection";
                

                    // $sql_sel_one = "";
                    // return $result;
                    $stmt->close();

                    $sql = "SELECT o.id_movie,o.seat,o.quantity FROM `orders` o WHERE `id_movie`=$id_movie";
                    $rs = $this->conn->query($sql);
                    $seats = $rs->fetch_all(MYSQLI_ASSOC);
                    function reduce($carry,$item){
                        if(!isset($carry[$item['id_movie']])){
                            $carry[$item['id_movie']] = [];
                            $carry[$item['id_movie']]['quantity'] = 0;
                            $carry[$item['id_movie']]['seats'] = [];
                        };
                        array_push($carry[$item['id_movie']]['seats'], $item['seat']);
                        $carry[$item['id_movie']]['quantity'] += $item['quantity'];
                        return $carry;
                    }
                    $seats = array_reduce($seats,"reduce",[]);
                    $sql = "SELECT m.id AS id_movie, m.name_zhtw, m.name_en, m.release_year, m.cf, 
                            s.id AS id_session, s.day, s.date, s.auditorium, s.time 
                            FROM `movie` m 
                            LEFT JOIN `session` s ON s.id_movie = m.id 
                            WHERE id_movie=$id_movie";
                    $rs = $this->conn->query($sql);
                    $result['collection_info'] = [];
                    $result['collection_info'] = $rs->fetch_all(MYSQLI_ASSOC);

                    $goal = ceil(108 * 0.7);
                    foreach ($result['collection_info'] as &$value) {
                        if($value['cf'] == 0){
                            $value['bookable_seats_count'] = 108;
                            $value['occupied'] = [];
                        }else{
                            $value['cf_progress'] = 0;
                        }
                        foreach ($seats as $k => $v) {
                            if($k == $value['id_movie']){
                                if($value['cf'] == 0){
                                    $value['bookable_seats_count'] -= $v['quantity'];
                                    $value['occupied'] = $v['seats'];
                                }else{
                                    $value['cf_progress'] = round(($v['quantity']/$goal), 2);
                                }
                            }
                        }
                    }
                    //查詢每部片的募資進度及剩餘空位
                    // $result.
                    return $result;
                elseif($affected_rows==0):
                    $result['message'] = "something wrong";
                    $stmt->close();
                    return $result;
                endif;
            else:
                $id_movie = explode("_",$id_movie);

                foreach($collection as $value){
                    foreach ($id_movie as $v) {
                    
                        if($value === $v){
                            $result['message'] = 'already in your collection';
                            return $result;
                            // exit;
                        }
                    }
                }
                $sql_insert = "INSERT INTO `collection`(`id_member`, `id_movie`) VALUES ";
                foreach ($id_movie as $value) {
                    $sql_insert .= "($id,$value),";
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
                if($affected_rows>0):
                    $result['message'] = "add collections";
                

                    // $sql_sel_one = "";
                    // return $result;
                    $stmt->close();
                    $id_movie = implode(",",$id_movie);

                    $sql = "SELECT o.id_movie,o.seat,o.quantity FROM `orders` o WHERE `id_movie` IN ($id_movie)";
                    $rs = $this->conn->query($sql);
                    $seats = $rs->fetch_all(MYSQLI_ASSOC);
                    function reduce($carry,$item){
                        if(!isset($carry[$item['id_movie']])){
                            $carry[$item['id_movie']] = [];
                            $carry[$item['id_movie']]['quantity'] = 0;
                            $carry[$item['id_movie']]['seats'] = [];
                        };
                        array_push($carry[$item['id_movie']]['seats'], $item['seat']);
                        $carry[$item['id_movie']]['quantity'] += $item['quantity'];
                        return $carry;
                    }
                    $seats = array_reduce($seats,"reduce",[]);
                    
                    $sql = "SELECT m.id AS id_movie, m.name_zhtw, m.name_en, m.release_year, m.cf, 
                            s.id AS id_session, s.day, s.date, s.auditorium, s.time 
                            FROM `movie` m 
                            LEFT JOIN `session` s ON s.id_movie = m.id 
                            WHERE m.id IN ($id_movie)";
                    $rs = $this->conn->query($sql);
                    $result['collection_info'] = [];
                    $result['collection_info'] = $rs->fetch_all(MYSQLI_ASSOC);

                    $goal = ceil(108 * 0.7);
                    foreach ($result['collection_info'] as &$value) {
                        if($value['cf'] == 0){
                            $value['bookable_seats_count'] = 108;
                            $value['occupied'] = [];
                        }else{
                            $value['cf_progress'] = 0;
                        }
                        foreach ($seats as $k => $v) {
                            if($k == $value['id_movie']){
                                if($value['cf'] == 0){
                                    $value['bookable_seats_count'] -= $v['quantity'];
                                    $value['occupied'] = $v['seats'];
                                }else{
                                    $value['cf_progress'] = round(($v['quantity']/$goal), 2);
                                }
                            }
                        }
                    }
                    //查詢每部片的募資進度及剩餘空位
                    // $result.
                    return $result;
                elseif($affected_rows==0):
                    $result['message'] = "something wrong";
                    $stmt->close();
                    return $result;
                endif;
            endif;


        }elseif($_SERVER['REQUEST_METHOD'] === 'PUT'){//登入後將storage片單更新至資料庫(增加不重複的部分)
            $json = file_get_contents('php://input');
            $obj = json_decode($json, true); 
            $id = $obj['id'];
            $sql_select = "SELECT c.id_movie FROM `collection` c WHERE `id_member`=$id";
            $rs = $this->conn->query($sql_select);
            $collection = [];
            while($row = $rs->fetch_assoc()){
                array_push($collection, $row['id_movie']);
            }
            $result = [];
            $id_movie = $obj['id_movie'];
            $add_id_movie = [];
            foreach ($id_movie as $value) {
                if(!in_array($value, $collection)){
                    array_push($add_id_movie, $value);
                }
            }

            if(empty($add_id_movie)){
                $result['message'] = "nothing to update";
                return $result;
            }


            $sql_insert = "INSERT INTO `collection`(`id_member`, `id_movie`) VALUES ";
            foreach ($add_id_movie as $value) {
                $sql_insert .= "($id,$value),";
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
            if($affected_rows>0):
                $result['message'] = "update collections";
            

                // $sql_sel_one = "";
                // return $result;
                $stmt->close();
                $id_movie = implode(",",array_merge($add_id_movie,$collection));

                $sql = "SELECT o.id_movie,o.seat,o.quantity FROM `orders` o WHERE `id_movie` IN ($id_movie)";
                $rs = $this->conn->query($sql);
                $seats = $rs->fetch_all(MYSQLI_ASSOC);
                function reduce($carry,$item){
                    if(!isset($carry[$item['id_movie']])){
                        $carry[$item['id_movie']] = [];
                        $carry[$item['id_movie']]['quantity'] = 0;
                        $carry[$item['id_movie']]['seats'] = [];
                    };
                    array_push($carry[$item['id_movie']]['seats'], $item['seat']);
                    $carry[$item['id_movie']]['quantity'] += $item['quantity'];
                    return $carry;
                }
                $seats = array_reduce($seats,"reduce",[]);
                
                $sql = "SELECT m.id AS id_movie, m.name_zhtw, m.name_en, m.release_year, m.cf, 
                        s.id AS id_session, s.day, s.date, s.auditorium, s.time 
                        FROM `movie` m 
                        LEFT JOIN `session` s ON s.id_movie = m.id 
                        WHERE m.id IN ($id_movie)";
                $rs = $this->conn->query($sql);
                $result['collection_info'] = [];
                $result['collection_info'] = $rs->fetch_all(MYSQLI_ASSOC);

                $goal = ceil(108 * 0.7);
                foreach ($result['collection_info'] as &$value) {
                    if($value['cf'] == 0){
                        $value['bookable_seats_count'] = 108;
                        $value['occupied'] = [];
                    }else{
                        $value['cf_progress'] = 0;
                    }
                    foreach ($seats as $k => $v) {
                        if($k == $value['id_movie']){
                            if($value['cf'] == 0){
                                $value['bookable_seats_count'] -= $v['quantity'];
                                $value['occupied'] = $v['seats'];
                            }else{
                                $value['cf_progress'] = round(($v['quantity']/$goal), 2);
                            }
                        }
                    }
                }
                //查詢每部片的募資進度及剩餘空位
                // $result.
                return $result;
            elseif($affected_rows==0):
                $result['message'] = "something wrong";
                $stmt->close();
                return $result;
            endif;


        }
    }

    public function booking(){
        // $id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 15;
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            // return $_POST;
            $json = file_get_contents('php://input');
            $obj = json_decode($json, true); //second param=true => return array
            // return $obj;

            $films = $obj["films"];
            $cffilms = $obj["cffilms"];
            $id = $obj['id'];

            $sql_select = '';
            $id_session_ar = [];
            foreach ($films as $value) {
                array_push($id_session_ar,$value['session']);
            }
            $id_session = implode(",",$id_session_ar);
            // return $id_session;
            // $sql_select = "SELECT o.seat, o.id_session FROM `orders` o WHERE `id_member`=$id AND `id_session` IN ($id_session)";
            $sql_select = "SELECT o.seat, o.id_session FROM `orders` o WHERE `id_session` IN ($id_session)";
            $rs = $this->conn->query($sql_select);
            $datas = [];            
            $occupied_seats = [];
            foreach ($id_session_ar as $key => $value) {
                $occupied_seats[$value] = [];
            }
            while($row = $rs->fetch_assoc()){
                array_push($occupied_seats[$row['id_session']],$row['seat']);
            }
            // return $occupied_seats;
            $repeat_seats = [];
            foreach ($id_session_ar as $key => $value) {
                $repeat_seats[$value] = [];
            }
            foreach($films as $value){
                $seats = $value['seats'];
                $session = $value['session'];
                foreach($seats as $v){
                    if(in_array($v, $occupied_seats[$session])){
                        array_push($repeat_seats[$session],$v);
                        // return $result;
                    }
                }
            }
            // return $repeat_seats;
            $is_empty = true;
            foreach ($repeat_seats as $key => $value) {
                if(!empty($value)) $is_empty = false;
            };
            if($is_empty){
                unset($repeat_seats);
            }
            $result = [];
            if(!empty($repeat_seats)){
                $result["message"] = "seats have been booked";
                $result["repeat_seats"] = $repeat_seats;
                return $result;
            }else{
                // $result["message"] = "could book";
                // return $result;
                $sql_insert = "INSERT INTO `orders`(`cf`, `id_movie`, `quantity`, `id_session`, `seat`, `id_member`, `order_date`) VALUES ";
                foreach($films as $value){
                    $s = $value['session'];
                    $id_m = $value['id_movie'];
                    foreach ($value['seats'] as $v) {                        
                        $sql_insert .= "(0,$id_m,1,$s, $v, $id, NOW()),";
                    }
                }
                foreach ($cffilms as $value) {
                    $id_m = $value['id_movie'];
                    $q = $value['quantity'];
                    $sql_insert .= "(1,$id_m,$q,0, 0, $id, NOW()),";
                }
                $sql_insert = chop($sql_insert, ',');
                // return $sql_insert;
                $stmt = $this->conn->prepare($sql_insert);

                if($this->conn->errno){
                    echo $this->conn->error;
                    exit;
                }

                $stmt->execute();

                $affected_rows = $stmt->affected_rows;

                //將修改後的資料update到session
                if($affected_rows>0){
                    $result['message'] = "booking success";
                }elseif($affected_rows==0){
                    $result['message'] = "something wrong";
                }
                return $result;
                $stmt->close();

            }

            // $id_session = trim($_SERVER['PATH_INFO'],'/');
            // $sql_select = "SELECT o.seat FROM `orders` o WHERE `id_member`=$id AND `id_session`=$id_session";
            // $rs = $this->conn->query($sql_select);
            // // $result = $rs->fetch_all(MYSQLI_ASSOC);
            // $occupied_seats = [];
            // while($row = $rs->fetch_assoc()){
            //     array_push($occupied_seats, $row['seat']);
            // }
            // $post_seats = explode('_', $_POST['seat']);
            // $result = [];
            // foreach($post_seats as $value){
            //     foreach($occupied_seats as $v){
            //         if($value === $v){
            //             $result['message'] = "seat $v occupied";
            //             return $result;
            //         }
            //     }
            // }
            // // $result['message'] = "could book";
            // // return $result;
            // $sql_insert = "INSERT INTO `orders`(`id_session`, `seat`, `id_member`, `order_date`) VALUES ";
            // foreach($post_seats as $value){
            //     $sql_insert .= "($id_session, $value, $id, NOW()),";
            // }
            // $sql_insert = chop($sql_insert, ',');

            // $stmt = $this->conn->prepare($sql_insert);

            // if($this->conn->errno){
            //     echo $this->conn->error;
            //     exit;
            // }

            // $stmt->execute();

            // $affected_rows = $stmt->affected_rows;

            // // //將修改後的資料update到session
            // if($affected_rows>0){
            //     $result['message'] = "booking success";
            // }elseif($affected_rows==0){
            //     $result['message'] = "something wrong";
            // }
            // return $result;
            // $stmt->close();
            // return $sql_insert;

            // return $seats;
            // return $_POST['seat'];

        }elseif($_SERVER['REQUEST_METHOD'] === 'GET'){//劃位查詢座位狀況
            
            $id = $_GET['id'];
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
                "bookable_seats_count" => 108 - count($occupied_seats)
            );
            return $result;
        }
    }


}