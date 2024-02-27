<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Withdraw;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class WithdrawController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Withdraw(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('uid');
            $grid->column('bank_name');
//            $grid->column('branch_name');
//            $grid->column('name');
//            $grid->column('card_no');
//            $grid->column('province');
//            $grid->column('city');
//            $grid->column('alias');
            $grid->column('amount');
            $grid->column('cost');
//            $grid->column('actual_name');
            $grid->column('status');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
            });


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
        return Show::make($id, new Withdraw(), function (Show $show) {
            $show->field('id');
            $show->field('uid');
            $show->field('bank_name');
            $show->field('branch_name');
            $show->field('name');
            $show->field('card_no');
            $show->field('province');
            $show->field('city');
            $show->field('alias');
            $show->field('amount');
            $show->field('cost');
            $show->field('actual_name');
            $show->field('status');
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
        return Form::make(new Withdraw(), function (Form $form) {
            $form->display('id');
            $form->text('uid');
            $form->text('bank_name');
            $form->text('branch_name');
            $form->text('name');
            $form->text('card_no');
            $form->text('province');
            $form->text('city');
            $form->text('alias');
            $form->text('amount');
            $form->text('cost');
            $form->text('actual_name');
            $form->text('status');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
