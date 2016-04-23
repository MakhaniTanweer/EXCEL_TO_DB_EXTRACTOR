<?php

function checkTypes($values,$schema,$mapping)
{
	$flag = true;
	foreach ($schema as $key => $value) {

		$type = $value['Type'];
		//var_dump($values[0][intval($mapping[$key])]);
		//var_dump($type);
		if(strpos(strtolower($type),'varchar'))
		    if(!is_string($values[0][$mapping[$key]]))
				$flag = false;

		if(strpos(strtolower($type),'int') || strpos(strtolower($type),'numeric'))
		    if(!is_float($values[0][$mapping[$key]]))
				$flag = false;

		//echo $flag ? 'true' : 'false';
	}	
	return $flag;
};

function addToDatabase($excelData,$schema,$mapping,$connection,$DestTable)
{
	$str = "";
	for ($i=0; $i < sizeof($schema); $i++) { 
		if($i == sizeof($schema)-1)
			$str = $str . '?';
		else
			$str  = $str . '?'.',';
	}

	$quer = "INSERT INTO ".$DestTable." VALUES (".$str.")";
	echo $quer;
	$stmt = $connection->prepare($quer);
	var_dump($stmt);
	$refs = array('dssss');
	foreach ($excelData[0] as $key => $value)
	{
	  var_dump($mapping[$key]);
	  $refs[] =& $excelData[0][intval($mapping[$key])];
	}
	
	call_user_func_array(array($stmt,'bind_param'),$refs);
	var_dump($refs);
	$stmt->execute();
};

$sourceFile   = $_POST["file"];
$sourceSheet  = $_POST["sheet"];
$DestDB       = $_POST["DB"];
$DestTable    = $_POST["Tab"];
$mode         = $_POST["mode"];
$map          = $_REQUEST["arr"];

$Database = $DestDB;
$tablename = $DestTable;
$ordering = "ADEBC";

$UserName = "root";
$Password = "fastiandevloper";
$server   = "localhost";

$sourceConnection = new mysqli($server, $UserName, $Password, $Database);
$sql = "DESCRIBE ".$tablename;
$resultSet = $sourceConnection->query($sql);
if(strtolower($mode) === 'overwrite')
	$sourceConnection->query("DELETE FROM ".$DestTable.";");
$feilds = array();
$idx = 0;

while ($row = $resultSet->fetch_assoc()) {
	$feilds[$idx++] =  $row;
}


set_include_path(get_include_path() . PATH_SEPARATOR . '../PHPExcel/Classes');


include 'PHPExcel/IOFactory.php';


$inputFileName = './'.$sourceFile.'';
$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objPHPExcel = $objReader->load($inputFileName);
$sheet = $objPHPExcel->getSheetByName($sourceSheet);
$highestRow = $sheet->getHighestRow(); 
$highestColumn = $sheet->getHighestColumn();

$flag = true;

//  Loop through each row of the worksheet in turn
for ($row = 2; $row <= $highestRow && $flag; $row++){ 
    //  Read a row of data into an array
    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                                    NULL,
                                    TRUE,
                                    FALSE);

    if(checkTypes($rowData,$feilds,$map))
    {
    	addToDatabase($rowData,$feilds,$map,$sourceConnection,$DestTable);
    }   
    else
    	$flag = false;
    //print_r($rowData);
}

?>