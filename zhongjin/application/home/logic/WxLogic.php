<?php
/**
 * 微信发消息操作逻辑层
 * by sherlock
 */

namespace app\home\logic;

use app\common\logic\AgentLogic;
use app\common\logic\FollowLogic;
use app\common\logic\UserLogic;
use app\common\controller\Errcode;
use my\Weixin;

class WxLogic{
	
	/** 
	 * 同步成员到企业号 
     * @param array $memids 需要同步的memids
	 * @param int	$type		应用类型
    */
    public static function contact_sync_qy($memids, $type)
    {
        // 获取accessToken和aid
		$info = self::_sendBefore($type);
		if($info === false) return;

        $filename = self::_createUserFile($info[0], $memids);
		if(empty($filename)) return true;
		//if(empty($filename))  throw new \Exception('生成通讯录模板文件失败', Errcode::CREATE_FILE_FAILE);
        
        $mediaId = Weixin::mediaUpload($info[0], $filename, 'file');
        if(! (is_string($mediaId) && !empty($mediaId))) throw new \Exception('模板文件上传失败', Errcode::FILE_ERR);
        
        unlink($filename);
        $jobid = Weixin::asyncReplaceContact($info[0], $mediaId, empty($memids) ? 1 : 2, []);
        if(is_array($jobid)) throw new \Exception('通讯录同步开启失败！'.$jobid['errmsg'], Errcode::WEIXIN_API_ERR);
        
		trace('wx_async: token: '.$info[0].' jobid: '.$jobid);
        return true;
    }
	
	/** 
	 * 同步成员到企业号 
     * @param array $admins 需要同步的admins
	 * @param int	$type		应用类型
    */
    public static function admin_sync_qy($admins, $type)
    {
        // 获取accessToken和aid
		$info = self::_sendBefore($type);
		if($info === false) return;

        $filename = self::_createUserFileByAdmin($info[0], $admins);
		
		if(empty($filename))  throw new \Exception('生成通讯录模板文件失败', Errcode::CREATE_FILE_FAILE);
        
        $mediaId = Weixin::mediaUpload($info[0], $filename, 'file');
        if(! (is_string($mediaId) && !empty($mediaId))) throw new \Exception('模板文件上传失败', Errcode::FILE_ERR);
        
        unlink($filename);
        $jobid = Weixin::asyncReplaceContact($info[0], $mediaId, empty($admins) ? 1 : 2, []);
        if(is_array($jobid)) throw new \Exception('通讯录同步开启失败！'.$jobid['errmsg'], Errcode::WEIXIN_API_ERR);
        
		trace('wx_async: token: '.$info[0].' jobid: '.$jobid);
        return true;
    }
	
	/**
	 * 发送图文消息 根据管理员IDs
	 * @param array $sendMsg	关联数组 需要发送的基本信息
	 * @param array $adminIds	管理员ids
	 * @param int	$type		应用类型
	 */
	public static function sendNewsMsgByAdmin($sendMsg, $adminIds, $type)
	{
		if(empty($type) || empty($sendMsg) || empty($adminIds)) return;
		
		// 获取accessToken和aid
		$info = self::_sendBefore($type);
		if($info === false) return;
		
		$userids = FollowLogic::getUseridByAdminids($adminIds);
		
		$articles = [];
		foreach($sendMsg as $k => $v){
			$articles[$k]['title'] = $v['title'];
			
			isset($v['desc'])	&& $articles[$k]['description'] = $v['desc'];
			isset($v['picurl']) && $articles[$k]['picurl'] = $v['picurl'];
			isset($v['url']) && $articles[$k]['url'] = $v['url'];
		}
		$articles = array_values($articles);
		
		self::_sendMsg($info[1], $info[0], $userids, $articles);
	}
	
	/**
	 * 发送图文消息
	 * @param array $sendMsg	关联数组 需要发送的基本信息
	 * @param array $memberIds	成员IDs
	 * @param int	$type		应用类型
	 */
	public static function sendNewsMsgByCustom($sendMsg, $memberIds, $type)
	{trace('====='.json_encode($sendMsg));
		$memberIds = array_values(array_filter(array_unique($memberIds)));
		if(empty($type) || empty($sendMsg) || empty($memberIds)) return;
		
		// 获取accessToken和aid
		$info = self::_sendBefore($type);
		if($info === false) return;
		
		$userids = FollowLogic::getUseridByMemids($memberIds);
		if(empty($userids))	 return;

		$articles = [];
		foreach($sendMsg as $k => $v){
			$articles[$k]['title'] = $v['title'];
			
			isset($v['desc'])	&& $articles[$k]['description'] = $v['desc'];
			isset($v['picurl']) && $articles[$k]['picurl'] = $v['picurl'];
			isset($v['url']) && $articles[$k]['url'] = $v['url'];
		}
		$articles = array_values($articles);
		
		self::_sendMsg($info[1], $info[0], $userids, $articles);
	}
	
	/**
	 * 发送文本消息 根据管理员IDs
	 * @param string $text		需要发送的信息
	 * @param array $adminIds	管理员ids
	 * @param int	$type		应用类型
	 */
	public static function sendTextMsgByAdmin($text, $adminIds, $type)
	{
		if(empty($type) || empty($text) || empty($adminIds)) return;
		
		// 获取accessToken和aid
		$info = self::_sendBefore($type);
		if($info === false) return;
		
		$userids = FollowLogic::getUseridByAdminids($adminIds);
		self::_sendMsg($info[1], $info[0], $userids, $text, 1);
	}
	
	/**
	 * 发送文本消息
	 * @param string $text		需要发送的信息
	 * @param array $memberIds	管理员ids
	 * @param int	$type		应用类型
	 */
	public static function sendTextMsgByCustom($text, $memberIds, $type)
	{
		if(empty($type) || empty($text) || empty($memberIds)) return;
		
		// 获取accessToken和aid
		$info = self::_sendBefore($type);
		if($info === false) return;
		
		$userids = FollowLogic::getUseridByMemids($memberIds);
		self::_sendMsg($info[1], $info[0], $userids, $text, 1);
	}
	
	/**
	 * 发送素材消息
	 * @param array $sendMsg	需要发送的信息
	 * @param array $memberIds	管理员ids
	 * @param int	$type		应用类型
	 */
	public static function sendMediaMsgByCustom($sendMsg, $memberIds, $type)
	{
		if(empty($type) || empty($sendMsg) || empty($memberIds)) return;
		
		// 获取accessToken和aid
		$info = self::_sendBefore($type);
		if($info === false) return;
		
		// 上传素材读取mediaid
		$sendMsg['type'] = strtolower($sendMsg['type']);
		switch($sendMsg['type']){
			case 'png':
			case 'jpg':
			case 'jpeg':
				$fileType = 2;
				$uploadType = 'image';
				break;
			case 'amr':
				$fileType = 4;
				$uploadType = 'voice';
				break;
			case 'mp4':
				$fileType = 5;
				$uploadType = 'video';
				break;
			default:
				$fileType = 3;
				$uploadType = 'file';
				break;
		}
		
		$mediaId = Weixin::mediaUpload($info[0], $sendMsg['path'], $uploadType, $sendMsg['origin_name']);
		if(! empty($mediaId)){
			$userids = FollowLogic::getUseridByMemids($memberIds);
			empty($userids) || self::_sendMsg($info[1], $info[0], $userids, $mediaId, $fileType);
		}
		return $mediaId;
	}
	
	/**
	 * 获取accessToken和aid
	 * @param string $type	应用类型
	 */
	private static function _sendBefore($type)
	{
		// 读取应用ID
		$aid = AgentLogic::getAidByType($type);
		if($aid == 0) return false;
		
		$accessToken = get_accesstoken($aid);
        if(empty($accessToken)) return false;
		
		return [$accessToken, $aid];
	}

	/**
	 * 发送消息
	 * @param int				$aid			应用ID
	 * @param string			$accessToken	
	 * @param array				$userid			关联数组 需要发送的对象
	 * @param array|string		$articles		发送的内容
	 * @param int				$type			0:发送图文消息 1：发送文本消息
	 */
	private static function _sendMsg($aid, $accessToken, $userids, $articles, $type=0)
	{
		$userids = array_chunk($userids, 1000);
		foreach($userids as $value){
			$users['touser'] = $value;
			switch($type){
				case 0:
					Weixin::sendNewsMsg($accessToken, $articles, $users, $aid);
					break;
				case 1: // 文本 $articles表示文字
					Weixin::sendTextMsg($accessToken, $articles, $users, $aid);
					break;
				case 2: // 图片 $articles表示mediaid
					Weixin::sendImageMsg($accessToken, $articles, $users, $aid);
					break;
				case 3: // 文件 $articles表示mediaid
					Weixin::sendFileMsg($accessToken, $articles, $users, $aid);
					break;
				case 4: // 语音 $articles表示mediaid
					Weixin::sendVoiceMsg($accessToken, $articles, $users, $aid);
					break;
				case 5: // 视频 $articles表示mediaid
					Weixin::sendVideoMsg($accessToken, $articles, '', '', $users, $aid);
					break;
				default :
					break;
			}
		}
	}

	 /** 
	  * 生成通讯录成员模板文件 
	  * @param string	$accrssToken
	  * @param array	$memids		用户IDs
	  */
    private static function _createUserFile($accessToken, $memids=[])
    {
		// 读取用户成员，返回已经不存在通讯录中的memids
		list($_memInfo, $unsetMemids) = CustomerLogic::getUnsetByMemids($memids);

        // 读取用户关注信息
		$followInfo = FollowLogic::getInfoByMemids($memids);
	
		$memInfo = [];
		foreach($_memInfo as $k => $v){
			$memInfo[$k]['title'] = $v['name'];
			$memInfo[$k]['mobile'] = $v['mobile'];
			$memInfo[$k]['userid'] = isset($followInfo[$k]['userid']) ? 
				$followInfo[$k]['userid'] : create_userid_by_name_mobile($v['name'], $v['mobile']);
		}
		unset($_memInfo);
		
		// 从企业号删除不在通讯录的成员
		$memDel = [];
		foreach($unsetMemids as $v){
			(isset($followInfo[$v]) && ! empty($followInfo[$v]['userid'])) && 
				$memDel[] = $followInfo[$v]['userid'];
		}
		if(! empty($memDel)){
			Weixin::batchdeleteUser($accessToken, $memDel);
		}
        unset($memDel);
        unset($unsetMemids);
        
        // 没有用户直接返回
        if(count($memInfo) <= 0)   return '';
    
        $content = "姓名,帐号,手机号,邮箱,所在部门\n";
        foreach($memInfo as $k => $v) {
            if(!empty($v['title']) && !empty($v['userid']) && !empty($v['mobile']) && is_mobile($v['mobile'])) {
                $content .= "{$v['title']},{$v['userid']},{$v['mobile']},,1,\n";
            }
        }
        unset($memInfo);
        
        return create_tmp_file("batch_users.csv", $content);
    }
	
	/** 
	  * 生成通讯录成员模板文件 
	  * @param string	$accrssToken
	  * @param array	$adminids		管理员IDs
	  */
    private static function _createUserFileByAdmin($accessToken, $adminids=[])
    {
		// 读取用户成员，返回已经不存在通讯录中的memids
		list($_adminInfo, $unsetAdminids) = UserLogic::getUnset($adminids);

        // 读取用户关注信息
		$followInfo = FollowLogic::getInfoByAdminids($adminids);
	
		$adminInfo = [];
		foreach($_adminInfo as $k => $v){
			$adminInfo[$k]['title'] = $v['name'];
			$adminInfo[$k]['mobile'] = $v['mobile'];
			$adminInfo[$k]['userid'] = isset($followInfo[$k]['userid']) ? 
				$followInfo[$k]['userid'] : create_userid_by_name_mobile($v['name'], $v['mobile']);
		}
		unset($_adminInfo);
		
		// 从企业号删除不在通讯录的成员
		$adminDel = [];
		foreach($unsetAdminids as $v){
			(isset($followInfo[$v]) && ! empty($followInfo[$v]['userid'])) && 
				$adminDel[] = $followInfo[$v]['userid'];
		}
		if(! empty($adminDel)){
			Weixin::batchdeleteUser($accessToken, $adminDel);
		}
        unset($adminDel);
        unset($unsetAdminids);
        
        // 没有管理员直接返回
        if(count($adminInfo) <= 0)   return '';
    
        $content = "姓名,帐号,手机号,邮箱,所在部门\n";
        foreach($adminInfo as $k => $v) {
            if(!empty($v['title']) && !empty($v['userid']) && !empty($v['mobile']) && is_mobile($v['mobile'])) {
                $content .= "{$v['title']},{$v['userid']},{$v['mobile']},,1,\n";
            }
        }
        unset($adminInfo);
        
        return create_tmp_file("batch_users.csv", $content);
    }
}
