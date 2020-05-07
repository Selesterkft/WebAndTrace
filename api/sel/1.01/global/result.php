<?php
include_once 'class/database.php';

//Check params exists
if(isset($_GET["id"])==false){
    echo "Missing parameter: id...";
    die();
}else{
    $p_id=$_GET["id"];
}

//Connect to database
$database = new Database();
$conn = $database->getConnection("MAIN");

if( $conn ) {
    $sql = "SELECT result FROM MAP_Request WHERE ID=".strval($p_id);
    $stmt = sqlsrv_query($conn,$sql);

    if($stmt==false){
        echo "Read params from server failed...";
        die();
   }

   $row = sqlsrv_fetch_array($stmt, SQLSRV_SCROLL_FIRST);

   echo json_encode($row);
}
?>