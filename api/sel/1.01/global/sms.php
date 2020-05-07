<?php
include_once '../../../seeme/seeme-gateway-class.php';
include_once 'functions.php';

//seeme api key
$APIkey="64n9ij4yh0tzf64se7np1dprt9qbtytfa592";

//Check post params exists
if(isset($_POST["request_params"])==false){
    echo "Missing post parameter: request_params...";
    http_response_code(400);
    die();
}else{
    $p_request_params=$_POST["request_params"];
    }

//Check JSON format
if(isJSON($p_request_params)==false){
    echo "request_params error: not valid JSON format";
    http_response_code(400);
    die();
}

//Check request_params
$_params=json_decode($p_request_params);

if(isset($_params->phonenumber)==false){
    echo "Missing parameter value: phonenumber...";
    http_response_code(400);
    die();
}else{
    $phonenumber=$_params->phonenumber;
}

if(isset($_params->messagetext)==false){
    echo "Missing parameter value: messagetext...";
    http_response_code(400);
    die();
}else{
    $messagetext=$_params->messagetext;
}

// Connect to SeeMe Gateway
$SeeMe = new SeeMeGateway($APIkey);

try {
  
  $SeeMe->sendSMS(
  	$phonenumber, // destination
  	$messagetext, // message
  	'senderID' // optional sender
  );
  
  print_r( $SeeMe->getResult() );
  
} catch ( Exception $e ) {
  // handle exception
  // print out the response
  // we will get an aassociative array
  print_r( $SeeMe->getResult() );
  die();
}

?>