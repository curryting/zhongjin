<?php
namespace app\weixin\controller;

use app\common\controller\Errcode;
use app\home\logic\ProfileLogic;

class Profile extends Root
{
	public function index()
	{
		$desc = ProfileLogic::get();
		return $this->resultOk(['content'=>htmlspecialchars_decode($desc)]);
	}
	
}
