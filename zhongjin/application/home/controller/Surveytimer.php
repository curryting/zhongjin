<?php
/**
 * 调研定时任务
 */
namespace app\home\controller;

use think\Controller;
use app\common\controller\Errcode;
use app\home\logic\SurveyLogic;

class Surveytimer extends Controller
{
	/**
	 * 更新状态
	 */
	public function update()
	{
		SurveyLogic::timerUpdate();
		return ['code'=>Errcode::SUCCESS];
	}
}
