<?php
function setRoutingResult($reqid, $selid, $function_params, $map_params){
//Check params exists
$_params=json_decode($function_params);
$_map_params=json_decode($map_params);

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
     $section=array();
     $markers=array();
     $summary=array();
     $from="";
     $to="";

     $p_baseURL = $row["Routing_baseURL"];
     $p_instructionsType="instructionsType=text";
     $p_language = "&language=hu-HU";
     $p_APIkey = "&key=".$row["APIkey"];

     $p_totalweight=0;

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

          $markers[]=array("lonlat"=>$_params[$i]->loc,"popuptitle"=>$_params[$i]->popuptitle,"popuphead"=>$_params[$i]->popuphead,"popupbody"=>$_params[$i]->popupbody);

          if($i==0){
               $from=$_params[$i]->loc;
               $to="";
          }else{
               $from=$_params[$i-1]->loc;
               $to=$_params[$i]->loc;
          }

          if($from!="" && $to!=""){
               //Call tomtom routing api
               $p_totalweight+=$_params[$i-1]->weight;

               if($from!=$to){
                    $routeoptions=$p_instructionsType.$p_language.$p_APIkey;

                    if(isset($_map_params->RouteType)){
                         $routeoptions.="&routeType=".$_map_params->RouteType;
                    }

                    if(isset($_map_params->TravelMode)){
                         $routeoptions.="&travelMode=".$_map_params->TravelMode;
                    }

                    if($_map_params->VehicleWeight>0){
                         $routeoptions.="&vehicleWeight=".$_map_params->VehicleWeight;
                    }

                    if($_map_params->VehicleCommercial==1){
                         $routeoptions.="&vehicleCommercial=true";
                    }

                    if($_map_params->TollRoads==1){
                         $routeoptions.="&avoid=tollRoads";
                    }

                    if($_map_params->Motorways==1){
                         $routeoptions.="&avoid=motorways";
                    }

                    if($_map_params->Ferries==1){
                         $routeoptions.="&avoid=ferries";
                    }

                    if($_map_params->UnpavedRoads==1){
                         $routeoptions.="&avoid=unpavedRoads";
                    }

                    if($_map_params->CarPools==1){
                         $routeoptions.="&avoid=carpools";
                    }

                    if($_map_params->BorderCrossing==1){
                         $routeoptions.="&avoid=borderCrossings";
                    }

                    if($_map_params->VehicleMaxSpeed>0){
                         $routeoptions.="&vehicleMaxSpeed=".$_map_params->VehicleMaxSpeed;
                    }

                    if($_map_params->VehicleAxleWeight>0){
                         $routeoptions.="&vehicleAxleWeight=".$_map_params->VehicleAxleWeight;
                    }

                    if($_map_params->VehicleLength>0){
                         $routeoptions.="&vehicleLength=".$_map_params->VehicleLength;
                    }

                    if($_map_params->VehicleWidth>0){
                         $routeoptions.="&vehicleWidth=".$_map_params->VehicleWidth;
                    }

                    if($_map_params->VehicleHeight>0){
                         $routeoptions.="&vehicleHeight=".$_map_params->VehicleHeight;
                    }

                    if($_map_params->VehicleLoadType!=""){
                         $routeoptions.="&vehicleLoadType=".$_map_params->VehicleLoadType;
                    }

                    if($_map_params->VehicleAdrTunnel!=""){
                         $routeoptions.="&vehicleAdrTunnelRestrictionCode=".$_map_params->VehicleAdrTunnel;
                    }

                    $routeURL = $p_baseURL."/".$from.":".$to."/json?".$routeoptions;

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
                    $section[]=array("section"=>$i,"weight"=>$p_totalweight,"points"=>$points);

                    unset($points);
                    $points=array();
               }
          }    
     }

     $resjson=json_encode(array("key"=>$row["APIkey"],"summary"=>$summary,"locs"=>$section,"markers"=>$markers,"map_settings"=>$map_params));

     if($map_params==""){
          $resURL="";
      }else{
          $resURL=$row["Routing_URL"]."?id=".$reqid;
     }
  
     setRequest("MAIN",$reqid,$resjson,$resURL);

     $requestres=array("reqid"=>$reqid,"URL"=>$resURL,"summary"=>$summary);
     return $requestres;
}else{
     echo "Connect to database failed...";
     die();
}
}
?>