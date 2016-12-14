<?php

require __DIR__ . '/Container.php';

/**
 * 旅行类接口
 */
interface TrafficTool
{
    public function go();
}

/**
 * 火车旅行
 */
class Train implements TrafficTool
{
    public function go()
    {
        echo 'Train...';
    }
}

/**
 * 徒步旅行
 */
class Leg implements TrafficTool
{
    public function go()
    {
        echo 'Leg...';
    }
}

/**
 * 旅行者
 */
class Traveller
{
    protected $trafficTool;

    /**
     * 旅行者构造方法
     *
     * @param TrafficTool $trafficTool 旅行者使用的旅行方式
     */
    public function __construct(TrafficTool $trafficTool)
    {
        $this->trafficTool = $trafficTool;
    }

    /**
     * 去西藏旅行
     */
    public function visitTibet()
    {
        $this->trafficTool->go();
    }
}

$app = new Container();

$app->bind('TrafficTool', 'Train');
$app->bind('travellerA', 'Traveller');

$traveller = $app->make('travellerA');
$traveller->visitTibet();
