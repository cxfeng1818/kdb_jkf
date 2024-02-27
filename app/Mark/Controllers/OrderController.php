<?php

namespace App\Mark\Controllers;

use App\Admin\Repositories\Order;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
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
            $grid->column('uid');
            $grid->column('sys_order');
            $grid->column('shop_order');
            $grid->column('amount');
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
//            $grid->column('aid');
//            $grid->column('aname');
             $grid->column('status')->display(function($status){
                if($status  == 'none'){
                    return '<span class="text">待支付</span>';
                }else if($status == 'load'){
                    return '<span class="text text-warning">支付中</span>';
                }else if($status == 'paid'){
                    return '<span class="text text-info">已支付</span>';
                }else if($status == 'success'){
                    return '<span class="text text-success">已通知</span>';
                }else if($status == 'fail'){
                    return '<span class="text text-danger">失效订单</span>';
                }
            });
//            $grid->column('notify_at');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('shop_order');
            });

            $grid->disableCreateButton();
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
