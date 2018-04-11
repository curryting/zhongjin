<?php
/**
 * 通知定时任务
 */
namespace app\home\controller;

use think\Controller;
use app\common\controller\Errcode;
use app\home\logic\NoticeLogic;

class Noticetimer extends Controller
{
	/**
	 * 发布到时间的通知
	 */
	public function publish()
	{
		NoticeLogic::timerDeal();
		return ['code'=>Errcode::SUCCESS];
	}
}
