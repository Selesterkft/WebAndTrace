<?php
function setRoutingResult($reqid, $selid, $function_params){
//Check JSON format
if(isJSON($function_params)==false){
     echo "function_params error: not valid JSON format";
     http_response_code(400);
     die();
 }
 
//Check params exists
$_params=json_decode($function_params);

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

     $points=array();
     $markers=array();
     $summary=array();
     $from="";
     $to="";

     $p_baseURL = $row["Routing_baseURL"];
     $p_language = "&language=".$row["Routing_p_language"];
     $p_APIkey = "&key=".$row["APIkey"];

     for($i=0;$i<count($_params);$i++){
          //Check params exists
          if(isset($_params[$i]->id)==false){
               echo "Missing parameter: id (".$i.")...";
               die();
          }

          if(isset($_params[$i]->loc)==false){
               echo "Missing parameter: loc (".$i.")...";
               die();
          }
          
          $markers[]=array("lonlat"=>$_params[$i]->loc,"popup"=>$_params[$i]->popup);

          if($i==0){
               $from=$_params[$i]->loc;
               $to="";
          }else{
               $from=$_params[$i-1]->loc;
               $to=$_params[$i]->loc;
          }

          if($from!="" && $to!=""){
               //Call tomtom routing api
               if($from!=$to){
                    $routeURL = $p_baseURL."/".$from.":".$to."/json?instructionsType=text".$p_language.$p_APIkey;
                    $result = file_get_contents($routeURL,false, stream_context_create(['http' => ['ignore_errors' => true]]));
                    if($result === FALSE){
                         echo "TomTom api call failed...";
                         die();
                    }
                    $arr=json_decode($result);

                    $summary[]=array("id"=>$_params[$i-1]->id,"lengthInMeters"=>$arr->routes[0]->legs[0]->summary->lengthInMeters,"travelTimeInSeconds"=>$arr->routes[0]->legs[0]->summary->travelTimeInSeconds);

                    foreach ($arr->routes[0]->legs[0]->points as $point){
                         $points[]=array("lon"=>$point->longitude,"lat"=>$point->latitude);
                    }
               }
          }    
     }

     $resjson=json_encode(array("key"=>$row["APIkey"],"summary"=>$summary,"locs"=>$points,"markers"=>$markers));
     $resURL=$row["Routing_URL"]."?id=".$reqid;

     setRequest("MAIN",$reqid,$resjson,$resURL);

     $requestres=array("reqid"=>$reqid,"URL"=>$resURL,"summary"=>$summary);
     return $requestres;
}else{
     echo "Connect to database failed...";
     die();
}
}
?>