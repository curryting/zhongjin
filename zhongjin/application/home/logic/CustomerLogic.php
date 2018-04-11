<?php
/**
 * 客户逻辑层
 * by sherlock
 */

namespace app\home\logic;

use app\common\model\Customer;
use app\common\model\Product;
use app\common\model\File;
use app\common\model\User;
use app\common\controller\Errcode;
use think\Db;

class CustomerLogic{
	
	/**
	 * 增加客户
	 * @param array $data	数据
	 */
	public static function add($data)
	{
		// 看是否是管理员
		$model = new User();
		$find = $model->getOne(['mobile'=>$data['mobile']], 'id');
		if(! empty($find)){
			return -2;
		}
		
		$model = new Customer();
		$find = $model->getCustomerOne(['mobile'=>$data['mobile'], 'is_del'=>0], 'id');
		if(empty($find)){
			$data['create_time'] = NOW_TIME;
			$res = $model->addCustomerOne($data);
		}else{
			return -1;
		}
		
		return $res <=0 ? false :  $res;
	}
	
	/**
	 * 修改客户
	 * @param int	$id		记录ID
	 * @param array $data	数据
	 */
	public static function update($id, $data)
	{
		$model = new Customer();
		
		$data['update_time'] = NOW_TIME;
		$data['type'] == 1 && $data['company_name']='';
		
		$res = $model->updateCustomer(['id'=>$id], $data);
		
		return $res <=0 ? false : true;
	}
	
	/**
	 * 删除客户
	 * @param int	$id		记录ID
	 */
	public static function del($id)
	{
		$model = new Customer();
		$res = $model->updateCustomer(['id'=>$id], ['update_time'=>NOW_TIME, 'is_del'=>1]);
		// 删除客户的投资
		$model->updateInvest(['memid'=>$id], ['update_time'=>NOW_TIME, 'is_del'=>1]);
		
		return $res <=0 ? false : true;
	}
	
	/**
	 * 客户列表
	 * @param int $type			客户类别 0:全部1:个人2:机构
	 * @param int $productId	产品ID
	 * @param int $offset		偏移量
	 * @param int $psize		一页的大小
	 * @param string $keywords	关键字
	 */
	public static function lists($type=0, $productId=0, $offset=0, $psize=10, $keywords='')
	{
		$model = new Customer();
		
		$where = ['is_del'=>0];
		$type ==0 || $where['type'] = $type;
		if(!empty($keywords)){
			if(is_mobile($keywords)){
				$where['mobile'] = $keywords;
			}else{
				$where['name'] = ['like', "%$keywords%"];
			}
		}
		
		if($productId >0){
			// 先查满足投资产品条件的客户 再查对应的客户类别
			$productInfo = $model->getInvestGroup([
				'product_id'=>$productId, 'is_del'=>0
				], 'memid', $offset, $psize, 'id DESC');
			
			$memids = [];
			foreach($productInfo as $v){
				$memids[] = $v['memid'];
			}
			
			$memids = array_values(array_filter(array_unique($memids)));
			if(! empty($memids)){
				$where['id'] = ['in', $memids];
			}else{
				return [[], 0];
			}
		}
		
		// 先查对应的客户类别
		$list = $model->getCustomerMorePage($where, $offset, $psize, 
			'id,name,company_name,mobile,credentials,credentials_type,type', 'id DESC');
		$count = $model->countCustomer($where);
			
		foreach($list as &$v){
			$v['phone'] = $v['mobile'];
			$v['client_type'] = $v['type'];
			$v['id_number'] = $v['credentials'];
			$v['id_type'] = $v['credentials_type'];
			unset($v['type']);
			unset($v['mobile']);
			unset($v['credentials']);
			unset($v['credentials_type']);
		}
		
		return [empty($list) ? []:$list, $count];
	}
	
	/**
	 * 获取客户详情
	 * @param int $id	客户ID
	 */
	public static function detail($id)
	{
		$model = new Customer();
		
		// 获取基本信息
		$info = $model->getCustomerOne(['id'=>$id, 'is_del'=>0], 
			'type,mobile,name,company_name,credentials,credentials_type,id');
		$info['phone']			= $info['mobile'];
		$info['client_type']	= $info['type'];
		$info['id_number']		= $info['credentials'];
		$info['id_type']		= $info['credentials_type'];
		unset($info['type']);
		unset($info['mobile']);
		unset($info['credentials']);
		unset($info['credentials_type']);
		
		return $info;
	}
	
	/**
	 * 获取投资产品
	 * @param $id		int		客户ID
	 * @param $offset	int		偏移量
	 * @param $psize	int		一页大小
	 */
	public static function getInvestProduct($id, $offset, $psize)
	{
		$info = $productIds = [];
		// 获取投资信息
		$customerModel = new Customer();
		$count = $customerModel->getInvestCount(['memid'=>$id, 'is_del'=>0]);
		$investInfo = $customerModel->getInvestMorePage([
			'memid'=>$id, 'is_del'=>0
		], 'id,product_id,money,status,update_time', $offset, $psize, 'id DESC');
		foreach($investInfo as $v){
			$info[$v['id']] = [
				'money'			=> $v['money'],
				'operation'	=> $v['status'],
				'quit'			=> $v['update_time'],
				'product_id'	=> $v['product_id'],
				'report'		=> 0,
				'id'			=> $v['id']
			];
			$productIds[] = $v['product_id'];
		}
		unset($investInfo);
		
		$productIds = array_values(array_filter(array_unique($productIds)));
		if(! empty($productIds)){
			// 获取产品基本信息
			$model = new Product();
			$productBaseInfo = $model->getBaseMore([
				'id'=>['in', $productIds], 'is_del'=>0
			], 'title,status,id');
			foreach($productBaseInfo as $v){
				$productInfo[$v['id']] = [
					'title'		=>$v['title'],
					'status'	=>$v['status'],
				];
			}
			unset($productBaseInfo);
			
			// 获取产品附加信息
			$productExtraInfo = $model->getExtraMore([
				'product_id'=>['in', $productIds]
			], 'establish_time,delisting_time,deadline,product_id');
			foreach($productExtraInfo as $v){
				$productInfo[$v['product_id']]['establish_time']	= $v['establish_time'];
				$productInfo[$v['product_id']]['delisting_time']	= $v['delisting_time'];
				$productInfo[$v['product_id']]['deadline']		= $v['deadline'];
			}
			unset($productExtraInfo);
			
			// 获取投资产品报告
			$_reportInfo = $customerModel->getReportMoreGroup(['invest_id'=>['in',array_keys($info)]], 'invest_id', 'invest_id');
			$reportInfo = [];
			foreach($_reportInfo as $v){
				$reportInfo[$v['invest_id']] = true;
			}
			
			foreach($info as $k => &$v){
				if(! isset($productInfo[$v['product_id']])){
					unset($info[$k]);
				}
				
				$v['status']		=  $productInfo[$v['product_id']]['status'];
				$v['name']			=  $productInfo[$v['product_id']]['title'];
				$v['establish']	=  date('Y-m-d', $productInfo[$v['product_id']]['establish_time']);
				$v['deadline']		=  $productInfo[$v['product_id']]['deadline'];
				
				// 如果已经退市，则显示退市时间
				$productInfo[$v['product_id']]['delisting_time'] != 0 && 
					$v['quit']	= $productInfo[$v['product_id']]['delisting_time'];
				if($v['quit'] == 0){
					$v['quit'] = null;
				}else{
					$v['quit'] = date('Y-m-d', $v['quit']);
				}
				
				// 是否存在报告
				isset($reportInfo[$k]) && $v['report'] = 1;
			}
		}
		
		return [array_values($info), $count];
	}
	
	/**
	 * 根据手机号查找用户信息
	 * @param string $mobile	手机号码
	 */
	public static function getByMobile($mobile, $field='*')
	{
		$model = new Customer();
		$find = $model->getCustomerOne(['mobile'=>$mobile, 'is_del'=>0], $field);
		return empty($find) ? [] : $find;
	}
	
	/**
	 * 根据关键字搜索
	 * @param string $keywords 手机号码或者姓名
	 * @param int	$offset	   偏移量
	 * @param int	$limit	   一页大小
	 */
	public static function getName($keywords='', $offset=-1, $limit=10)
	{
		$model = new Customer();
		
		$where = ['is_del'=>0];
		if(! empty($keywords)){
			if(is_numeric($keywords)){
				// 填入的为手机号
				$where['mobile'] = ['like', "$keywords%"];
			}else{
				$where['name'] = ['like', "%$keywords%"];
			}
		}
		
		if($offset >=0){
			$list = $model->getCustomerMorePage($where, $offset, $limit, 'id,name,mobile,company_name,credentials,credentials_type,type');
		}else{
			$list = $model->getCustomerMore($where, 'id,name,mobile');
		}
		
		return empty($list) ? [] : $list;
	}
	
	/**
	 * 根据关键字搜索已经参与投资的客户
	 * @param string $keywords 手机号码或者姓名
	 */
	public static function getNameJoinInvestment($keywords='')
	{
		$model = new Customer();
		
		$where = ['a.status'=>0, 'a.is_del'=>0, 'b.is_del'=>0];
		if(! empty($keywords)){
			if(is_numeric($keywords)){
				// 填入的为手机号
				$where['b.mobile'] = ['like', "$keywords%"];
			}else{
				$where['b.name'] = ['like', "%$keywords%"];
			}
		}
		
		$join = [['member b', 'a.memid=b.id', 'LEFT']];
		$list = $model->getJoinCustomInvest($where, $join, 'a.product_id,b.name,b.id,b.mobile',
			0, 20, 'a.id DESC', 'b.id');
		
		return empty($list) ? [] : $list;
	}
	
	/**
	 * 根据产品ID获取客户
	 * @param int $productId	产品ID
	 * @param int	$offset	   偏移量
	 * @param int	$limit	   一页大小
	 */
	public static function getNameByProduct($productId, $offset=0, $limit=10)
	{
		$model = new Customer();
		
		if($offset > 0){
			$info = $model->getInvestMorePage(['product_id'=>$productId, 'status'=>0,'is_del'=>0], 
				'memid', $offset, $limit);
		}else{
			$info = $model->getInvestMore(['product_id'=>$productId, 'status'=>0,'is_del'=>0], 'memid');
		}
		
		$memids = [];
		foreach($info as $v){
			$memids[] = $v['memid'];
		}
		$memids = array_values(array_filter(array_unique($memids)));
		
		if(! empty($memids)){
			$memberInfo = $model->getCustomerMore(['id'=>['in',$memids], 'is_del'=>0], 'id,name');
		}
		$count = $model->countCustomer(['id'=>['in',$memids], 'is_del'=>0]);
		
		return [isset($memberInfo) ? $memberInfo : [], $count];
	}
	
	/**
	 * 导入客户数据
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
		$personalData = $companyData = $mobileArr = [];
		$customerModel = new Customer();
		$userModel = new User();
		
		for($k=3; $k<=$highestRow; $k++){
			$type = $objPHPExcel->getActiveSheet()->getCell("A$k")->getValue(); 
			if(empty($type)){
				//unlink($filename);
				throw new \Exception('第'.$k.'行客户类型不能为空', Errcode::FILE_DATA_ERR);
			}
			if(! in_array($type, [1,2])){
				//unlink($filename);
				throw new \Exception('第'.$k.'行客户类型只能是数字1或者2', Errcode::FILE_DATA_ERR);
			}

			$name = $objPHPExcel->getActiveSheet()->getCell("B$k")->getValue(); 
			if(empty($name)){
				//unlink($filename);
				throw new \Exception('第'.$k.'行客户姓名不能为空', Errcode::FILE_DATA_ERR);
			}
			if(mb_strlen($name,'UTF-8') > 20){
				//unlink($filename);
				throw new \Exception('第'.$k.'行客户姓名长度不能超过20个字符', Errcode::FILE_DATA_ERR);
			}
			
			$credentialsType = $objPHPExcel->getActiveSheet()->getCell("D$k")->getValue(); 
			if(empty($credentialsType)){
				//unlink($filename);
				throw new \Exception('第'.$k.'行证件类型不能为空', Errcode::FILE_DATA_ERR);
			}
			if(! in_array($credentialsType, [1,2,3,4,5,6])){
				//unlink($filename);
				throw new \Exception('第'.$k.'行证件类型只能是数字1,2,3,4,5,6', Errcode::FILE_DATA_ERR);
			}
			
			$credentials = $objPHPExcel->getActiveSheet()->getCell("E$k")->getValue(); 
			if(empty($credentials)){
				//unlink($filename);
				throw new \Exception('第'.$k.'行通信证号不能为空', Errcode::FILE_DATA_ERR);
			}
			if(strlen($credentials) > 32){
				//unlink($filename);
				throw new \Exception('第'.$k.'行证件号码长度不能超过32个字符', Errcode::FILE_DATA_ERR);
			}
			
			$mobile = $objPHPExcel->getActiveSheet()->getCell("F$k")->getValue(); 
			if(empty($mobile)){
				//unlink($filename);
				throw new \Exception('第'.$k.'行手机号码不能为空', Errcode::FILE_DATA_ERR);
			}
			$info = $userModel->getOne(['mobile'=>$mobile], 'id');
			if(! empty($info)){
				//unlink($filename);
				throw new \Exception('第'.$k.'行'.$name.'已经是管理员，不能添加成客户', Errcode::FILE_DATA_ERR);
			}
			$info = $customerModel->getCustomerOne(['mobile'=>$mobile, 'is_del'=>0], 'id');
			if(! empty($info)){
				//unlink($filename);
				throw new \Exception('第'.$k.'行'.$name.'客户已经存在', Errcode::FILE_DATA_ERR);
			}
			if(in_array($mobile, $mobileArr)){
				throw new \Exception('第'.$k.'行'.$name.'客户的手机号码重复', Errcode::FILE_DATA_ERR);
			}
			$mobileArr[] = $mobile;
			
			if($type ==1){
				$personalData[$k]['type'] = $type;
				$personalData[$k]['name'] = $name;
				$personalData[$k]['mobile'] = $mobile;
				$personalData[$k]['create_time'] = NOW_TIME;
				$personalData[$k]['credentials'] = $credentials;
				$personalData[$k]['credentials_type'] = $credentialsType;
			}else{
				$companyData[$k]['type'] = $type;
				$companyData[$k]['name'] = $name;
				$companyData[$k]['mobile'] = $mobile;
				$companyData[$k]['create_time'] = NOW_TIME;
				$companyData[$k]['credentials'] = $credentials;
				$companyData[$k]['credentials_type'] = $credentialsType;
				
				$companyName = $objPHPExcel->getActiveSheet()->getCell("C$k")->getValue(); 
				if(empty($companyName)){
					//unlink($filename);
					throw new \Exception('第'.$k.'行公司名称不能为空', Errcode::FILE_DATA_ERR);
				}
				if(mb_strlen($companyName,'UTF-8') > 32){
					//unlink($filename);
					throw new \Exception('第'.$k.'行公司名称长度不能超过32个字符', Errcode::FILE_DATA_ERR);
				}
				$companyData[$k]['company_name'] = $companyName;
			}
		}
		unset($mobileArr);
		
		// 快速返回
		header('Content-Type:application/json; charset=utf-8');
		echo json_encode(['code'=>200]);
		fastcgi_finish_request();
		ignore_user_abort(true);
		set_time_limit(0);
			
		//开启事务
		Db::startTrans();
		try {
			$ids = [];
			$data = array_merge($companyData,$personalData);
			foreach($data as $v){
				$ids[] = $customerModel->addCustomerOne($v);
			}
			
			//empty($companyData) || $customerModel->addCustomerMore($companyData);
			//empty($personalData) || $customerModel->addCustomerMore($personalData);
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			throw new \Exception($e->getMessage(), $e->getCode());
		}
		
		return $ids;
	}
	
	/**
	 * 获取memids中不存在通讯录的memids 并返回存在的信息
	 * @param array $memids		客户ids
	 */
	public static function getUnsetByMemids($memids)
	{
		$model = new Customer();
		
		$info = [];
		$_info = $model->getCustomerMore(['id'=>['in', $memids], 'is_del'=>0], 'id,name,mobile');
		foreach($_info as $v){
			$info[$v['id']] = $v;
		}
		
		return [$info, array_values(array_diff($memids, array_keys($info)))];
	}
	
	/**
	 * 根据客户ids获取用户名和手机号
	 * @param array $ids		客户ids
	 * @param bool  $name		是否读取名字
	 * @param bool  $mobile		是否读取手机号
	 */
	public static function getInfoByMemids($ids, $name=true, $mobile=true)
	{
		$field = ['id'];
		$name && $field[] = 'name';
		$mobile && $field[] = 'mobile';
		$field = implode(',', $field);

		$model = new Customer();
		$info = $model->getCustomerMore(['id'=>['in', $ids]], $field);
		
		return empty($info) ? [] : $info;
	}
	
	/**
	 * 读取客户的总人数
	 */
	public static function countMem()
	{
		$model = new Customer();
		return $model->countCustomer(['is_del'=>0]);
	}
}
