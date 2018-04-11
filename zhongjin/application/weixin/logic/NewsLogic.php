<?php
/**
 * 新闻操作逻辑层
 * by sherlock
 */

namespace app\weixin\logic;

use think\Db;
use app\common\controller\Errcode;
use app\common\logic\AgentLogic;
use app\common\model\News;
use app\common\model\File;
use app\common\model\Customer;
use app\common\model\Follow;
use app\common\model\User;
use app\home\logic\WxLogic;

class NewsLogic{
	
	/**
	 * 读取全部
	 * @param int $type		1:项目动态，2:精选文摘,3:公司动态
	 * @param int $offset	偏移量
	 * @param int $psize	一页大小
	 * @param int $memid	成员ID 0表示是管理员读取
	 */
	public static function lists($type, $offset=0, $psize=10, $memid=0)
	{
		$newsIds = $lists = [];
		$model = new News();
		if($memid != 0){
			// 成员读取
			$where = ['memid'=>$memid, 'type'=>$type, 'is_del'=>0];
			$_list = $model->getMemberMorePage($where, 'news_id', $offset, $psize);
			foreach($_list as $v){
				$newsIds[] = $v['news_id'];
			}
			unset($_list);
			$newsIds = array_values(array_filter(array_unique($newsIds)));
			$where = ['type'=>$type, 'is_del'=>0, 'id'=>['in', $newsIds]];
			$lists = $model->getBaseMore($where, 'id,create_time,title,imgid');
		}else{
			$where = ['type'=>$type, 'is_del'=>0];
			$lists = $model->getBaseMorePage($where, 'id,create_time,title,imgid', $offset, $psize);
		}

		$imgids = [];
		foreach($lists as $v){
			$imgids[] = $v['imgid'];
		}
		
		// 查找图片地址
		$imgids = array_values(array_filter(array_unique($imgids)));
		if(! empty($imgids)){
			$fileModel = new File();
			$_imgInfo = $fileModel->getMore(['id'=>['in', $imgids]], 'id,http_path');
			foreach($_imgInfo as $v){
				$imgInfo[$v['id']] = APP_HTTP.TLD.'.'.WEB_DOMAIN.$v['http_path'];
			}
			unset($_imgInfo);
		}
		
		foreach($lists as &$v){
			$v['timestamp'] = date('Y-m-d H:i:s', $v['create_time']);
			$v['imgurl'] = isset($imgInfo[$v['imgid']]) ? $imgInfo[$v['imgid']] : '';
			unset($v['imgid']);
			unset($v['create_time']);
		}
		
		return $lists;
	}
	
	/**
	 * 详情
	 * @param int $id	新闻ID
	 */
	public static function detail($id)
	{
		$model = new News();
		$customModel = new Customer();
		$followModel = new Follow();
		// 获取基本信息
		$baseInfo = $model->getBaseOne(['id'=>$id], 'title,create_time,userid,is_del');
		if(empty($baseInfo)) throw new \Exception('新闻不存在', Errcode::NEWS_UNSET);
		if($baseInfo['is_del']) throw new \Exception('新闻已删除', Errcode::NEWS_DEL);
		
		if(intval(MEMID) > 0){
			// 客户进来变成已读
			$model->updateMemberMore(['news_id'=>$id, 'memid'=>MEMID], ['is_read'=>1]);
			// 客户读取本人信息
			$memInfo = $customModel->getCustomerOne(['id'=>MEMID], 'name');
			$followInfo = $followModel->getOne(['memid'=>MEMID], 'avatar');
		}else{
			// 管理员读取本人信息
			$userModel = new User();
			$memInfo = $userModel->getOne(['id'=>ADMINID], 'name');
			$followInfo = $followModel->getOne(['adminid'=>ADMINID], 'avatar');
		}
		
		$info = [];		
		// 获取发送人
		$author = '';
		if($baseInfo['userid'] >0){
			$userModel = new User();
			$userInfo = $userModel->getOne(['id'=>$baseInfo['userid']], 'name');
			$author = $userInfo['name'];
		}
		// 获取附加信息
		$extraInfo = $model->getExtraOne(['id'=>$id], 'abstract,content');
	
		return [
			'isAdmin' => intval(ADMINID) >0 ? 1 : 0,
			'memid'	 => intval(MEMID), 'admin_id' => intval(ADMINID),
			'id' => $id, 'title'=> htmlspecialchars_decode($baseInfo['title']),
			'name' => isset($memInfo['name']) ? $memInfo['name'] : '',
			'avatar' => isset($followInfo['avatar']) ? $followInfo['avatar'] : '',
			'author' => $author, 'timestamp' => date('Y-m-d H:i:s', $baseInfo['create_time']),
			'abstract' => htmlspecialchars_decode($extraInfo['abstract']),
			'content' => htmlspecialchars_decode($extraInfo['content'])
		];
	}
	
	/**
	 * 预览详情
	 * @param int $id	记录ID
	 */
	public static function previewDetail($id)
	{
		$model = new News();

		$info = [];
		// 获取基本信息
		$info = $model->getPreviewOne(['id'=>$id]);
		// 获取发送人
		$author = '';
		if($info['userid'] >0){
			$userModel = new User();
			$userInfo = $userModel->getOne(['id'=>$info['userid']], 'name');
			$author = $userInfo['name'];
		}
		
		return [
			'id' => $id, 'title'=> htmlspecialchars_decode($info['title']),
			'name' => $author, 'timestamp' => date('Y-m-d H:i:s', $info['create_time']),
			'abstract' => htmlspecialchars_decode($info['abstract']),
			'content' => htmlspecialchars_decode($info['content'])
		];
	}
	
	/**
	 * 读取评论获取回复
	 * @param int $id			读取评论是表示新闻ID	读取回复时表示评论ID
	 * @param int $type			0：读取评论 1:读取回复
	 * @param int $offset		偏移量
	 * @param int $psize		一页大小
	 * @param int $memid		成员ID 0表示管理员，读取全部 
	 */
	public static function readCommentReply($id, $type, $offset=0, $psize=10, $memid=0)
	{
		if($type == 0){
			// 读取评论
			$where = ['news_id'=>$id, 'type'=>0];
			$memid !=0 && $where['memid'] = $memid;
			$field = 'id,create_time,memid,admin_id,content';
			$order = 'id DESC';
		}else{
			// 读取回复
			$where = ['parent_id'=>$id, 'type'=>1];
			$field = 'create_time,memid,admin_id,to_memid,to_adminid,content';
			$order = 'id DESC';
		}

		// 读取评论列表
		$model = new News();
		$list = $model->getCommentMorePage($where, $field, $offset, $psize, $order);
		
		// 读取姓名和图像
		$memids = $adminids = $ids = [];
		foreach($list as $v){
			$memids[] = $v['memid'];
			isset($v['to_memid']) && $memids[] = $v['to_memid'];
			$adminids[] = $v['admin_id'];
			isset($v['to_adminid']) && $adminids[] = $v['to_adminid'];
			$type == 0 && $ids[] = $v['id'];
		}
		$memids = array_values(array_filter(array_unique($memids)));
		$adminids = array_values(array_filter(array_unique($adminids)));
		
		if(! empty($memids)){
			$customModel = new Customer();
			$_memInfo = $customModel->getCustomerMore(['id'=>['in', $memids]], 'id,name');
			foreach($_memInfo as $v){
				$memInfo[$v['id']] = $v['name'];
			}
			unset($_memInfo);
			
			$followModel = new Follow();
			$_followInfo = $followModel->getMore(['memid'=>['in', $memids]], 'memid,avatar');
			foreach($_followInfo as $v){
				$followInfo['memid'][$v['memid']] = $v['avatar'];
			}
			unset($_followInfo);
		}
		
		if(! empty($adminids)){
			$userModel = new User();
			$_userInfo = $userModel->getMore(['id'=>['in', $adminids]], 'id,name');
			foreach($_userInfo as $v){
				$userInfo[$v['id']] = $v['name']; 
			}
			unset($_userInfo);
			
			isset($followModel) || $followModel = new Follow(); 
			$_followInfo = $followModel->getMore(['adminid'=>['in',$adminids]], 'adminid,avatar');
			foreach($_followInfo as $v){
				$followInfo['adminid'][$v['adminid']] = $v['avatar'];
			}
			unset($_followInfo);
		}
		
		if($type == 0){
			// 查看这条评论是否有回复
			if(! empty($ids)){
				$_replyInfo = $model->countCommentGroup($ids);
				foreach($_replyInfo as $v){
					$replyInfo[$v['parent_id']] = $v['count'];
				}
				unset($_replyInfo);
			}
		}
		
		foreach($list as &$v){
			$v['timestamp'] = format_pub_time($v['create_time']);
			$v['content'] = htmlspecialchars_decode($v['content']);
			$v['name'] = $v['to_name'] = $v['avatar'] = $v['to_avatar'] = '';
			
			// 读取回复人信息
			if(! empty($v['memid'])){
				isset($memInfo[$v['memid']]) && $v['name'] = $memInfo[$v['memid']];
				isset($followInfo['memid'][$v['memid']]) && $v['avatar'] = $followInfo['memid'][$v['memid']];
			}else if(! empty($v['admin_id'])){
				isset($userInfo[$v['admin_id']]) && $v['name'] = $userInfo[$v['admin_id']];
				isset($followInfo['adminid'][$v['admin_id']]) && $v['avatar'] = $followInfo['adminid'][$v['admin_id']];
			}
			
			// 读取被回复的信息
			if(! empty($v['to_memid'])){
				isset($memInfo[$v['to_memid']]) && $v['to_name'] = $memInfo[$v['to_memid']];
				isset($followInfo['memid'][$v['to_memid']]) && $v['to_avatar'] = $followInfo['memid'][$v['to_memid']];
			}else if(! empty($v['to_adminid'])){
				isset($userInfo[$v['to_adminid']]) && $v['to_name'] = $userInfo[$v['to_adminid']];
				isset($followInfo['adminid'][$v['to_adminid']]) && $v['to_avatar'] = $followInfo['adminid'][$v['to_adminid']];
			}
			
			if($type == 0){
				$v['reply_count'] = isset($replyInfo[$v['id']]) ? $replyInfo[$v['id']] : 0;
			}
			
			unset($v['to_memid']);
			unset($v['to_adminid']);
			unset($v['create_time']);
		}
		unset($memInfo);
		unset($followInfo);
		
		return $list;
	}
	
	/**
	 * 评论或者回复
	 * @param int $id			新闻ID
	 * @param int $memid		客户id
	 * @param string $content	内容
	 * @param int $type			回复类型(0:评论 1:回复)
	 * @param int $parentId		回复时，回复的评论ID
	 */
	public static function comment($id, $content, $memid=0, $adminId=0, $type=0, 
		$parentId=0, $toMemid=0, $toAdminid=0)
	{
		$model = new News();
		
		$commentId = $model->addCommentOne([
			'create_time'		=> NOW_TIME,
			'news_id'			=> $id,
			'memid'				=> $memid,
			'to_memid'			=> $toMemid,
			'to_adminid'		=> $toAdminid,
			'admin_id'			=> $adminId,
			'content'			=> $content,
			'parent_id'		=> $parentId,
			'type'				=> $type
		]);
		
		// 发送消息
		$aid = intval(session('agentid')) >0 ? intval(session('agentid')) : AgentLogic::getAidByType('news');
		$url = APP_HTTP.TLD.'.'.WEB_DOMAIN.'/weixin/index.html#/news/'.$id.'/detail/1/aid/'.$aid;
		if($type == 0){
			$content = "您有新的新闻评论！请注意<a href='{$url}'>查收</a>";
			$info = $model->getBaseOne(['id'=>$id], 'userid');
			empty($info['userid']) || WxLogic::sendTextMsgByAdmin($content, [$info['userid']], 'news');
		}else{
			$content = "您有新的新闻回复！请注意<a href='{$url}'>查收</a>";
			if($toMemid != 0){
				WxLogic::sendTextMsgByCustom($content, [$toMemid], 'news');
			}else if($toAdminid != 0){
				WxLogic::sendTextMsgByAdmin($content, [$toAdminid], 'news');
			}else{
				// 发送到第一条评论人身上
				$info = $model->getCommentOne(['id'=>$parentId], 'memid,admin_id');
				empty($info['memid']) || WxLogic::sendTextMsgByCustom($content, [$info['memid']], 'news');
				empty($info['admin_id']) || WxLogic::sendTextMsgByAdmin($content, [$info['admin_id']], 'news');
			}
		}
		
		return $commentId >=0 ? $commentId : false;
	}
}
