<?php

namespace App\Admin\Actions;

use App\Models\Order;
use App\Models\ChannelAccount;
use App\Models\User;
use App\Models\UserRecord;
use Dcat\Admin\Actions\Action;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Lib\BsPaySdk\core\BsPayClient;
use App\Lib\BsPaySdk\request\V2TradePaymentScanpayQueryRequest;

class OrderCheckActions extends RowAction
{
    /**
     * @return string
     */
	protected $title = '<i class="fa fa-eye">订单查询</i>';


    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        require app_path().'/Lib/BsPaySdk/loader.php';
        $order = Order::where('id', $this->getKey())->first();
        if($order['status'] == 'fail'){
            return $this->response()->error('订单已失效');
        }
        if($order['status'] == 'none'){
            return $this->response()->error('订单未支付');
        }
        $account = ChannelAccount::where('id', $order['aid'])->first();
        $request = new V2TradePaymentScanpayQueryRequest();
        $request->setHuifuId($account['mchid']);
        $request->setOrgReqSeqId($order['sys_order']);
        $request->setOrgReqDate(date('Ymd'));
        $client = new BsPayClient();
        $config = [];
        $config['huifu'] = $account['signkey'];
        $config['public'] = $account['public_secret'];
        $config['private'] = $account['private_secret'];
        $result = $client->postRequest($request, $config);
        $data = $result->getRspDatas()['data'];
        
        if($data['trans_stat'] == 'S'){
            if($order['status'] == 'load'){
                User::where('id', $order['uid'])->increment('amount', $order['shop_amount']);
                $this->createLog($order['id'], $order['uid'], $order['shop_amount']);
                User::where('id', $order['ms_id'])->increment('amount', $order['code_amount']);
                $this->createLog($order['id'], $order['ms_id'], $order['code_amount']);
                Order::where('sys_order', $data['org_req_seq_id'])->update([
                    'status' => 'paid',
                    'updated_at' => time(),
                    'out_trans_id' => $data['out_trans_id']
                ]);
                //todo 发送回调
            }
            return $this->response()->success($data['bank_desc'])->refresh();
        }else{
            return $this->response()->error($data['bank_desc'])->refresh();
        }

    }

    private function createLog($oid, $uid, $amount)
    {
        $beforeAmount = User::where('id',$uid)->value('amount');
        UserRecord::create([
            'uid' => $uid,
            'oid' => $oid,
            'befor_amount' => $beforeAmount,
            'amount' => $amount,
            'after_amount' => ($beforeAmount + $amount),
            'created_at' => time()
        ]);
    }

    /**
     * @return string|array|void
     */
    public function confirm()
    {
        // return ['Confirm?', 'contents'];
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


}
