<?php

namespace app\weixin\controller;

use think\Controller;
class Test extends Controller{
	
	public function index()
	{
		$res = cache('111',233);
		return $res;
	}
	
	public function index1()
	{
		$res = cache('111');
		return $res;
	}
}

