<?php

function checkTypes($values,$schema,$mapping)
{
	$flag = true;
	foreach ($schema as $key => $value) {

		$type = $value['Type'];
		//var_dump($values[0][intval($mapping[$key])]);
		//var_dump($type);

		if( intval($mapping[$key])=== 10000 || intval($mapping[$key]) === -1 )
			continue;

		if(strpos(strtolower($type),'varchar') !== false)
		    if(!is_string($values[0][$mapping[$key]]))
				$flag = false;

		if(strpos(strtolower($type),'int') !== false || strpos(strtolower($type),'numeric') !== false)
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
	//echo $quer;
	$stmt = $connection->prepare($quer);
	//var_dump($stmt);
	$types = array();
	foreach ($schema as $key => $value) {
		$type = $value['Type'];
		if(strpos(strtolower($type),'varchar') !== false)
			$types[] = 's';
		else if (strpos(strtolower($type),'int') !== false)
			$types[] = 'i';
		else if (strpos(strtolower($type),'numeric') !== false)
			$types[] = 'i';
		else if (strpos(strtolower($type),'decimal') !== false)
			$types[] = 'd';
		else if (strpos(strtolower($type),'date') !== false)
			$types[] = 's';
		else
			$types[] = 's';
	}
	$datatype = implode($types);
	$refs = array($datatype);

	foreach ($excelData[0] as $key => $value)
	{
	  if( intval($mapping[$key])=== 10000 || intval($mapping[$key]) === -1 )
	  {
	  	$nul = null;
	  	$refs[] = & $nul;
	  }
	  else
	  	$refs[] =& $excelData[0][intval($mapping[$key])];
	}
	//var_dump($refs);
	call_user_func_array(array($stmt,'bind_param'),$refs);

	//var_dump($stmt);
	$stmt->execute();
};

$sourceFile   = $_POST["file"];
$sourceSheet  = $_POST["sheet"];
$DestDB       = $_POST["DB"];
$DestTable    = $_POST["Tab"];
$mode         = $_POST["mode"];
$map          = $_REQUEST["arr"];

//var_dump($map);

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

$succ = 0;
$unsucc = 0;
$errorlog = array();;

//  Loop through each row of the worksheet in turn
for ($row = 2; $row <= $highestRow; $row++){ 
    //  Read a row of data into an array
    $unsucc++;
    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                                    NULL,
                                    TRUE,
                                    FALSE);

    if(checkTypes($rowData,$feilds,$map))
    {
    	$succ++;
    	//echo "string";
    	addToDatabase($rowData,$feilds,$map,$sourceConnection,$DestTable);
    }   
    else
    {
    	$errorlog [] = implode(" ",$rowData[0]);
    }
    //print_r($rowData);
}
$errorlist  = implode("\n",$errorlog);
$result = array();
$unsucc = $unsucc - $succ;
$result[] = $succ;
$result[] = $unsucc;
$result[] = $errorlist;
echo json_encode($result);
?>