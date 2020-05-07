<?php
function setRequest($provider,$reqid,$resjson,$URL){
//Connect to database
$database=new Database();
$conn=$database->getConnection($provider);

if($conn){
    $sql= "{call MAP_SET_REQUEST_RESULT (?,?,?)}";
    
    $params=array(
                    array(&$reqid, SQLSRV_PARAM_IN),
                    array(&$resjson, SQLSRV_PARAM_IN),
                    array(&$URL, SQLSRV_PARAM_IN)
    );

    $stmt=sqlsrv_query($conn, $sql, $params);

    if($stmt==false){
        echo "Creating request failed...".print_r(sqlsrv_errors());
        die();
   }

   sqlsrv_free_stmt($stmt);
   sqlsrv_close($conn);
}
}

function isJSON($string){
    return is_string($string) && is_array(json_decode($string, true)) ? true : false;
 }
?>