<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ChannelEditAction;
use App\Admin\Repositories\Channel;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class ChannelController extends AdminController
{
    protected $title = '通道管理';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Channel(), function (Grid $grid) {
//            $grid->setActionClass(\Dcat\Admin\Grid\Displayers\Actions::class); // 列中操作直接显示 不要默认的三个点
            $grid->enableDialogCreate();
            $grid->disableQuickEditButton();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('code');
            $grid->column('encode', '通道编号');
            $grid->column('mode')->display(function ($mode){
                    if($mode == 'poll'){
                        return '通道轮询';
                    }elseif ($mode == 'list'){
                        return '码商轮询';
                    }elseif ($mode == 'firm'){
                        return '个企随机';
                    }
            });
            $grid->column('status','状态')->switch('green');
            $grid->column('start_time');
            $grid->column('end_time');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('encode');
            });

            $grid->actions(function (Grid\Displayers\Actions $actions){
                $actions->append(ChannelEditAction::make());
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
        return Show::make($id, new Channel(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('code');
            $show->field('encode');
            $show->field('mode');
            $show->field('mode_time');
            $show->field('is_decline');
            $show->field('in_time');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Channel(), function (Form $form) {
            $form->display('id');
            $form->text('name')->required();
            $form->text('code')->required();
            $form->text('encode', '通道编号')->required();
            $form->radio('mode')->options([
                'poll' => '通道轮询',
//                'list' => '码商轮询',
//                'firm' => '个企随机',
            ])->default('poll')->required();
            $form->hidden('interval_time', '订单间隔秒数')->default(0);
            $form->hidden('top_amount', '账号额度')->default(0);
            $form->hidden('order_num', '商户订单数')->default(0);
            $form->hidden('top_num', '商户成功数')->default(0);
            $form->hidden('top_order', '通道订单(分钟)')->default(0);
            $form->hidden('done_amount', '通道单日额度')->default(0);
            $form->range('decline_min', 'decline_max', '下跌金额')->default(0);
            $form->time('start_time')->default('00:00:00');
            $form->time('end_time')->default('23:59:59');
            $form->hidden('status')
                ->saving(function ($v) {
                    return $v ? 1 : 0;
                });
//            $form->disableHeader();
            $form->disableViewButton();
            $form->disableDeleteButton();
        });
    }
}
