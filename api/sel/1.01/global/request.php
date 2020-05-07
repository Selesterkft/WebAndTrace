<?php
include_once 'functions.php';
include_once 'class/database.php';
include_once 'class/settings.php';
include_once '../../../../selmap/tomtom/1.01/api/search.php';
include_once '../../../../selmap/tomtom/1.01/api/routing.php';

//Check post params exists
if(isset($_POST['request_params'])==false){
    echo "Missing post parameter: request_params...";
    http_response_code(400);
    die();
}else{
    $p_request_params=$_POST['request_params'];
    }

if(isset($_POST['function_params'])==false){
    echo "Missing post parameter: function_params...";
    http_response_code(400);
    die();
}else{
    $p_function_params=$_POST['function_params'];
    }

if(isset($_POST['map_params'])==false){
    $p_map_params="";
}else{
    $p_map_params=$_POST['map_params'];
    }
        
//Check JSON format
if(isJSON($p_request_params)==false){
    echo "request_params error: not valid JSON format";
    http_response_code(400);
    die();
}

if(isJSON($p_function_params)==false){
    echo "function_params error: not valid JSON format";
    http_response_code(400);
    die();
}

if (isset($map_params)){
    if(isJSON($p_map_params)==false){
        echo "map_params error: not valid JSON format";
        http_response_code(400);
        die();
    }
}

//Get client ip
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

//Check request_params
$_params=json_decode($p_request_params);

if(isset($_params->selid)==false){
    echo "Missing parameter value: selid...";
    http_response_code(400);
    die();
}

if(isset($_params->provider)==false){
    echo "Missing parameter value: provider...";
    http_response_code(400);
    die();
}else{
    $provider=$_params->provider;
}

if(isset($_params->action)==false){
    echo "Missing parameter value: action...";
    http_response_code(400);
    die();
}

if(isset($_params->terminal)==false){
    echo "Missing parameter value: terminal...";
    http_response_code(400);
    die();
}

if(isset($_params->userid)==false){
    echo "Missing parameter value: userid...";
    http_response_code(400);
    die();
}

//Connect to database
$database=new Database();
$conn=$database->getConnection("MAIN");

if($conn){
    $sql= "{call MAP_ADD_REQUEST (?,?,?,?)}";
    
    $params=array(
                    array(&$p_request_params, SQLSRV_PARAM_IN),
                    array(&$p_function_params, SQLSRV_PARAM_IN),
                    array(&$p_map_params, SQLSRV_PARAM_IN),
                    array(&$ip, SQLSRV_PARAM_IN)
    );

    $stmt=sqlsrv_query($conn, $sql, $params);

    if($stmt==false){
        echo "Creating request failed...".print_r(sqlsrv_errors());
        die();
   }
   
   $reqid = sqlsrv_fetch_array($stmt)["reqid"];

   sqlsrv_free_stmt($stmt);
   sqlsrv_close($conn);
}

$results=array();
if($_params->provider=="TOMTOM" && ($_params->action=="API:SEARCH" || $_params->action=="MAP:SEARCH")){
    $results=setSearchResult($reqid, $_params->selid, $p_function_params, $p_map_params);
}

if($_params->provider=="TOMTOM" && ($_params->action=="API:ROUTING" || $_params->action=="MAP:ROUTING")){
    $results=setRoutingResult($reqid, $_params->selid, $p_function_params, $p_map_params);
}

echo json_encode($results);
?>