<?php
/**
 * excel处理
 * by sherlock
 */

namespace app\common\logic;

use app\common\model\File;
use app\common\controller\Errcode;

class ExeclLogic{
	
	/**
	 * 生成execl表格下载
	 * @param string $tilte 生成的excel名
	 * @param array $tableHead 表头数据 
	 * format [
	 *			['title'=>value,'width'=>value,'hight'=>value,'horizontal'=>value, 'vertical'=>value],
	 *			倒数第二个参数的值得选择为'general','left','right','center','centerContinuous','justify'
	 *			最后一个参数的值选择为'bottom','top','center','justify'
	 *			[]
	 *		 ]  
	 * @param array $data 数据
	 * format  [ 
	 *			key => [ key表示一部分的标题
	 *					[key=>value,key=>value],[key=>value,key=>value] 
	 *				],
	 *			
	 *		   ]
	 */
	public static function download($title, $tableHead, $data)
	{
		vendor('phpexcel.PHPExcel');
		$letter = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N',
			'O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD'];
		$horizontal = [
			'general'	=> \PHPExcel_Style_Alignment::HORIZONTAL_GENERAL,
			'left'		=> \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			'right'		=> \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			'center'	=> \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'justify'	=> \PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY,
			'centerContinuous' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER_CONTINUOUS
		];
		$vertical = [
			'bottom'	=> \PHPExcel_Style_Alignment::VERTICAL_BOTTOM,
			'top'		=> \PHPExcel_Style_Alignment::VERTICAL_TOP,
			'center'	=> \PHPExcel_Style_Alignment::VERTICAL_CENTER,
			'justify'	=> \PHPExcel_Style_Alignment::VERTICAL_JUSTIFY
		];
		
		$excel = new \PHPExcel();
		
		$i = 0;
		$j = 1;
		$q = 0;
		foreach($data as $k => $block){
			$count = count($tableHead[$q]);
			$excel->getActiveSheet()->setCellValue("A{$j}",$k);
			$excel->getActiveSheet()->mergeCells('A'.$j.':'.$letter[$count-1].$j);
			$j++;
			foreach($tableHead[$q] as $v){
				$excel->getActiveSheet()->setCellValue($letter[$i].$j, $v['title']);
				isset($v['width']) && $excel->getActiveSheet()->getColumnDimension($letter[$i])->setWidth($v['width']);
				isset($v['hight']) && $excel->getActiveSheet()->getRowDimension($letter[$i])->setRowHeight($v['hight']);
				isset($v['horizontal']) && isset($horizontal[$v['horizontal']]) && $excel->getActiveSheet()->getStyle($letter[$i])
					->getAlignment()->setHorizontal($horizontal[$v['horizontal']]);
				isset($v['vertical']) && isset($horizontal[$v['vertical']]) && $excel->getActiveSheet()->getStyle($letter[$i])
					->getAlignment()->setVertical($vertical[$v['vertical']]);
				$i++;
			}
			
			$i=0;
			$j++;
			foreach($block as $row){
				foreach($row as $col){
					$excel->getActiveSheet()->setCellValue($letter[$i].$j,$col);
					$i++;
				}
				$i = 0;
				$j++;
			}
			$q++;
		}
		
		$write = new \PHPExcel_Writer_Excel5($excel);
		$title = urlencode($title);
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl;charset=utf-8");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");
		header("Content-Disposition:attachment;filename={$title}.xls");//要生成的表名
		header("Content-Transfer-Encoding:binary");
		$write->save('php://output');
		exit;
	}
}
