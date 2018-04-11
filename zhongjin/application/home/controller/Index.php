<?php
namespace app\home\controller;

use app\home\logic\CustomerLogic;
use app\common\logic\FollowLogic;
use app\common\logic\RecordLogic;

class Index extends Root
{
    public function index()
    {
		$customerCount = CustomerLogic::countMem();
		$followCount = FollowLogic::count();
		
		$unfollowCount = $customerCount-$followCount;
		
		// 近七天信息发送量
		$msgCount = RecordLogic::countLastSevenDay();
		
		// 昨日用户活跃数和登录次数
		list($activeCount, $loginCount) = RecordLogic::activeAndLogin();
		return $this->resultOk([
			'customerCount'=>$customerCount, 'followCount'=>$followCount,
			'unfollowCount'=>$unfollowCount, 'msgCount'=>$msgCount,
			'activeCount'=>$activeCount, 'loginCount'=>$loginCount
		]);
    }
}
