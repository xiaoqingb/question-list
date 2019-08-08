<?php
// $do="";
// if(isset($_POST["do"])){
//     $do=$_POST['do'];
// }

// if(isset($_GET["do"])){
//     $do=$_GET['do'];
// }

// $do();
function regist(){
    include_once(App::$base."/model/Account.php");
    $account=$_POST["account"];
    $name=$_POST["name"];
    $pass=$_POST["password"];
    $pass1=$_POST["comfirmPass"];
    $accounter = new Account();
    $result=$accounter->create($account,$pass,$pass1,$name);
    switch($result){
            case 0:
            $response = [
                "code" => "0001",
                "msg" => "账号已存在"
            ];
            die(json_encode($response));

        case 1:
            $response = [
                "code" => "0000",
                "msg" => "注册成功,三秒后跳转到登录页面"
            ];
            die(json_encode($response));

        case 2:
            $response = [
                "code" => "0002",
                "msg" => "创建账号的时候发生了未知的错误"
            ];
            die(json_encode($response));
            
        case 3:
            $response = [
                "code" => "0003",
                "msg" => "两次输入密码不一致"
            ];
            die(json_encode($response));

        case 4:
            $response = [
                "code" => "0004",
                "msg" => "输入内容不能为空"
            ];
            die(json_encode($response));
        case 5:
            $response = [
                "code" => "0005",
                "msg" => "密码必须同时包含一个大写字母，小写字母和数字，且长度大于6，小于20!"
            ];
            die(json_encode($response));
        case 6:
            $response = [
                "code" => "0006",
                "msg" => "邮箱格式有误!"
            ];
            die(json_encode($response));
        }

}
// 登录验证
function login(){
    include_once(App::$base."/model/Account.php");
    $Account = new Account();
    if ($Account->isConnectError) {
        $response=[
            "code" => "0010",
            "msg" =>"服务器异常"
        ];
        die(json_encode($response));
    }
    // 账号密码非空判断
    $account=$_POST["account"];
    $pass=$_POST["password"];
    if(!($pass&&$account)){
        $response=[
            "code" => "0009",
            "msg" =>"账号密码不能为空"
        ];
        die(json_encode($response));
    }
    $pass=(string)md5($pass);
    // 查询数据库是否有该账号密码
    $sql='select * from `users` where account = "'.$account.'"and password="'.$pass.'"';
    $result=$Account->query($sql);
    if($row=$result->fetch_array()){
        session_start();
        $_SESSION['id']=$row['id'];
        $_SESSION['name']=$row['name'];
        setcookie('user',$row['id']."::".$row['name'],time()+7*24*60*60,'/');
        $response=[
            "code" => "0000",
            "msg" =>"验证成功"
        ];
        die(json_encode($response));
    }else{
        $response=[
            "code" => "0011",
            "msg" =>"账号或密码错误"
        ];
        die(json_encode($response));
    }
}



// 用户登出
function logout(){
    session_start();
    session_destroy();
    setcookie('name','',time()-3600,'/'); 
    $response=[
        "code" => "0000",
        "msg" =>"登出成功"
    ];
    die(json_encode(response));
}


// 获取用户名
function getName(){
    // 判断用户的登录情况
    session_start();
    // 1. 判断 cookie 中是否有那个我们自己给的 cookie
    // 如果有，把 cookie 的数据写入到 session
    if(isset($_COOKIE['name'])){
        $Question['name'];
    }
    // 2. 判断 session 中是否有数据
    if(!isset($_SESSION['name'])){
        // session 没数据，跳转到登录页
        $response = [
            "code" => "0001",
            "msg" => "当前不存在任何用户"
        ];
        die(json_encode($response));
    }
    // 传送用户名
    $response = [
        "code" => "0000",
        "msg" => "获取用户名成功",
        "data" => $_SESSION['name']
    ];
    // 打印出用户名，这个用户名用于页面的导航栏显示
    die(json_encode($response));
}

