<?php

namespace App\Admin\Forms;

use App\Models\Channel;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Admin;

class ChannelEditForm extends Form
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
        $update = Channel::where('id', $input['id'])->update($input);
        if($update){
            return $this
                ->response()
                ->success('提交成功')
                ->refresh();
        }else{
            return $this
                ->response()
                ->error('提交失败')
                ->refresh();
        }
    }

    /**
     * Build a form here.
     */
    public function form()
    {
//        $form->display('id');
//        $form->text('name')->required();
//        $form->text('code')->required();
//        $form->text('encode', '通道编号')->required();
//        $form->radio('mode')->options([
//            'poll' => '通道轮询',
//            'list' => '码商列表轮询',
//            'firm' => '个企轮询',
//        ])->default('poll')->required();
//        $form->range('decline_min', 'decline_max', '下跌金额')->default(0);
//        $form->time('start_time')->default('00:00:00');
//        $form->time('end_time')->default('23:59:59');
        $this->hidden('id')->required();
        $this->text('name')->required();
        $this->text('code')->required();
        $this->text('encode', '通道编号')->required();
        $this->radio('mode')->options([
            'poll' => '通道轮询',
//            'list' => '码商轮询',
            'firm' => '个企随机',
        ])->required();
        $admin = Admin::user();
        if($admin['id'] == '4'){
             $this->text('interval_time', '订单间隔秒数');
             $this->range('decline_min', 'decline_max', '金额区间');
             $this->text('order_win', '通道成功数')->default(0);
             $this->text('order_lose', '通道失败数')->default(0);
            //  $this->text('done_amount', '通道单日额度')->default(0);
             $this->text('top_amount', '商户额度')->default(0);
             $this->text('order_num', '三方下单数')->default(0);
             $this->text('top_num', '三方成功数')->default(0);
             $this->text('top_order', '通道订单(分钟)')->default(0);
             $this->text('done_amount', '通道单日额度')->default(0);
        }
       
        $this->time('start_time')->default('00:00:00');
        $this->time('end_time')->default('23:59:59');
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        $id = $this->payload['id'];
        $data = Channel::find($id);

         return [
            'id' => $data['id'],
            'name'  =>  $data['name'],
            'code'  =>  $data['code'],
            'encode'  =>  $data['encode'],
            'mode'  =>  $data['mode'],
            'decline_min'  =>  $data['decline_min'],
            'decline_max'  =>  $data['decline_max'],
            'start_time'  =>  $data['start_time'],
            'end_time'  =>  $data['end_time'],
            'interval_time'  =>  $data['interval_time'],
            'top_amount' => $data['top_amount'],
            'order_num' => $data['order_num'],
            'top_num' => $data['top_num'],
            'top_order' => $data['top_order'],
            'order_win' => $data['order_win'],
            'order_lose' => $data['order_lose'],
            'done_amount' => $data['done_amount'],
        ];
    }
}
