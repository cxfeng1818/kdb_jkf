<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ComplainFilterActions;
use App\Admin\Repositories\ComplaintList;
use App\Admin\Actions\RefundAction;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class ComplaintListController extends AdminController
{
    protected $title = '投诉管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new ComplaintList(), function (Grid $grid) {
            $grid->disableEditButton();
            $grid->disableBatchActions();
            $grid->disableFilterButton();
            $grid->enableDialogCreate();
            $grid->disableViewButton();
            $grid->disableRowSelector();
            $grid->disableCreateButton();


            $grid->column('huifu_id', '商户ID');
            $grid->column('transaction_id', '交易单号');
//            $grid->column('id')->sortable();
            $grid->column('amount', '金额')->display(function(){
                return $this->amount / 100;
            });
//            $grid->column('apply_refund_amount');
//            $grid->column('complaint_full_refunded');
//            $grid->column('complaint_id');
//            $grid->column('complaint_media_list');
//            $grid->column('complainted_mchid');
//            $grid->column('first_agent_id');
//            $grid->column('first_agent_name');
//            $grid->column('incoming_user_response');
//            $grid->column('mchid');
//            $grid->column('out_trade_no', '交易单号');
            $grid->column('payer_phone', '交易手机号');
            $grid->column('problem_description', '投诉描述');
            $grid->column('complaint_detail', '投诉详情');
            $grid->column('complaint_time', '投诉时间');
            $grid->column('complaint_state', '投诉状态')->display(function(){
                  if($this->complaint_state == 'PROCESSING'){
                      return '<span class="text text-warning">处理中</span>';
                  }else if($this->complaint_state == 'PENDING'){
                      return '<span class="text text-info">等待处理</span>';
                  }else{
                      return '<span class="text text-success">已处理</span>';
                  }
            });
            
            $grid->column('reply', '是否回复')->display(function(){
                if($this->reply == '1'){
                    return '已回复';
                }
                    return '未回复';
            });
            $grid->column('refund', '是否退款')->display(function(){
                if($this->refund == '1'){
                    return '已退款';
                }
                    return '未退款';
            });
            
            $grid->actions(function (Grid\Displayers\Actions $actions){
                $actions->append(RefundAction::make());
            });
//            $grid->column('problem_type');
//            $grid->column('reg_name');
//            $grid->column('upper_huifu_id');
//            $grid->column('user_complaint_times');
//            $grid->column('user_tag_list');
//            $grid->filter(function (Grid\Filter $filter) {
//                $filter->equal('id');
//            });
//            $grid->tools(function  (Grid\Tools  $tools)  {
//                $tools->append(ComplainFilterActions::make());
//            });
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
        return Show::make($id, new ComplaintList(), function (Show $show) {
            $show->field('id');
            $show->field('amount');
            $show->field('apply_refund_amount');
            $show->field('complaint_detail');
            $show->field('complaint_full_refunded');
            $show->field('complaint_id');
            $show->field('complaint_media_list');
            $show->field('complaint_state');
            $show->field('complaint_time');
            $show->field('complainted_mchid');
            $show->field('first_agent_id');
            $show->field('first_agent_name');
            $show->field('huifu_id');
            $show->field('incoming_user_response');
            $show->field('mchid');
            $show->field('out_trade_no');
            $show->field('payer_phone');
            $show->field('problem_description');
            $show->field('problem_type');
            $show->field('reg_name');
            $show->field('transaction_id');
            $show->field('upper_huifu_id');
            $show->field('user_complaint_times');
            $show->field('user_tag_list');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new ComplaintList(), function (Form $form) {
            $form->display('id');
            $form->text('amount');
            $form->text('apply_refund_amount');
            $form->text('complaint_detail');
            $form->text('complaint_full_refunded');
            $form->text('complaint_id');
            $form->text('complaint_media_list');
            $form->text('complaint_state');
            $form->text('complaint_time');
            $form->text('complainted_mchid');
            $form->text('first_agent_id');
            $form->text('first_agent_name');
            $form->text('huifu_id');
            $form->text('incoming_user_response');
            $form->text('mchid');
            $form->text('out_trade_no');
            $form->text('payer_phone');
            $form->text('problem_description');
            $form->text('problem_type');
            $form->text('reg_name');
            $form->text('transaction_id');
            $form->text('upper_huifu_id');
            $form->text('user_complaint_times');
            $form->text('user_tag_list');
        });
    }
}
