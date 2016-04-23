<?php
$UserName = "root";
$Password = "fastiandevloper";
$server   = "localhost";

$sourceConnection = new mysqli($server, $UserName, $Password, "Company");

$sql = "Show Databases";

$resultSet = $sourceConnection->query($sql);

$response = array();
while($row = $resultSet->fetch_assoc()){
	$response[] = $row['Database'];
}
	print(json_encode($response));
?>