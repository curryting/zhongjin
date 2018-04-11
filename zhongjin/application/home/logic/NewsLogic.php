<?php
/**
 * 新闻操作逻辑层
 * by sherlock
 */

namespace app\home\logic;

use think\Db;
use app\common\model\News;
use app\common\model\File;
use app\common\model\User;
use app\common\model\Customer;
use app\common\model\Follow;
use app\common\logic\AgentLogic;
use app\common\controller\Errcode;

define('APP_ID', 1);

class NewsLogic{
	
	/**
	 * 添加
	 * @param array $list	基本参数
	 * @param int $type		0: 发全部 1： 发具体的成员
	 */
	public static function add($list, $memids)
	{
		//开启事务
		Db::startTrans();
		try {
			list($sendMsg, $fileIds) = self::_add($list, $memids);
			
			// 图片地址解析
			$fileIds = array_values(array_filter(array_unique($fileIds)));
			if(! empty($fileIds)){
				$fileModel = new File();
				$_info = $fileModel->getMore(['id'=>['in', $fileIds]], 'id,http_path');
				
				$info = [];
				foreach($_info as $v){
					$info[$v['id']] = APP_HTTP.TLD.'.'.WEB_DOMAIN.$v['http_path'];
				}
				unset($_info);
				
				foreach($list as $k => $v){
					isset($info[$v['cover_id']]) && $sendMsg[$k]['picurl'] = $info[$v['cover_id']];
				}
			}
			
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return false;
		}
		
		// 发送微信消息
		WxLogic::sendNewsMsgByCustom($sendMsg, $memids, 'news');
		return true;
	}
	
	/**
	 * 列表
	 * @param string $keyword	关键字
	 * @param int $type			0:全部 1:项目动态，2:精选文摘,3:公司动态
	 * @param int $offset
	 * @param int $pasize
	 */
	public static function lists($keyword='', $type=0, $offset=0, $psize=10)
	{
		$where = ['is_del'=>0];
		$type == 0 || $where['type'] = $type;
		empty($keyword) || $where['title'] = ['like', "%$keyword%"];
		
		$model = new News();
		$userids = [];
		$list = $model->getBaseMorePage($where, 'id,create_time,userid,title,type', $offset, $psize);
		foreach($list as &$v){
			$userids[] = $v['userid'];
			$v['time'] = date('Y-m-d H:i:s', $v['create_time']);
			$v['news_type_id'] = $v['type'];
			unset($v['type']);
			unset($v['create_time']);
		}
		unset($v);
		
		// 读取发送人的信息
		$userids = array_values(array_filter(array_unique($userids)));
		if(! empty($userids)){
			$userModel = new User();
			$_userInfo = $userModel->getMore(['id'=>['in', $userids]], 'id,name');
			foreach($_userInfo as $v){
				$userInfo[$v['id']] = $v['name'];
			}
			unset($_userInfo);
			
			foreach($list as &$v){
				$v['author'] = isset($userInfo[$v['userid']]) ? $userInfo[$v['userid']] : '';
				unset($v['userid']);
			}
		}
		
		$count = $model->countBase($where);
		
		return [empty($list) ? [] : $list, $count];
	}
	
	/**
	 * 删除
	 * @param int $id	记录ID
	 */
	public static function del($id)
	{
		$model = new News();
		
		Db::startTrans();
		try{
			$model->updateBase(['id'=>$id], ['is_del'=>1]);
			$model->updateMemberMore(['news_id'=>$id], ['is_del'=>1]);
			Db::commit();
		}catch(\Exception $e){
			Db::rollback();
			return false;
		}
		
		return true;
	}

	/**
	 * 详情
	 * @param int $id	记录ID
	 */
	public static function detail($id)
	{
		$model = new News();
		
		$info = [];
		// 获取基本信息
		$baseInfo = $model->getBaseOne(['id'=>$id], 'title,create_time,userid,is_del');
		if(empty($baseInfo)) throw new \Exception('新闻不存在', Errcode::NEWS_UNSET);
		if($baseInfo['is_del']) throw new \Exception('新闻已删除', Errcode::NEWS_DEL);
			
		// 获取发送人
		$author = '';
		if($baseInfo['userid'] >0){
			$userModel = new User();
			$userInfo = $userModel->getOne(['id'=>$baseInfo['userid']], 'name');
			$author = $userInfo['name'];
		}
		// 获取附加信息
		$extraInfo = $model->getExtraOne(['news_id'=>$id], 'abstract,content');
		
		// 读取已读人数
		$readCount = $model->countMember(['news_id'=>$id, 'is_read'=>1]);
		// 读取未读人数
		$unreadCount = $model->countMember(['news_id'=>$id, 'is_read'=>0]);
		
		return [
			'id' => $id, 'title'=> htmlspecialchars_decode($baseInfo['title']),
			'author' => $author, 'timestamp' => date('Y-m-d H:i:s', $baseInfo['create_time']),
			'abstract' => htmlspecialchars_decode($extraInfo['abstract']),
			'content' => htmlspecialchars_decode($extraInfo['content']),
			'had_read' => $readCount, 'not_read' => $unreadCount
		];
	}

	/**
	 * 获取一个新闻的已读或者未读
	 * @param int $id	记录ID
	 * @param int $type	0:读取未读 1:读取已读
	 */
	public static function member($id, $type)
	{
		$model = new News();
		
		$canRemind = 1;
		if($type == 0){
			// 更新提醒状态
			$baseInfo = $model->getBaseOne(['id'=>$id], 'update_time,is_remind');
			if($baseInfo['is_remind'] == 1){
				// 已经提醒过 并且时间是否已经过去半个小时
				if(NOW_TIME - $baseInfo['update_time'] >60*30){
					$model->updateBase(['id'=>$id], ['is_remind'=>0]);
				}else{
					$canRemind = 0;
				}
			}
		}

		$info = $model->getMemberMore(['news_id' => $id, 'is_read' => $type], 'memid');
		
		$memids = $members = [];
		foreach($info as $v){
			$memids[] = $v['memid'];
		}
		
		// 读取名称
		if(! empty($memids)){
			$model = new Customer();
			$info = $model->getCustomerMore(['id'=>['in', $memids]], 'name');
			foreach($info as $v){
				$members[] = $v['name'];
			}
		}
		
		return [$members, $canRemind];
	}
	
	/**
	 * 提醒
	 * @param int $id	记录ID
	 */	
	public static function remind($id)
	{
		$model = new News();
		$aid = AgentLogic::getAidByType('notice');
		
		// 读取未读的人
		$member = $model->getMemberMore(['news_id'=>$id, 'is_read'=>0, 'is_del'=>0], 'memid');
		$memids = [];
		foreach($member as $v){
			$memids[] = $v['memid'];
		}
		$memids = array_values(array_filter(array_unique($memids)));
		
		if(! empty($memids)){
			WxLogic::sendNewsMsgByCustom([[
				'title' => '收到新的新闻内容，请注意查看',
				'url'	 => APP_HTTP.TLD.'.'.WEB_DOMAIN.'/weixin/index.html#/news/'.$id.'/detail/1/aid/'.$aid
			]], $memids, 'news');
		}
		
		// 设置为已提醒状态
		$model->updateBase(['id'=>$id], ['is_remind'=>1, 'update_time'=>NOW_TIME]);
		return true;
	}

	/**
	 * 预览
	 * @param array $list	基本参数
	 */
	public static function preview($list)
	{
		//开启事务
		Db::startTrans();
		try {
			list($sendMsg, $fileIds) = self::_addPreview($list);
			
			// 图片地址解析
			$fileIds = array_values(array_filter(array_unique($fileIds)));
			if(! empty($fileIds)){
				$fileModel = new File();
				$_info = $fileModel->getMore(['id'=>['in', $fileIds]], 'id,http_path');
				
				$info = [];
				foreach($_info as $v){
					$info[$v['id']] = APP_HTTP.TLD.'.'.WEB_DOMAIN.$v['http_path'];
				}
				unset($_info);
				
				foreach($list as $k => $v){
					isset($info[$v['cover_id']]) && $sendMsg[$k]['picurl'] = $info[$v['cover_id']];
				}
			}
			
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return false;
		}
		
		// 发送微信消息
		WxLogic::sendNewsMsgByAdmin($sendMsg, [ADMINID], 'news');
		return true;
	}
	
	/**
	 * 读取评论获取回复
	 * @param int $id		读取评论是表示新闻ID	读取回复时表示评论ID
	 * @param int $offset	偏移量
	 * @param int $psize	一页大小
	 * @param int $type		0：读取评论 1:读取回复
	 */
	public static function readCommentReply($id, $type, $offset=0, $psize=10)
	{
		if($type == 0){
			// 读取评论
			$where = ['news_id'=>$id, 'type'=>0];
			$field = 'id,create_time,memid,admin_id,content';
			$order = 'id DESC';
		}else{
			// 读取回复
			$where = ['parent_id'=>$id, 'type'=>1];
			$field = 'create_time,memid,admin_id,to_memid,to_adminid,content';
			$order = 'id ASC';
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
			$_followInfo = $followModel->getMore(['adminid'=>['in', $adminids]], 'adminid,avatar');
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
	 * 添加到表
	 * @param array $list	数据
	 * @param array $memids	成员
	 */
	private static function _add($list, $memids)
	{
		// 读取应用ID
		$aid = AgentLogic::getAidByType('news');
		
		$sendMsg = $fileIds = [];
		$model = new News();
		
		foreach($list as $k => $v){
			$fileIds[] = $v['cover_id'];
			// 添加基本数据
			$baseId = $model->addBaseOne([
				'userid'	=> ADMINID,
				'title'		=> $v['title'],
				'imgid'		=> $v['cover_id'],
				'type'		=> $v['news_type'],
				'is_push'	=> $v['sync'],
				'create_time'	=> NOW_TIME
			]);

			// 添加附加信息
			$model->addExtraOne([
				'news_id'	=> $baseId,
				'abstract'	=> $v['abstract'],
				'content'	=> $v['content'],
			]);

			// 添加成员信息
			$memData = [];
			foreach($memids as $v1){
				$memData[] = [
					'news_id'	=> $baseId,
					'memid'		=> $v1,
					'type'		=> $v['news_type']
				];
			}
			$model->addMemberMore($memData);

			// 发送微信消息信息
			$sendMsg[$k] = [
				'title'		=> $v['title'],
				'url'		=> APP_HTTP.TLD.'.'.WEB_DOMAIN.'/weixin/index.html#/news/'.$baseId.'/detail/1/aid/'.$aid,
				'desc'		=> mb_substr($v['abstract'], 0, 50, 'UTF-8')
			];
		}
		
		return [$sendMsg, $fileIds];
	}
	
	/**
	 * 预览添加到表
	 * @param array $list	数据
	 */
	private static function _addPreview($list)
	{
		// 读取应用ID
		$aid = AgentLogic::getAidByType('news');
		$sendMsg = $fileIds = [];
		$model = new News();
		
		foreach($list as $k => $v){
			$fileIds[] = $v['cover_id'];
			// 添加基本数据
			$baseId = $model->addPreviewOne([
				'title'		=> $v['title'],
				'imgid'		=> $v['cover_id'],
				'abstract'	=> $v['abstract'],
				'content'	=> $v['content'],
				'userid'	=> ADMINID,
				'create_time' => NOW_TIME
			]);

			// 发送微信消息信息
			$sendMsg[$k] = [
				'title'		=>	$v['title'],
				'url'		=> APP_HTTP.TLD.'.'.WEB_DOMAIN.'/weixin/index.html#/news/'.$baseId.'/detail/0/aid/'.$aid,
			];
		}
		
		return [$sendMsg, $fileIds];
	}
}
