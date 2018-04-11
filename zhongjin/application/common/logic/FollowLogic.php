<?php
/**
 * 微信关注
 * by sherlock
 */

namespace app\common\logic;

use app\common\model\Follow;
use app\common\controller\Errcode;
use app\home\logic\CustomerLogic;
use my\Weixin;

class FollowLogic{
	
	/**
	 * 新关注时更新用户关注信息 
     * @param string $userid 企业号用户帐号
     * @return true成功;false失败;
	 */
	public static function update($userid)
    {
        $userid = trim($userid);
        if(empty($userid)) return false;
        
        $accessToken = get_accesstoken();
        if(empty($accessToken))    return false;
        
        $qyUserinfo = Weixin::getUser($accessToken, $userid);
		if(isset($qyUserinfo['errcode']) || empty($qyUserinfo))    return false;

        $data = [];
        isset($qyUserinfo['gender']) && $data['gender'] = intval($qyUserinfo['gender']);
        (isset($qyUserinfo['avatar']) && !empty($qyUserinfo['avatar'])) && $data['avatar'] = $qyUserinfo['avatar'];
        (isset($qyUserinfo['status']) && !empty($qyUserinfo['status'])) && $data['status'] = $qyUserinfo['status'];
        (isset($qyUserinfo['weixinid']) && !empty($qyUserinfo['weixinid'])) && $data['weixinid'] = $qyUserinfo['weixinid'];
        
        // 查找对应的用户ID
        $mobile = (isset($qyUserinfo['mobile']) && !empty($qyUserinfo['mobile'])) ? $qyUserinfo['mobile'] : '';
        $memInfo = (empty($mobile) || !is_mobile($mobile)) ? [] : CustomerLogic::getByMobile($mobile, 'id');
        if(empty($memInfo)) {
			// 不是客户 查看是不是管理员
			$adminInfo = UserLogic::getByPhone($mobile, 'id');
			if(! empty($adminInfo)){
				$memid = 0;
				$adminid = $adminInfo['id'];
				$finfo = self::getInfoByAdminid($adminid);
			}else{
				// 既不是客户 又不是管理员，不执行
				return false;
			}
		}else{
			 // 查找用户关注记录
			$adminid = 0;
			$memid = intval($memInfo['id']);
			$finfo = self::getInfoByMemid($memid);
		}
       
		$followModel = new Follow();
        if(empty($finfo)){
            $data['memid'] = $memid;
            $data['adminid'] = $adminid;
            $data['userid'] = $userid;
            $data['create_time'] = NOW_TIME;
            $followModel->addOne($data);
        }else{
            $data['userid'] = $userid;
            $data['update_time'] = NOW_TIME;
			$followModel->updateMore(['id'=>$finfo['id']], $data);
        }

        return true;
    }
	
	/** 更新关注状态 
     * @param string $userid 企业号用户帐号
     * @param int $status 状态
     * @return true成功;false失败;
    */
    public static function setStatus($userid, $status)
    {
        $userid = trim($userid);
        $status = intval($status);
        if(empty($userid) || !in_array($status, [1,2,4])) return false;
        
		$followModel = new Follow();
		$info = $followModel->getMore(['userid'=>$userid], 'id');
        if(empty($info))    return false;
        
		$ids = [];
		foreach($info as $v){
			$ids[] = $v['id'];
		}
		empty($ids) || $followModel->updateMore(['id'=>['in', $ids]], ['status' => $status, 'update_time' => NOW_TIME]);
        
        return true;
    }
	
	/**
	 * 根据memid获取关注信息
	 * @param int $memid	成员ID
	 */
	public static function getInfoByMemid($memid)
	{
		$model = new Follow();
		$find = $model->getOne(['memid'=>$memid], 'id,status');
		return empty($find) ? [] : $find;
	}
	
	/**
	 * 根据userid获取关注信息
	 * @param string $userid	用户ID
	 */
	public static function getInfoByUserid($userid)
	{
		$model = new Follow();
		$find = $model->getOne(['userid'=>$userid, 'status'=>1], 'id,memid,adminid');
		return empty($find) ? [] : $find;
	}
	
	/**
	 * 根据adminid获取关注信息
	 * @param int $adminid		管理员ID
	 */
	public static function getInfoByAdminid($adminid)
	{
		$model = new Follow();
		$find = $model->getOne(['adminid'=>$adminid], 'id,status');
		return empty($find) ? [] : $find;
	}
	
	/**
	 * 根据memids查找对应的userid
	 * @param array $memids		成员ids
	 */
	public static function getUseridByMemids($memids)
	{
		if(empty($memids) || !is_array($memids))
			return [];
		
		$model = new Follow();
	
		$userids = [];
		$_info = $model->getMore(['memid'=>['in', $memids]], 'userid');
		foreach($_info as $v){
			$userids[] = $v['userid'];
		}
		return $userids;
	}
	
	/**
	 * 根据adminids查找对应的userid
	 * @param array $memids		成员ids
	 */
	public static function getUseridByAdminids($adminids)
	{
		if(empty($adminids) || !is_array($adminids))
			return [];
		
		$model = new Follow();
	
		$userids = [];
		$_info = $model->getMore(['adminid'=>['in', $adminids]], 'userid');
		foreach($_info as $v){
			$userids[] = $v['userid'];
		}
		return $userids;
	}
	
	/**
	 * 根据memids获取还没有注册到企业微信的memid
	 * @param array $memids		成员ids
	 */
	public static function getUnsetByMemids($memids)
	{
		$model = new Follow();
		$info = $model->getMore(['memid'=>['in',$memids]], 'memid');
		$issetMemids = [];
		foreach($info as $v){
			$issetMemids[] = $v['memid'];
		}
		
		return array_values(array_diff($memids, $issetMemids));
	}
	
	/**
	 * 根据memids获取成员关注信息
	 * @param array $memids		成员ids
	 */
	public static function getInfoByMemids($memids)
	{
		$model = new Follow();
		$_info = $model->getMore(['memid'=>['in',$memids]], 'memid,userid,avatar');
		$info = [];
		
		foreach($_info as $v){
			$info[$v['memid']] = $v;
		}
		return $info;
	}
	
	/**
	 * 根据adminids获取成员关注信息
	 * @param array $adminids		管理员ids
	 */
	public static function getInfoByAdminids($adminids)
	{
		$model = new Follow();
		$_info = $model->getMore(['adminid'=>['in',$adminids]], 'adminid,userid,avatar');
		$info = [];
		
		foreach($_info as $v){
			$info[$v['adminid']] = $v;
		}
		return $info;
	}
	
	/**
	 * 获取关注人数
	 */
	public static function count()
	{
		$model = new Follow();
		
		return $model->count(['status'=>1, 'memid'=>['gt', 0]]);
	}
}
