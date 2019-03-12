<?php

namespace App\Admin\Controllers;

use App\Model\MaterialModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use GuzzleHttp;
use Illuminate\Http\Request;

class ForeverController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MaterialModel);

        $grid->id('Id');
        $grid->media_id('Media id');
        $grid->add_time('Add time');
        $grid->media_url('Media url');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(MaterialModel::findOrFail($id));

        $show->id('Id');
        $show->media_id('Media id');
        $show->add_time('Add time');
        $show->media_url('Media url');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MaterialModel);
        $form->file('media_id', 'Media id');
        return $form;
    }
    //获取微信Access_Token
    public function getWXAccessToken(){
        //得到缓存
        $token = Redis::get($this->redis_weixin_access_token);
        if(!$token){        // 无缓存 请求微信接口
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WEIXIN_APPID').'&secret='.env('WEIXIN_APPSECRET');
            $data = json_decode(file_get_contents($url),true);

            //记录缓存
            $token = $data['access_token'];
            Redis::set($this->redis_weixin_access_token,$token);
            Redis::setTimeout($this->redis_weixin_access_token,3600);
        }
        return $token;
    }
    //获取永久素材列表
    public function fileshow(Request $request){
        //保存文件  测试
        $img_file=$request->file('media');
        //print_r($img_file);
        $img_orign_name=$img_file->getClientOriginalName();
        //print_r($img_orign_name);
        $file_ext=$img_file->getClientOriginalExtension();  //得到扩展名字
        //print_r($file_ext);exit;
        $new_file_name=str_random(15).'.'.$file_ext;  //重命名
        print_r($new_file_name);
        //文件保存路径
        $save_file_path=$request->media->storeAs("fileshow",$new_file_name);
        echo $save_file_path;
        //上传微信到永久素材
        $this->upMaterial($save_file_path);
    }


    //上传素材
    public function upMaterial($file_path)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$this->getWXAccessToken().'&type=image';
        $client = new GuzzleHttp\Client();
        $response = $client->request('POST',$url,[
            'multipart' => [
                [
                    'name'     => 'username',
                    'contents' => 'ceshi'
                ],
                [
                    'name'     => 'media',
                    'contents' => fopen($file_path, 'r')
                ],
            ]
        ]);
        $body = $response->getBody();
        echo $body;
        $d = json_decode($body,true);
       // print_r($d);exit;
        $data=[
            'media_id'=>$d['media_id'],
            'add_time'=>time(),
            'media_url'=>$d['url']
        ];
        $res=MaterialModel::insertGetId($data);

    }
}
