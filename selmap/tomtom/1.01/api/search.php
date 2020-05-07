<?php
function setSearchResult($reqid, $selid, $function_params, $map_params){
//Check params exists
$_params=json_decode($function_params);

if(isset($_params->popuptitle)){
    $p_popuptitle=$_params->popuptitle;
}

if(isset($_params->popuphead)){
    $p_popuphead=$_params->popuphead;
}
if(isset($_params->popupbody)){
    $p_popupbody=$_params->popupbody;
}
$p_query="";

if(isset($_params->country)){
    $p_country=$_params->country;
    $p_query=$p_country;
}

if(isset($_params->zip)){
    $p_zip=$_params->zip;
    $p_query.=' '.$p_zip;
}

if(isset($_params->city)){
    $p_city=$_params->city;
    $p_query.=" ".$p_city;
}

if(isset($_params->addr)){
    $p_addr=$_params->addr;
    $p_query.=" ".$p_addr;
}

if($p_query==""){
    echo "Missing search params...";
    die();
}else{
    $p_query=urlencode($p_query);
}

//Connect to database
$database = new Database();
$conn = $database->getConnection("MAIN");

if( $conn ) {
     //Read tomtom params from database
     $settings = new Settings();
     $stmt = $settings->getSettings($conn,$selid);

    if($stmt==false){
         echo "Read params from server failed...";
         die();
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_SCROLL_FIRST);

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
 
    $p_baseURL = $row["Search_baseURL"];
    $p_APIkey = "&key=".$row["APIkey"];

    //Call tomtom routing api
    $routeURL = $p_baseURL."/".$p_query.".json?".$p_APIkey;

    $result = file_get_contents($routeURL,false, stream_context_create(["http" => ["ignore_errors" => true]]));
    if($result === FALSE){
         echo "TomTom api call failed...";
         die();
    }

    $out=false;
    $arr=json_decode($result);
    foreach ($arr->results as $res) {
        if($res->type=="Point Address") {
            $out=true;
        }

        if($res->type=="Cross Street" && $out==false) {
            $out=true;
        }

        if($res->type=="Street" && $out==false) {
            $out=true;
        }

        if($res->type=="Geography" && $out==false) {
            $out=true;
        }

        if($res->type=="POI" && $out==false) {
            $out=true;
        }

        if($out==true){
            $type=$res->type;
            $freeformAddress=$res->address->freeformAddress;
            $lat=$res->position->lat;
            $lon=$res->position->lon;
    
            $resjson=json_encode(array("key"=>$row["APIkey"],"popuptitle"=>$type,"popuphead"=>$p_popuphead,"popupbody"=>$p_popupbody,"lat"=>$lat,"lon"=>$lon,"map_settings"=>$map_params));

            break;
        }
    }

    if($map_params==""){
        $resURL="";
    }else{
        $resURL=$row["Search_URL"]."?id=".$reqid;
    }

    setRequest("MAIN",$reqid,$resjson,$resURL);

    $requestres=array("reqid"=>$reqid,"URL"=>$resURL,"lat"=>$lat,"lon"=>$lon,"freeformAddress"=>$freeformAddress,"type"=>$type);
    return $requestres;
}else{
    echo "Connect to database failed...";
    die();
}
}
?>
