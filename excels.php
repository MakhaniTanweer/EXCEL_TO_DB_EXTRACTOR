<?php
$response = array();
foreach (scandir('.') as $file)
    if(strpos($file,"xls") !== false || strpos($file,"xlsx") !== false) 
    	$response[] = $file;
print(json_encode($response));
?>