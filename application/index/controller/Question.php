<?php
namespace app\index\controller;

use app\index\model\Question as QuestionModel;
use think\Session;
use think\Cookie;
use think\Validate;

class Question extends Auth{

    protected $beforeActionList=[
        'isAuthed'
    ];
    /**
     * 创建问题
     */
    public function createQuestion()
    {
        //step 1.验证用户的输入
        $questionContent= input('post.question','');
        $isAnonymous= input('post.isAnoymous','false');

        $rule =[
            'question'=>'require',
        ];
        $msg=[
            'question.require'=>'您没有输入问题',
        ];
        $data=[
            'question'=>$questionContent,
            'isAnonymous'=>$isAnonymous
        ];
        $validate = new Validate($rule,$msg);
        $result =$validate->check($data);
        if(!$result){
            return json_encode(
                [
                    'code'=>'0001',
                    'msg'=>$validate->getError()
                ]
                );
        }
        
        //step 2.获取用户名的和id
        $isAnonymous=$isAnonymous=== "true" ? true : false ;
        if($isAnonymous){
            $userId=0;
            $userName = "匿名";
        }else{
            $userId = Session::get('id');
            $userName = Session::get('name');
        }
        //step 3.创建问题
        $time=date('Y-m-d H:i:s');
        $question =new QuestionModel();
        $question->user_id = $userId;
        $question->content = $questionContent;
        $question->time=$time;
        $result=$question->save();
        if(!$result){
            return json_encode(
                [
                    'code' =>'0002',
                    'msg' =>'提交问题时发生了错误'
                ]
            );
        }

        //step 4.创建成功还要返回该新增问题的id
        return json_encode(
            [
                'code'=>'0000',
                'msg'=>'提交成功!',
                'data'=>[
                    'id'=>$question->id,
                    'time'=>$time,
                    'content'=>$questionContent,
                    'userName'=>$userName
                ],
            ]
        );
    }
    
    /**
     * 删除问题
     */
    public function deleteQuestion(){

        $questionId=input("post.id");
        
        //step 1. 验证用户提交的问题id
        $rule = [
            'id'  => 'require|number',
        ];
        $msg = [
            'question.require' => '您提交的数据有误',
            'question.number' => '您提交的数据有误',
        ];

        $data = [
            'id' => $questionId
        ];
        $validate = new Validate($rule,$msg);
        $result   = $validate->check($data);
        if(!$result){
            return json_encode(
                [
                    'code' => '0001',
                    'msg' => $validate->getError()
                ]
            );
        }

        //step 2. 删除问题
        $question = new QuestionModel();
        $result=$question->where('id',$questionId)->delete();
        if(!$result){
            return json_encode(
                [
                    'code'=>'0001',
                    'msg'=>'删除问题时发生了错误'
                ]
            );
        }
        return json_encode(
            [
                'code'=>'0000',
                'msg'=>'删除成功'
            ]
        );
    }

    // 获取问题
    public function getAllQuestions(){
        $result = $this->query('select * from `question-list`');
        return $result;
    }

    public function getQuestionMasterId($id){
        $page=input("get.page","1");

        //step 1. 验证用户的输入
        // step 1. 验证用户的输入
        $rule = [
            'page'  => 'require|number',
        ];
        $msg = [
            'page.require' => '您提交的数据有误',
            'page.number' => '您提交的数据有误',
        ];
        $data = [
            'page' => $page
        ];
        $validate = new Validate($rule,$msg);
        $result   = $validate->check($data);
        if(!$result){
            return json_encode(
                [
                    'code' => '0001',
                    'msg' => $validate->getError()
                ]
            );
        }

        //step 2. 格式化数据
        $page =(int) $page;

        //step 3. 计算分页
        $question = new QuestionModel();
        $pageSize=10;
        $totalCount = $question->count();
        $pageCount= ceil($totalCount/$pageSize);

        //step 4. 获取分页数据

        $result =$question->page($page.$pageSize)->select();
        if(!$result){
            return json_encode(
                [
                    'code' => '0002',
                    'msg' => '获取问题时发生了错误'
                ]
            );
        }

        //step 5. 获取么一条问题的主人

        $questions=[];
        foreach($result as $q){
            $user = $q->user;
            if(!$user){
                $userName = "匿名";
            }else{
                $userName = $user->name;
            }
            array_push($questions,[
                "question"=>$q->content,
                "time" => $q->time,
                "id"=> $q->id,
                "userName" => $userName
            ]);
        }
        return json_encode(
            [
                'code' => '0000',
                'msg' => '获取成功',
                "data" => [
                    'pageCount' => $pageCount,
                    'questions' => $questions
                ]
            ]
        );
    }


}

    
    