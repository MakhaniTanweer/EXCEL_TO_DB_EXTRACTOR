<?PHP

set_include_path(get_include_path() . PATH_SEPARATOR . '../PHPExcel/Classes');

/** PHPExcel_IOFactory */
include 'PHPExcel/IOFactory.php';

//$file = $_POST["file"];
//$sheet= $_POST["sheet"];

$file = $_POST["file"];
$sheet = $_POST["sheet"];


$inputFileName = './'.$file;

$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objPHPExcel = $objReader->load($inputFileName);


$sheetData = $objPHPExcel->getSheetByName($sheet)->toArray(null,true,true,true);

$columns = array();
foreach ($sheetData[1] as $key => $value) {
	$columns[] = $value;
}

print json_encode($columns);

?>