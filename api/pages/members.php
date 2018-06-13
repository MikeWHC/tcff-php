<?php
class Members{
 
    // database connection and table name
    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    
    //註冊
    public function create($member_arr){ 
        $sql = "INSERT INTO `members`(`email`, `password`, `username`, `created_at`) VALUES (?, ?, ?, NOW())";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param('sss',
            $member_arr['email'],
            $member_arr['password'],
            $member_arr['username']
        );
        $stmt->execute();
        $affected_rows = $stmt->affected_rows;

        return $affected_rows;
    }

    //登入
    public function login($email, $password){ 
        
        if(!empty($email)){
            $doChecked = true;

            $sql = sprintf("SELECT `id`, `email`, `username` FROM `members` WHERE `email`='%s' AND `password`='%s'",
                $this->conn->escape_string($email),
                $password
                );

            $rs = $this->conn->query($sql);
            
            $result = [];

            if($rs->num_rows==1){ //成功登入
                $row = $rs->fetch_assoc();

                $result['success'] = true;
                $result['user'] = $row;
            }else{ //查不到用戶
                $result['success'] = false;
            }
            return $result;
        }else{ //沒給郵件
            $result['success'] = false;
            return $result;
        }
    }

    //更新資料
    public function update(){ 
        if(isset($_POST['password'])){
            $sql = sprintf("SELECT * FROM `members` WHERE `id`=%s AND `password`='%s'",
            $_SESSION['user']['id'],
            $_POST['password']
            );

            $rs = $this->conn->query($sql);

            if($rs->num_rows==0){
                //密碼錯誤
                $message = "wrongPass";
                return $message;
            }else {               
            
                $sql = "UPDATE `members` SET `username`=? WHERE `id`=?";

                $stmt = $this->conn->prepare($sql);

                if($this->conn->errno){
                    echo $this->conn->error;
                    exit;
                }

                $stmt->bind_param('si',
                    $_POST['username'],
                    $_SESSION['user']['id']
                );

                $stmt->execute();

                $affected_rows = $stmt->affected_rows;

                //將修改後的資料update到session
                if($affected_rows==1){
                    $_SESSION['user']['username'] = $_POST['username'];
                    $message = "success";
                }elseif($affected_rows==0){
                    $message = "not change";
                }
                return $message;
                $stmt->close();

            }
        }
    }

    public function orders(){ 
        $sql = sprintf("SELECT o.id_session, o.seat, o.order_date, m.name_zhtw, m.id, s.date, s.time, s.auditorium, o.quantity FROM `orders` o 
        JOIN `movie` m ON o.id_movie=m.id
        LEFT JOIN `session` s ON s.id_movie=m.id
        WHERE `id_member`=%s ORDER BY order_date DESC, id_session ASC",
            $_GET['id']
            );

        $rs = $this->conn->query($sql);

        return $rs;
    }


}