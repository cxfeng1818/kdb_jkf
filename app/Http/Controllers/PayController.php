<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ChannelAccount;
use App\Models\FreezeLog;
use App\Models\Order;
use App\Models\User;
use App\Models\UserChannel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayController extends BaseController
{
    protected $user;
    protected $data;

    public function index(Request $request)
    {
        $data = $request->input();
        Log::debug(json_encode($data, true));

        $this->data = $data;
        $user = User::where('id', $data['account'])->first();
        if (empty($user)) {
            $this->log('[' . $data['account'] . ']???未知用户' . PHP_EOL . json_encode($data, true) . PHP_EOL);
            return $this->returnJson(500, '[system]???未知用户');
        }

        if ($user['type'] != 'shop') {
            $this->log('[' . $data['account'] . ']用户类型错误;请联系管理员' . PHP_EOL . json_encode($data, true) . PHP_EOL);
            return $this->returnJson(500, '[account]用户类型错误;请联系管理员');
        }

        $this->user = $user;

        $checkIp = $this->checkIp();
        if($checkIp){
         $this->log('[' . $data['account'] . ']请求IP错误'.$request->ip() . PHP_EOL . json_encode($data, true) . PHP_EOL);
            return $this->returnJson(500, '请求IP错误;请联系管理员');
        }

//        $checkSign = $this->checkSign($data);
//        if ($checkSign) {
//            $this->log('[' . $data['account'] . '][sign]参数错误' . PHP_EOL . json_encode($data, true) . PHP_EOL);
//            return $this->returnJson(500, '[sign]参数错误');
//        }

        if(empty($data['code'])){
              return $this->returnJson(500, '[code]参数不能为空');
        }

        $channel = Channel::where("encode", $data['code'])
                           ->where('end_time', '>', date('H:i:s'))->where('start_time', '<', date('H:i:s'))->first();
        // $channel = Channel::where("encode", $data['code'])->first();
        if ($channel) {
            if ($channel['status'] == '0') {
                $this->log('[' . $data['account'] . '][code]通道未开启;请联系管理员' . PHP_EOL . json_encode($data, true) . PHP_EOL);
                return $this->returnJson(500, '[code]通道未开启;请联系管理员');
                exit;
            }
            if($channel['order_win'] != '0'){
                  $start = strtotime(date('Ymd 00:00:00'));
                  $end = strtotime(date('Ymd 23:59:59'));
                  $count = Order::where('cid', $channel['id'])->whereBetween('created_at', [$start, $end])->whereIn('status', ['paid', 'success'])->count();
                  if($count >= $channel['order_win']){
                      Channel::where('id', $channel['id'])->update(['status' => 0]);
                      return $this->returnJson(500, '[code]通道未开启;请联系管理员');
                      exit;
                  }
            }

            if($channel['order_lose'] != '0'){
                 $start = strtotime(date('Ymd 00:00:00'));
                  $end = strtotime(date('Ymd 23:59:59'));
                  $count = Order::where('cid', $channel['id'])->whereBetween('created_at', [$start, $end])->where('status', 'load_fail')->where('client_ip', '!=','通道并发限制')->count();
                  if($count >= $channel['order_lose']){
                       Channel::where('id', $channel['id'])->update(['status' => 0]);
                       return $this->returnJson(500, '[code]通道未开启;请联系管理员');
                       exit;
                  }
            }
        } else {
            $this->log('[' . $data['account'] . '][code]通道不存在' . PHP_EOL . json_encode($data, true) . PHP_EOL);
            return $this->returnJson(500, '[code]通道不存在');
            exit;
        }

        if($channel['decline_min'] > $data['amount'] || $channel['decline_max'] < $data['amount'])
        {
             $this->log('[' . $data['account'] . '][code]订单金额错误;请联系客服' . PHP_EOL . json_encode($data, true) . PHP_EOL);
             return $this->returnJson(500, '[code]订单金额错误;请联系客服');
             exit;
        }




        $userChannel = UserChannel::where('uid', $data['account'])->where('cid', $channel['id'])->first();

        if (empty($userChannel)) {
            $this->log('[' . $data['account'] . '][code]用户未分配该通道;请联系管理员' . PHP_EOL . json_encode($data, true) . PHP_EOL);
            return $this->returnJson(500, '[code]用户未分配该通道;请联系管理员');
        }

        if ($userChannel['status'] == '0') {
            $this->log('[' . $data['account'] . '][code]用户通道未开启;请联系管理员' . PHP_EOL . json_encode($data, true) . PHP_EOL);
            return $this->returnJson(500, '[code]用户通道未开启;请联系管理员');
        }

        $account = [];
        $account['uid'] = 0;
        $account['id'] = 0;
        $account['name'] = 0;
        $account['aptitude'] = 0;
        $shopAmount = 0;
        $userAmount = 0;
        $codeAmount = 0;

        $declineAmount = 0; //下跌金额
    //    if($data['account'] != '1010'){
      //      if ($channel['decline_min'] != '0' || $channel['decline_max'] != '0') {
                $declineAmount = rand((int)(0 * 100), (int)(0.1 * 100));
                $declineAmount = sprintf("%.2f", $declineAmount / 100);
                $data['amount'] = $data['amount'] - $declineAmount;
       //     }
     //   }
        if($channel['mode'] == 'poll'){
             $account = $this->polling($channel);
            //  dd($account);
        }else if($channel['mode'] == 'list'){
            //todo 待完成
             $account = $this->respective($channel);
        }else if($channel['mode'] == 'firm'){
             $account = $this->firm($channel);
        }

        if($account === false){
            return $this->returnJson(500, '无可用账号;请联系管理员');
        }

        //计算费率
        if($account['type'] == 'code'){
              $codeChannel = UserChannel::where('uid', $account['uid'])->where('cid', $account['cid'])->first();
              if(empty($codeChannel)){
                  $codeChannel['rate'] = 0;
              }
              $codeAmount = $data['amount'] * ($codeChannel['rate'] / 100);
              $userAmount = $data['amount'] * ($userChannel['rate'] / 100);
              $shopAmount = $data['amount'] - $codeAmount - $userAmount;
        }
        else
        {
            $userAmount = $data['amount'] * ($userChannel['rate'] / 100);
            $shopAmount = $data['amount'] - $userAmount;
            $codeAmount = 0;
        }

        // dump($codeAmount);
        // dump($userAmount);
        // dump($shopAmount);
        // die;

        $create = Order::create([
            'user_type' => $this->user['attr'],
            'uid' => (int)$data['account'],
            'sys_order' => 'KZ'.date('ymdHis') . rand(0000, 9999).rand(1,9),
            'shop_order' => $data['order'],
            'amount' => $data['amount'],
            'shop_amount' => $shopAmount,
            'cost_amount' => $userAmount,
            'code_amount' => $codeAmount,
            'decline_amount' => $declineAmount,
            'source_url' => $request->header('HTTP_REFERER') ?? '',
            'notify_url' => $data['notify_url'],
            'callback_url' => $data['callback_url'],
            'cid' => $channel['id'],
            'encode' => $channel['encode'],
            'codename' => $channel['name'],
            'client' => '',
            'client_ip' => '',
            'organize' => $account['qrcode'],
            'ms_id' => $account['uid'],
            'aid' => $account['id'],
            'aname' => $account['name'],
            'aptitude' => $account['aptitude'],
            'status' => 'none',
        ]);

        // $freezeAmount = User::where('id', $account['uid'])->value('freeze_amount');

        // FreezeLog::create([
        //     'uid' => $account['uid'],
        //     'before' => $freezeAmount,
        //     'amount' => $this->data['amount'],
        //     'after' => $freezeAmount - $this->data['amount'],
        //     'oid' => $create->id,
        //     'type' => 'del',
        //     'created_at' => time(),
        // ]);

        // User::where('id', $account['uid'])->decrement('freeze_amount', $this->data['amount']);

        $controller = app("App\Http\Controllers\\" . $channel['code'] . 'Controller');

        return $controller->backLink($create);
    }

    public function check(Request $request)
    {
          $data = $request->input();
          $user = User::where('id', $data['account'])->first();
            if (empty($user)) {
                $this->log('[' . $data['account'] . ']???未知用户' . PHP_EOL . json_encode($data, true) . PHP_EOL);
                return $this->returnJson(500, '[sign]???未知用户');
            }

            if ($user['type'] != 'shop') {
                $this->log('[' . $data['account'] . ']用户类型错误;请联系管理员' . PHP_EOL . json_encode($data, true) . PHP_EOL);
                return $this->returnJson(500, '[sign]用户类型错误;请联系管理员');
            }

            $this->user = $user;

            $checkIp = $this->checkIp();
            if($checkIp){
              $this->log('['.$data['account'].']请求IP错误:'.$request->ip());
              return $this->returnJson(500, '请求IP错误;请联系管理员');
            }

            $checkSign = $this->checkSign($data);
            if ($checkSign) {
                $this->log('[' . $data['account'] . '][sign]参数错误' . PHP_EOL . json_encode($data, true) . PHP_EOL);
                return $this->returnJson(500, '[sign]参数错误');
            }
            $order = Order::where('shop_order', $data['order'])->where('uid', $data['account'])->first();
            if(empty($order)){
                $this->log('[' . $data['account'] . '][order]订单错误' . PHP_EOL . json_encode($data, true) . PHP_EOL);
                return $this->returnJson(500, '[order]未找到对应订单');
            }
            return $this->returnJson(200, '查询成功', [
                'account' => $data['account'],
                'order'   => $order['shop_order'],
                'amount'  => $order['amount'] + $order['decline_amount'],
                'time'    => $order['created_at'],
                'transaction_id' => $order['sys_order'],
                'status'  => $order['status']
           ]);
    }

    public function firm($channel, $filterArr = [])
    {
        $oneOrder = Order::where('uid', $this->user->id)
            ->where('cid', $channel['id'])
            ->orderBy('id', 'desc')
            ->first();
        if (empty($oneOrder)) {
            $account = ChannelAccount::where('cid', $channel['id'])
                ->where('status', '1')
                ->where('end_time', '>', date('H:i:s'))
                ->where('start_time', '<', date('H:i:s'))->orderBy('id', 'asc')->first();
            if (empty($account)) {
                return false;
            }
            $account = $account->toArray();
        } else {
            if (count($filterArr) >= 1) {
                $accountArr = ChannelAccount::where('cid', $channel['id'])
                    ->where('status', '1')
                    ->whereNotIn('id', $filterArr)
                    ->where('aptitude', '!=', $oneOrder['aptitude'])
                    ->where('end_time', '>', date('H:i:s'))
                    ->where('start_time', '<', date('H:i:s'))->orderByRaw("RAND()")->get()->toArray();
                if (empty($accountArr)) {
                    return false;
                }
            } else {
                $accountArr = ChannelAccount::where('cid', $channel['id'])
                    ->where('status', '1')
                    ->where('aptitude', '!=', $oneOrder['aptitude'])
                    ->where('end_time', '>', date('H:i:s'))
                    ->where('start_time', '<', date('H:i:s'))->orderByRaw("RAND()")->get()->toArray();
                if (empty($accountArr)) {
                    return false;
                }
            }

            $key = array_push($accountArr);
            $account = $accountArr[$key - 1];
        }

        if ($account['day_limit'] != 0) {
            $start = strtotime(date('Ymd 00:00:00'));
            $end = strtotime(date('Ymd 23:59:59'));
            $count = Order::where('aid', $account['id'])->whereBetween('created_at', [$start, $end])->count();
            if (($count == $account['day_limit'])) {
                ChannelAccount::where('id', $account['id'])->update([
                    'status' => '0'
                ]);
                array_push($filterArr, $account['id']);
                return $this->firm($channel, $filterArr);
            }
        }
        if ($channel['interval_time'] != 0) {
            $time = time() - $channel['interval_time'];
            $isExist = Order::where('uid', $this->user->id)
                ->where('aid', $account['id'])
                ->where('created_at', '>', $time)->exists();
            if ($isExist) {
                array_push($filterArr, $account['id']);
                return $this->firm($channel, $filterArr);
            }
        }
        return $account;
    }

//    private function respective($channel)
//    {
//        $oneOrder = Order::where('uid', $this->user->id)
//                         ->where('cid', $channel['id'])
//                         ->latest()
//                         ->first();
//        if(empty($oneOrder)){
//            $account = ChannelAccount::where('cid', $channel['id'])
//                                     ->where('status', '1')
//                                     ->where('end_time', '>', date('H:i:s'))
//                                     ->where('start_time', '<', date('H:i:s'))->orderBy('id', 'asc')->first();
//            if(empty($account)){
//                return false;
//            }
//            $account = $account->toArray();
//        }else{
//            $msId = ChannelAccount::where('uid', '>', $oneOrder['ms_id'])->where('cid', $channel['id'])->orderBy('id', 'asc')->value('uid');
//            if($msId){
//                $lastAccount = Order::where('ms_id', $msId)->where("cid", $channel['id'])->orderBy('id', 'asc')->first();
//                if(empty($lastAccount)){
//                    $account = ChannelAccount::where('uid', $msId)
//                                            ->where('cid', $channel['id'])
//                                             ->where('status', '1')
//                                             ->where('end_time', '>', date('H:i:s'))
//                                             ->where('start_time', '<', date('H:i:s'))
//                                             ->orderBy('id', 'asc')->first();
//                    if($account){
//                        $account = $account->toArray();
//                    }else{
//                        return $this->respective($channel);
//                    }
//                }else{
//                    dd(1);
//                }
//            }else{
////                ChannelAccount::where('')
//            }
//        }
//        return $account;
//    }

    private function polling123($channel, $filterArr = [])
    {
        $oneOrder = Order::where('uid', $this->user->id)
                        ->where('cid', $channel['id'])
                        ->orderBy('id', 'desc')
                        ->first();
        if (empty($oneOrder)) {
            $account = ChannelAccount::where('cid', $channel['id'])
                ->where('status', '1')
                ->where('end_time', '>', date('H:i:s'))
                ->where('start_time', '<', date('H:i:s'))->orderBy('id', 'asc')->first();
            if (empty($account)) {
                return false;
            }
            $account = $account->toArray();
        } else {
            if (count($filterArr) >= 1) {
                $account = ChannelAccount::where('cid', $channel['id'])
                    ->whereNotIn('id', $filterArr)
                    ->where('status', '1')
                    ->where('id', '>', $oneOrder['aid'])
                    ->where('end_time', '>', date('H:i:s'))
                    ->where('start_time', '<', date('H:i:s'))
                    ->orderBy('id', 'asc')->first();
                if ($account) {
                    $account = $account->toArray();
                } else {
                    $account = ChannelAccount::where('cid', $channel['id'])
                        ->whereNotIn('id', $filterArr)
                        ->where('status', '1')
                        ->where('end_time', '>', date('H:i:s'))
                        ->where('start_time', '<', date('H:i:s'))->orderBy('id', 'asc')->first();
                    if ($account) {
                        $account = $account->toArray();
                    } else {
                        return false;
                    }
                }
            } else {
                $account = ChannelAccount::where('cid', $channel['id'])
                                         ->where('status', '1')
                                         ->where('id', '>', $oneOrder['aid'])
                                         ->where('end_time', '>', date('H:i:s'))
                                         ->where('start_time', '<', date('H:i:s'))
                                         ->orderBy('id', 'asc')->first();
                if ($account) {
                    $account = $account->toArray();
                } else {
                    $account = ChannelAccount::where('cid', $channel['id'])
                        ->where('status', '1')
                        ->where('end_time', '>', date('H:i:s'))
                        ->where('start_time', '<', date('H:i:s'))->orderBy('id', 'asc')->first();
                    if ($account) {
                        $account = $account->toArray();
                    } else {
                        return false;
                    }
                }
            }
        }

    //   $excat = true;
    //   $amountArr = json_decode($account['qrcode'], true);
    //   foreach ($amountArr as $item){
    //       if($item['amount'] == $this->data['amount']){
    //             $excat = false;
    //       }
    //   }

    //   $orderTime = time() - 300;
    //   $first = Order::where('aid', $account['id'])
    //                       ->whereIn('status', ['load', 'none'])
    //                       ->where("amount", $this->data['amount'])
    //                       ->where('created_at', '>=', $orderTime)
    //                       ->first();
    //     if($first){
    //         array_push($filterArr, $account['id']);
    //         return $this->polling($channel, $filterArr);
    //     }

    //   if($excat){
          //  array_push($filterArr, $account['id']);
         //   return $this->polling($channel, $filterArr);
    //   }


        if($account['day_limit'] != 0) {
            $start = strtotime(date('Ymd 00:00:00'));
            $end = strtotime(date('Ymd 23:59:59'));
            $count = Order::where('aid', $account['id'])->whereBetween('created_at', [$start, $end])->count();
            if (($count == $account['day_limit'])) {
                ChannelAccount::where('id', $account['id'])->update([
                    'status' => '0',
                    'msg' => '账号已达到最大笔数限制'
                ]);
                return $this->polling($channel, $filterArr);
            }
        }
        if ($channel['interval_time'] != 0) {
            $time = time() - $channel['interval_time'];
            $isExist = Order::where('uid', $this->user->id)
                ->where('aid', $account['id'])
                ->where('created_at', '>', $time)->exists();
            if ($isExist) {
                array_push($filterArr, $account['id']);
                return $this->polling($channel, $filterArr);
            }
        }

        // if($account['uid'] != '0'){
        //     $freezeAmount = User::where('id', $account['uid'])->value('freeze_amount') ?? "0.00";
        //     if($this->data['amount'] > $freezeAmount){
        //         $array = ChannelAccount::where('uid', $account['uid'])->pluck('id')->toArray();
        //         $filterArr = array_merge($filterArr, $array);
        //         return $this->polling($channel, $filterArr);
        //     }
        // }
        return $account;
    }

    private function polling($channel, $filterArr = [])
    {
        $group = ChannelAccount::distinct()->where('cid', $channel['id'])->where('status', '1')->pluck('qrcode')->toArray();
        $oneOrder = Order::where('cid', $channel['id'])
                          ->orderBy('id', 'desc')
                          ->first();
        if(empty($oneOrder)){
            $account = ChannelAccount::where('cid', $channel['id'])
                                    ->where('status', '1')
                                    ->where('end_time', '>', date('H:i:s'))
                                    ->where('start_time', '<', date('H:i:s'))->orderBy('id', 'asc')->first();
            if (empty($account)) {
                return false;
            }
        }else{
            $qrcode = ChannelAccount::where('id', $oneOrder['aid'])->value('qrcode');
            $groupKey = array_search($qrcode, $group);
            $groupKey = $groupKey + 1;
            if($groupKey >= count($group) ){
                $groupKey = 0;
            }
            $findOld = Order::where('organize', $group[$groupKey])
                            ->where('cid', $channel['id'])
                            ->orderBy('id', 'desc')
                            ->value('aid');

            if(empty($findOld)){
                if (count($filterArr) >= 1) {
                    $account = ChannelAccount::where('cid', $channel['id'])
                                            ->whereNotIn('id', $filterArr)
                                            ->where('qrcode', $group[$groupKey])
                                            ->where('status', '1')
                                            ->where('end_time', '>', date('H:i:s'))
                                            ->where('start_time', '<', date('H:i:s'))->orderBy('id', 'asc')->first();
                }else{
                    $account = ChannelAccount::where('cid', $channel['id'])
                                            ->where('qrcode', $group[$groupKey])
                                            ->where('status', '1')
                                            ->where('end_time', '>', date('H:i:s'))
                                            ->where('start_time', '<', date('H:i:s'))->orderBy('id', 'asc')->first();
                }
            }else{
                if(count($filterArr) >= 1){
                                $accArr = ChannelAccount::where('cid', $channel['id'])
                                            ->where('qrcode', $group[$groupKey])
                                            ->whereNotIn('id', $filterArr)
                                            ->where('status', '1')
                                            ->where('end_time', '>', date('H:i:s'))
                                            ->where('start_time', '<', date('H:i:s'))
                                            ->orderBy('id', 'asc')
                                            ->pluck('id')
                                            ->toArray();
                            if(empty($accArr)){
                                    $groupKey = $groupKey + 1;
                                    if($groupKey >= count($group) ){
                                        $groupKey = 0;
                                    }
                                $accArr = ChannelAccount::where('cid', $channel['id'])
                                                    ->where('qrcode', $group[$groupKey])
                                                    ->where('status', '1')
                                                    ->where('end_time', '>', date('H:i:s'))
                                                    ->where('start_time', '<', date('H:i:s'))
                                                    ->orderBy('id', 'asc')
                                                    ->pluck('id')
                                                    ->toArray();
                            }
                            if(empty($accArr)){
                                    return false;
                            }
                            $accKey = array_search($findOld, $accArr);
                            $accKey = $accKey + 1;
                            if($accKey >= count($accArr) ){
                                $accKey = 0;
                            }
                            $account = ChannelAccount::where('id', $accArr[$accKey])->first();
                            if(empty($account)){
                                 return false;
                            }
                }else{
                    $accArr = ChannelAccount::where('cid', $channel['id'])
                                            ->where('qrcode', $group[$groupKey])
                                            ->where('status', '1')
                                            ->where('end_time', '>', date('H:i:s'))
                                            ->where('start_time', '<', date('H:i:s'))
                                            ->orderBy('id', 'asc')
                                            ->pluck('id')
                                            ->toArray();
                    if(empty($accArr)){
                        $groupKey = $groupKey + 1;
                        if($groupKey >= count($group) ){
                            $groupKey = 0;
                        }
                        $accArr = ChannelAccount::where('cid', $channel['id'])
                                            ->where('qrcode', $group[$groupKey])
                                            ->where('status', '1')
                                            ->where('end_time', '>', date('H:i:s'))
                                            ->where('start_time', '<', date('H:i:s'))
                                            ->orderBy('id', 'asc')
                                            ->pluck('id')
                                            ->toArray();
                    }
                    if(empty($accArr)){
                            return false;
                    }
                    $accKey = array_search($findOld, $accArr);
                    $accKey = $accKey + 1;
                    if($accKey >= count($accArr) ){
                        $accKey = 0;
                    }
                    $account = ChannelAccount::where('id', $accArr[$accKey])->first();
                }
            }

        }

        if($channel['order_num'] != '0'){
            $start = strtotime(date('Ymd 00:00:00'));
            $end = strtotime(date('Ymd 23:59:59'));
            $count = Order::where('aid', $account['id'])->whereIn('status', ['load','load_fail','paid','success'])->where('client_ip', '!=', '通道并发限制')->whereBetween('created_at', [$start, $end])->count();
            if (($count >= $channel['order_num'])) {
                    ChannelAccount::where('id', $account['id'])->update([
                        'status' => '0'
                    ]);
                array_push($filterArr, $account['id']);
                return $this->polling($channel, $filterArr);
            }
        }

        if ($channel['interval_time'] != 0) {
            $time = time() - $channel['interval_time'];
            $isExist = Order::where('uid', $this->user->id)
                            ->where('aid', $account['id'])
                            ->where('created_at', '>', $time)->exists();
            if ($isExist) {
                array_push($filterArr, $account['id']);
                return $this->polling($channel, $filterArr);
            }
        }
        if(empty($account)){
            return false;
        }
        $account = $account->toArray();

        // Log::record('分配账号'.json_encode($account, true), 'debug');

        if($account['day_limit'] != 0) {
            $start = strtotime(date('Ymd 00:00:00'));
            $end = strtotime(date('Ymd 23:59:59'));
            $count = Order::where('aid', $account['id'])->whereIn('status', ['load','paid','success'])->whereBetween('created_at', [$start, $end])->count();
            if (($count == $account['day_limit'])) {
                ChannelAccount::where('id', $account['id'])->update([
                    'status' => '0',
                    'msg' => '账号已达到最大笔数限制'
                ]);
                return $this->polling($channel, $filterArr);
            }
        }
        return $account;
    }

    public function testNotify()
    {
        $data = request()->input();
        // Log::record(json_encode($data, true), 'debug');
        exit('OK');
    }

    public function test()
    {
         $user = User::where('id', '1023')->first();
         $this->user = $user;
         $channel = Channel::where('encode','101')->first();
        if (request()->isMethod('POST')) {
                $input = request()->input();
                $check = $this->checkSign($input);
                if ($check) {
                    return $this->returnJson(500, '[sign]参数错误');
                }
                $channelAccount = ChannelAccount::where('id', $input['channel_account'])->first();

                $channel = Channel::where('id', $channelAccount['cid'])->first();
                $create = Order::create([
                    'user_type' => 'test',
                    'uid' => (int)$input['account'],
                    'sys_order' => 'KZ'.date('ymdHis') . rand(0000, 9999).rand(1,9),
                    'shop_order' => $input['order'],
                    'amount' => $input['amount'],
                    'shop_amount' => 0,
                    'cost_amount' => 0,
                    'code_amount' => 0,
                    'decline_amount' => 0,
                    'source_url' => '',
                    'notify_url' => $input['notify_url'],
                    'callback_url' => '',
                    'cid' => $channel['id'],
                    'encode' => $channel['encode'],
                    'codename' => $channel['name'],
                    'client' => '',
                    'client_ip' => '',
                    'ms_id' => $channelAccount['uid'],
                    'aid' => $channelAccount['id'],
                    'aname' => $channelAccount['name'],
                    'aptitude' => $channelAccount['aptitude'],
                    'status' => 'none',
                ]);
                $controller = app("App\Http\Controllers\\" . $channel['code'] . 'Controller');
                return $controller->backLink($create);
        }
        $data = User::where("id", '1010')->first();

        $key = $data['api_key'];  //apikey
        $data = [
            'account' => $data['id'],  //账户号
            'order' => date('YmdHis') . rand(11111, 99999),  //订单号
            'time' => time(),  //下单时间
            'code' => 101,       //通道编码
            'notify_url' => 'https://kkk.luozhansicun.cn/testNotify', //异步回调
            'callback_url' => 'https://kkk.luozhansicun.cn/testNotify',  //同步回调//
            'amount' => '0.01',                  //金额
        ];
        $data['sign'] = $this->createSign($data, $key);
        $result = $this->curlPost('http://' . request()->server('HTTP_HOST') . '/submitOrder', $data);
        $result = json_decode($result, true);
        if($result['code'] == 500){
            exit('<h1 style="font-size:40px">下单失败</h1>');
        }
        header("location:". $result['data']['url']);
    }

    function curlPost($url, $post_data = array(), $timeout = 5, $header = "", $data_type = "")
    {
        $header = empty($header) ? '' : $header;
        //支持json数据数据提交
        if ($data_type == 'json') {
            $post_string = json_encode($post_data);
        } elseif ($data_type == 'array') {
            $post_string = $post_data;
        } elseif (is_array($post_data)) {
            $post_string = http_build_query($post_data, '', '&');
        }

        $ch = curl_init();    // 启动一个CURL会话
        curl_setopt($ch, CURLOPT_URL, $url);     // 要访问的地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // 对认证证书来源的检查   // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
//        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        //curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($ch, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);     // Post提交的数据包
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);     // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     // 获取的信息以文件流的形式返回
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //模拟的header头
        $result = curl_exec($ch);
        // 打印请求的header信息
        //$a = curl_getinfo($ch);
        curl_close($ch);
        return $result;
    }


}
