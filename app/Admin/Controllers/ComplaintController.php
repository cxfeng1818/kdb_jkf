<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ComplaintListActions;
use App\Admin\Repositories\Complaint;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class ComplaintController extends AdminController
{
    protected $title = '投诉管理';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Complaint(), function (Grid $grid) {
//            $grid->disableEditButton();
            $grid->disableBatchActions();
            $grid->disableFilterButton();
            $grid->enableDialogCreate();
            $grid->disableViewButton();
            $grid->disableRowSelector();
            $grid->disableQuickEditButton();

            $grid->column('id')->sortable();
            $grid->column('name', '名称');
            $grid->column('huifu_id');
            $grid->column('reply', '回复内容');
//            $grid->column('public');
//            $grid->column('private');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
            });

            $grid->actions(function (Grid\Displayers\Actions $actions){
                $actions->append(ComplaintListActions::make());
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
        return Show::make($id, new Complaint(), function (Show $show) {
            $show->field('id');
            $show->field('huifu_id');
            $show->field('public');
            $show->field('private');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Complaint(), function (Form $form) {
            $form->hidden('id');
            $form->text('name', '名称');
            $form->text('huifu_id');
            $form->textarea('public');
            $form->textarea('private');
            $form->textarea('reply', '自动回复内容');
        });
    }
}
