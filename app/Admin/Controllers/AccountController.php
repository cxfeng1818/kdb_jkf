<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Account;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class AccountController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Account(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('type');
            $grid->column('username');
            $grid->column('password');
            $grid->column('amount');
            $grid->column('freeze_amount');
            $grid->column('api_key');
            $grid->column('api_ip');
//            $grid->column('created_at');
//            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');

            });
            $grid->enableDialogCreate();
            $grid->disableViewButton();
            
            $grid->actions(function (Grid\Displayers\Actions $actions) {

                 $actions->append( 123);

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
        return Show::make($id, new Account(), function (Show $show) {
            $show->field('id');
            $show->field('id');
            $show->field('type');
            $show->field('username');
            $show->field('password');
            $show->field('amount');
            $show->field('freeze_amount');
            $show->field('api_key');
            $show->field('api_ip');
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
        return Form::make(new Account(), function (Form $form) {
            $form->display('id');
            $options = [
                'shop' => '商户',
                'code' => '码商',
            ];
            $form->radio('type')->options($options);
            $form->text('username');
            $form->text('password');
//            $form->text('api_key');
//            $form->text('api_ip');

            $form->display('created_at');
            $form->display('updated_at');

            $form->submitted(function (Form $form) {
                // 获取用户提交参数
                if($form->type == 'shop'){
                    $form->api_key = time();
                }

                return ;
            });

            $form->disableResetButton();
            $form->disableViewCheck();
            $form->disableEditingCheck();
            $form->disableCreatingCheck();
        });
    }
}
