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

//check task type
if( $taskOut['inputParams']['header']['taskType'] != TASK_TYPE_TRANSPORT ) {
    new errorMsg( REQUEST_WRONG_TASK_TYPE, 'Wrong task type (' . $taskOut['inputParams']['header']['taskType'] . ')', $taskOut );
}

//set task status to transferred
$status = new status( $taskOut );
$status->switchStatus( status::CONST_TRANSFERRED, '' );

$transport = new transport( $taskOut );
$trnspOut = $transport->getTransportData();

//set task status to accepted

$status->switchStatus( status::CONST_ACCEPTED, 'taskList' );

$out = [
    'newToken' => $taskOut['token'],
    'result' => $trnspOut
];

echo json_encode( $out );
?>