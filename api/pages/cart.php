<?php
class Cart{
 
    // database connection and table name
    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    //收藏
    public function collection(){        
        if($_SERVER['REQUEST_METHOD'] === 'GET'){//進入我的片單(分會員/訪客)

            //會員
            if(!empty($_GET)):
                //會員id(query string)
                $id = $_GET['id'];

                //查詢會員收藏影片的id
                $sql = "SELECT c.id_movie FROM `collection` c WHERE `id_member`=$id";
                $rs = $this->conn->query($sql);
                // $result = $rs->fetch_all(MYSQLI_NUM); //[["1"],["2"]]

                $id_movie = [];
                while($row = $rs->fetch_assoc()){
                    array_push($id_movie,$row['id_movie']);
                };

                //若會員還未曾加入影片則返回訊息，跳出
                if(empty($id_movie)){ 
                    $result = array(
                        "message" => "empty collection"
                    );
                    return $result;
                };

                //查詢目前所有訂單
                $id_movie = implode(',',$id_movie);
                $sql = "SELECT o.id_movie,o.seat,o.quantity FROM `orders` o WHERE `id_movie` IN ($id_movie)";
                $rs = $this->conn->query($sql);
                $seats = $rs->fetch_all(MYSQLI_ASSOC);

                //彙整所有電影(場次)和募資(訂位)的關聯陣列
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

                //查詢會員收藏的詳細資料
                $sql = "SELECT m.id AS id_movie, m.name_zhtw, m.name_en, m.release_year, m.cf, 
                        s.id AS id_session, s.day, s.date, s.auditorium, s.time 
                        FROM `collection` c 
                        JOIN `movie` m ON m.id=c.id_movie 
                        LEFT JOIN `session` s ON s.id_movie = m.id 
                        WHERE `id_member`=$id";
                $rs = $this->conn->query($sql);
                $result = $rs->fetch_all(MYSQLI_ASSOC);

                
                //將收藏清單加上募資(訂位)狀況
                $goal = ceil(108 * 0.7); // 目標為總座位數的70%
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

                return $result;
            
            else: //訪客
                //欲查詢的電影id(以逗號分隔)
                $id_movie = preg_replace('/_/',",",trim($_SERVER['PATH_INFO'],'/'));

                //查詢全部訂單
                $sql = "SELECT o.id_movie,o.seat,o.quantity FROM `orders` o WHERE `id_movie` IN ($id_movie)";
                $rs = $this->conn->query($sql);
                $seats = $rs->fetch_all(MYSQLI_ASSOC);

                //彙整所有電影(場次)和募資(訂位)的關聯陣列
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

                //查詢電影的詳細資料
                $sql = "SELECT m.id AS id_movie, m.name_zhtw, m.name_en, m.release_year, m.cf, 
                        s.id AS id_session, s.day, s.date, s.auditorium, s.time 
                        FROM `movie` m 
                        LEFT JOIN `session` s ON s.id_movie = m.id 
                        WHERE m.id IN ($id_movie)";
                $rs = $this->conn->query($sql);
                $result = $rs->fetch_all(MYSQLI_ASSOC);

                //將收藏清單加上募資(訂位)狀況
                $goal = ceil(108 * 0.7);// 目標為總座位數的70%
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
                return $result;
            endif;

        }elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){//刪除一筆collection(細節頁、列表頁、我的片單頁)，刪多筆(結帳頁)
            $result = [];

            $info = explode('/',trim($_SERVER['PATH_INFO'],'/'));
            $id_movie = $info[0];
            $id_user = $info[1];

            //刪除一筆
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

                if($affected_rows==1){
                    $result['message'] = "delete 1 data";
                }elseif($affected_rows==0){
                    $result['message'] = "something wrong";
                }
                return $result;
                $stmt->close();
            }else{ //刪除多筆
                //將id_movie從底線改成逗號分隔
                $id_movie = preg_replace("/_/",",",$id_movie);
                // $id_movie = str_replace("_",",",$id_movie);
                // $result["id_movie"] = $id_movie;
                $sql = "DELETE FROM `collection` WHERE id_member=$id_user AND id_movie IN ($id_movie)";
                // $result["sql"] = $sql;
                $stmt = $this->conn->prepare($sql);

                if($this->conn->errno){
                    echo $this->conn->error;
                    exit;
                }

                $stmt->execute();

                $affected_rows = $stmt->affected_rows;

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

            //會員id
            $id = $obj['id'];

            //查詢舊有收藏
            $sql_select = "SELECT c.id_movie FROM `collection` c WHERE `id_member`=$id";
            $rs = $this->conn->query($sql_select);
            //彙整其電影id
            $collection = [];
            while($row = $rs->fetch_assoc()){
                array_push($collection, $row['id_movie']);
            }

            $result = [];

            //欲增加的電影id
            $id_movie = $obj['id_movie'];

            //加一部
            if(strpos($id_movie, "_") === false):

                //檢查重複，重複則回傳訊息且跳出
                foreach($collection as $value){
                    if($value === $id_movie){
                        $result['message'] = 'already in your collection';
                        return $result;
                    }
                }

                //不重複，寫入db
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

                if($affected_rows==1): //成功加入一筆
                    $result['message'] = "add 1 collection";

                    $stmt->close();

                    //查詢全部訂單，回傳詳細電影資料
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

                    return $result;

                elseif($affected_rows==0): //寫入失敗
                    $result['message'] = "something wrong";
                    $stmt->close();
                    return $result;
                endif;
            else://加多部

                //欲增加的電影id(陣列)
                $id_movie = explode("_",$id_movie);

                //檢查重複，重複則回傳訊息且跳出(任一重複)
                foreach($collection as $value){
                    foreach ($id_movie as $v) {                    
                        if($value === $v){
                            $result['message'] = 'already in your collection';
                            return $result;
                        }
                    }
                }

                //不重複，寫入db
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

                if($affected_rows>0): //成功寫入
                    $result['message'] = "add collections";
                
                    $stmt->close();

                    $id_movie = implode(",",$id_movie);

                    //查詢全部訂單，回傳詳細電影資料
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

                    return $result;
                elseif($affected_rows==0)://寫入失敗
                    $result['message'] = "something wrong";
                    $stmt->close();
                    return $result;
                endif;
            endif;


        }elseif($_SERVER['REQUEST_METHOD'] === 'PUT'){//登入後將storage片單更新至資料庫(增加不重複的部分)
            $json = file_get_contents('php://input');
            $obj = json_decode($json, true); 
            //會員id
            $id = $obj['id'];
            //查詢舊有收藏id
            $sql_select = "SELECT c.id_movie FROM `collection` c WHERE `id_member`=$id";
            $rs = $this->conn->query($sql_select);
            $collection = [];
            while($row = $rs->fetch_assoc()){
                array_push($collection, $row['id_movie']);
            }

            $result = [];

            //欲更新的收藏id
            $id_movie = $obj['id_movie'];
            $add_id_movie = [];

            //彙整成一組id(不重複)
            foreach ($id_movie as $value) {
                if(!in_array($value, $collection)){
                    array_push($add_id_movie, $value);
                }
            }

            //若沒有需要增加的就回傳訊息，跳出
            if(empty($add_id_movie)){
                $result['message'] = "nothing to update";
                return $result;
            }

            //寫入db
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

            if($affected_rows>0): //成功寫入
                $result['message'] = "update collections";
            
                $stmt->close();

                //更新後的所有收藏id
                $id_movie = implode(",",array_merge($add_id_movie, $collection));

                //查詢全部訂單，回傳所有收藏的詳細資料(包含訂位/募資狀況)
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

                return $result;

            elseif($affected_rows==0):
                $result['message'] = "something wrong";
                $stmt->close();
                return $result;
            endif;


        }
    }

    //結帳
    public function booking(){
        //訂位及參與募資(可多部同時)
        if($_SERVER['REQUEST_METHOD'] === 'POST'){

            $json = file_get_contents('php://input');
            $obj = json_decode($json, true); //second param=true => return array

            //訂購資訊
            $films = $obj["films"]; //確映(座位、場次id、電影id、數量)
            $cffilms = $obj["cffilms"];//募資(電影id、數量)
            $id = $obj['id'];//會員id

            //所有欲訂購的場次id
            $id_session_ar = [];
            foreach ($films as $value) {
                array_push($id_session_ar, $value['session']);
            }
            $id_session = implode(",", $id_session_ar); //逗號分隔

            //查詢訂位狀況
            $sql_select = "SELECT o.seat, o.id_session FROM `orders` o WHERE `id_session` IN ($id_session)";
            $rs = $this->conn->query($sql_select);

            //彙整目前訂走的座位
            $occupied_seats = [];
            //以場次id為key
            foreach ($id_session_ar as $key => $value) {
                $occupied_seats[$value] = [];
            }
            while($row = $rs->fetch_assoc()){
                array_push($occupied_seats[$row['id_session']],$row['seat']);
            }

            //統整重複的座位(劃到已經被訂走的)
            $repeat_seats = [];
            //以場次id為key
            foreach ($id_session_ar as $key => $value) {
                $repeat_seats[$value] = [];
            }
            foreach($films as $value){
                $seats = $value['seats'];
                $session = $value['session'];
                foreach($seats as $v){
                    if(in_array($v, $occupied_seats[$session])){
                        array_push($repeat_seats[$session],$v);
                    }
                }
            }

            $is_empty = true;
            //只要任一場次有重複座位
            foreach ($repeat_seats as $key => $value) {
                if(!empty($value)) $is_empty = false;
            };
            //清空陣列(key)
            if($is_empty){
                unset($repeat_seats);
            }
            $result = [];

            //有重複訂位就回傳訊息和重複的位號加場次，跳出
            if(!empty($repeat_seats)){
                $result["message"] = "seats have been booked";
                $result["repeat_seats"] = $repeat_seats;
                return $result;
            }else{ //全都不重複，寫入db
                $sql_insert = "INSERT INTO `orders`(`cf`, `id_movie`, `quantity`, `id_session`, `seat`, `id_member`, `order_date`) VALUES ";

                //組合字串(VALUE)
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

                $stmt = $this->conn->prepare($sql_insert);

                if($this->conn->errno){
                    echo $this->conn->error;
                    exit;
                }

                $stmt->execute();

                $affected_rows = $stmt->affected_rows;

                //成功寫入
                if($affected_rows>0){
                    $result['message'] = "booking success";
                }elseif($affected_rows==0){ //寫入失敗
                    $result['message'] = "something wrong";
                }
                return $result;
                $stmt->close();

            }

        }elseif($_SERVER['REQUEST_METHOD'] === 'GET'){//劃位(單場)查詢座位狀況
            //欲查詢電影的場次id
            $id_session = trim($_SERVER['PATH_INFO'],'/');

            $sql_select = "SELECT o.seat FROM `orders` o WHERE `id_session`=$id_session ORDER BY `seat`";
            $rs = $this->conn->query($sql_select);

            //彙整已訂走的座位
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