<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\OrderBackActions;
use App\Admin\Actions\OrderCheckActions;
use App\Admin\Actions\OrderDetailActions;
use App\Admin\Actions\TestOrderAction;
use App\Admin\Repositories\Order;
use Dcat\Admin\Form;
use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use App\Models\User;
use App\Models\ChannelAccount;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class OrderController extends AdminController
{
    protected $title = '订单管理';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Order(), function (Grid $grid) {
//               Admin::script(
//                 <<<JS
//               var interVal = null;
//               var time = localStorage.getItem('time');
//                 if(time){
//                     if(time != '0'){
//                       interVal =  setInterval(function(){
//                       var loadTime = $("#time").val();
//                           if(loadTime){
//                                 clearInterval(interVal)
//                                 $(".grid-refresh").click()
//                           }
//                       }, time +'000');
//                       $("#time").val(time)
//                       console.log(interVal)
//                     }else{
//                         $("#time").val(time)
//                     }
//                 }else{
//                     time = $("#time").val();
//                     localStorage.setItem('time', time);
//                 }

//                 $("#time").change(function(){
//                     clearInterval(interVal)
//                     localStorage.setItem('time', $("#time").val());
//                     location.reload()
//                 })


// JS
//             );
            // $grid->model()->resetOrderBy();
            $grid->enableDialogCreate();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableColumnSelector();
            $grid->disableBatchActions();
            $grid->disableRowSelector();
            $grid->disableCreateButton();
            $grid->addTableClass(['table-text-center']);
            $grid->paginate(10);
            $grid->column('id');
            $grid->column('uid', '商户名称')->display(function($uid){
                $username = User::where('id', $uid)->value('username');
                if($username){
                    return $username;
                }else{
                    return "测试订单";
                }
            });
            $grid->column('sys_order');
            $grid->column('shop_order');
            $grid->column('amount', '支付金额' );
            $grid->column('decline_amount', '订单金额')->display(function(){
                return sprintf('%01.2f', ($this->amount + $this->decline_amount));
            });
//            $grid->column('shop_amount');
//            $grid->column('cost_amount');
//            $grid->column('code_amount');
//            $grid->column('source_url');
//            $grid->column('notiry_url');
//            $grid->column('callback_url');
//            $grid->column('cid');
//            $grid->column('encode');
            $grid->column('codename');
//            $grid->column('client');
//            $grid->column('client_ip');
            $grid->column('ms_id', '码商名称')->display(function(){
                if($this->ms_id == '0'){
                    return '系统';
                }
                return User::where('id', $this->ms_id)->value('name');
            });
            $grid->column('aid', '通道账号')->display(function($aid){
                $name = ChannelAccount::where('id', $aid)->value('name');
                if($this->aptitude == 'company'){
                    return '<p style="margin-bottom:0rem !important">'.$name.'<span class="right badge badge-danger"></span> </p>';
                }else{
                    return $name;
                }
            });
            // $grid->column('aptitude', '账号类型')->display(function($aptitude){

            // });
            $grid->column('status')->display(function($status){
                if($status  == 'none'){
                    return '<span class="text">待支付</span>';
                }else if($status == 'load'){
                    return '<span class="text text-warning">支付中</span>';
                }else if($status == 'paid'){
                    return '<span class="text text-info">已支付</span>';
                }else if($status == 'success'){
                    return '<span class="text" style="color:#0564eb !important">已通知</span>';
                }else if($status == 'fail'){
                    return '<span class="text text-danger">失效订单</span>';
                }else if($status == 'load_fail'){
                    return '<span class="text text-danger">失效订单</span>';
                }else if($status == 'black_open'){
                    return '<span class="text">黑名单</span>';
                }else if($status == 'black_ip'){
                    return '<span class="text">黑名单</span>';
                }
            });
//            $grid->column('notify_at');

            $grid->column('client_ip', 'IP地址');
            $grid->column('openid')->display(function(){
                    return "<div style='width: 250px;height: 20px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;' title=".$this->openid.">".$this->openid."</div>";
            });
            $grid->column('created_at', '下单时间');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $user = User::where('type', 'shop')->pluck('name', 'id');
                $filter->equal('uid')->select($user);
                $filter->equal('sys_order', '系统订单号');
                $filter->equal('shop_order');
                $filter->equal('client_ip');
                $filter->equal('openid');
                $filter->equal('aid', '账号ID');
                $filter->equal('out_trans_id', '交易单号');
                $statusArr = [
                    'none' => '未支付',
                    'load' => '支付中',
                    'paid' => '已支付',
                    'success' => '已通知',
                    'load_fail' => '支付失效',
                    'fail' => '失效订单',
                    'black_ip' => 'IP黑名单',
                    'black_open' => 'openid黑名单',
                ];
                $code = User::where('type', 'code')->pluck('name', 'id');
                $filter->equal('ms_id', '码商名称')->select($code);
                $filter->equal('status')->select($statusArr);
                $filter->whereBetween('created_at', function ($q){
                    $start = $this->input['start'];
                    $end = $this->input['end'];
                    $start = strtotime(date($start.' 00:00:00'));
                    $end = strtotime(date($end.' 23:59:59'));
                    $q->whereBetween('created_at', [$start, $end]);
                })->date();
            });

            $grid->actions(function (Grid\Displayers\Actions $actions){
                $actions->append(OrderDetailActions::make());
            });
//            $grid->actions(function (Grid\Displayers\Actions $actions){
//                $actions->append(OrderCheckActions::make());
//            });
            $grid->actions(function (Grid\Displayers\Actions $actions){
                $actions->append(OrderBackActions::make());
            });

            $grid->tools(function (Grid\Tools  $tools)  {
                $tools->append(TestOrderAction::make());
            });

            $uid = request()->input('uid') ?? '';
            $time = request()->input('created_at') ?? '';
            if(empty($uid) && empty($time)){
                $start = strtotime(date('Ymd 00:00:00'));
                $end = strtotime(date('Ymd 23:59:59'));
                $count = \App\Models\Order::whereBetween('created_at', [$start, $end])->count();
                $countAmount = \App\Models\Order::whereBetween('created_at', [$start, $end])->sum('amount');
                $success = \App\Models\Order::whereIn('status', ['paid', 'success'])->whereBetween('created_at', [$start, $end])->count();
                $successAmount = \App\Models\Order::whereBetween('created_at', [$start, $end])->whereIn('status', ['paid', 'success'])->sum('amount');
            }
            else{
                $start = strtotime(date($time['start'].' 00:00:00'));
                $end = strtotime(date($time['end'].' 23:59:59'));
                // whereBetween('created_at', [$start, $end])
                $countSql = (new \App\Models\Order);
                $countAmountSql = (new \App\Models\Order);
                $successSql = (new \App\Models\Order)->whereIn('status', ['paid', 'success']);
                $successAmountSql = (new \App\Models\Order)->whereIn('status', ['paid', 'success']); 
                
                if(!empty($time['start'])){
                    $countSql = $countSql->whereBetween('created_at', [$start, $end]);
                    $countAmountSql = $countAmountSql->whereBetween('created_at', [$start, $end]);
                    $successSql = $successSql->whereBetween('created_at', [$start, $end]);
                    $successAmountSql = $successAmountSql->whereBetween('created_at', [$start, $end]);
                }
                
                if(!empty($uid)){
                    $countSql = $countSql->where('uid', $uid);
                    $countAmountSql = $countAmountSql->where('uid', $uid);
                    $successSql = $successSql->where('uid', $uid);
                    $successAmountSql = $successAmountSql->where('uid', $uid);
                }
                
                if(!empty($msId)){
                    $countSql = $countSql->where('ms_id', $msId);
                    $countAmountSql = $countAmountSql->where('ms_id', $msId);
                    $successSql = $successSql->where('ms_id', $msId);
                    $successAmountSql = $successAmountSql->where('ms_id', $msId);
                }
                    $count = $countSql->count();
                    $countAmount = $countAmountSql->sum('amount');
                    $success = $successSql->count();
                    $successAmount = $successAmountSql->sum('amount');
            }

            if($count != 0){
                $rate = $success / $count;
                $rate = sprintf("%.2f", $rate * 100);
            }else{
                $rate = 0;
            }

            $grid->tools('<a class="btn btn-sm btn-default">订单笔数：'.$count.'</a>');
            $grid->tools('<a class="btn btn-sm btn-default">订单总金额：'.$countAmount.'</a>');
            $grid->tools('<a class="btn btn-sm btn-default">订单成功笔数：'.$success.'</a>');
            $grid->tools('<a class="btn btn-sm btn-default">订单成功金额：'.$successAmount.'</a>');
            $grid->tools('<a class="btn btn-sm btn-default">成功率：'.$rate.'%</a>');
            // $grid->tools('<input class="form-control" style="width: 50px;float: right;" id="time" value="60"  title="刷新秒数;为0不刷新" />');

            $grid->async(false);

        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new Order(), function (Show $show) {
            $show->field('id');
            $show->field('uid');
            $show->field('sys_order');
            $show->field('shop_order');
            $show->field('amount');
            $show->field('shop_amount');
            $show->field('cost_amount');
            $show->field('code_amount');
            $show->field('source_url');
            $show->field('notiry_url');
            $show->field('callback_url');
            $show->field('cid');
            $show->field('encode');
            $show->field('codename');
            $show->field('client');
            $show->field('client_ip');
            $show->field('aid');
            $show->field('aname');
            $show->field('status');
            $show->field('notify_at');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Order(), function (Form $form) {
            $form->display('id');
            $form->text('uid');
            $form->text('sys_order');
            $form->text('shop_order');
            $form->text('amount');
            $form->text('shop_amount');
            $form->text('cost_amount');
            $form->text('code_amount');
            $form->text('source_url');
            $form->text('notiry_url');
            $form->text('callback_url');
            $form->text('cid');
            $form->text('encode');
            $form->text('codename');
            $form->text('client');
            $form->text('client_ip');
            $form->text('aid');
            $form->text('aname');
            $form->text('status');
            $form->text('notify_at');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
