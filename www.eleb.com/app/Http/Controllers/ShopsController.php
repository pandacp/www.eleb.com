<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Menu_category;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopsController extends Controller
{
    //
    public function index(Request $request)
    {
        $keyword = $request->keyword;

        if($keyword){
            $Shops = Shop::where('shop_name','like',"%{$keyword}%")->get();
        }else {
            $Shops = Shop::all();
        }
        foreach($Shops as &$shop){
            $shop['distance']=500;
            $shop['estimate_time']=30;
            unset($shop['rating']);
            unset($shop['status']);
            unset($shop['shop_category_id']);
            unset($shop['created_at']);
            unset($shop['updated_at']);
        }

       return json_encode($Shops);
    }

    public function shop(Request $request)
    {
        $shops = Shop::where('id',$request->id)->first();//商家
//        var_dump($shops);
        $menu_categories = Menu_category::where('shop_id',$shops->id)->get();//菜品分类
//        var_dump($menu_categories);
        $Shops=[];
        foreach($menu_categories as $menu_category) {
            $Shops = $shops;
//            $Shops = $menu_category;
            $menus = Menu::where('category_id', $menu_category->id)->get();//菜品集合
            foreach ($menus as $menu) {
                //不需要的字段
                unset($Shops['rating']);
                unset($Shops['status']);
                unset($Shops['shop_category_id']);
                unset($Shops['created_at']);
                unset($Shops['updated_at']);
                //添加的字段
                $Shops['distance']=500;
                $Shops['estimate_time']=30;
                $Shops['service_code']=4.6;
                $Shops['foods_code']=4.5;
                $Shops['high_or_low']=true;
                $Shops['h_l_percent']= 30;
                $Shops['evaluate'] = [[
                "user_id"=>12344,
                "username"=>"w******k",
                "user_img"=>"http://www.homework.com/images/slider-pic4.jpeg",
                "time"=>"2017-2-22",
                "evaluate_code"=>4.6,
                "send_time"=>30,
                "evaluate_details"=>"不怎么好吃",
                ],[
                "user_id"=> 12344,
                "username"=> "w******k",
                "user_img"=>"http://www.homework.com/images/slider-pic4.jpeg",
                "time"=> "2017-2-22",
                "evaluate_code"=>4.5,
                "send_time"=> 30,
                "evaluate_details"=> "很好吃"
                ]
            ];
                $Shops['commodity'] = [[
                    "description" => $menu_category->description,
                    'is_selected' => $menu_category->is_selected,
                    'name' => $menu_category->name,
                    'type_accumulation' => $menu_category->type_accumulation,
                    'goods_list' => [[
                        'goods_id' => $menu->id,
                        'goods_name' => $menu->goods_name,
                        'rating' => $menu->rating,
                        'goods_price' => $menu->goods_price,
                        'description' => $menu->description,
                        'month_sales' => $menu->month_sales,
                        'rating_count' => $menu->rating_count,
                        'tips' => $menu->tips,
                        'satisfy_count' => $menu->satisfy_count,
                        'satisfy_rate' => $menu->satisfy_rate,
                        'goods_img' => $menu->goods_img,
                    ]]
                ]];

            }
        }
        //赶回数据
        return json_encode($Shops);
    }

    public function loginCheck()
    {

    }
}
