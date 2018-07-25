<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Menu_category;
use App\Models\Shop;
use App\Models\Yh;
use App\SignatureHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ShopsController extends Controller
{
    //所有商家列表
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
    //指定商家信息
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
    //发送短信
    public function sms(Request $request)
    {
        $tel = $request->tel;
//        $params = [];
////        $tel = $request->input('tel',13658010910);
//        // *** 需用户填写部分 ***
//        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
//        $accessKeyId = "LTAIfhptcWxBzIYt";
//        $accessKeySecret = "S4FxaWQ4wvY9hoP7jwdID4e9iDA2E0";
//
//        // fixme 必填: 短信接收号码
//        $params["PhoneNumbers"] = $tel;
//
//        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
//        $params["SignName"] = "陈盼";
//
//        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
//        $params["TemplateCode"] = "SMS_140505052";
//
//        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
//        $params['TemplateParam'] = Array (
//            "code" => random_int(1111,9999),
////            "product" => "阿里通信"
//        );
//
//        // fixme 可选: 设置发送短信流水号
//        $params['OutId'] = "12345";
//
//        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
//        $params['SmsUpExtendCode'] = "1234567";
//
//        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
//        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
//            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
//        }
//
//        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
//        $helper = new SignatureHelper();
//
//        // 此处可能会抛出异常，注意catch
//        $content = $helper->request(
//            $accessKeyId,
//            $accessKeySecret,
//            "dysmsapi.aliyuncs.com",
//            array_merge($params, array(
//                "RegionId" => "cn-hangzhou",
//                "Action" => "SendSms",
//                "Version" => "2017-05-25",
//            ))
//        // fixme 选填: 启用https
//        // ,true
//        );
        $params = array ();

        // *** 需用户填写部分 ***
        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = "LTAIfhptcWxBzIYt";
        $accessKeySecret = "S4FxaWQ4wvY9hoP7jwdID4e9iDA2E0";

        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $tel;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = "陈盼";

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = "SMS_140505052";

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = Array (
            "code" => random_int(1111,9999),
//            "product" => "阿里通信"
        );

        // fixme 可选: 设置发送短信流水号
        $params['OutId'] = "12345";

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        $params['SmsUpExtendCode'] = "1234567";

        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelper();

        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        // fixme 选填: 启用https
        // ,true
        );
        //保存验证码到缓存中
        $code = substr($params['TemplateParam'],'-5','4');
//        var_dump($code);
        Redis::set('code',$code);
//        Redis::expire('code',120);
        $codes = Redis::get('code');
//        var_dump($codes);
//        dd($content);
        if(!empty($content)){
            $result = [
                "status"=>"false",
                "message"=>"获取短信验证码失败"
            ];
        }else{
            $result = [
                "status"=>"true",
                "message"=>"获取短信验证码成功"
            ];
        }
//      dd($content);
        return json_encode($result);
    }
    //注册
    public function regist(Request $request)
    {
        $request->username;
        $request->password;
        $request->tel;
        $sms = $request->sms;
//        var_dump($sms);
        $code = Redis::get('code');
        if($sms!=$code){
            $result = [
                "status"=>"false",
                "message"=>"注册失败"
            ];
            return json_encode($result);
        }
        //如果
        $rs = Yh::create([
            'username'=>$request->username,
            'password'=>bcrypt($request->password),
            'tel'=>$request->tel,
            'remember_token'=>'qweasdzxc',
        ]);
        $result = [
            "status"=>"true",
            "message"=>"注册成功"
        ];

        return json_encode($result);
    }
    //登录
    public function login(Request $request)
    {
        $username = $request->name;
        $password = $request->password;
        if(Auth::attempt([
            'username'=>$username,
            'password'=>$password,
        ])){
            $id = Auth::user()->id;
            $result = [
                "status"=>"true",
                "message"=>"登录成功",
                "user_id"=>"{$id}",
                "username"=>"{$username}"
            ];
            return json_encode($result);
        }else{
            $result = [
                "status"=>"false",
                "message"=>"登录失败",
            ];
            return json_encode($result);
        }


//        if(Auth::attempt([
//            'username'=>$username,
//            'password'=>$password,
//        ])){
//            $id = Auth::user()->id;
//            $result = [
//                "status"=>"true",
//                "message"=>"登录成功",
//                "user_id"=>"{$id}",
//                "username"=>"{$username}"
//            ];
//            return json_encode($result);
//        }else{
//            $result = [
//                "status"=>"false",
//                "message"=>"登录失败",
//            ];
//            return json_encode($result);
//        }


    }

}
