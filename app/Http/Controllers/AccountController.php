<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\ChannelAccount;
use App\Models\User;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AccountController extends BaseController
{
    protected $user;

    public function create()
    {
        $data = request()->post();
        $user = User::where('id', $data['account'])->first();
        if(empty($user)){
            $this->log('['.$data['account'].']???未知用户'.PHP_EOL.json_encode($data, true).PHP_EOL);
            return $this->returnJson(500, '[sign]???未知用户');
        }

        if($user['type'] != 'code'){
            $this->log('['.$data['account'].']用户类型错误;请联系管理员'.PHP_EOL.json_encode($data, true).PHP_EOL);
            return $this->returnJson(500, '[sign]用户类型错误;请联系管理员');
        }

        $find = Channel::where('encode', $data['code'])->first();
        if(!$find){
            return $this->returnJson(500, '通道不存在');
        }

        $this->user = $user;

    //    $checkIp = $this->checkIp();

        $check = $this->checkSign($data);

        if(!$check){
            $isFind = ChannelAccount::where("mchid", $data['mchid'])->first();
            if(!$isFind){
                ChannelAccount::create([
                    'cid' => $find['id'],
                    'type' => 'code',
                    'uid' => $data['account'],
                    'name' => $data['name'],
                    'qrcode' => $data['qrcode'],
                    'mchid' => $data['mchid'],
                    'signkey' => $data['upid'],
                    'appid' => $data['appid'],
                    'secret' => $data['secret'],
                    'public_secret' => $data['public_secret'],
                    'private_secret' => $data['private_secret'],
                    'aptitude' => $data['aptitude'],
                    'product' => $data['product'] ?? 'PAYUN'
                ]);
                return $this->returnJson(200, '推送成功');
            }
            return $this->returnJson(202, '重复账号');
        }else{
            return $this->returnJson(500, '[sign]验证失败');
        }

//        if($checkIp){
//           $this->log('['.$data['account'].']请求IP错误:'.$request->ip());
//
//        }
        dd($data);
    }

    public function check()
    {
        $group = request()->post('input') ?? request()->post('group');
        $one = ChannelAccount::where("qrcode", $group)->first();
        if($one){
             return $this->returnJson(500, '重复组别');
        }else{
             return $this->returnJson(200, '正常推送');
        }
    }

    public function test()
    {
        
        
//        $data = User::where("id", '1003')->first();
//        $key = $data['api_key'];  //apikey
//        $data = [
//            'account'  => $data['id'],  //账户号
//            'order'   => date('YmdHis').rand(11111,99999),  //订单号
//            'time' => time(),  //下单时间
//            'code'  => 102,       //通道编码
//            'name'  => '测试账号',
//            'mchid' => '商户号',
//            'upid'  => '渠道号',
//            'appid' => 'appid',
//            'secret' => 'secret',
//            'public_secret' => '公钥',
//            'private_secret' => '私钥',
//            'aptitude' => 'person',
//        ];
//        $data['sign'] = $this->createSign($data, $key);
//        dd($data);
//        die;
    }


}
