<?php

namespace App\Admin\Forms;

use App\Models\Channel;
use App\Models\ChannelAccount;
use App\Models\Order;
use App\Models\User;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;

class OrderDetailForm extends Form
{
    use LazyWidget;
    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
        // dump($input);

        // return $this->response()->error('Your error message.');

        return $this
				->response()
				->success('Processed successfully.')
				->refresh();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->disableResetButton();
        $this->disableSubmitButton();
        $this->text('id')->readOnly();
        $this->text('sys_order')->readOnly();
        $this->text('shop_order')->readOnly();
        $this->text('amount')->readOnly();
        $this->text('shop_amount')->readOnly();
        $this->text('cost_amount')->readOnly();
        $this->text('code_amount', '码商金额')->readOnly();
        $this->text('decline_amount', '下跌金额')->readOnly();
        $this->text('notify_url', '异步回调')->readOnly();
        $this->text('callback_url')->readOnly();
        $this->text('codename')->readOnly();
        $this->text('encode')->readOnly();
        $this->text('ms_id', '所属码商')->readOnly();
        // $this->text('aid')->readOnly();
        $this->text('aname')->readOnly();
        $this->text('aptitude', '账号类型')->readOnly();
        $this->text('status')->readOnly();
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        $id = $this->payload['id'];
        $order = Order::find($id);
        $msName = User::where('id', $order['ms_id'])->value('name');
        if($order['status'] == 'none'){
            $status = '未支付';
        }elseif ($order['status'] == 'load'){
            $status = '支付中';
        }elseif ($order['status'] == 'paid'){
            $status = '已支付';
        }else{
            $status = '已通知';
        }
        if($order['aptitude'] == 'person'){
                $aptitude = '个体';
        }else{
            $aptitude = '企业';
        }
        return [
            'id'  => $order['id'],
            'sys_order' => $order['sys_order'],
            'shop_order' => $order['shop_order'],
            'amount' => $order['amount'],
            'shop_amount' => $order['shop_amount'],
            'cost_amount' => $order['cost_amount'],
            'code_amount' => $order['code_amount'],
            'decline_amount' => $order['decline_amount'],
            'notify_url' => $order['notify_url'],
            'callback_url' => $order['callback_url'],
            'encode' => $order['encode'],
            'codename' => $order['codename'],
            'ms_id' => $msName,
            'aname' => $order['aname'],
            'aptitude' => $aptitude,
            'status' => $status,
        ];
    }
}
