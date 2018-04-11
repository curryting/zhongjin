<?php
namespace app\home\controller;

use app\home\logic\ProfileLogic;
use app\common\controller\Errcode;

class Profile extends Root
{
    public function update()
    {
		$content = input('param.profile', '', 'htmlspecialchars');
		empty($content) && $this->result('', Errcode::PARAM_ERR, '参数错误');
		$res = ProfileLogic::update($content);
		$res || $this->result('', Errcode::UPDATE_ERR, '更新失败');
		return $this->resultOk();
    }
	
	/**
	 * 获取详情
	 */
	public function detail()
	{
		$desc = ProfileLogic::get();
		return $this->resultOk(['content'=>htmlspecialchars_decode($desc)]);
	}
}
