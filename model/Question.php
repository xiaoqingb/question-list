<?php
include_once(__DIR__."/SqlHelper.php");
// 数据库模型
class Question extends SqlHelper{
    // 创建问题
    public function create($content,$checkbox,$id,$name){
        $time=date("Y-m-d H:i:s");
        // error_reporting(E_ALL^E_WARNING^E_NOTICE);
        if($checkbox==="false"){
            $sql="insert into `question-list`(question,time,name,user_id) values('$content','$time','$name','$id')";
        }else{
            $sql="insert into `question-list`(question,time,name,user_id) values('$content','$time','匿名','$id')";
        }
        $result=$this->query($sql);
        if(!$result){
            return false;
        }else{
            return true;
        }
    }


    // 删除问题
    public function delete($id){

        $result =$this->query('delete from `question-list` where id='.$id);
        if(!$result){
            return false;
        }else{
            return true;
        }
    }

    // 获取问题
    public function getAllQuestions(){
        $result = $this->query('select * from `question-list`');
        return $result;
    }

    public function getQuestionMasterId($id){
    $result = $this->query('select `user_id`  from `question-list` where id ='.$id);
    $result = $result->fetch_assoc();
    return $result;
}

    // 获取最后一个问题
    public function getLastestOne(){
        $result = $this->query('SELECT * FROM `question-list` ORDER BY `id` desc limit 1');
        return $result;
    }

    // 获取所有的问题数量
    public function getTotalCount(){
        $result = $this->query('select count(*) as total_count from `question-list`');
        $row = $result->fetch_assoc();
        return $row['total_count'];
    }

    // 按分页获取问题列表
    public function getQuestionListByPage($currentPage,$pageSize=10){
        $result = $this->query('select * from `question-list` limit '.($currentPage-1)*$pageSize.','.$pageSize);
        return $result;
    }


}

    
    