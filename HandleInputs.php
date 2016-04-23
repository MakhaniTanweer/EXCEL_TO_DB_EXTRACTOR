<?php
if (isset($_POST['source']))
{	
	$dbhost = "localhost";
    $dbuser = "root";
    $dbpass = "fastiandevloper";
    $dbname = $_POST['source'];
    $conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
    $quer = "Show Tables";
    $respone = "";
    $result = $conn->query($quer);
    if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $keys = array_keys($row);
        $respone = $respone.$row[$keys[0]];
        $respone = $respone.(" ");
        
        }
    echo $respone;
    }
}

?>