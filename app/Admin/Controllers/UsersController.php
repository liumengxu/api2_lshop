<?php
namespace App\Admin\Controllers;

use Encore\Admin\Controllers\HasResourceActions;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use App\Model\UserModel;

class UsersController extends Controller{
    use HasResourceActions;

    public function index(Content $content){
        return $content->header("用户管理")
            ->description("用户列表")
            ->body($this->grid());
    }
    protected function grid(){
        $grid=new Grid(new UserModel());
        $grid->model()->orderBy("id","asc");
        $grid->id("用户ID");
        $grid->name("用户名称");
        $grid->pwd("密码");
        $grid->age("年龄");
        $grid->email("邮箱");
        $grid->time('添加时间')->display(function($time){
            return date('Y-m-d H:i:s',time());
        });
        return $grid;
    }
    protected function form(){
        $form = new Form(new UserModel());
        $form->display('id', '用户ID');
        $form->text('name', '用户名称');
        $form->password('pwd', '密码');
        $form->number('age', '年龄');
        $form->email('email', '邮箱');
        return $form;
    }
    public function edit($id,Content $content){
        return $content->header("用户管理")
            ->description("编辑")
            ->body($this->form()->edit($id));
    }
    //添加
    public function create(Content $content){
        return $content->header("用户管理")
            ->description("添加")
            ->body($this->form());
    }
    //修改
    public function update($id){
        echo "修改";
    }
    //删除
    public function del($id){
        echo "删除";
        $respone=[
            "status"=>true,
            "message"=>"ok"
        ];
        return $respone;
    }
    //展示商品详情
    public function show(){
        echo "展示";
    }
}