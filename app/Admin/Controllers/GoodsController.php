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
use App\Model\GoodsModel;
class GoodsController extends Controller{

    use HasResourceActions;
    public function index(Content $content){
        return $content->header("商品管理")
                        ->description("商品列表")
                        ->body($this->grid());
    }
    protected function grid(){
        $grid=new Grid(new GoodsModel());
        $grid->model()->orderBy("goods_id","desc");
        $grid->goods_id("商品ID");
        $grid->name("商品名称");
        $grid->num("库存");
        $grid->price("价格");
        $grid->add_time('添加时间')->display(function($time){
            return date('Y-m-d H:i:s',time());
        });
        return $grid;
    }
    protected function form(){
        $form = new Form(new GoodsModel());
        $form->display('goods_id', '商品ID');
        $form->text('name', '商品名称');
        $form->number('num', '库存');
       // $form->ckeditor('content');
        $form->currency('price', '价格')->symbol('¥');
        return $form;
    }

    public function edit($goods_id,Content $content){
        return $content->header("商品管理")
            ->description("编辑")
            ->body($this->form()->edit($goods_id));
    }
    //添加
    public function create(Content $content){
        return $content->header("商品管理")
                        ->description("添加")
                        ->body($this->form());
    }
    //修改
//    public function update($goods_id){
//        echo "修改";
//
//    }
    //删除
    public function del($goods_id){
        //echo "删除";
        $respone=[
            "status"=>true,
            "message"=>"ok"
        ];
        return $respone;
    }
    //展示商品详情
    public function show(Request $request){
        echo "展示";

    }
    //分页
    public function page(Request $request){
            echo 1;
        $name=$request->input("name");
        $results =DB::table('goods')->where('name','like','%$name%')->paginate(2);
        return view('adminlist.user_list_seach', ['adminlist'=> $results,'name'=>$name]);
    }

}