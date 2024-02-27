<?php

namespace App\Admin\Actions;

use App\Admin\Forms\UserEditForm;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Dcat\Admin\Widgets\Modal;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Lib\BsPaySdk\core\BsPayClient;
use App\Lib\BsPaySdk\request\V2MerchantComplaintListInfoQueryRequest;
use App\Lib\BsPaySdk\request\V2MerchantComplaintReplyRequest;
use App\Lib\BsPaySdk\request\V2MerchantComplaintUpdateRefundprogressRequest;
use App\Lib\BsPaySdk\request\V2TradePaymentScanpayRefundRequest;
use App\Models\ChannelAccount;
use App\Models\Complaint;
use App\Models\ComplaintList;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Lib\BsPaySdk\request\V2MerchantComplaintCompleteRequest;
use Illuminate\Support\Facades\Log;

class RefundAction extends RowAction
{
    /**
     * @return string
     */
    protected $title = '退款';

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        $id = $this->getKey();
        
        //更新结果
        require app_path() . '/Lib/BsPaySdk/loader.php';
        $one = ComplaintList::where('id', $id)->first();
        if($one['complaint_state'] == 'PROCESSED'){
              return $this->response()->error('该投诉已处理')->refresh();
        }
        // if($one[''])
        // $request = new V2MerchantComplaintUpdateRefundprogressRequest();
        // $request->setReqSeqId(date("YmdHis") . mt_rand());
        // $request->setReqDate(date("Ymd"));
        // $request->setComplaintId($one['complaint_id']);
        // $request->setAction("APPROVE");
        // $request->setMchId($one['mchid']);
        // $extendInfoMap = $this->getApproveInfos();
        // $request->setExtendInfo($extendInfoMap);
        // $client = new BsPayClient();
        $config = [];
        $comp = Complaint::where('id', $one['pid'])->first();
        $config['huifu'] = $comp['huifu_id'];
        $product = ChannelAccount::where('signkey', $config['huifu'])->value('product');
        $config['product'] = $product;
        $config['public'] = $comp['public'];
        $config['private'] = $comp['private'];
     
        // $result = $client->postRequest($request, $config);
        // $result = $result->getRspDatas()['data'];
      
        // Log::debug('退款处理:'.json_encode($result, true));
        // ComplaintList::where('id', $one['id'])->update(['approve' => '1']);
        //同意退款
        if($one['refund'] == '0'){
            $orderTime = DB::table('order')->where('out_trans_id', $one['transaction_id'])->value('created_at');
            $request = new V2TradePaymentScanpayRefundRequest();
            $request->setReqDate(date("Ymd"));
            $request->setReqSeqId(date("YmdHis").mt_rand());
            $request->setHuifuId($one['huifu_id']);
            $request->setOrdAmt(sprintf('%.2f', ($one['amount'] / 100)));
            $request->setOrgReqDate(date('Ymd', $orderTime));
            $extendInfoMap = $this->refundInfos($one['out_trade_no']);
            $request->setExtendInfo($extendInfoMap);
            $client = new BsPayClient();
            $result = $client->postRequest($request, $config);
            if (!$result || $result->isError()) {  //失败处理
                // var_dump($result -> getErrorInfo());
                 Log::debug('退款结果错误'.json_encode($result->getErrorInfo(), true));
            } else {    //成功处理
                // dump($result);
                $data = $result->getRspDatas()['data'];
                Log::debug('退款结果'.json_encode($data, true));
                if($data['resp_code'] == '00000100' && $data['resp_desc'] == '交易处理中'){
                     ComplaintList::where('id', $id)->update(['refund' => '1']);
                     return $this->response()->success('已发起退款')->refresh();
                }else{
                    return $this->response()->error($data['resp_desc'])->refresh();
                }
                //处理完成
                // dump($result->getRspDatas()['data']);
            }
        }else{
             return $this->response()->error('该投诉已退款')->refresh();
        }
       
    }

    /**
     * @return string|array|void
     */
    public function confirm()
    {
        // return ['Confirm?', 'contents'];
    }
    
     public function refundInfos($data)
    {
        $extendInfoMap = array();
        // 原交易全局流水号
        $extendInfoMap["org_party_order_id"]= $data;
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

    /**
     * @param Model|Authenticatable|HasPermissions|null $user
     *
     * @return bool
     */
    protected function authorize($user): bool
    {
        return true;
    }

    /**
     * @return array
     */
    protected function parameters()
    {
        return [];
    }
    
    
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

    // public function render()
    // {
    // $form = UserEditForm::make()->payload(['id' => $this->getKey()]);
    // return Modal::make()->lg()->centered()->title($this->title)->body($form)->button('<i class="fa fa-edit">退款</i> ');
    // }


}
