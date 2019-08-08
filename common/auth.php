<?php
// 判断当前的访问有没有进行过登录
function isAuthed(){
    if(!session_id()) session_start();
    if(isset($_COOKIE['name'])){
        $_SESSION['name'] = $_COOKIE['name'];
    }
    if(!isset($_SESSION['name'])){
        return false;
    }
    return true;
}
