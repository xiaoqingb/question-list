<?php
include_once(__DIR__."/SqlHelper.php");
class Account extends SqlHelper{
    // 创建用户
    public function create($account,$password,$password1,$name){
    if(!($account&&$password&&$password1&&$name)){
        return 4;
    }
    // 验证邮箱格式
    $pattern = '/\w{2,}@[a-zA-Z0-9]{2,}\.[a-zA-Z0-9]{2,9}/';
    preg_match($pattern,$account,$match);
    if(!$match){
        return 6;
    }
    // 验证密码长度,6-20位
    $pattern = '/[a-zA-Z0-9]{6,20}/';
    preg_match($pattern,$password,$match);
    if(!$match){
        return 5;
    }
    if($password!==$password1){
        return 3;
    }  
    // 至少一位大写字母，一位小写字母，和一个数字
    $pattern = '/[a-z]{1,}/';
    $a=preg_match($pattern,$password,$match);
    $pattern1 = '/[A-Z]{1,}/';
    $b=preg_match($pattern1,$password,$match1);
    $pattern2 = '/[0-9]{1,}/';
    $c=preg_match($pattern2,$password,$match2);
    if(!$a || !$b || !$c){
        return 5;
    }

    // 插入
    $password = md5($password);
    $result = $this->isExist($account);
    if($result){
        return 0;
    }
    $result = $this->query('insert into `users` (`account`,`password`,`name`) values ("'.$account.'","'.$password.'","'.$name.'")');
    if($result){
        return 1;
    }else{
        return 2;
    }
}

    // 判断用户是否存在
    public function isExist($account){
        $result = $this->query('select account from `users` where account = "'.$account.'"');
        if($result->num_rows!==0){
            return true;
        }else{
            return false;
        }
    }

    // 验证用户的信息是否正确
    public function auth($account,$pass){
        $pass = md5($pass);
        $result = $this->query('select * from `users` where account = "'.$account.'" and password="'.$pass.'"');
        if($result->num_rows!==0){
            $row = $result->fetch_assoc();
            return $row;
        }else{
            return false;
        }
    }

    //获取用户权限
    public function  getRight($id){
        $result = $this->query('SELECT * FROM `users` WHERE `id`=' . $id);
        $result = $result->fetch_assoc();
        return $result;
    }
}