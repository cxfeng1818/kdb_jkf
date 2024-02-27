<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Black;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class BlackController extends AdminController
{
    
     protected $title = '黑名单管理';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Black(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('openid');
            $grid->column('ip');
            
            // $grid->column('created_at');
            // $grid->column('updated_at');
//            $grid->column('branch_name');
//            $grid->column('name');
//            $grid->column('card_no');
//            $grid->column('province');
//            $grid->column('city');
//            $grid->column('alias');
            // $grid->column('amount');
            // $grid->column('cost');
//            $grid->column('actual_name');
            // $grid->column('status');
            // $grid->column('created_at');
            // $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('openid');
                $filter->equal('ip');
            });
            $grid->enableDialogCreate();
            $grid->disableViewButton();
            // $grid->disableEditButton();
            // $grid->disableDeleteButton();
            $grid->disableColumnSelector();
            $grid->disableBatchActions();
            $grid->disableRowSelector();
            // $grid->disableCreateButton();
            $grid->addTableClass(['table-text-center']);
            $grid->paginate(10);

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
        return Show::make($id, new Black(), function (Show $show) {
            $show->filed('openid');
            $show->filed('ip');
            
            $show->disableEditButton();
            $show->disableDeleteButton();
            // $show->filed('created_at');
            // $show->filed('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Black(), function (Form $form) {
            // $form->display('id');
            $form->text('openid');
            $form->text('ip');
            
            $form->disableViewCheck();
            $form->disableDeleteButton();
            $form->disableEditingCheck();
            $form->disableViewButton();
            // $form->display('created_at');
            // $form->display('updated_at');
        });
    }
}
