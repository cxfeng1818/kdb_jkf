<?php

namespace App\Http\Controllers;

use App\Lib\BsPaySdk\core\BsPayClient;
use App\Lib\BsPaySdk\request\V2MerchantComplaintListInfoQueryRequest;
use App\Lib\BsPaySdk\request\V2MerchantComplaintReplyRequest;
use App\Lib\BsPaySdk\request\V2MerchantComplaintUpdateRefundprogressRequest;
use App\Lib\BsPaySdk\request\V2TradePaymentScanpayRefundRequest;
use App\Models\ChannelAccount;
use App\Models\Complaint;
use App\Models\ComplaintList;
use App\Models\Order;
use App\Lib\BsPaySdk\request\V2MerchantComplaintCompleteRequest;
use Illuminate\Support\Facades\Log;

class ComplaintController extends Controller
{

    public function run()
    {
        require app_path().'/Lib/BsPaySdk/loader.php';
        $comp = Complaint::get();
        foreach ($comp as $row)
        {
            // dump($row['id']);
            $request = new V2MerchantComplaintListInfoQueryRequest();
            $request->setReqSeqId(date("YmdHis").mt_rand());
            $request->setReqDate(date("Ymd"));
            $request->setBeginDate(date('Y-m-d'));
            $request->setEndDate(date('Y-m-d'));
            $extendInfoMap = $this->getRunInfos();
            $request->setExtendInfo($extendInfoMap);
            $client = new BsPayClient();
            $config = [];
            $config['huifu'] = $row['huifu_id'];
            
            $product = ChannelAccount::where('signkey', $config['huifu'])->value('product');
           if($product){
                $config['product'] = $product;
            }else{
                $config['product'] = 'PAYUN';
            }
            //dd($config);
            $config['public'] = $row['public'];
            $config['private'] = $row['private'];
            $result = $client->postRequest($request, $config);
            // dd($result);
            $data = $result->getRspDatas()['data'];
            $list = json_decode($data['complaint_list'], true);
            if(empty($list)){
                continue;
            }
            foreach ($list as $item) {
                $find = ComplaintList::where('transaction_id', $item['transaction_id'])->first();
                if($find){
                    // if($find['complaint_state'] != $item['complaint_state']){
                        ComplaintList::where('id', $find['id'])->update([
                            'amount' => $item['amount'],
                            'apply_refund_amount' => $item['apply_refund_amount'],
                            'complaint_detail' => $item['complaint_detail'],
                            'complaint_full_refunded' => $item['complaint_full_refunded'],
                            'complaint_id' => $item['complaint_id'],
                            'complaint_media_list' => $item['complaint_media_list'],
                            'complaint_state' => $item['complaint_state'],
                            'complaint_time' => $item['complaint_time'],
                            'complainted_mchid' => $item['complainted_mchid'],
                            'first_agent_id' => $item['first_agent_id'],
                            'first_agent_name' => $item['first_agent_name'],
                            'huifu_id' => $item['huifu_id'],
                            'incoming_user_response' => $item['incoming_user_response'],
                            'mchid' => $item['mchid'],
                            'out_trade_no' => $item['out_trade_no'],
                            'payer_phone' => $item['payer_phone'],
                            'problem_description' => $item['problem_description'],
                            'problem_type' => $item['problem_type'],
                            'reg_name' => $item['reg_name'] ?? '',
                            'transaction_id' => $item['transaction_id'],
                            'upper_huifu_id' => $item['upper_huifu_id'],
                            'user_complaint_times' => $item['user_complaint_times'],
                            'user_tag_list' => $item['user_tag_list'],
                            'refund' => $item['complaint_full_refunded'],
                        ]);
                    // }
                }else{
                    if($item['problem_description'] == '交易被骗'){
                        $qrcode = ChannelAccount::where('mchid', $item['huifu_id'])->value('qrcode');
                        ChannelAccount::where('qrcode', $qrcode)->update(['status' => '0', 'msg' => '一类投诉']);
                    }else{
                        ChannelAccount::where('mchid', $item['huifu_id'])->update(['status' => '0']);
                    }
                    ComplaintList::create([
                        'pid' => $row['id'],
                        'amount' => $item['amount'],
                        'apply_refund_amount' => $item['apply_refund_amount'],
                        'complaint_detail' => $item['complaint_detail'],
                        'complaint_full_refunded' => $item['complaint_full_refunded'],
                        'complaint_id' => $item['complaint_id'],
                        'complaint_media_list' => $item['complaint_media_list'],
                        'complaint_state' => $item['complaint_state'],
                        'complaint_time' => $item['complaint_time'],
                        'complainted_mchid' => $item['complainted_mchid'],
                        'first_agent_id' => $item['first_agent_id'],
                        'first_agent_name' => $item['first_agent_name'],
                        'huifu_id' => $item['huifu_id'],
                        'incoming_user_response' => $item['incoming_user_response'],
                        'mchid' => $item['mchid'],
                        'out_trade_no' => $item['out_trade_no'],
                        'payer_phone' => $item['payer_phone'],
                        'problem_description' => $item['problem_description'],
                        'problem_type' => $item['problem_type'],
                        'reg_name' => $item['reg_name'] ?? '',
                        'transaction_id' => $item['transaction_id'],
                        'upper_huifu_id' => $item['upper_huifu_id'],
                        'user_complaint_times' => $item['user_complaint_times'],
                        'user_tag_list' => $item['user_tag_list'],
                        'reply' => 0,
                        'approve' => 0,
                        'refund' => 0,
                        'created_at' => time()
                    ]);
                }
            }
        }
        $http = request()->isSecure() ? 'https://' : 'http://';
        $this->requestGet($http.request()->host().'/complaintReply');
    }

    public function reply()
    {
        sleep(2);
        require app_path().'/Lib/BsPaySdk/loader.php';
        $list = ComplaintList::where('reply', '0')->get();
        if(empty($list)){
            exit;
        }
        foreach ($list as $row)
        {
            $config  = [];
            $comp = Complaint::where('id', $row['pid'])->first();
            $config['huifu'] = $comp['huifu_id'];
            $config['public'] = $comp['public'];
            $config['private'] = $comp['private'];
            $request = new V2MerchantComplaintReplyRequest();
            $request->setReqSeqId(date("YmdHis").mt_rand());
            $request->setReqDate(date("Ymd"));
            $request->setComplaintId($row['complaint_id']);
            $request->setComplaintedMchid($row['complainted_mchid']);
            $request->setMchId($row['mchid']);
            $request->setResponseContent($comp['reply']);
            $client = new BsPayClient();
            $result = $client->postRequest($request, $config);
            $result = $result->getRspDatas()['data'];
            if($result['resp_code'] == '00000000'){
                ComplaintList::where('id', $row['id'])->update(['reply' => '1']);
            }
        }
    }

    public function approve()
    {
        require app_path().'/Lib/BsPaySdk/loader.php';
        $list = ComplaintList::where('approve', '0')->get();
        foreach ($list as $item)
        {
            $request = new V2MerchantComplaintUpdateRefundprogressRequest();
            $request->setReqSeqId(date("YmdHis").mt_rand());
            $request->setReqDate(date("Ymd"));
            $request->setComplaintId($item['complaint_id']);
            $request->setAction("APPROVE");
            $request->setMchId($item['mchid']);
            $extendInfoMap = $this->getApproveInfos();
            $request->setExtendInfo($extendInfoMap);
            $client = new BsPayClient();
            $config  = [];
            $comp = Complaint::where('id', $item['pid'])->first();
            $config['huifu'] = $comp['huifu_id'];
            $config['public'] = $comp['public'];
            $config['private'] = $comp['private'];
            $result = $client->postRequest($request, $config);
            $result = $result->getRspDatas()['data'];
            ComplaintList::where('id', $item['id'])->update(['approve' => '1']);
            //todo 同意退款
            // $http =  request()->isSecure() ? 'https://' : 'http://';
            // $this->sendAsyncRequest($http.request()->host().'/complaintRefund?row='.$item['id']);
        }
    }

    public function refund()
    {
        set_time_limit(0);
        sleep(3);
        $ids = request()->input('ids');
        if(empty($ids)){
            exit;
        }
        require app_path().'/Lib/BsPaySdk/loader.php';
        $one = ComplaintList::where('id', $ids)->where('refund', '1')->first();
        if(empty($one)){
            exit;
        }
        $comp = Complaint::where('id', $one['pid'])->first();
        if($one['refund'] == '1'){
            exit;
        }
        $order = Order::where('out_trans_id', $one['transaction_id'])->first();
        $account = ChannelAccount::where('id', $order['aid'])->first();
        $config  = [];
        $config['huifu'] = $comp['huifu_id'];
        $config['public'] = $comp['public'];
        $config['private'] = $comp['private'];
        $request = new V2TradePaymentScanpayRefundRequest();
        $request->setReqDate(date("Ymd"));
        $request->setReqSeqId(date("YmdHis").mt_rand());
        $request->setHuifuId($account['mchid']);
        $request->setOrdAmt($order['amount']);
        $request->setOrgReqDate(date('Ymd'));
        $extendInfoMap = $this->refundInfos($one['out_trade_no']);
        $request->setExtendInfo($extendInfoMap);
        $client = new BsPayClient();
        $result = $client->postRequest($request, $config);
      
        ComplaintList::where('id', $ids)->update(['refund' => '1']);
        if (!$result || $result->isError()) {  //失败处理
            var_dump($result -> getErrorInfo());
        } else {    //成功处理
            // dump($result);
            Log::debug('退款结果'.json_encode($result->getRspDatas()['data'], true));
            print_r($result->getRspDatas()['data']);
            $http =  request()->isSecure() ? 'https://' : 'http://';
            $result = $this->requestGet($http.request()->host().'/complaintDone?ids='.$id);
        }
    }

    public function done()
    {
        set_time_limit(0);
        sleep(3);
        $ids = request()->input('ids');
        $one = ComplaintList::where('id', $ids)->first();

        require app_path().'/Lib/BsPaySdk/loader.php';
        $request = new V2MerchantComplaintCompleteRequest();
        $request->setReqSeqId(date("YmdHis").mt_rand());
        $request->setReqDate(date("Ymd"));
        $request->setComplaintId($one['complaint_id']);
        $request->setComplaintedMchid($one['complainted_mchid']);
        $request->setMchId($one['mchid']);

        $comp = Complaint::where('id', $one['pid'])->first();
        $config  = [];
        $config['huifu'] = $comp['huifu_id'];
        $config['public'] = $comp['public'];
        $config['private'] = $comp['private'];
        $client = new BsPayClient();
        $result = $client->postRequest($request, $config);
        
        if (!$result || $result->isError()) {  //失败处理
            var_dump($result -> getErrorInfo());
        } else {    //成功处理
            // print_r($result);
            print_r($result->getRspDatas()['data']);
            Log::debug('处理结果'.json_encode($result->getRspDatas()['data'], true));
        }
    }

    public function refundInfos($data)
    {
        $extendInfoMap = array();
        // 原交易全局流水号
        $extendInfoMap["org_party_order_id"] = $data;
        return $extendInfoMap;
    }

    public function getApproveInfos() {
        // 设置非必填字段
        $extendInfoMap = array();
        // 预计发起退款时间
        $extendInfoMap["launch_refund_day"] = "0";
        // 文件列表
        return $extendInfoMap;
    }

    private function getRunInfos() {
        // 设置非必填字段
        $extendInfoMap = array();
        // 分页开始位置
        $extendInfoMap["offset"]= "0";
        // 分页大小
        $extendInfoMap["limit"]= "100";
        // 被诉的汇付商户ID
        // $extendInfoMap["huifu_id"]= "";
        // 被诉的商户名称
        // $extendInfoMap["reg_name"]= "";
        // 微信订单号
        // $extendInfoMap["transaction_id"]= "";
        // 微信投诉单号
        // $extendInfoMap["complaint_id"]= "";
        // 投诉状态
        // $extendInfoMap["complaint_state"]= "";
        // 用户投诉次数
        // $extendInfoMap["user_complaint_times"]= "";
        // 是否有待回复的用户留言
        // $extendInfoMap["incoming_user_response"]= "1";
        return $extendInfoMap;
    }

    /**
     * 异步发送一个请求
     * @param string $url    请求的链接
     * @param mixed  $params 请求的参数
     * @param string $method 请求的方法
     * @return boolean TRUE
     */
   public function requestGet($url,$type=1){
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_TIMEOUT,5000);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($curl,CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl,CURLOPT_URL,$url);
        if($type == 1){
        	curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        }
        $res = curl_exec($curl);
        if($res){
        	curl_close($curl);
        	return $res;
        }else {
        	$error = curl_errno($curl);
        	curl_close($curl);
        	return $error;
        }
    }

}
