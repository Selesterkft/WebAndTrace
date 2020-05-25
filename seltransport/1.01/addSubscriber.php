<?php
//init
require_once( 'ini.php' );
require_once( CONFIG_PATH );

$config = new config( __FILE__ );
$settings = $config->getSettings();

require_once( $settings['version_path'].'/autoload.php' );

//create new task
$task = new syncTask();
$taskOut = $task->addTask();

$task->validateParameter( 'body/userName', STRING );
$task->validateParameter( 'body/subscriberId', INTEGER );

//check task type
if( $taskOut['inputParams']['header']['taskType'] != TASK_TYPE_SUBSCRIBER ) {
    new errorMsg( REQUEST_WRONG_TASK_TYPE, 'Wrong task type (' . $taskOut['inputParams']['header']['taskType'] . ')', $taskOut );
}

//set task status to transferred
$status = new status( $taskOut );
$status->switchStatus( status::CONST_TRANSFERRED, '' );

$subscriber = new subscriber( $taskOut );
$sbsOut = $subscriber->addSubscriber();

//set task status to accepted
$status->switchStatus( status::CONST_ACCEPTED, json_encode( $sbsOut ) );

$out = [
    'newToken' => $taskOut['token'],
    'result' => $sbsOut
];

echo json_encode( $out );
?>