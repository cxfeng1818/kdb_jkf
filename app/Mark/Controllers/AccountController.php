<?php

namespace App\Mark\Controllers;

use App\Mark\Actions\ChannelAccountEditAction;
use App\Admin\Repositories\ChannelAccount;
use App\Mark\Actions\AccountCodeAction;
use App\Models\Channel;
use App\Models\User;
use App\Models\UserChannel;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use PHPZxing\PHPZxingDecoder;

class AccountController extends AdminController
{
    protected $title = '账号管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new ChannelAccount(), function (Grid $grid) {
//            $grid->setActionClass(\Dcat\Admin\Grid\Displayers\Actions::class); // 列中操作直接显示 不要默认的三个点
            $grid->enableDialogCreate();
            $grid->disableViewButton();
            $grid->disableEditButton();
            
            $grid->column('id', 'ID')->sortable();
            $grid->column('cid', '通道名称')->display(function($cid){
                return Channel::where('id', $cid)->value('name');
            });
            $grid->column('name');
            $grid->column('amount', '账号营收');
            
//            $grid->column('mchid');
//            $grid->column('signkey');
            $grid->column('appid');
//            $grid->column('secret');
//            $grid->column('qrcode');
//            $grid->column('start_time');
//            $grid->column('end_time');
//            $grid->column('public_secret');
//            $grid->column('private_secret');
//            $grid->column('day_limit', '当日最大笔数');
//            $grid->column('min_amount');
//            $grid->column('max_amount');
            $grid->column('status', '状态')->switch('green');
//            $grid->column('created_at');
            $grid->column('updated_at', '编辑时间')->sortable();
            
            // $grid->image('配置信息')->display(function(){
                // return QrCode::size('100')->generate('123');
//                    return '<img src="/2031.JPG" style="width: 50px">';
            // });

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $user = Admin::user();
                $channel = UserChannel::where('uid', $user['id'])->with(['channel'])->get();
                $channelType = [];
                foreach ($channel as $item)
                {
                    $channelType[$item->channel['id']] = $item->channel['name'] .'-'.$item->channel['encode'];
                }
                $filter->equal('cid', '所属通道')->select($channelType);
            });

            $grid->actions(function (Grid\Displayers\Actions $actions){
                $actions->append(ChannelAccountEditAction::make());
            });

             $grid->actions(function (Grid\Displayers\Actions $actions){
                $actions->append(AccountCodeAction::make());
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
        return Show::make($id, new ChannelAccount(), function (Show $show) {
            $show->field('id');
            $show->field('cid');
            $show->field('type');
            $show->field('name');
            $show->field('mchid');
            $show->field('signkey');
            $show->field('appid');
            $show->field('secret');
            $show->field('qrcode');
            $show->field('start_time');
            $show->field('end_time');
            $show->field('public_secret');
            $show->field('private_secret');
            $show->field('day_limit');
            $show->field('min_amount');
            $show->field('max_amount');
            $show->field('status');
//            $show->field('created_at');
//            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new ChannelAccount(), function (Form $form) {
            $user = Admin::user();
            $channel = UserChannel::where('uid', $user['id'])->with(['channel'])->get();
            $channelType = [];
            foreach ($channel as $item)
            {
                $channelType[$item->channel['id']] = $item->channel['name'] .'-'.$item->channel['encode'];
            }

            $form->display('id');
            $form->select('cid', '所属通道')
                ->options($channelType)->required();
            $form->hidden('type')->value('code');
            $form->hidden('uid')->value($user['id']);
            $form->text('name')->required();
            $form->hidden('mchid', '商户号');
            $form->hidden('signkey', '渠道号');
            $form->hidden('appid', 'Appid');
//            $form->hidden('secret', '密钥');
//            $form->image('qrcode', '二维码')->accept('jpg,png,gif,jpeg', 'image/*')
//                ->autoUpload()
//                ->uniqueName();
//            $form->keyValue('qrcode', '二维码')
//                ->setKeyLabel('金额')
//                ->setValueLabel('链接地址')->saving(function ($paths) {
//                    return json_encode($paths, true);
//                });
            $form->array('qrcode', function($item){
                 $item->text('amount', '对应金额');
                 $item->image('qrcode')->uniqueName()->maxSize(2048);
            })->saving(function ($data){
                $decoder = new PHPZxingDecoder(['try_harder' => true]);
                $decoder->setJavaPath("/usr/bin/java");
                foreach ($data as $key => $value)
                {
                    // dump(storage_path('app/qrcode').'/'.$value['qrcode']);
                    if(empty($value['qrcode'])){
                         throw new \Exception('请检查图片是否上传');
                    }
                    $result = $decoder->decode(storage_path('app/qrcode').'/'.$value['qrcode']);
                    // dump($result);
                    if(!empty($result)){
                        $data[$key]['link'] = $result->getImageValue();
                    }else{
                        // $data[$key]['link'] = 'https://m.tb.cn/a.ZRs8?scm=20140619.pc_detail.itemId.0&id=740810327937';
                        throw new \Exception($value['amount'].'金额;二维码解析错误.请重新生成');
                    } 
                }
                $linkArr = array_column($data, 'link');
                $linkArr = array_unique($linkArr);
                if(count($linkArr) != count($data)){
                    throw new \Exception('二维码图片有重复;请检查');
                }
               
                return json_encode($data, true);
            });
//            $form->array('qrcode', function ($table) {
//                $table->text('amount', '金额');
//                $table->image('code', '二维码')->accept('jpg,png,gif,jpeg', 'image/*')
//                ->autoUpload()
//                ->uniqueName();
//            })->saveAsJson();
            $form->hidden('aptitude', '账号属性')->options([
                'company' => '企业',
                'person' => '个人',
            ])->default('company');
            $form->hidden('public_secret', '公钥配置');
            $form->hidden('private_secret', '私钥配置');
            $form->time('start_time', '开始时间')->default('00:00:00');
            $form->time('end_time', '结束时间')->default('23:59:59');
            $form->text('day_limit', '当天最大笔数')->default(0)->placeholder('当天最大交易笔数');
            $form->text('min_amount', '最低金额')->default(0)->placeholder('订单最小金额');
            $form->text('max_amount', '最高金额')->default(50000)->placeholder('订单最大金额');
            $form->hidden('status')
                ->customFormat(function ($v) {
                    return $v == '1' ? 1 : 0;
                })
                ->saving(function ($v) {
                    return 1;
                });
          
//            $form->display('created_at');
//            $form->display('updated_at');
        });
    }

   
}
