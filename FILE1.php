<?php
if (isset($_POST['sourceDB']) && isset($_POST['sourceTable']) && isset($_POST['mode']) && isset($_POST['destDB'])) {
    $sourceDatabase = $_POST['sourceDB'];
    $sourceTables = $_POST['sourceTable'];
    $modeOfInsertion = $_POST['mode'];
    $destinationDatabase = $_POST['destDB'];

    $UserName = "root";
    $Password = "fastiandevloper";
    $server = "localhost";
    $sql = "";

    $destTable = "";
    $result = null;
    $tablesInDest = null;
    $sourceSchema = array();
    $sourceColumns = array();
    $destSchema = array();
    $destColumns = array();

    $sourceConnection = new mysqli($server, $UserName, $Password, $sourceDatabase);
    $destConnection = new mysqli($server, $UserName, $Password, $destinationDatabase);

    //get the source schema
    if ($sourceConnection->connect_error) {
        die("Connection failed: " . $sourceConnection->connect_error);
    }


    $sql = "DESCRIBE " . strtolower($sourceTables);
    $result = $sourceConnection->query($sql);

    if ($result->num_rows > 0) {
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            $sourceSchema[] = $row["Type"];
            $sourceColumns[] = $row["Field"];
        }
    } else {
        echo "0 results";
    }

    //get the tables in destination database
    if ($destConnection->connect_error) {
        die("Connection failed: " . $destConnection->connect_error);
    }
    $tablesInDest = array();
    $sql = "SHOW TABLES";
    $result = $destConnection->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tablesInDest[] = $row["Tables_in_" . strtolower($destinationDatabase)];
        }
    } else {
        echo "0 results";
    }

    //get the tables in destination database

    //compare each table in destination database
    for ($j = 0; $j < count($tablesInDest); $j++) {
        $sql = "DESCRIBE " . strtolower($tablesInDest[$j]);
        $result = $destConnection->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $destSchema[] = $row["Type"];
                $destColumns[] = $row["Field"];
            }

        } else {
            echo "0 results";
        }
        //check if both these schemas match
        $flag = true;
        for ($i = 0; $i < count($sourceSchema) && $i < count($destSchema); $i++) {
            if (strcmp($sourceSchema[$i], $destSchema[$i])) {
                $flag = false;
            }

        }
        //check if both these schemas match
        if ($flag && count($destSchema) >= count($sourceSchema)) {
            $destTable = $tablesInDest[$j];
            break;
        } else {
            $destSchema = null;
            $destSchema = array();
            $destColumns = null;
            $destColumns = array();
        }
    }
    //compare each table in destination database


    if ($destTable === "")
        echo "Failure";

    else {

        AddtoDestination($sourceTables, $sourceConnection, $destConnection, $sourceColumns, $sourceSchema, $destColumns,$destTable,$destSchema,$modeOfInsertion);
        echo "Success";
    }

}

function AddtoDestination($sourceTables, $sourceConnection, $destConnection, $sourceColumns, $sourceSchema, $destColumns,$destTable,$destSchema,$method)
{
    $sql = "SELECT * FROM " . strtolower($sourceTables);
    $result = $sourceConnection->query($sql);
    $dataout = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $dataout = null;
            $dataout = array();
            for ($i = 0; $i < count($result->fetch_fields()); $i++) {
                $dataout[] = $row[$sourceColumns[$i]];
            }
            $QUERY = prepare_String($dataout, $sourceSchema,$destSchema);
            if(strtolower($method) === 'overwrite')
            {
                $records = $result->num_rows;
                $query = "DELETE FROM ".$destTable." LIMIT ".$records.";";
                $destConnection->query($query);
            }
            $sql = "INSERT INTO " . strtolower($destTable) . "(" . implode(",", $destColumns) . ") VALUES (" . $QUERY . "); ";
	    
            $destConnection->query($sql);
        }

    }
}

function prepare_String($dataout, $sourceSchema,$destSchema)
{
    $QUERY = "";
    $NULL = "null";
    for ($i = 0; $i < count($sourceSchema); $i++) {

        if (strpos($sourceSchema[$i], "int") === false || !strtolower($dataout[$i]) === "null") {
            $QUERY = $QUERY . "'" . $dataout[$i] . "',";
        } else {
            $QUERY = $QUERY . $dataout[$i] . ",";
        }
    }
    for($i = count($sourceSchema);$i<count($destSchema);$i++)
    {
        $QUERY = $QUERY . '0' . ",";
    }
    return substr($QUERY, 0, strlen($QUERY) - 1);
}
?>