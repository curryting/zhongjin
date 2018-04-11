<?php
/**
 * 通知操作逻辑层
 * by sherlock
 */

namespace app\home\logic;

use think\Db;
use app\common\controller\Errcode;
use app\common\model\Notice;
use app\common\model\File;
use app\common\model\User;
use app\common\model\Customer;
use app\common\model\Follow;
use app\common\logic\AgentLogic;

define('APP_ID', 2);

class NoticeLogic{
	
	/**
	 * 添加
	 * @param array $list	基本参数
	 * @param int $memids	发送的成员
	 * @param int $type		消息类型(1:图文，2:文字,3:文件)	
	 * @param int $timeout	定时发送 0 马上发送， 具体时间具体时间发送
	 * @param string $sendObj 选择发送对象 定时发送时有用
	 */
	public static function add($list, $memids, $type, $timeout=0, &$sendObj='')
	{
		switch($type){
			case 1:
				try{
					list($sendMsg, $fileIds) = self::_addNews($list, $memids, $timeout, $sendObj);
				}catch(\Exception $e){
					return false;
				}

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
				// 发送微信消息
				$timeout == 0 && WxLogic::sendNewsMsgByCustom($sendMsg, $memids, 'notice');
				break;
			case 2:
				try{
					list($id, $aid) = self::_addText($list['title'], $list['content'], $list['sync'], $memids, $timeout, $sendObj);
				}catch(\Exception $e){
					return false;
				}
				// 发送微信消息
				$sendMsg = [
					'title'		=> $list['title'],
					'desc'		=> mb_substr($list['content'], 0, 50, 'UTF-8') . '...',
					'url'		=> APP_HTTP.TLD.'.'.WEB_DOMAIN.'/weixin/index.html#notice/'.$id.'/detail/1/2/aid/'.$aid,
				];
				$timeout == 0 && WxLogic::sendNewsMsgByCustom([$sendMsg], $memids, 'notice');
				break;
			case 3:
				$fileModel = new File();
				// 读取文件的原始名称
				$fileInfo = $fileModel->getOne(['id'=>$list['fileId']], 'origin_name,path,ext');
				try{
					$id = self::_addFile($list['fileId'], $fileInfo['origin_name'], $list['sync'], $memids, $timeout, $sendObj);
				}catch(\Exception $e){
					return false;
				}
				
				// 发送微信消息
				$timeout == 0 && self::_sendFile($id, $fileInfo['origin_name'], $fileInfo['path'], $fileInfo['ext'], $memids);
				break;
			default:
				break;
		}

		return true;
	}
	
	/**
	 * 预览
	 * @param array $list	基本参数
	 */
	public static function preview($list)
	{
		try{
			list($sendMsg, $fileIds) = self::_addPreview($list);
		}catch(\Exception $e) {
			return false;
		}
				
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
			
		// 发送微信消息
		WxLogic::sendNewsMsgByAdmin($sendMsg, [ADMINID], 'notice');
		return true;
	}
	
	/**
	 * 列表
	 * @param string $keyword	关键字
	 * @param int $offset
	 * @param int $pasize
	 */
	public static function lists($keyword='', $offset=0, $psize=10)
	{
		$where = ['is_del'=>0];
		empty($keyword) || $where['title'] = ['like', "%$keyword%"];
		
		$model = new Notice();
		$userids = [];
		$list = $model->getBaseMorePage($where, 'id,create_time,userid,title,status', $offset, $psize);
		foreach($list as &$v){
			$userids[] = $v['userid'];
			$v['timestamp'] = date('Y-m-d H:i:s', $v['create_time']);
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
		$model = new Notice();
		
		$info = $model->getBaseOne(['id'=>$id], 'status');
		if($info['status'] == 1){
			// 已发送的
			$model->updateMemberMore(['notice_id'=>$id], ['is_del'=>1]);
		}
		$res = $model->updateBase(['id'=>$id], ['is_del'=>1]);
		return $res > 0 ? true : false;
	}
	
	/**
	 * 详情
	 * @param int $id	记录ID
	 */
	public static function detail($id)
	{
		$model = new Notice();
		
		$info = [];
		// 获取基本信息
		$baseInfo = $model->getBaseOne(['id'=>$id], 'title,create_time,userid,type,is_del');
		if(empty($baseInfo)) throw new \Exception('通知不存在', Errcode::NEWS_UNSET);
		if($baseInfo['is_del']) throw new \Exception('通知已删除', Errcode::NEWS_DEL);
		
		// 获取发送人
		$author = '';
		if($baseInfo['userid'] >0){
			$userModel = new User();
			$userInfo = $userModel->getOne(['id'=>$baseInfo['userid']], 'name');
			$author = $userInfo['name'];
		}
		
		switch($baseInfo['type']){
			case 1:
				$newsInfo = $model->getNewsOne(['notice_id'=>$id], 'abstract,content');
				break;
			case 2:
				$textInfo = $model->getTextOne(['notice_id'=>$id], 'content');
				break;
			case 3:
				$fileInfo = $model->getFileOne(['notice_id'=>$id], 'file_id');
				// 读取文件信息
				$fileModel = new File();
				$fileInfo = $fileModel->getOne(['id'=>$fileInfo['file_id']], 'origin_name,http_path');
				break;
			default:
				break;
		}
		
		// 读取已读人数
		$readCount = $model->countMember(['notice_id'=>$id, 'is_read'=>1]);
		// 读取未读人数
		$unreadCount = $model->countMember(['notice_id'=>$id, 'is_read'=>0]);
		
		return [
			'id' => $id, 'type'=>$baseInfo['type'], 'author' => $author,
			'had_read'		=> $readCount, 'not_read' => $unreadCount,
			'title'			=> htmlspecialchars_decode($baseInfo['title']),
			'timestamp'	=> date('Y-m-d H:i:s', $baseInfo['create_time']),
			'filename'		=> isset($fileInfo) ? $fileInfo['origin_name'] : '',
			'filepath'		=> isset($fileInfo) ? $fileInfo['http_path'] : '',
			'abstract'		=> isset($newsInfo) ? htmlspecialchars_decode($newsInfo['abstract']) : '',
			'content'		=> isset($newsInfo) ? htmlspecialchars_decode($newsInfo['content']) : 
				(isset($textInfo) ? htmlspecialchars_decode($textInfo['content']) : '')
		];
	}
	
	/**
	 * 撤销
	 * @param int $id	记录ID
	 */
	public static function revoke($id)
	{
		$model = new Notice();
		// 判断是否是待发送状态
		$baseInfo = $model->getBaseOne(['id'=>$id], 'status,is_del');
		if(empty($baseInfo)) throw new \Excepetion('通知不存在', Errcode::NOTICE_UNSET);
		if($baseInfo['is_del'] == 1) throw new \Exception('通知已删除', Errcode::NOTICE_DEL);
		if($baseInfo['status'] == 1) throw new \Exception('通知已发送不能撤销', Errcode::NOTICE_SEND);
		
		//开启事务
		Db::startTrans();
		try{
			// 删除定时任务
			$model->updateTask(['notice_id'=>$id], ['is_del'=>1]);
			$model->updateBase(['id'=>$id], ['status'=>3]);
			Db::commit();
		}catch(\Exception $e){
			Db::rollback();
			throw new \Exception('撤销失败', Errcode::INSERT_ERR);
		}
	}
	
	/**
	 * 提醒
	 * @param int $id	记录ID
	 */	
	public static function remind($id)
	{
		$model = new Notice();
		$baseInfo = $model->getBaseOne(['id'=>$id], 'type');
		
		// 读取未读的人
		$member = $model->getMemberMore(['notice_id'=>$id, 'is_read'=>0, 'is_del'=>0], 'memid');
		$memids = [];
		foreach($member as $v){
			$memids[] = $v['memid'];
		}
		$memids = array_values(array_filter(array_unique($memids)));
		
		$aid = AgentLogic::getAidByType('notice');
		
		if(! empty($memids)){
			WxLogic::sendNewsMsgByCustom([[
				'title' => '收到新的通知内容，请注意查看',
				'url'	 => APP_HTTP.TLD.'.'.WEB_DOMAIN.'/weixin/index.html#notice/'.$id.'/detail/1/'.$baseInfo['type'].'/aid/'.$aid
			]], $memids, 'notice');
		}
		
		// 设置为已提醒状态
		$model->updateBase(['id'=>$id], ['is_remind'=>1, 'update_time'=>NOW_TIME]);
		return true;
	}
	
	/**
	 * 获取一个新闻的已读或者未读
	 * @param int $id	记录ID
	 * @param int $type	0:读取未读 1:读取已读
	 */
	public static function member($id, $type)
	{
		$model = new Notice();
		
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

		$info = $model->getMemberMore(['notice_id' => $id, 'is_read' => $type], 'memid');
		
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
	 * 读取评论获取回复
	 * @param int $id			读取评论是表示新闻ID	读取回复时表示评论ID
	 * @param int $noticeType	1:图文2文本3文件
	 * @param int $type			0：读取评论 1:读取回复
	 * @param int $offset		偏移量
	 * @param int $psize		一页大小
	 */
	public static function readCommentReply($id, $noticeType, $type, $offset=0, $psize=10)
	{
		if($type == 0){
			// 读取评论
			$where = ['notice_id'=>$id, 'type'=>0];
			$field = 'id,create_time,memid,admin_id,content';
			$order = 'id DESC';
		}else{
			// 读取回复
			$where = ['parent_id'=>$id, 'type'=>1];
			$field = 'create_time,memid,admin_id,to_memid,to_adminid,content';
			$order = 'id ASC';
		}

		// 读取评论列表
		$model = new Notice();
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
	 * 处理定时任务
	 */
	public static function timerDeal()
	{
		$model = new Notice();
		
		$list = $model->getTaskMore([
			'task_time'	=> ['elt', NOW_TIME],
			'status'		=> ['in', [0,1]],
			'is_del'		=> 0
		], 'id,notice_id,url,child_ids,to_contact,title,imgid,type');
		
		foreach($list as $v){
			$ids[] = $v['id'];
		}
		isset($ids) && $model->updateTask(['id'=>['in', $ids]], ['status'=>1]);
		
		foreach($list as $v){
			// 解析发送对象
			$contact = json_decode($v['to_contact'], true);
			$memids = [];
			if(empty($contact)){
				// 发给全体成员
				$info = CustomerLogic::getName();
				foreach($info as $v1){
					$memids[] = $v1['id'];
				}
				unset($info);
			}else{
				$memids = ArchitectureLogic::parseToMember($contact);
			}
			$memids = array_values(array_filter(array_unique($memids)));
			if(empty($memids)) continue;
			
			$sendMsg = $fileIds = $childNoticeIds = [];
			$type = intval($v['type']);
			switch($type){
				case 1:
					// 图文消息
					$fileIds[] = $v['imgid'];
					$sendMsg[] = [
						'title'		=> $v['title'],
						'url'		=> $v['url'],
						'picurl'	=> $v['imgid']
					];
					
					$childIds = explode(',', $v['child_ids']);
					if(! empty($childIds)){
						// 读取所有的附加图文信息
						$_list = $model->getTaskNewsMore(['id'=> ['in', $childIds]], 'imgid,title,url,notice_id');
						foreach($_list as $v1){
							$sendMsg[] = [
								'title'		=> $v1['title'],
								'url'		=> $v1['url'],
								'picurl'	=> $v1['imgid']
							];
							$fileIds[] = $v1['imgid'];
							$childNoticeIds[] = $v1['notice_id'];
						}
					}
					
					// 图片地址解析
					$fileIds = array_values(array_filter(array_unique($fileIds)));
					if(! empty($fileIds)){
						$fileModel = new File();
						$_info = $fileModel->getMore(['id'=>['in', $fileIds]], 'id,http_path');

						$info = [];
						foreach($_info as $v2){
							$info[$v2['id']] = APP_HTTP.TLD.'.'.WEB_DOMAIN.$v2['http_path'];
						}
						unset($_info);

						foreach($sendMsg as &$v2){
							isset($info[$v2['picurl']]) && $v2['picurl'] = $info[$v2['picurl']];
						}
						unset($v2);
					}
					WxLogic::sendNewsMsgByCustom($sendMsg, $memids, 'notice');
					break;
				case 2:
					// 文本
					// 读取文本的内容
					$_info = $model->getTextOne(['notice_id'=>$v['notice_id']], 'content');
					$sendMsg[] = [
						'title'	=> $v['title'],
						'desc'	=> mb_substr($_info['content'], 0, 50, 'UTF-8') . '...',
						'url'	=> $v['url']	
					];
					WxLogic::sendNewsMsgByCustom($sendMsg, $memids, 'notice');
					break;
				case 3:
					// 文件
					$fileModel = new File();
					// 读取文件的原始名称
					$fileInfo = $fileModel->getOne(['id'=>$v['imgid']], 'path,ext');
					self::_sendFile($v['notice_id'], $v['title'], $fileInfo['path'], $fileInfo['ext'], $memids);
					break;
			}
			
			$model->updateTask(['id'=>$v['id']], ['status'=>2]);
			$model->updateBase(['id'=>['in', array_merge($childNoticeIds,[$v['notice_id']])]], ['status'=>1]);
			$data = [];
			foreach($memids as $memid){
				$data[] = [
					'notice_id'	=> $v['notice_id'],
					'memid'			=> $memid,
					'type'			=> $type
				];
			}
			$model->addMemberMore($data);
			
			// 避免数据过多，分开增加
			if(! empty($childNoticeIds)){
				foreach($childNoticeIds as $v){
					$data = [];
					foreach($memids as $memid){
						$data[] = [
							'notice_id'	=> $v,
							'memid'			=> $memid,
							'type'			=> $type
						];
					}
					$model->addMemberMore($data);
				}
			}
		}
	}
	
	/**
	 * 添加图文
	 * @param array $list	数据
	 * @param array $memids	成员
	 * @param int $timeout	定时发送 0 马上发送， 具体时间具体时间发送
	 * @param string $sendObj 选择发送对象
	 * @return [$sendMsg, $fileIds]	待发送的微信消息   文件IDs
	 */
	private static function _addNews($list, $memids, $timeout=0, &$sendObj='')
	{
		$count = 0;
		$sendMsg = $fileIds = $baseIds = $timerNewsIds = [];
		$model = new Notice();
		
		$aid = AgentLogic::getAidByType('notice');
		//开启事务
		Db::startTrans();
		try {
			foreach($list as $k => $v){
				$fileIds[] = $v['cover_id'];
				// 添加基本数据
				$baseId = $model->addBaseOne([
					'userid'	=> ADMINID,
					'title'		=> $v['title'],
					'type'		=> 1,
					'is_push'	=> $v['sync'],
					'status'	=> $timeout ==0 ? 1 : 2,
					'create_time'	=> NOW_TIME
				]);
				$baseIds[] = $baseId;
				
				// 添加图文信息
				$model->addNewsOne([
					'notice_id'	=> $baseId,
					'abstract'		=> $v['abstract'],
					'content'		=> $v['content'],
					'imgid'			=> $v['cover_id']
				]);
				
				// 是否添加定时任务
				if($timeout != 0){
					$data = [
						'notice_id'	=> $baseId,
						'imgid'			=> $v['cover_id'],
						'title'			=> $v['title'],
						'url'			=> APP_HTTP.TLD.'.'.WEB_DOMAIN.'/weixin/index.html#/notice/'.$baseId.'/detail/1/1/aid/'.$aid,
						'to_contact'	=> $sendObj
					];
					if($count == 0){
						$firstTimerId = $model->addTaskOne(array_merge($data, [
							'task_time'	=> $timeout,
							'type'			=> 1
						]));
					}else{
						$newsIds = $model->addTaskNewsOne($data);
						$timerNewsIds[] = $newsIds;
					}
					$count++;
				}else{
					// 添加成员信息
					$memData = [];
					foreach($memids as $v1){
						$memData[] = [
							'notice_id'=> $baseId,
							'memid'		=> $v1,
							'type'		=> 1
						];
					}
					$model->addMemberMore($memData);
				}

				// 发送微信消息信息
				$sendMsg[$k] = [
					'title'		=> $v['title'],
					'url'		=> APP_HTTP.TLD.'.'.WEB_DOMAIN.'/weixin/index.html#/notice/'.$baseId.'/detail/1/1/aid/'.$aid,
					'desc'		=> mb_substr($v['abstract'], 0, 50, 'UTF-8')
				];
			}
			
			isset($firstTimerId) && $model->updateTask(['id'=>$firstTimerId], [
				'child_ids'	=>	implode(',', $timerNewsIds)
			]);
			
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			throw new \Exception('增加失败', $e->getCode());
		}
		
		return [$sendMsg, $fileIds];
	}
	
	/**
	 * 添加文字
	 * @param string $title		标题
	 * @param string $content	内容
	 * @param int $sync			同步
	 * @param array $memids		成员
	 * @param int $timeout		定时发送 0 马上发送， 具体时间具体时间发送
	 * @param string $sendObj	选择发送对象
	 */
	private static function _addText($title, $content, $sync, $memids, $timeout=0, &$sendObj='')
	{
		$model = new Notice();
		
		$aid = AgentLogic::getAidByType('notice');
		
		//开启事务
		Db::startTrans();
		try{
			// 添加基本数据
			$baseId = $model->addBaseOne([
				'userid'	=> ADMINID,
				'title'		=> $title,
				'type'		=> 2,
				'is_push'	=> $sync,
				'status'	=> $timeout ==0 ? 1 : 2,
				'create_time'	=> NOW_TIME
			]);
			
			$model->addTextOne([
				'notice_id'	=> $baseId,
				'content'		=> $content
			]);
			
			if($timeout != 0){
				// 是否添加定时任务
				$model->addTaskOne([
					'task_time'	=> $timeout,
					'notice_id'	=> $baseId,
					'url'			=> APP_HTTP.TLD.'.'.WEB_DOMAIN.'/weixin/index.html#/notice/'.$baseId.'/detail/1/2/aid/'.$aid,
					'to_contact'	=> $sendObj,
					'title'			=> $title,
					'type'			=> 2
				]);
			}else{
				// 添加成员信息
				$memData = [];
				foreach($memids as $v1){
					$memData[] = [
						'notice_id'=> $baseId,
						'memid'		=> $v1,
						'type'		=> 2
					];
				}
				$model->addMemberMore($memData);
			}
			 
			Db::commit();
		}catch(\Exception $e){
			Db::rollback();
			throw new \Exception('添加失败', $e->getCode());
		}
		
		return [$baseId, $aid];
	}
	
	/**
	 * 添加文件
	 * @param int $fileId		文件ID
	 * @param string $title		标题
	 * @param int $sync			同步
	 * @param array $memids		成员
	 * @param int $timeout		定时发送 0 马上发送， 具体时间具体时间发送
	 * @param string $sendObj	选择发送对象
	 */
	private static function _addFile($fileId, $title, $sync, $memids, $timeout=0, &$sendObj='')
	{
		$model = new Notice();
		
		//开启事务
		Db::startTrans();
		try{
			// 添加基本数据
			$baseId = $model->addBaseOne([
				'userid'	=> ADMINID,
				'title'		=> $title,
				'type'		=> 3,
				'is_push'	=> $sync,
				'status'	=> $timeout ==0 ? 1 : 2,
				'create_time'	=> NOW_TIME
			]);
			
			$model->addFileOne([
				'notice_id'	=> $baseId,
				'file_id'		=> $fileId
			]);
			
			if($timeout != 0){
				// 是否添加定时任务
				$model->addTaskOne([
					'task_time'	=> $timeout,
					'notice_id'	=> $baseId,
					'to_contact'	=> $sendObj,
					'title'			=> $title,
					'imgid'			=> $fileId,
					'type'			=> 3
				]); 
			}else{
				// 添加成员信息
				$memData = [];
				foreach($memids as $v1){
					$memData[] = [
						'notice_id'=> $baseId,
						'memid'		=> $v1,
						'type'		=> 3,
					];
				}
				$model->addMemberMore($memData);
			}

			Db::commit();
		}catch(\Exception $e){
			Db::rollback();
			throw new \Exception('添加失败', $e->getCode());
		}
		
		return $baseId;
	}
	
	/**
	 * 预览添加到表
	 * @param array $list	数据
	 */
	private static function _addPreview($list)
	{
		$sendMsg = $fileIds = [];
		$model = new Notice();
		
		$aid = AgentLogic::getAidByType('notice');
		//开启事务
		Db::startTrans();
		try{
			foreach($list as $k => $v){
				$fileIds[] = $v['cover_id'];
				// 添加基本数据
				$baseId = $model->addPreviewOne([
					'title'		=> $v['title'],
					'imgid'		=> $v['cover_id'],
					'abstract'	=> $v['abstract'],
					'content'	=> $v['content'],
					'userid'	=> ADMINID,
					'create_time'	=> NOW_TIME
				]);

				// 发送微信消息信息
				$sendMsg[$k] = [
					'title'		=>	$v['title'],
					'url'		=> APP_HTTP.TLD.'.'.WEB_DOMAIN.'/weixin/index.html#notice/'.$baseId.'/detail/0/1/aid/'.$aid,
				];
			}
			Db::Commit();
		}catch(\Exception $e){
			Db::rollback();
			throw new \Exception('增加失败', $e->getCode());
		}
		return [$sendMsg, $fileIds];
	}
	
	/**
	 * 发送文件消息
	 * @param int $id			通知ID
	 * @param string $title		标题
	 * @param string $url		路径
	 * @param string $type		后缀
	 * @param array $memids		发送的成员
	 */
	private static function _sendFile($id, $title, $url, $type, $memids)
	{
		$sendMsg = [
			'type'				=> $type,
			'path'				=> $url,
			'origin_name'		=> $title
		];
		
		$mediaId = WxLogic::sendMediaMsgByCustom($sendMsg, $memids, 'notice');
		$model = new Notice();
		$model->updateFile(['notice_id'=>$id], ['media_id'=>$mediaId]);
		return true;
	}
	
}
