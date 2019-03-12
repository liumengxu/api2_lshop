<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class goodsModel extends Model
{
    public $table = 'shop_goods';
    public $timestamps = false;
    public $primaryKey = 'goods_id';

    public function goodsInfo($goods_id){
        $where=[
            'goods_id'=>$goods_id
        ];
        return goodsModel::where($where)->get();
    }
    //获取某字段时 格式化 该字段的值
    public function getPriceAttribute($price)
    {
        return $price / 100;
    }

    //获取某字段时 格式化 该字段的值
    public function getStoreAttribute($store)
    {
        return '>' . $store .' <';
    }
}
