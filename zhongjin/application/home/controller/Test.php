<?php

namespace app\home\controller;

use think\Controller;
use app\common\model\Customer;
use app\common\model\News;
use app\common\model\Vote;
use my\Weixin;


class Test extends Root{
	
	public function index()
	{
		
		return $this->resultOk();
	}
	
	public function index1()
	{
		vendor('phpexcel.PHPExcel.IOFactory');

		$inputFileType = 'Excel5';
		$inputFileName = '/tmp/example1.xls';

		/**  Create a new Reader of the type defined in $inputFileType  **/
		$objReader = \PHPExcel_IOFactory::createReader($inputFileType);
		/**  Load $inputFileName to a PHPExcel Object  **/
		$objPHPExcel = $objReader->load($inputFileName);


		echo '<hr />';

		/**  Read the document's creator property  **/
		$creator = $objPHPExcel->getProperties()->getCreator();
		echo '<b>Document Creator: </b>',$creator,'<br />';

		/**  Read the Date when the workbook was created (as a PHP timestamp value)  **/
		$creationDatestamp = $objPHPExcel->getProperties()->getCreated();
		/**  Format the date and time using the standard PHP date() function  **/
		$creationDate = date('l, d<\s\up>S</\s\up> F Y',$creationDatestamp);
		$creationTime = date('g:i A',$creationDatestamp);
		echo '<b>Created On: </b>',$creationDate,' at ',$creationTime,'<br />';

		/**  Read the name of the last person to modify this workbook  **/
		$modifiedBy = $objPHPExcel->getProperties()->getLastModifiedBy();
		echo '<b>Last Modified By: </b>',$modifiedBy,'<br />';

		/**  Read the Date when the workbook was last modified (as a PHP timestamp value)  **/
		$modifiedDatestamp = $objPHPExcel->getProperties()->getModified();
		/**  Format the date and time using the standard PHP date() function  **/
		$modifiedDate = date('l, d<\s\up>S</\s\up> F Y',$modifiedDatestamp);
		$modifiedTime = date('g:i A',$modifiedDatestamp);
		echo '<b>Last Modified On: </b>',$modifiedDate,' at ',$modifiedTime,'<br />';

		/**  Read the workbook title property  **/
		$workbookTitle = $objPHPExcel->getProperties()->getTitle();
		echo '<b>Title: </b>',$workbookTitle,'<br />';

		/**  Read the workbook description property  **/
		$description = $objPHPExcel->getProperties()->getDescription();
		echo '<b>Description: </b>',$description,'<br />';

		/**  Read the workbook subject property  **/
		$subject = $objPHPExcel->getProperties()->getSubject();
		echo '<b>Subject: </b>',$subject,'<br />';

		/**  Read the workbook keywords property  **/
		$keywords = $objPHPExcel->getProperties()->getKeywords();
		echo '<b>Keywords: </b>',$keywords,'<br />';

		/**  Read the workbook category property  **/
		$category = $objPHPExcel->getProperties()->getCategory();
		echo '<b>Category: </b>',$category,'<br />';

		/**  Read the workbook company property  **/
		$company = $objPHPExcel->getProperties()->getCompany();
		echo '<b>Company: </b>',$company,'<br />';

		/**  Read the workbook manager property  **/
		$manager = $objPHPExcel->getProperties()->getManager();
		echo '<b>Manager: </b>',$manager,'<br />';
		
		echo '<hr />';
		
		return ;
	}
	
	public function index2()
	{
		vendor('phpexcel.PHPExcel.IOFactory');
		
		$inputFileType = 'Excel2007';
		$inputFileName = '/tmp/example1.xlsx';

		/**  Create a new Reader of the type defined in $inputFileType  **/
		$objReader = \PHPExcel_IOFactory::createReader($inputFileType);
		/**  Load $inputFileName to a PHPExcel Object  **/
		$objPHPExcel = $objReader->load($inputFileName);

		/**  Read an array list of any custom properties for this document  **/
		$customPropertyList = $objPHPExcel->getProperties()->getCustomProperties();

		echo '<b>Custom Property names: </b><br />';
		foreach($customPropertyList as $customPropertyName) {
			echo $customPropertyName,'<br />';
		}

		echo '<hr />';

		/**  Read an array list of any custom properties for this document  **/
		$customPropertyList = $objPHPExcel->getProperties()->getCustomProperties();

		echo '<b>Custom Properties: </b><br />';
		/**  Loop through the list of custom properties  **/
		foreach($customPropertyList as $customPropertyName) {
			echo '<b>',$customPropertyName,': </b>';
			/**  Retrieve the property value  **/
			$propertyValue = $objPHPExcel->getProperties()->getCustomPropertyValue($customPropertyName);
			/**  Retrieve the property type  **/
			$propertyType = $objPHPExcel->getProperties()->getCustomPropertyType($customPropertyName);

			/**  Manipulate properties as appropriate for display purposes  **/
			switch($propertyType) {
				case 'i' :	//	integer
					$propertyType = 'integer number';
					break;
				case 'f' :	//	float
					$propertyType = 'floating point number';
					break;
				case 's' :	//	string
					$propertyType = 'string';
					break;
				case 'd' :	//	date
					$propertyValue = date('l, d<\s\up>S</\s\up> F Y g:i A',$propertyValue);
					$propertyType = 'date';
					break;
				case 'b' :	//	boolean
					$propertyValue = ($propertyValue) ? 'TRUE' : 'FALSE';
					$propertyType = 'boolean';
					break;
			}

			echo $propertyValue,' (',$propertyType,')<br />';
		}
		
		return ;
	}
	
	public function index3()
	{
		vendor('phpexcel.PHPExcel.IOFactory');
		
		$inputFileType = 'Excel5';
		$inputFileName = '/tmp/example2.xls';

		/**  Create a new Reader of the type defined in $inputFileType  **/
		$objReader = \PHPExcel_IOFactory::createReader($inputFileType);
		/**  Load $inputFileName to a PHPExcel Object  **/
		$objPHPExcel = $objReader->load($inputFileName);


		echo '<hr />';

		echo 'Reading the number of Worksheets in the WorkBook<br />';
		/**  Use the PHPExcel object's getSheetCount() method to get a count of the number of WorkSheets in the WorkBook  */
		$sheetCount = $objPHPExcel->getSheetCount();
		echo 'There ',(($sheetCount == 1) ? 'is' : 'are'),' ',$sheetCount,' WorkSheet',(($sheetCount == 1) ? '' : 's'),' in the WorkBook<br /><br />';

		echo 'Reading the names of Worksheets in the WorkBook<br />';
		/**  Use the PHPExcel object's getSheetNames() method to get an array listing the names/titles of the WorkSheets in the WorkBook  */
		$sheetNames = $objPHPExcel->getSheetNames();
		foreach($sheetNames as $sheetIndex => $sheetName) {
			echo 'WorkSheet #',$sheetIndex,' is named "',$sheetName,'"<br />';
		}
	}
	
	public function index4()
	{
		vendor('phpexcel.PHPExcel.IOFactory');
		
		$inputFileType = 'Excel5';
		$inputFileName = '/data/www/zhongjin/../zhongjin_uploads/20171103/c71390fde728c74392b1cb579e6c9dde.xlsx';
		
		echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using PHPExcel_Reader_Excel5<br />';
		$fileType = \PHPExcel_IOFactory::identify($inputFileName);
		$objReader = \PHPExcel_IOFactory::createReader($fileType);
		//$objReader->setLoadSheetsOnly('Data Sheet #1');
		//$objReader = new \PHPExcel_Reader_Excel5();
		//	$objReader = new PHPExcel_Reader_Excel2007();
		//	$objReader = new PHPExcel_Reader_Excel2003XML();
		//	$objReader = new PHPExcel_Reader_OOCalc();
		//	$objReader = new PHPExcel_Reader_SYLK();
		//	$objReader = new PHPExcel_Reader_Gnumeric();
		//	$objReader = new PHPExcel_Reader_CSV();
		$objPHPExcel = $objReader->load($inputFileName);

		
		echo '<hr />';

		//$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		//echo '<pre>';
		//var_dump($sheetData);
		$sheet = $objPHPExcel->getSheet(0);
		$highestRow = $sheet->getHighestRow();
		$highestColumn = $sheet->getHighestColumn();
		$tempArray = array();  
		for($j=2; $j<=$highestRow; $j++){  
			for($k='A';$k<=$highestColumn;$k++){   
				if($k=='F'){//指定H列为时间所在列  
					$time = \PHPExcel_Shared_Date::ExcelToPHP($objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue()); 
					$time = date('Y-m-d', $time);
					$time = strtotime($time);
				   $tempArray[] =   $time;
				}else{  
					 $tempArray[] = $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();  
				}  
				echo "<pre>";  
				print_r($tempArray);  
				unset($tempArray);  
				echo "</pre>";  
			}  
		}
	}
	
	public function index5()
	{
		vendor('phpexcel.PHPExcel.IOFactory');
		
		$inputFileType = 'Excel2007';
		//$inputFileName = '/data/www/zhongjin/../zhongjin_uploads/20171101/c4cfe74f2de9cb1494c32f6a4afd620f.xlsx';
		$inputFileName = '/tmp/example1.xlsx';
		echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using PHPExcel_Reader_Excel5<br />';
		//$objReader = \PHPExcel_IOFactory::createReader($inputFileType);
		
		//$objReader = new \PHPExcel_Reader_Excel5();
			$objReader = new \PHPExcel_Reader_Excel2007();
		//	$objReader = new PHPExcel_Reader_Excel2003XML();
		//	$objReader = new PHPExcel_Reader_OOCalc();
		//	$objReader = new PHPExcel_Reader_SYLK();
		//	$objReader = new PHPExcel_Reader_Gnumeric();
		//	$objReader = new PHPExcel_Reader_CSV();
		echo 'Turning Formatting off for Load<br />';
		//$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load($inputFileName);


		echo '<hr />';

		$sheetData = $objPHPExcel->getActiveSheet();
		//$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		echo '<pre>';
		//var_dump($sheetData);
	}
	
	public function index6()
	{
		vendor('phpexcel.PHPExcel.IOFactory');
		$inputFileType = 'Excel5';
		//	$inputFileType = 'Excel2007';
		//	$inputFileType = 'Excel2003XML';
		//	$inputFileType = 'OOCalc';
		//	$inputFileType = 'Gnumeric';
		$inputFileName = '/tmp/ex2.xls';

		echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using IOFactory with a defined reader type of ',$inputFileType,'<br />';
		/**  Create a new Reader of the type defined in $inputFileType  **/
		$objReader = \PHPExcel_IOFactory::createReader($inputFileType);


		echo '<hr />';


		/**  Define how many rows we want for each "chunk"  **/
		$chunkSize = 20;

		/**  Loop to read our worksheet in "chunk size" blocks  **/
		for ($startRow = 2; $startRow <= 240; $startRow += $chunkSize) {
			echo 'Loading WorkSheet using configurable filter for headings row 1 and for rows ',$startRow,' to ',($startRow+$chunkSize-1),'<br />';
			/**  Create a new Instance of our Read Filter, passing in the limits on which rows we want to read  **/
			$chunkFilter = new Test1($startRow,$chunkSize);
			/**  Tell the Reader that we want to use the new Read Filter that we've just Instantiated  **/
			$objReader->setReadFilter($chunkFilter);
			/**  Load only the rows that match our filter from $inputFileName to a PHPExcel Object  **/
			$objPHPExcel = $objReader->load($inputFileName);

			//	Do some processing here

			$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			//echo '<pre>';
			var_dump($sheetData);
			echo '<br /><br />';
		}
	}
	
	public function index7()
	{
		$dom = new \DOMDocument('1.0');
		
		$books = $dom->appendChild($dom->createElement('books'));
		$book = $books->appendChild($dom->createElement('book'));
		$name = $book->appendChild($dom->createElement('name'));
		$name->appendChild($dom->createTextNode('ws'));
		
		$dom->formatOutput = true;
		
		$test = $dom->saveXML();
		echo $test;
		$dom->save('test.xml');
//		$doc = new \DOMDocument('1.0');
//		// we want a nice output
//		$doc->formatOutput = true;
//
//		$root = $doc->createElement('book');
//		$root = $doc->appendChild($root);
//
//		$title = $doc->createElement('title');
//		$title = $root->appendChild($title);
//
//		$text = $doc->createTextNode('This is the title');
//		$text = $title->appendChild($text);
//
//		echo "Saving all the document:\n";
//		echo $doc->saveXML() . "\n";
//
//		echo "Saving only the title part:\n";
//		echo $doc->saveXML($title);
//		$doc->save('text1.xml');
	}
	
	public function index8()
	{
		$dom = new \DOMDocument();
		//$dom->load('test.xml');
		$dom->load('test.xml');
		echo $dom->saveXML();
		
	}
	
	public function index9()
	{
//		$xml = new \SimpleXMLElement('test.xml');
//		echo $xml->books[0]->book[0]->name;
		$file = 'test.xml';
		$xml = simplexml_load_file($file);
		var_dump($xml);
		
		echo $xml[0]->book[0]->name;
	}
}

