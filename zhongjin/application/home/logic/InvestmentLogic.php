<?php
/**
 * 投资逻辑层
 * by sherlock
 */

namespace app\home\logic;

use app\common\model\Customer;
use app\common\model\Product;
use app\common\model\File;
use app\common\controller\Json;
use app\common\controller\Errcode;
use think\Db;

class InvestmentLogic{
	
	/**
	 * 增加投资
	 * @param array $data	数据
	 */
	public static function add($data)
	{
		$model = new Customer();
		
		$data['create_time'] = NOW_TIME;
		$id = $model->addInvestmentOne($data);
		
		return $id <=0 ? false : $id;
	}
	
	/**
	 * 投资列表
	 * @param int $type			客户类别 0:全部1:个人2:机构
	 * @param int $productId	产品ID
	 * @param int $offset		偏移量
	 * @param int $psize		一页的大小
	 * @param string $keywords	关键字
	 * @param int $stime		开始时间
	 * @param int $etime		结束时间
	 */
	public static function lists($type=0, $productId=0, $offset=0, $psize=10, $keywords='', $stime='', $etime='')
	{
		$model = new Customer();
		
		$where = ['a.is_del'=>0];
		$productId >0 && $where['a.product_id'] = $productId;
		$type >0 && $where['b.type'] = $type;
		
		if(! empty($keywords)){
			// 判断是否为手机号或者名字
			if(is_mobile($keywords)){
				$where['b.mobile'] = $keywords;
			}else{
				$where['b.name'] = ['like', "%$keywords%"];
			}
		}
		
		if(! empty($stime)){
			// 投资日期
			if(! empty($etime)){
				$where['a.time'] = ['BETWEEN', [$stime, $etime]];
			}else{
				$where['a.time'] = ['gt', $stime];
			}
		}else if(! empty($etime)){
			$where['a.time'] = ['lt', $etime];
		}
		
		$join = [['member b', 'a.memid=b.id', 'LEFT']];
		$list = $model->getJoinCustomInvest($where, $join, 
			'a.bank,a.bank_account,a.money,a.time,a.id,a.product_id,b.name,b.mobile,b.type', 
			$offset, $psize);
		
		foreach($list as $k => &$v){
			$v['phone'] = $v['mobile'];
			$v['bank_name'] = $v['bank'];
			$v['product'] = $v['product_id'];
			$v['invest_date'] = date('Y-m-d', $v['time']);
			unset($v['bank']);
			unset($v['time']);
			unset($v['mobile']);
			unset($v['product_id']);
		}
	
		$count = $model->getJoinCustomInvestCount($where, $join);
		return [empty($list) ? []:$list, $count];
	}
	
	/**
	 * 删除一条记录
	 * @param int $id	记录ID
	 */
	public static function del($id)
	{
		$model = new Customer();
		$res = $model->updateInvest(['id'=>$id], ['update_time'=>NOW_TIME, 'is_del'=>1]);
		
		return $res <=0 ? false : true;
	}
	
	/**
	 * 查找详情
	 * @param int $id	记录ID
	 */
	public static function detail($id)
	{
		$data = [];
		$model = new Customer();
		$info = $model->getInvestOne(['id'=>$id, 'is_del'=>0], 'memid,product_id,money,time,bank,bank_account');
		if(! empty($info)){
			$data = [
				'bank_name'=>$info['bank'], 'bank_account'=>$info['bank_account'], 
				'money'=>$info['money'], 'invest_date'=>date('Y-m-d',$info['time'])
			];
			
			$memInfo = $model->getCustomerOne(['id'=>$info['memid']], 'name,mobile,credentials,type,credentials_type');
			if(! empty($memInfo)){
				$data['phone'] = $memInfo['mobile'];
				$data['client_type'] = $memInfo['type'];
				$data['client_name'] = $memInfo['name'];
				$data['id_number'] = $memInfo['credentials'];
				$data['id_type'] = $memInfo['credentials_type'];
			}
			unset($memInfo);
			
			$model = new Product();
			$productInfo = $model->getBaseOne(['id'=>$info['product_id']], 'title,status');
			if(! empty($productInfo)){
				$data['status'] = $productInfo['status'];
				$data['product_name'] = $productInfo['title'];
			}
		}
		
		return $data;
	}
	
	/**
	 * 导入数据
	 * @parma int $id	记录ID
	 */
	public static function import($id)
	{
		$model = new File();
		$info = $model->getOne(['id'=>$id], 'path,ext');
		if(empty($info)){
			throw new \Exception('文件上传失败', Errcode::FILE_NO_EXISET);
		}
		
		if(! file_exists($info['path'])){
			throw new \Exception('上传的文件不存在！', Errcode::FILE_NO_EXISET);
		}
		
		if(! ($info['ext'] == 'xls' || $info['ext'] == 'xlsx')){
			//unlink($info['path']);
			throw new \Exception('文件格式不对，请上传xls,xlsx格式的文件', Errcode::FILE_NO_EXISET);
		}
		
		$filename = $info['path'];
		vendor('phpexcel.PHPExcel.IOFactory');
		$fileType = \PHPExcel_IOFactory::identify($filename);
		$objReader = \PHPExcel_IOFactory::createReader($fileType);
		$objPHPExcel = $objReader->load($filename);
		
		$currentSheet = $objPHPExcel->getSheet(0);
		$highestRow = $currentSheet->getHighestRow();
		if(intval($highestRow) > 502){
			//unlink($filename);
			throw new \Exception('一次上传数据不能超过500', Errcode::FILE_DATA_BIG);
		}
		
		// 数据检查
		$data = [];
		$customerModel = new Customer();
		$productModel = new Product();
		for($k=3; $k<=$highestRow; $k++){
			$bank = $objPHPExcel->getActiveSheet()->getCell("C$k")->getValue();  
			if(empty($bank)){
				//unlink($filename);
				throw new \Exception('第'.$k.'行开户银行不能为空', Errcode::FILE_DATA_ERR);
			}
			if(mb_strlen($bank,'UTF-8') > 16){
				//unlink($filename);
				throw new \Exception('第'.$k.'行开户银行长度不能超过16个字符', Errcode::FILE_DATA_ERR);
			}
			$data[$k]['bank'] = $bank;
			
			$bankAccount = $objPHPExcel->getActiveSheet()->getCell("D$k")->getValue();
			if(empty($bankAccount)){
				//unlink($filename);
				throw new \Exception('第'.$k.'行银行账户不能为空', Errcode::FILE_DATA_ERR);
			}
			if(mb_strlen($bankAccount,'UTF-8') > 24){
				//unlink($filename);
				throw new \Exception('第'.$k.'行银行账号长度不能超过24个字符', Errcode::FILE_DATA_ERR);
			}
			$data[$k]['bank_account'] = $bankAccount;
			
			$money = $objPHPExcel->getActiveSheet()->getCell("E$k")->getValue();
			if(empty($money)){
				//unlink($filename);
				throw new \Exception('第'.$k.'行投资金额不能为空', Errcode::FILE_DATA_ERR);
			}
			$data[$k]['money'] = $money;
			
			// 时间处理 会相差8个小时, 可以先转Y-m-d 再转回时间撮
			$time = $objPHPExcel->getActiveSheet()->getCell("F$k")->getValue();
			if(empty($time)){
				//unlink($filename);
				throw new \Exception('第'.$k.'行投资日期不能为空', Errcode::FILE_DATA_ERR);
			}
			$time = date('Y-m-d', \PHPExcel_Shared_Date::ExcelToPHP($time));
			$time = strtotime($time);
			$data[$k]['time'] = $time;
			
			$name = $objPHPExcel->getActiveSheet()->getCell("A$k")->getValue();
			if(empty($name)){
				//unlink($filename);
				throw new \Exception('第'.$k.'行姓名不能为空', Errcode::FILE_DATA_ERR);
			}
			
			$mobile = $objPHPExcel->getActiveSheet()->getCell("B$k")->getValue();
			if(empty($mobile)){
				//unlink($filename);
				throw new \Exception('第'.$k.'行手机号码不能为空', Errcode::FILE_DATA_ERR);
			}
			$info = $customerModel->getCustomerOne(['mobile'=>$mobile], 'id');
			if(empty($info)){
				//unlink($filename);
				throw new \Exception('第'.$k.'行'.$name.'客户不存在，请先添加该客户', Errcode::FILE_DATA_ERR);
			}
			$data[$k]['memid'] = $info['id'];
			
			$product = $objPHPExcel->getActiveSheet()->getCell("G$k")->getValue();
			if(empty($product)){
				//unlink($filename);
				throw new \Exception('第'.$k.'行投资项目不能为空', Errcode::FILE_DATA_ERR);
			}
			$info = $productModel->getBaseOne(['title'=>$product], 'id');
			if(empty($info)){
				//unlink($filename);
				throw new \Exception('第'.$k.'行项目不存在，请先添加该项目', Errcode::FILE_DATA_ERR);
			}
			$data[$k]['product_id'] = $info['id'];
			$data[$k]['create_time'] = NOW_TIME;
		}
		
		try {
			//unlink($filename);
			$res = $customerModel->addInvestmentMore($data);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		}
		
		return $res >0 ? true : false;
	}
	
	/**
	 * 赎回
	 * @param int $id	客户ID
	 */
	public static function redeem($id)
	{
		$model = new Customer();
		
		$res = $model->updateInvest(['id'=>$id], [
			'status'=>1, 'update_time'=>NOW_TIME
		]);
		
		return $res >0 ? true : false;
	}
	
	/**
	 * 分页读取报告
	 * @param int $id		投资ID
	 * @param int $offset	偏移量
	 * @param int $psize	一页大小
	 * @param int $report_type	报告类型
	 */
	public static function reportLists($id, $offset, $psize, $report_type)
	{
		$model = new Customer();
		$where = $report_type == 0 ? ['invest_id'=>$id] : ['invest_id'=>$id, 'report_type'=>$report_type];
		$list = $model->getReportMore($where, 'create_time,file_id,report_type', $offset, $psize, 'id DESC');
		$fileIds = [];
		foreach($list as $v){
			$fileIds[] = $v['file_id'];
		}
		
		$fileIds = array_values(array_filter(array_unique($fileIds)));
		if(! empty($fileIds)){
			// 读取所有路径
			$fileModel = new File();
			$_fileInfo = $fileModel->getMore(['id'=>['in', $fileIds]], 'id,path,http_path,ext');
			foreach($_fileInfo as $v){
				// $path = explode('/', $v['path']);
				// $fileInfo[$v['id']] = [
				// 	'path' => $path[5].'/'.$path[6].'/'.$path[7],
				// 	'ext' => $v['ext']
				// ];
				// $path = explode('/', $v['http_path']);
				$fileInfo[$v['id']] = [
					'path' => $v['http_path'],
					'ext' => $v['ext']
				];
			}
			
			foreach($list as &$v){
				$v['url'] = isset($fileInfo[$v['file_id']]) ? APP_HTTP.TLD.'.'.WEB_DOMAIN.$fileInfo[$v['file_id']]['path'] : '';
				$v['ext'] = isset($fileInfo[$v['file_id']]) ? $fileInfo[$v['file_id']]['ext'] : '';
				$v['time'] = date('Y-m-d', $v['create_time']);
				unset($v['create_time']);
			}
		}
		
		$count = $model->getReportCount($where);
		return [empty($list) ? [] : $list , $count];
	}

	/**
	 * 转让
	 * @param int $id		投资记录ID
	 * @param int $memid	转让给客户的ID
	 */
	public static function transfer($id, $memid)
	{
		$model = new Customer();
		
		$info = $model->getInvestOne(['id'=>$id], 'product_id,bank,bank_account,money,time');
		if(! empty($info)){
			//开启事务
			Db::startTrans();
			try{
				$data = [
					'bank'			=> $info['bank'],
					'time'			=> $info['time'],
					'money'			=> $info['money'],
					'memid'			=> $memid,
					'product_id'	=> $info['product_id'],
					'create_time'	=> NOW_TIME,
					'bank_account'	=> $info['bank_account'],
				];
				$model->addInvestmentOne($data);
				$model->updateInvest(['id'=>$id], ['status'=>2, 'update_time'=>NOW_TIME]);
				Db::commit();
				return true;
			}catch(\Exception $e){
				Db::rollback();
				return false;
			}
		}
		
		return false;
	}
	
	/**
	 * 导入报告数据
	 * @parma int $id	投资记录ID
	 * @param int $fileId	上传文件ID
	 * @param int $report_type	报告类型
	 */
	public static function importReport($id, $fileId, $report_type)
	{
		$model = new File();
		$info = $model->getOne(['id'=>$fileId], 'name');
		if(empty($info)){
			throw new \Exception('文件上传失败', Errcode::FILE_NO_EXISET);
		}
		
		$model = new Customer();
		$res = $model->addReportOne([
			'create_time'=>NOW_TIME, 'file_id'=>$fileId, 'invest_id'=>$id, 'report_type' => $report_type
		]);
		
		return $res >0 ? true : false;
	}
}
