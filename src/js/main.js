import $ from "jquery";
import 'bootstrap';
window.jQuery=$;
jQuery=$;
window.$=window.jQuery;
/*当前页面作为全局变量*/
let currentPage = 1;
// 提交问题
$("#submit").click((e)=>{
    e.preventDefault();
    $.ajax({
        url: "/question-list/public/index/Question/createQuestion",
        type: "post",
        data: {
            question: $("#question-input").val(),
            isAnonymous: $("input[type='checkbox']").is(":checked"),
        },
        success:  (response)=>{
            response = JSON.parse(response);
            if (response.code !== "0000") {
                $("#error-text").html(response.msg);
                return;
            }
            // // 提交完要清空输入栏
            getQuestionList(currentPage);
            $("#question-input").val("");
            bindDeleteListener(true);
        },
        error: ()=>{
            $("#error-text").html("好像我与服务器分手了TAT");
        }
    });
});

// 绑定删除单击事件
function bindDeleteListener(onlyLast) {
    onlyLast = onlyLast === undefined ? false : onlyLast;
    let $deleteBtn = $(".delete-btn");
    if (onlyLast) {
         $deleteBtn = $deleteBtn.last();
    }
    $deleteBtn.click(function () {
        $("#error-text").html("");
        /*let $questionList = $(this).parent();*/
        $.ajax({
            url: "/question-list/public/index/Question/deleteQuestion",
            type: "post",
            data: {
                id: $(this).data("id"),
            },
            success:  (response)=>{
                // 记得转化为json对象
                response = JSON.parse(response);
                // 错误则输出信息
                if (response.code !== "0000") {
                    $("#error-text").html(response.msg);
                    return;
                }
                getQuestionList(currentPage);
            }
        });

    });
}
bindDeleteListener();

// 登出
$("#logout").click(()=>{
    $.ajax({
        url: "/question-list/public/index/user/logout",
        type: "post",
        success:  (response)=>{
            location.href = "login.html";
        }
    })
})

/*在跳转input处按下回车跳转*/
$('#turn-to').bind('keydown',  (event)=>{
    if (event.keyCode == "13") {
        getQuestionList($("#turn-to").data("page"));
    }
});

function getQuestionList(page){
    page = page === undefined?1:page;
    $.ajax({
        url: "/question-list/public/index/question/getAllQuestions",
        type: "get",
        data:{
            page:page
        },
        success: (response)=>{
            response = JSON.parse(response);
            if(response.code!=="0000"){
                $("#error-text").html(response.msg);
                return;
            }
            renderQuestionList(response.data.questions);
            generatePaginationBtn(page,response.data.pageCount);
        },
        error:()=>{
            $("#error-text").html("好像我与服务器分手了TAT");
        }
    });
}
// 渲染问题列表
function renderQuestionList(data) {
    // 提前清空
    $("#question-list").html("");
    // 遍历输出问题列表
    for (let index in data) {
        let question = data[index];
        // 输出问题
        console.log(question.name);
        $("#question-list").append('\
                        <div class="item">\
                        <div class="question">' + question.content + '</div>\
                        <div class="time">' + question.time + '&nbsp;&nbsp;&nbsp;&nbsp; By ' + question.name + '</div>\
                        <button type="submit" class="delete-btn"  data-id=' + question.id + '>X</button>\
                        </div>\
            ');
    }
    // 重新绑定删除按钮的监听事件
    bindDeleteListener();
}


// 窗口加载后获取昵称
$(window).on("load",  ()=>{
    $.ajax({
        url: "/question-list/public/index/User/getName",
        type: "get",
        success:  (response)=>{
            response = JSON.parse(response);
            // 获取失败则跳转dao登录页面
            if (response.code !== "0000") {
                location.href = "login.html";
                return;
            }
            // 获取成功则输出昵称
            $("#user-name").html(response.data);
            if (/(page)=(\w+)/.exec(location.search))
                currentPage = /(page)=(\w+)/.exec(location.search)[2];
            console.log(currentPage);
            getQuestionList(currentPage);
        }
    });
    console.log("sdasdasd");
})



function generatePaginationNum(currentPage,pageCount,paginationBtnCount) {
    paginationBtnCount=paginationBtnCount === undefined ? 5: paginationBtnCount;
    let result=[];
    //当页面数目小于按钮数目的时候
    if(paginationBtnCount>=pageCount){
        for(let i=0;i<pageCount;i++){
            result.push(i+1);
        }
        return result;
    }
    //当前页之前按钮数目
    let btnCountBefore= Math.floor((paginationBtnCount-1)/2);
    //当前页之后按钮数目
    let btnCountAfter= Math.ceil((paginationBtnCount-1)/2);

    for(let i=currentPage-btnCountBefore;i<paginationBtnCount;i++){
        result.push(i);
    }
    result.push(currentPage);
    for(let i=currentPage+1;i<currentPage+btnCountAfter;i++){
        result.push(i);
    }

    //如果数组第一个数小于一,则往右滚动
    if(result[0]<1){
        let offset=1-result[0];
        for(let i=0;i<paginationBtnCount;i++){
            result[i]+=offset;
        }
    }
    //如果数组最后一个数大于总页面个数，则往左滚动
    if(result[paginationBtnCount-1]>pageCount){
        let offset=result[paginationBtnCount-1]-pageCount;
        for(let i=0;i<paginationBtnCount;i++){
            result[i]-=offset;
        }
    }

    //如果页面数跟btn数目不等，则切割btn数目
    result=result.slice(0,pageCount);
    console.log(result);
    return result;
}

function generatePaginationBtn(currentPage,pageCount){

    let paginationNumArr=generatePaginationNum(currentPage,pageCount);
    $(".pagination").html('');
    $(".pagination").append('<li id="previous">\n' +
        '             <a  href="#" aria-label="Previous">\n' +
        '                 <span aria-hidden="true">&laquo;</span>\n' +
        '             </a>\n' +
        '         </li>');

    for (let i = 0; i <paginationNumArr.length; i++) {
        /*给每一个跳转按钮赋上id还有内容*/
        $(".pagination").append('<li class="pagination-num-btn" data-page="'+paginationNumArr[i]+'"><a href="#">'+paginationNumArr[i]+'</a></li>\
            ');
    }
    $(".pagination").append('\
            <li class="pagination-next-btn">\
                <a href="#" aria-label="Next">\
                    <span aria-hidden="true">&raquo;</span>\
                </a>\
            </li>');

    $(".pagination-num-btn").click(function () {
        location.href="index?page="+$(this).data("page");
    });

    $(".pagination-prev-btn").click(function(){
        if(currentPage!==1){
            currentPage=parseInt(currentPage)-1;
            location.href="index?page="+(currentPage);
            /*getQuestionList(currentPage-1);
            window.currentPage = currentPage-1;*/
        }
    });

    $(".pagination-next-btn").click(function(){
        if(currentPage!==pageCount){
            currentPage=parseInt(currentPage)+1;
            location.href="index?page="+(currentPage);
            /*getQuestionList(currentPage+1);
            window.currentPage = currentPage+1;*/
        }
    });
}

