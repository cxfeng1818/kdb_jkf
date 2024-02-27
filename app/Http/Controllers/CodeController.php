<?php

namespace App\Http\Controllers;

use App\Models\ChannelAccount;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;

class CodeController extends BaseController
{
    public function backLink($order)
    {
//        return $this->returnJson(200, '下单成功', [ 'url' =>  'alipayqr://platformapi/startapp?saId=10000007&clientVersion=3.8.0.0918&qrcode=https://'.request()->server('HTTP_HOST').'/aliCode/'.$order['shop_order']]);
        return $this->returnJson(200, '下单成功', [ 'url' => (request()->isSecure() ? 'https://' : 'http://').request()->server('HTTP_HOST').'/aliCode/'.$order['shop_order']]);
    }

    public function status()
    {
        $order = request()->input('order');
        $code = request()->input('code');
        if(empty($order)) exit;
        $order = Order::where('shop_order', $order)->first();
        if(empty($order)) exit;

        if($order['status'] == 'fail'){
            return $this->returnJson(500, '订单失效');
        }

        if($code == '0'){  //获取二维码
            $qrcode = ChannelAccount::where('id', $order['aid'])->value('qrcode');
            $qrcode = json_decode($qrcode, true);
            foreach ($qrcode as $key => $code)
            {
                if($code['amount'] == $order['amount']){
                    return $this->returnJson(101, '获取成功', [
                        'code' => str_replace('https://','taobao://',$code['link'].'&remark='.$order['client'])
                    ]);
                }
            }
//            $cache = Cache::get($order['aid'].'_'.$order['amount']);
//            if($cache){
//                Order::where('id', $order['id'])->update(['status'=>'load', 'updated_at' => time()]);
//                return $this->returnJson(101, '获取成功', [
//                    'code' => $cache
//                ]);
//            }
        }
        if($order['status'] == 'load'){
            return $this->returnJson(202, '等待支付');
        }
        if($order['status'] == 'paid' || $order['status'] == 'success'){
            return $this->returnJson(200, '支付成功');
        }


    }

    public function change()
    {
        $order = request()->input('order');
        $order = Order::where('shop_order', $order)->first();
        if($order['status'] == 'fail'){
             return $this->returnJson(500, '订单失效');
        }
        Order::where('id', $order['id'])->update(['status' => 'load', 'client_ip' => request()->ip()]);
    }

    public function show($order = null)
    {
          $ip = request()->ip();
          $time = time() - 3600;
          $one =  Order::where('shop_order', $order)->first();
          $first = Order::where('client_ip', $ip)->where('shop_order', 'neq', $order)->where('created_at', '>', $time)->whereIn('status', ['paid', 'success'])->first();
          if($first){
              Order::where('id', $one['id'])->update([
                    'client_ip' => '重复订单['.$first['id'].']',
                    'status' => 'fail',
              ]);
              exit('<h1 style="width:100%;text-align:center;font-size:60px;margin-top:100px;">系统繁忙;请稍后重试</h1>');
          }
        
        $time =  (strtotime($one['created_at']) + 600) - time();
         
        if($time <= 0){
              exit('<h1 style="width:100%;text-align:center;font-size:60px;margin-top:100px;">订单已失效;请重新下单</h1>');
        }
        // if(empty($one) || $one['status'] == 'paid' || $one['status'] == 'success'){
        //     exit('<h1 style="width:100%;text-align:center;font-size:60px;margin-top:100px;">请重新下单</h1>');
        // }
        return view('ali.show')
              ->with('order', $order)
              ->with('amount', $one['amount'])
              ->with('decline_amount', $one['decline_amount'])
              ->with('time', $time);
    }

}
