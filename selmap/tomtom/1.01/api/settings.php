<?php
include_once '../../../api/1.01/global/class/database.php';
include_once '../../../api/1.01/global/class/settings.php';

//Check params exists
if(isset($_POST['id'])==false){
    echo "Missing parameter: id...";
    die();
}else{
    $p_id=$_POST['id'];
}

//Connect to database
$database = new Database();
$conn = $database->getConnection();

if( $conn ) {
    //Read tomtom params from database
    $settings = new Settings();
    $stmt = $settings->getSettings($conn,$p_id);

    if($stmt==false){
        echo "Read params from server failed...";
        die();
   }

   $row = sqlsrv_fetch_array($stmt, SQLSRV_SCROLL_FIRST);

   echo json_encode($row);
}
?>