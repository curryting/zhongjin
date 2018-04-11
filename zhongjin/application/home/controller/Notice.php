<?php
namespace app\home\controller;

use app\common\controller\Errcode;
use app\common\logic\FileLogic;
use app\home\logic\NoticeLogic;
use app\home\logic\ArchitectureLogic;
use app\home\logic\CustomerLogic;

class Notice extends Root
{
	/**
	 * 增加
	 */
	public function add()
	{
		$type = input('param.notice_type', 1, 'intval');
		in_array($type, [1,2,3]) || $this->result('', Errcode::PARAM_ERR, '参数错误');
		$timeout = input('param.clocking', 0, 'strtotime');
		
		switch($type){
			case 1:
				// 图文
				$list = $this->_getAddParam();
				break;
			case 2:
				// 文本
				$title = input('param.title', '', 'htmlspecialchars');
				$content = input('param.content', '', 'htmlspecialchars');
				$sync = input('param.sync', 0, 'intval');
				(empty($title) || empty($content) || !in_array($sync, [0,1])) 
					&& $this->result('', Errcode::PARAM_ERR, '参数错误');
				$list = [
					'title'=>$title, 'content'=>$content, 'sync'=>$sync
				];
				break;
			case 3:
				// 文件
				$sync = input('param.sync', 0, 'intval');
				$fileId = input('param.file_id', 0, 'intval');
				$list = [
					'sync'=>$sync, 'fileId'=>$fileId
				];
				break;
			default:
				break;
		}
		
		$memids = $sendObj = [];
		$sendType = input('param.send_type', 0, 'intval');
		if($sendType == 1){
			// 发给指定成员
			$sendObj = input('param.send_obj/a');
			empty($sendObj) && $this->result('', Errcode::PARAM_ERR, '成员不能为空');
			$memids = ArchitectureLogic::parseToMember($sendObj);
		}else{
			$info = CustomerLogic::getName();
			foreach($info as $v){
				$memids[] = $v['id'];
			}
			unset($info);
		}
		$memids = array_values(array_filter(array_unique($memids)));
		empty($memids) && $this->result('', Errcode::PARAM_ERR, '该项目下没有成员');
		
		$sendObj = json_encode($sendObj);
		$res = NoticeLogic::add($list, $memids, $type, $timeout, $sendObj);
		$res === false && $this->result('', Errcode::INSERT_ERR, '增加失败');
	
		return $this->resultOk();
	}
	
	/**
	 * 预览
	 */
	public function preview()
	{
		$list = $this->_getAddParam();
		
		$res = NoticeLogic::preview($list);
		$res === false && $this->result('', Errcode::INSERT_ERR, '发送失败');
	
		return $this->resultOk();
	}
	
	/**
	 * 列表
	 */
	public function lists()
	{
		$keywords = input('param.search', '', 'htmlspecialchars');
		
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = NoticeLogic::lists($keywords, $offset, $psize);
		return $this->resultOk(['list'=>$info[0], 'total'=>$info[1]]);
	}
	
	/**
	 * 删除
	 */
	public function delete()
	{
		$id = input('param.id', 0, 'intval');
		$id<=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$res = NoticeLogic::del($id);
		$res === false && $this->result('', Errcode::UPDATE_ERR, '删除失败');
		
		return $this->resultOk();
	}
	
	/**
	 * 详情
	 */
	public function detail()
	{
		$id = input('param.id', 0, 'intval');
		$id<=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		try{
			$info = NoticeLogic::detail($id);
		} catch (\Exception $e) {
			$this->result('', $e->getCode(), $e->getMessage());
		}
		
		return $this->resultOk(['detail'=>$info]);
	}
	
	/**
	 * 撤销
	 */
	public function revoke()
	{
		$id = input('param.id', 0, 'intval');
		$id<=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		try{
			NoticeLogic::revoke($id);
		}catch(\Exception $e){
			$this->result('', $e->getCode(), $e->getMessage());
		}
		
		return $this->resultOk();
	}
	
	/**
	 * 未读提醒
	 */
	public function remind()
	{
		$id = input('param.id', 0, 'intval');
		$id<=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		NoticeLogic::remind($id);
		return $this->resultOk();
	}
	
	/**
	 * 获取已读名字
	 */
	public function readMember()
	{
		$id = input('param.id', 0, 'intval');
		$id<=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$info = NoticeLogic::member($id, 1);
		return $this->resultOk(['member'=>$info[0]]);
	}
	
	/**
	 * 获取未读名字
	 */
	public function unreadMember()
	{
		$id = input('param.id', 0, 'intval');
		$id<=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$info = NoticeLogic::member($id, 0);
		return $this->resultOk(['member'=>$info[0], 'can_remind'=>$info[1]]);
	}
	
	/**
	 * 读取评论
	 */
	public function readComment()
	{
		$id = input('param.id', 0, 'intval');
		$type = input('param.type', 0, 'intval');	//  通知类型
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = NoticeLogic::readCommentReply($id, $type, 0, $offset, $psize);
		return $this->resultOk(['list'=>$info]);
	}
	
	/**
	 * 读取回复
	 */
	public function readReply()
	{
		$id = input('param.id', 0, 'intval');	//	表示评论的ID
		$type = input('param.type', 0, 'intval');	//  通知类型
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = NoticeLogic::readCommentReply($id, $type, 1, $offset, $psize);
		return $this->resultOk(['list'=>$info]);
	}
	
	/**
	 * 上传客户文件 
	 */
	public function uploadImg()
	{
		try {
			$data = FileLogic::uploadOne('file', ['size'=>1024*1024*2, 'ext'=>'png,jpg,jpeg']);
		}catch(\Exception $e){
			return $this->resultErr($e->getMessage(), $e->getCode());
		}
		
		return $this->resultOk($data);
	}
	
	/**
	 * 上传客户文件 
	 */
	public function uploadFile()
	{
		try {
			$data = FileLogic::uploadOne('file', ['size'=>1024*1024*5, 'ext'=>'txt,xml,pdf,zip,rar,tar,gz,7z,doc,ppt,xls,docx,pptx,xlsx,xlsm']);
		}catch(\Exception $e){
			return $this->resultErr($e->getMessage(), $e->getCode());
		}
		
		return $this->resultOk($data);
	}
	
	/**
	 * 获取增加时参数
	 * @return type
	 */
	private function _getAddParam()
	{
		$list = input('param.list/a');
		empty($list) && $this->result('', Errcode::PARAM_ERR, '参数错误');
		count($list) > 6 && $this->result('', Errcode::PARAM_ERR, '不能超过6条');
		
		foreach($list as $k => &$v){
			if(isset($v['sync'])){
				in_array($v['sync'], [0,1]) || $this->result('', Errcode::PARAM_ERR, '第'.($k+1).'条同步到公众号参数类型错误');
			}else{
				$v['sync'] = 0;
			}
			
			$v['cover_id'] >=0 || $this->result('', Errcode::PARAM_ERR, '第'.($k+1).'条新闻上传图片为空');
			
			$v['title'] = htmlspecialchars($v['title']);
			empty($v['title']) && $this->result('', Errcode::PARAM_ERR, '第'.($k+1).'条新闻标题为空');
			mb_strlen($v['title'],'UTF-8') >32 && $this->result('', Errcode::PARAM_ERR, '第'.($k+1).'条新闻标题不能超过32个字符');
			
			$v['content'] = deal_xiumi_image($v['content']);
			$v['content'] = htmlspecialchars($v['content']);
			empty($v['content']) && $this->result('', Errcode::PARAM_ERR, '第'.($k+1).'条新闻内容为空');
			
			$v['abstract'] = htmlspecialchars($v['abstract']);
		}
		unset($v);
		
		return $list;
	}
}
