<?php

namespace App\Admin\Metrics\Examples;

use App\Models\Channel;
use App\Models\User;
use App\Models\Order;
use App\Models\UserChannel;
use App\Models\ChannelAccount;
use App\Models\ComplaintList;
use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Metrics\Donut;
use Illuminate\Contracts\Support\Renderable;

class UserShow extends Donut
{
    protected $labels = ['总订单', '已支付',  '待支付', '未支付'];

    protected $user = null;
    protected $footer;
    protected $name;

    public function __construct($user)
    {
        $this->user = $user;
        parent::__construct();
    }

    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        parent::init();
        $channel = User::where('id', $this->user)->first();
        $color = Admin::color();
        $colors = [ $color->success(), $color->alpha('gray', 0.5), $color->alpha('yellow', 0.4)];
        $this->name = $channel['name'];
        // $this->title($channel['name']);

        $start = strtotime(date('Ymd 00:00:00'));
        $end = strtotime(date('Ymd 23:59:59'));
        $cost = Order::where('uid', $this->user)->whereBetween('created_at', [$start, $end])->whereIn('status', ['paid', 'success'])->sum('cost_amount');
        $msId = Order::where('uid', $this->user)->whereBetween('created_at', [$start, $end])->value('ms_id');
        $name = User::where('id', $msId)->value('name');
        $this->subTitle('系统费：'.$cost.','.'渠道['.$name.']');
        $this->chartLabels($this->labels);
        // 设置图表颜色
        $this->chartColors($colors);
    }

    /**
     * 渲染模板
     *
     * @return string
     */
    public function render()
    {
        $this->fill();

        return parent::render();
    }

    /**
     * 写入数据.
     *
     * @return void
     */
    public function fill()
    {
        //   389 => "KZ2310051531563"
        //   408 => "KZ2310051559872"
        //   477 => "KZ2310051723701"
        //   486 => "KZ2310051737986"
        $start = strtotime(date('Ymd 00:00:00'));
        $end = strtotime(date('Ymd 23:59:59'));
        $count = Order::where('uid', $this->user)->whereBetween('created_at', [$start, $end])->count();
        $done = Order::where('uid', $this->user)->whereBetween('created_at', [$start, $end])->whereIn('status', ['paid', 'success'])->count();
        $none = Order::where('uid', $this->user)->whereBetween('created_at', [$start, $end])->whereIn('status', ['none', 'load', 'fail'])->count();
        
        $fail = Order::where('uid', $this->user)->whereBetween('created_at', [$start, $end])->whereIn('status', ['load_fail'])->where('client_ip', '!=','通道并发限制')->count();
        if(($fail + $done) == 0 || $done == 0){
             $sucRate = 0;
        }else{
             $sucRate = $done / ($fail + $done);
        }
       
        $this->name .= '<span class="label" title="成功率" style="background: #0abd64;margin-left:20px;cursor:default;font-size:75% !important;">成功率：'.sprintf('%.2f', $sucRate) * 100 . '%'.'</span>';
        $this->title($this->name);
     
        $amount = Order::where('uid', $this->user)->whereBetween('created_at', [$start, $end])->whereIn('status', ['paid', 'success'])->sum('amount');
       
        $doneAmount = Order::where('uid', $this->user)->whereBetween('created_at', [$start, $end])->whereIn('status', ['paid', 'success'])->sum('shop_amount');
        $cost = Order::where('uid', $this->user)->whereBetween('created_at', [$start, $end])->whereIn('status', ['paid', 'success'])->sum('cost_amount');
        $code = Order::where('uid', $this->user)->whereBetween('created_at', [$start, $end])->whereIn('status', ['paid', 'success'])->sum('code_amount');
        $doneAmount = $amount - $code;
        
        $channel = UserChannel::where('uid', $this->user)->value('cid');
        $account = ChannelAccount::where('cid', $channel)->pluck('mchid');
       
        $tui = ComplaintList::whereBetween('created_at', [$start, $end])->whereIn('huifu_id', $account)->where('refund', '1')->sum('amount');
        // $account = ChannelAccount::whereIn('mchid', $tui)->pluck('id');
        // dd($tui);    
        // $tui = 0;
        $this->up($amount, $doneAmount, $code, ($tui / 100));

        $this->withContent($count, $done, $none, $fail);

        
        // $done = $done / $count;
        // $done = sprintf('%.2f', $done);
        
        // 图表数据
         $this->withChart([$done, $none, $fail]);
    }

    /**
     * 设置图表数据.
     *
     * @param array $data
     *
     * @return $this
     */
    public function withChart(array $data)
    {
        return $this->chart([
            'series' => $data,
            'chart' => [ 'type' => 'pie'],
            'labels' => ['已支付','未支付','待支付']
        ]);
    }

    /**
     * 设置卡片头部内容.
     *
     * @param mixed $desktop
     * @param mixed $mobile
     *
     * @return $this
     */
    protected function withContent($count, $desktop, $mobile, $none)
    {
        $blue = Admin::color()->alpha('gray', 0.5);

        $style = 'margin-bottom: 8px';
        $labelWidth = 120;

        return $this->content(
            <<<HTML
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle text-primary"></i> {$this->labels[0]}
    </div>
    <div>{$count}</div>
</div>
<div class="d-flex pl-1 pr-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle text-success"></i> {$this->labels[1]}
    </div>
    <div>{$desktop}</div>
</div>
<div class="d-flex pl-1 pr-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: rgba(237, 195, 14, 0.4)"></i> {$this->labels[3]}
    </div>
    <div>{$none}</div>
</div>
<div class="d-flex pl-1 pr-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $blue"></i> {$this->labels[2]}
    </div>
    <div>{$mobile}</div>
</div>


HTML
        );
    }

    /**
     * @param int $percent
     *
     * @return $this
     */
    public function up($percent,$doneAmount, $codeAmount, $tui)
    {
        return $this->footer(
            <<<HTML
<div class="d-flex justify-content-between p-1" style="padding-top: 0!important;">
    <div class="text-center">
        <p style="margin-bottom: 0.2rem !important;">成交金额</p>
        <span class="font-sm-3">￥{$percent}</span>
    </div>
    <div class="text-center">
        <p style="margin-bottom: 0.2rem !important;">三方手续费</p>
        <span class="font-sm-3">￥{$codeAmount}</span>
    </div>
    <div class="text-center">
        <p style="margin-bottom: 0.2rem !important;">实收金额</p>
        <span class="font-sm-3">￥{$doneAmount}</span>
    </div>
    <div class="text-center">
        <p style="margin-bottom: 0.2rem !important;">退款金额</p>
        <span class="font-sm-3">￥{$tui}</span>
    </div>
</div>
HTML
        );
    }

    /**
     * 设置卡片底部内容.
     *
     * @param string|Renderable|\Closure $footer
     *
     * @return $this
     */
    public function footer($footer)
    {
        $this->footer = $footer;

        return $this;
    }

    /**
     * 渲染卡片内容.
     *
     * @return string
     */
    public function renderContent()
    {
        $content = parent::renderContent();

        return <<<HTML
<div class="d-flex justify-content-between align-items-center mt-1" style="margin-bottom: 2px">
   {$content}
</div>
<div class="mt-1 font-weight-bold text-80">
    {$this->renderFooter()}
</div>
HTML;
    }

    /**
     * 渲染卡片底部内容.
     *
     * @return string
     */
    public function renderFooter()
    {
        return $this->toString($this->footer);
    }
}
