<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Menu;
use App\Models\Menu_category;
use App\Models\Order;
use App\Models\Order_good;
use App\Models\Shop;
use App\Models\User;
use App\Models\Yh;
use App\SignatureHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Mockery\Exception;

class ShopsController extends Controller
{
    //所有商家列表
    public function index(Request $request)
    {
        $keyword = $request->keyword;

        if ($keyword) {
            $Shops = Shop::where('shop_name', 'like', "%{$keyword}%")->get();
        } else {
            $Shops = Shop::all();
        }
        foreach ($Shops as &$shop) {
            $shop['distance'] = 500;
            $shop['estimate_time'] = 30;
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
        $shops = Shop::where('id', $request->id)->first();//商家
//        var_dump($shops);
        $menu_categories = Menu_category::where('shop_id', $shops->id)->get();//菜品分类
//        return json_encode($menu_categories);
        $Shops = [];
        foreach ($menu_categories as $menu_category) {
            $menus = Menu::where('category_id', $menu_category->id)->get();//菜品集合
            $Shops = $shops;
            //不需要的字段
            unset($Shops['rating']);
            unset($Shops['status']);
            unset($Shops['shop_category_id']);
            unset($Shops['created_at']);
            unset($Shops['updated_at']);
            //添加的字段
            $Shops['distance'] = 500;
            $Shops['estimate_time'] = 30;
            $Shops['service_code'] = 4.6;
            $Shops['foods_code'] = 4.5;
            $Shops['high_or_low'] = true;
            $Shops['h_l_percent'] = 30;
            $Shops['evaluate'] = [
                [
                    "user_id" => 12344,
                    "username" => "w******k",
                    "user_img" => "http://www.homework.com/images/slider-pic4.jpeg",
                    "time" => "2017-2-22",
                    "evaluate_code" => 4.6,
                    "send_time" => 30,
                    "evaluate_details" => "不怎么好吃",
                ],
                [
                    "user_id" => 12344,
                    "username" => "w******k",
                    "user_img" => "http://www.homework.com/images/slider-pic4.jpeg",
                    "time" => "2017-2-22",
                    "evaluate_code" => 4.5,
                    "send_time" => 30,
                    "evaluate_details" => "很好吃"
                ]
            ];

            foreach ($menus as $menu) {
                $menu['goods_id'] = $menu->id;
                unset($menu['id']);
                unset($menu['shop_id']);
                unset($menu['category_id']);
                unset($menu['created_at']);
                unset($menu['updated_at']);
            }
            $menu_category['goods_list'] = $menus;

            unset($menu_category['id']);
            unset($menu_category['shop_id']);
            unset($menu_category['created_at']);
            unset($menu_category['updated_at']);
//            $Shops['commodity'] = [$menu_category];
        }
        $Shops['commodity'] = $menu_categories;


        //返回数据
        return json_encode($Shops);
    }
    //发送验证码短信
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
        $params = array();

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
        $params['TemplateParam'] = Array(
            "code" => random_int(1111, 9999)
        );

        // fixme 可选: 设置发送短信流水号
        $params['OutId'] = "12345";

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        $params['SmsUpExtendCode'] = "1234567";

        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }
        //-----------------//
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
        //-----------------//

        //保存验证码到缓存中
        $code = substr($params['TemplateParam'], -5, 2);
        $tel = substr($params["PhoneNumbers"], 8, 3);
        $codes = $tel . $code;

        Redis::set('code', $codes);
        Redis::expire('code', 300);

//        if (!empty($content)) {
//            $result = [
//                "status" => "false",
//                "message" => "获取短信验证码失败"
//            ];
//        } else {
//
//        }
        $result = [
            "status" => "true",
            "message" => "获取短信验证码成功"
        ];
//      dd($content);
        return json_encode($result);
    }

    //注册
    public function regist(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|unique:yhs',
            'password' => 'required',
            'tel' => 'required|unique:yhs',
        ], [
            'username.required' => '用户名不能为空',
            'username.unique' => '用户名已存在',
            'password.required' => '密码不能为空',
            'tel.required' => '手机号码不能为空',
            'tel.unique' => '手机号码已存在',
        ]);

        $request->username;
        $request->password;
        $request->tel;
        $sms = $request->sms;
//        var_dump($sms);
        $code = Redis::get('code');
        if ($sms != $code) {
            $result = [
                "status" => "false",
                "message" => "验证码不正确"
            ];
            return json_encode($result);
        }
        //如果
        $rs = Yh::create([
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'tel' => $request->tel,
            'remember_token' => 'qweasdzxc',
        ]);
        $result = [
            "status" => "true",
            "message" => "注册成功"
        ];

        return json_encode($result);
    }

    //登录
    public function login(Request $request)
    {
        $username = $request->name;
        $password = $request->password;
        if (Auth::attempt([
            'username' => $username,
            'password' => $password,
        ])
        ) {
            $id = Auth::user()->id;
            $result = [
                "status" => "true",
                "message" => "登录成功",
                "user_id" => "{$id}",
                "username" => "{$username}"
            ];
            return json_encode($result);
        } else {
            $result = [
                "status" => "false",
                "message" => "登录失败",
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

    //地址列表接口
    public function addressList()
    {

        $user_id = Auth::user()->id;
        $addresses = Address::where('user_id', $user_id)->get();
        foreach ($addresses as $address) {
            unset($address['user_id']);
            unset($address['created_at']);
            unset($address['updated_at']);
            $address['provence'] = $address['province'];
            unset($address['province']);
            $address['area'] = $address['county'];
            unset($address['county']);
            $address['detail_address'] = $address['address'];
            unset($address['address']);
            unset($address['is_default']);
        }
        return json_encode($addresses);
    }

    //添加保存地址
    public function addAddress(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'tel' => 'required',
            'provence' => 'required',
            'city' => 'required',
            'area' => 'required',
            'detail_address' => 'required',
        ], [
            'name.required' => '用户名不能为空',
            'tel.required' => '手机号不能为空',
            'provence.required' => '省不能为空',
            'city.required' => '市不能为空',
            'area.required' => '县不能为空',
            'detail_address.required' => '详细地址不能为空',
        ]);
        $user_id = Auth::user()->id;//用户id
        $name = $request->name;//收货人姓名
        $tel = $request->tel;//收货人电话
        $province = $request->provence;
        $city = $request->city;
        $county = $request->area;
        $address = $request->detail_address;
        Address::create([
            'user_id' => $user_id,
            'name' => $name,
            'tel' => $tel,
            'province' => $province,
            'city' => $city,
            'county' => $county,
            'address' => $address,
            'is_default' => 0,
        ]);
        $result = [
            "status" => "true",
            "message" => "添加成功"
        ];
        return json_encode($result);

    }

    //获取指定地址
    public function address(Request $request)
    {
        $address = Address::where('id', $request->id)->first();

        unset($address['user_id']);
        unset($address['created_at']);
        unset($address['updated_at']);
        $address['provence'] = $address['province'];
        unset($address['province']);
        $address['area'] = $address['county'];
        unset($address['county']);
        $address['detail_address'] = $address['address'];
        unset($address['address']);
        unset($address['is_default']);

        return $address;
    }

    //保存修改地址
    public function editAddress(Request $request)
    {

        $this->validate($request, [
            'name' => 'required',
            'tel' => 'required',
            'provence' => 'required',
            'city' => 'required',
            'area' => 'required',
            'detail_address' => 'required',
        ], [
            'name.required' => '用户名不能为空',
            'tel.required' => '手机号不能为空',
            'provence.required' => '省不能为空',
            'city.required' => '市不能为空',
            'area.required' => '县不能为空',
            'detail_address.required' => '详细地址不能为空',
        ]);
//        $user_id = Auth::user()->id;//用户id
        $address = Address::where('id', $request->id)->first();
        $rs = $address->update([
//            'user_id'=>$user_id,
            'name' => $request->name,
            'tel' => $request->tel,
            'province' => $request->provence,
            'city' => $request->city,
            'county' => $request->area,
            'address' => $request->detail_address,
//            'is_default'=>0,
        ]);
        if ($rs == false) {
            $result = [
                "status" => "false",
                "message" => "修改失败"
            ];
        } else {
            $result = [
                "status" => "true",
                "message" => "修改成功"
            ];
        }
        return json_encode($result);

    }

    //保存购物车接口
    public function addCart(Request $request)
    {
        $goodsLists = $request->goodsList;
        $goodsCounts = $request->goodsCount;
        foreach ($goodsLists as $key => $goodsList) {
            Cart::create([
                'user_id' => Auth::user()->id,
                'goods_id' => $goodsList,
                'amount' => $goodsCounts[$key]

            ]);
        }
        $result = [
            "status" => "true",
            "message" => "添加成功"
        ];
        return json_encode($result);
    }

    //获取购物车数据接口
    public function cart()
    {
        $user_id = Auth::user()->id;
        $carts = Cart::where('user_id', $user_id)->get();
        $goods_list = [];
        $total = 0;
        foreach ($carts as $cart) {
            $goods_id = $cart['goods_id'];
            $goods = Menu::where('id', $goods_id)->first();
            $cart['goods_id'] = $goods['id'];
            $cart['goods_name'] = $goods['goods_name'];
            $cart['goods_price'] = $goods['goods_price'];
            $cart['goods_img'] = $goods['goods_img'];
            unset($cart['created_at']);
            unset($cart['updated_at']);
            unset($cart['id']);
            unset($cart['user_id']);
            $total += $cart['amount'] * $goods['goods_price'];
        }
        $goods_list['totalCost'] = $total;

        $goods_list['goods_list'] = $carts;
        return json_encode($goods_list);
    }

    //添加订单接口
    public function addorder(Request $request)
    {

//        DB::table('carts')->truncate();
        DB::beginTransaction();
        try{
            $address_id = $request->address_id;//地址id,根据地址id查询获取地址信息
            $address = Address::where('id', $address_id)->first();
            $user_id = $address->user_id;//用户id
            //获取总共消费的价格
            $carts = Cart::where('user_id', $user_id)->get();
            $total = 0;
            foreach ($carts as $cart) {
                $goods_id = $cart['goods_id'];
                $goods = Menu::where('id', $goods_id)->first();
                $total += $cart['amount'] * $goods['goods_price'];//购物车数量*菜品价格
            }
            //获取商店id
            $cart = Cart::where('user_id', $user_id)->first();
            $good = Menu::where('id', $cart->goods_id)->first();
            $shop_id = $good->shop_id;
            //创建订单表
            $rs1 = Order::create([
                'user_id' => $user_id,
                'shop_id' => $shop_id,
                'sn' => date('YmdHis', time()) . random_int(0000, 9999),
                'province' => $address->province,
                'city' => $address->city,
                'county' => $address->county,
                'address' => $address->address,
                'tel' => $address->tel,
                'name' => $address->name,
                'total' => $total,
                'status' => 0,
                'out_trade_no' => random_int(0000, 9999),
            ]);
            if(!$rs1){
                throw new Exception(1);
            }
            $order = DB::table('orders')->latest()->first();//查询最新的订单

            $order_id = $order->id;
            foreach ($carts as $cart) {
                $goods_id = $cart['goods_id'];
                $goods = Menu::where('id', $goods_id)->first();
                //保存订单信息到订单表中
                $rs2 = Order_good::create([
                    'order_id'=>$order_id,
                    'goods_id'=>$cart['goods_id'],
                    'amount'=>$cart['amount'],
                    'goods_name'=>$goods['goods_name'],
                    'goods_img'=>$goods['goods_img'],
                    'goods_price'=>$goods['goods_price'],
                ]);
                if(!$rs2){
                    throw new Exception(2);
                }
            }
            DB::commit();
            //清空购物车
//            DB::table('carts')->truncate();
            $result = [
                "status" => "true",
                "message" => "添加成功",
                "order_id" => $order->id,
            ];
            $shop = Shop::where('id',$order->shop_id)->first();//获取商家名
//            return json_encode($order->tel);
            $params = array();
            // *** 需用户填写部分 ***
            // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
            $accessKeyId = "LTAIfhptcWxBzIYt";
            $accessKeySecret = "S4FxaWQ4wvY9hoP7jwdID4e9iDA2E0";

            // fixme 必填: 短信接收号码
            $params["PhoneNumbers"] = $order->tel;

            $params["SignName"] = "陈盼";

            $params["TemplateCode"] = "SMS_140722123";

            // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
            $params['TemplateParam'] = Array(
                "name" =>$shop->shop_name
            );
            // fixme 可选: 设置发送短信流水号
            $params['OutId'] = "12345";

            // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
            $params['SmsUpExtendCode'] = "1234567";

            // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
            if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
                $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
            }
//-----------------//
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
            //-----------------//
            //下单成功发送邮件通知店家
            $user = User::where('shop_id',$order->shop_id)->first();
            $_SERVER['email']= $user->email;
            Mail::raw('有新订单产生',function($message){
                $message->subject('食而不语');
                $message->to(["{$_SERVER['email']}"]);
                $message->from('13658010910@163.com','13658010910');

            });
            return json_encode($result);
        }catch (Exception $e){
            DB::rollback();//事务回滚
            echo $e->getMessage();
            echo $e->getCode();
            $order = DB::table('orders')->latest()->first();//查询最新的订单
            $result = [
                "status" => "false",
                "message" => "添加失败",
                "order_id" => $order->id,
            ];
            return json_encode($result);
        }

    }

    //获得指定订单接口
    public function order(Request $request)
    {
        $order_id = $request->id;
        $order = Order::where('id',$order_id)->first();//订单信息
        $shop = Shop::where('id',$order->shop_id)->first();//商店信息

        $orders = [
            'id'=>$order_id,
            'order_code'=>$order->sn,
            'order_birth_time'=>date('Y-m-d H:i',strtotime($order->created_at)),
            'order_status'=>$order->status,
            'shop_id'=>$shop->id,
            'shop_name'=>$shop->shop_name,
            'shop_img'=>$shop->shop_img,
            'order_price'=>$order->total,
            'order_address'=>$order->province.$order->city.$order->county.$order->address,
        ];
        //根据订单里的用户id查询购物车的数据
        $carts = Cart::where('user_id',$order->user_id)->get();

        $goods_list=[];
        foreach ($carts as $cart){
                $menu = Menu::where('id',$cart->goods_id)->first();//查询菜品信息
                $goods_list['goods_id']=$cart['goods_id'];
                $goods_list['goods_name']=$menu['goods_name'];
                $goods_list['goods_img']=$menu['goods_img'];
                $goods_list['amount']=$cart['amount'];
                $goods_list['goods_price']=$menu['goods_price'];
        }
        $orders['goods_list']=$carts;

        return json_encode($orders);
    }
    //获得订单列表接口
    public function orderList()
    {
        $orders = Order::where('user_id',Auth::user()->id)->get();//根据当前用户查询订单
        $lists =[];
        foreach ($orders as $order){
            $shop = Shop::where('id',$order->shop_id)->first();//商店信息
            //根据订单的id ,查询 order_goods 表里的商品信息
            $orders = [
                'id'=>$order->id,
                'order_code'=>$order->sn,
                'order_birth_time'=>date('Y-m-d H:i',strtotime($order->created_at)),
                'order_status'=>$order->status,
                'shop_id'=>$shop->id,
                'shop_name'=>$shop->shop_name,
                'shop_img'=>$shop->shop_img,
                'order_price'=>$order->total,
                'order_address'=>$order->province.$order->city.$order->county.$order->address,
            ];
            $order_goods = Order_good::where('order_id',$order->id)->get();
            foreach($order_goods as $order_good){
                $orders['goods_list']=[
                    'goods_id'=>$order_good->goods_id,
                    'goods_name'=>$order_good->goods_name,
                    'goods_img'=>$order_good->goods_img,
                    'amount'=>$order_good->amount,
                    'goods_price'=>$order_good->goods_price,
                ];
                unset($order_good['order_id']);
                unset($order_good['created_at']);
                unset($order_good['updated_at']);
                unset($order_good['id']);
                unset($order_good['order_id']);
            }
            $orders['goods_list']=$order_goods;
        }

        return json_encode([$orders]);
    }
    //忘记密码接口
    public function forgetPassword(Request $request)
    {
        $tel = $request->tel;
        $user = Yh::where('tel',$tel)->first();
        //根据传入的号码查找数据库是否有该用户,有即可修改密码
        if(empty($user)){
            $result =[
                "status"=>"false",
                "message"=>"手机号码不正确"
            ];
        }else{
            $user->update([
                'password'=>bcrypt($request->password),
            ]);
            $result =[
                "status"=>"true",
                "message"=>"添加成功"
            ];
        }
        return json_encode($result);
    }
    //修改密码接口
    public function changePassword(Request $request)
    {
        $this->validate($request,[
           'oldPassword'=>'required',
           'newPassword'=>'required',
        ],[
            'oldPassword.required'=>'旧密码不能为空',
            'newPassword.required'=>'新密码不能为空',
        ]);
        //根据当前用户id查询旧密码
        $id = Auth::user()->id;
        //数据库查询出来的密码
        $user = Yh::where('id',$id)->first();
//        if(!Hash::check($request->oldPassword,$user->password)){
//            $result = [
//                "status"=>"false",
//                "message"=>"旧密码错误"
//            ];
//            return json_encode($result);
//        };
//        $user = Yh::where('id',$id)->first();
        $rs = $user->update([
            'password'=>bcrypt($request->newPassword),
        ]);
        if($rs==false){
            $result = [
                "status"=>"false",
                "message"=>"修改失败"
            ];
        }else{
            $result = [
                "status"=>"true",
                "message"=>"修改成功"
            ];
        }
        return json_encode($result);
    }


}
