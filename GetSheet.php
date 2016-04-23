<?PHP
set_include_path(get_include_path() . PATH_SEPARATOR . '../PHPExcel/Classes');

include 'PHPExcel/IOFactory.php';

$file = $_POST["file"];


$inputFileName = './'.$file;
$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$worksheetNames = $objReader->listWorksheetNames($inputFileName);

$response = array();

foreach ($worksheetNames as $value) {
	$response[] = $value;
}

print(json_encode($response));

?>