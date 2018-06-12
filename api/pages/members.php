<?php
class Members{
 
    // database connection and table name
    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    
    // sign up 
    public function create($member_arr){ 
        $sql = "INSERT INTO `members`(`email`, `password`, `created_at`) VALUES (?, ?, NOW())";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param('ss',
            $member_arr['email'],
            $member_arr['password']
        );
        $stmt->execute();
        $affected_rows = $stmt->affected_rows;

        return $affected_rows;
    }

    // log in
    public function login($email, $password){ 
        
        if(!empty($email)){
            $doChecked = true;

            $sql = sprintf("SELECT `id`, `email`, `username` FROM `members` WHERE `email`='%s' AND `password`='%s'",
                $this->conn->escape_string($email),
                $password
                );

            $rs = $this->conn->query($sql);
            
            $result = [];

            if($rs->num_rows==1){
                $row = $rs->fetch_assoc();
                // $_SESSION['user'] = $row;
                // $success = true;
                $result['success'] = true;
                $result['user'] = $row;
            }else{
                $result['success'] = false;
            }
            return $result;
        }else{
            $result['success'] = false;
            return $result;
        }
    }

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