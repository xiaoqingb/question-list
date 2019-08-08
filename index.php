<?php

class App
{
    public static $version = "1.0";
    public static $dbHost = "localhost";
    public static $dbAccount = "root";
    public static $dbPassword = "qqq112233";
    public static $dbName = "questions";
    public static $dbPort = "3306";
    public static $base = __DIR__;

}

if (!isset($_GET['handler']) || !isset($_GET['method'])) {
    include_once(App::$base . '/page/index.html');
    die();
}
$handler = $_GET['handler'];
$method = $_GET['method'];
include_once(App::$base . "/handler/" . $handler . ".php");
$method();