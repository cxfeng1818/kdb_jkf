<?php

namespace App\Admin\Metrics\Examples;

use App\Models\Channel;
use App\Models\Order;
use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Metrics\Donut;

class NewDevices extends Donut
{
    protected $labels = ['已支付', '未支付'];

    protected $channel = null;

    public function __construct($channel)
    {
        $this->channel = $channel;
        parent::__construct();
    }



    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        parent::init();
        $channel = Channel::where('id', $this->channel)->first();
        $color = Admin::color();
        $colors = [$color->success(), $color->alpha('gray', 0.2)];
        $this->title($channel['name']);
        $start = strtotime(date('Ymd 00:00:00'));
        $end = strtotime(date('Ymd 23:59:59'));
        $done = Order::where('cid', $this->channel)->whereBetween('created_at', [$start, $end])->whereIn('status', ['paid', 'success'])->sum('amount');
        $this->subTitle($done);
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
        $start = strtotime(date('Ymd 00:00:00'));
        $end = strtotime(date('Ymd 23:59:59'));
        $done = Order::where('cid', $this->channel)->whereBetween('created_at', [$start, $end])->whereIn('status', ['paid', 'success'])->count();
        $none = Order::where('cid', $this->channel)->whereBetween('created_at', [$start, $end])->whereIn('status', ['none', 'load', 'fail'])->count();
        $this->withContent($done, $none);


        // 图表数据
        $this->withChart([$done, $none]);
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
            'series' => $data
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
    protected function withContent($desktop, $mobile)
    {
        $blue = Admin::color()->alpha('gray', 0.2);

        $style = 'margin-bottom: 8px';
        $labelWidth = 120;

        return $this->content(
            <<<HTML
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle text-success"></i> {$this->labels[0]}
    </div>
    <div>{$desktop}</div>
</div>
<div class="d-flex pl-1 pr-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $blue"></i> {$this->labels[1]}
    </div>
    <div>{$mobile}</div>
</div>
HTML
        );
    }
}
