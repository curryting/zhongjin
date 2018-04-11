<?php
/**
 * 对短信的操作
 * by sherlock
 */
namespace app\common\model;

use think\Model;
use think\Response;
use think\exception\HttpResponseException;

class Estone extends Model{
	protected $table = 'sms_mt';
	
	// 设置当前模型的数据库连接
    protected $connection = [
        // 数据库类型
        'type'        => 'mysql',
        // 服务器地址
        'hostname'    => 'rdsryyvrarei2qj.mysql.rds.aliyuncs.com',
        // 数据库名
        'database'    => 'estone',
        // 数据库用户名
        'username'    => 'jiaxiaotong',
        // 数据库密码
        'password'    => 'Kuning-Rds-@$160418',
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',
        // 数据库调试模式
        'debug'       => false,
    ];
	
	protected function initialize()
    {
        parent::initialize();
    }
	
	
	
	/**
	 * 增加一条数据
	 * @param array $data	数据
	 */
	public function addOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->insert($data, false, true);
	}
	
}


