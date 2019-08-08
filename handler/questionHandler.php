<?php

// $do = "";
// if(isset($_POST['do'])){
//     $do = $_POST['do'];
// }
// if(isset($_GET['do'])){
//     $do = $_GET['do'];
// }

// $do();


function getAllQuestions(){
    include_once(App::$base . "/common/auth.php");
    if (!isAuthed()) {
        $response = [
            "code" => "0007",
            "msg" => "您还未登录"
        ];
        die(json_encode($response));
    }

    include_once(App::$base . "/model/Question.php");
    // 创建数据库对象
    $question = new Question();
    $page=$_GET['page'];
    if ($question->isConnectError) {
        $response = [
            "code" => "0001",
            "msg" => "与数据库连接发生中断",
        ];
        die(json_encode($response));
    }
    $pageSize=7;
    $totalCount=$question->getTotalCount();
    $pageCount=ceil($totalCount/$pageSize);
    $result=$question->getQuestionListByPage($page,$pageSize);
    if(!$result){
        $response = [
            "code" => "0002",
            "msg" => "获取问题列表时发生了错误"
        ];
        die(json_encode($response));
    }else {
        if ($result->num_rows === 0) {
            $response = [
                "code" => "0000",
                "msg" => "获取成功",
                "data" => []
            ];
            die(json_encode($response));
        }
        $questions = [];
        while ($row = $result->fetch_assoc()) {
            $question = $row['question'];
            $time = $row['time'];
            $id = $row['id'];
            $name = $row['name'];
            array_push($questions, [
                "question" => $question,
                "time" => $time,
                "id" => $id,
                "name" => $name,
            ]);
        }
        $response = [
            "code" => "0000",
            "msg" => "获取成功",
            "data" => [
                'pageCount' => $pageCount,
                'questions' => $questions
            ],
        ];
        die(json_encode($response));
    }
}


function deleteQuestion()
{
    //声明删除动作 执行前要先进行下方验证
    function doneDelete()
    {
        include_once(App::$base . "/common/auth.php");
        if (!isAuthed()) {
            $response = [
                "code" => "0007",
                "msg" => "您还未登录"
            ];
            die(json_encode($response));
        }
        include_once(App::$base . "/model/Question.php");
        $question = new Question();
        if ($question->isConnectError) {
            $response = [
                "code" => "0001",
                "msg" => "连接数据库时发生了错误"
            ];
            die(json_encode($response));
        }

        $result = $question->delete($_POST['id']);
        if (!$result) {
            $response = [
                "code" => "0002",
                "msg" => "删除问题时发生了一些错误"
            ];
            die(json_encode($response));
        }
        $response = [
            "code" => "0000",
            "msg" => "删除问题成功",

        ];
        die(json_encode($response));
    }

    //删除前需要验证三个值: 1.问题主人id，
    //                   2.当前用户的权限
    //                   3.当前用户id

    // 1.获取问题的主人id
    include_once(App::$base . "/model/Question.php");
    $question = new Question();
    if ($question->isConnectError) {
        $response = [
            "code" => "0001",
            "msg" => "与数据库连接发生中断",
        ];
        die(json_encode($response));
    }
    $result = $question->getQuestionMasterId($_POST['id']);
    $questionMasterId=$result['user_id'];

    // 2.去数据表users读取用户权限，在执行删除操作
    include_once(App::$base . "/model/Account.php");
    $Account = new Account();
    if ($Account->isConnectError) {
        $response = [
            "code" => "0001",
            "msg" => "与数据库连接发生中断",
        ];
        die(json_encode($response));
    }
    if (session_start() === PHP_SESSION_NONE) {
        session_start();
    }
    $who = $_SESSION['id'];
    /*读取用户信息，用来获取权限*/
    $result = $Account->getRight($_SESSION['id']);
    $currentUserRight=$result['right'];

    //验证
    //条件1   问题的user_id和当前用户ID是否一致
    //条件2     当前用户若是管理员
    if (((int)$who === (int)$questionMasterId) || (int)$currentUserRight === 1) {
        //验证通过，执行删除
        doneDelete();
    } else {
        $response = [
            "code" => "0010",
            "msg" => "你不是管理员，不能删除别人的信息",
        ];
        die(json_encode($response));
    }


}

function createQuestion()
{
    // error_reporting(E_ALL^E_WARNING^E_NOTICE);
    include_once(App::$base . "/common/auth.php");
    if (!isAuthed()) {
        $response = [
            "code" => "0001",
            "msg" => "您还未登录"
        ];
        die(json_encode($response));
    }
    include_once(App::$base . "/model/Question.php");
    // error_reporting(E_ALL^E_WARNING^E_NOTICE);
    if (isset($_POST["question"])) {
        $question = new Question();
        if ($question->isConnectError) {
            $response = [
                "code" => "0004",
                "msg" => "提交问题时发生了一些错误"
            ];
            die(json_encode($response));
        }

        $result = $question->create($_POST['question'], $_POST["checkbox"], $_SESSION['id'], $_SESSION['name']);
        if (!$result) {
            $response = [
                "code" => "0004",
                "msg" => "提交问题时发生了一些错误"
            ];
            die(json_encode($response));
        }
        $result = $question->getLastestOne();
        $row = $result->fetch_assoc();
        $response = [
            "code" => "0000",
            "msg" => "提交成功！",
            "data" => [
                "id" => $row['id'],
                "time" => $row['time'],
                "question" => $row['question'],
                "name" => $row['name']
            ]
        ];

        die(json_encode($response));

    }

    function delete()
    {
        include_once(App::$base . "/common/auth.php");
        if (!isAuthed()) {
            $response = [
                "code" => "0007",
                "msg" => "您还未登录"
            ];
            die(json_encode($response));
        }
        include_once(App::$base . "/model/Question.php");
        $question = new Question();
        if ($question->isConnectError) {
            $response = [
                "code" => "0001",
                "msg" => "连接数据库时发生了错误"
            ];
            die(json_encode($response));
        }

        $result = $question->delete($_POST['id']);
        if (!$result) {
            $response = [
                "code" => "0002",
                "msg" => "删除问题时发生了一些错误"
            ];
            die(json_encode($response));
        }
        $response = [
            "code" => "0000",
            "msg" => "删除问题成功",

        ];
        die(json_encode($response));
    }
}


