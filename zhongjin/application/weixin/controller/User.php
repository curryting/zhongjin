<?php
namespace app\weixin\controller;

use app\common\controller\Errcode;
use app\common\logic\UserLogic;
use app\common\logic\FollowLogic;
use app\common\logic\RecordLogic;
use think\Request;
use my\Weixin;

class User extends Root
{
	protected function _initialize()
	{
	}
	
	/**
	 * 登录
	 */
    public function login()
    {
		$aid = input('param.aid', -1, 'intval');
		$this->_oauthUserInfo($aid);
		// 登录成功,跳转至登录失效的页面, 前台页面地址必须是 /weixin/user/login/aid/1/callback/http:地址   callback必须放到最后面
		$url = Request::instance()->server('REQUEST_URI');
		$callback_start_pos = strpos($url, 'callback');
		$callback_end_pos = strpos($url, '?');
		if($callback_end_pos === false){
			$url = substr($url, $callback_start_pos+9);
		}else{
			$url = substr($url, $callback_start_pos+9, $callback_end_pos-$callback_start_pos-9);
		}
		$url = urldecode($url);
		$this->redirect($url);
    }
	
	/**
	 * 是否已经登录
	 */
	public function isLogin()
	{
		$memid = session('customer');
		$adminid = session('admin');
		if(empty($memid) && empty($adminid)){
			return ['code'=>Errcode::LOGIN_INVALID];
		}
		
		return ['code'=>Errcode::SUCCESS];
	}
	
	/**
	 * 验证用户信息
	 * @param int $agentid 应用ID
	 */
    private function _oauthUserInfo($agentid=-1)
    {
		$userid = intval(session('user'));
		if($userid <= 0) {
			$userid = 0;
			$openid = '';
			$deviceid = '';
			$code = input('param.code', '', 'trim');
			
			if(empty($code)) {
				// code 不存在，跳转至微信地址拿code
				$corpid = Wxhandle::CORPID;
				$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
				$url = urlencode($url);
				$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$corpid}&redirect_uri=$url&response_type=code&scope=snsapi_base#wechat_redirect";
				$this->result(['url'=>$url], Errcode::SUCCESS);
				// $this->redirect($url);
			}
			
			// 微信回调此地址带code
			$accessToken = get_accesstoken($agentid);
			empty($accessToken) && $this->result('', Errcode::INVALID_ACCESS_TOKEN, '无效的Accesstoken');
			
			$userInfo = Weixin::getUserinfo($accessToken, $code);
			isset($userInfo['errcode']) && $this->result('', $userInfo['errcode'], '校验失败, ' .$userInfo['errmsg']);

			if(isset($userInfo['UserId'])) {
				$res = FollowLogic::getInfoByUserid($userInfo['UserId']);
				empty($res) && $this->result('', Errcode::USER_UNSET, '请先在管理后台添加成客户或者成为管理员，并重新关注');
				
				($res['memid'] <=0 && $res['adminid'] <=0) && $this->result('', Errcode::USER_UNSET, '用户不存在');
			
				// 为一个用户记录状态
				$res['memid'] >0 && session('customer', $res['memid']);
				$res['adminid'] >0 && session('admin', $res['adminid']);

				$res['memid'] > 0 && $this->_recordLoginCount($res['memid']);
			}else{
				$this->result('', Errcode::NO_AUTH, '请先关注企业号');
			}
        }
    }
	
	
	/**
	 * 记录用户登录次数
	 * @param  int $memid 客户ID
	 */
	private function _recordLoginCount($memid)
	{
		$time = date('Y-m-d');
		$time = strtotime($time);
		RecordLogic::addLogin(['memid'=>$memid, 'create_time'=>$time]);
	}
}
