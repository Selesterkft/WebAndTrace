<?php
//init
require_once('ini.php');
require_once(CONFIG_PATH);

$config = new config(__FILE__);
$settings = $config->getSettings();

require_once($settings['version_path'].'/autoload.php');

//create new task
$task = new syncTask();
$taskOut = $task->addTask();

//check task type
if($taskOut['inputParams']['header']['taskType'] != TASK_TYPE_SMS) {
    new errorMsg(REQUEST_WRONG_TASK_TYPE, 'Wrong task type (' . $taskOut['inputParams']['header']['taskType'] . ')', $taskOut);
}

//set task status to transferred
$status = new status($taskOut);
$status->switchStatus(status::CONST_TRANSFERRED, '');

//setup message text
switch ($taskOut['inputParams']['body']['messageType']) {
    case SMS_FREETEXT:
        $messageText = $taskOut['inputParams']['body']['messageText'];
        break;

    case SMS_INVITE:
        $messageText = WEB_HOME . '/' . WEB_SELTRANSPORT_DL;
        break;  

    case SMS_REGISTRATION:
        $messageText = ''; //még meg kell csinálni (VI)
        break;

    case SMS_TRACKING:
        $messageText = ''; //még meg kell csinálni (VI)
        break;

    default:
        new errorMsg(SMS_INVALID_MESSAGE_TYPE, 'Invalid SMS message type (' . $taskOut['inputParams']['body']['messageType'] . ')', $taskOut);
        break;
}

//send sms
switch ($taskOut['inputParams']['body']['provider']) {
    case 'SEEME':
        $sms = new seeMeSMS($taskOut, $messageText);
        $out = $sms->send();
        break;

    default:
        new errorMsg(SMS_INVALID_PROVIDER, 'Invalid SMS provider (' . $taskOut['inputParams']['body']['provider'] . ')', $taskOut);
        break;
}

//set task status to accepted
$status->switchStatus(status::CONST_ACCEPTED, json_encode($out));

echo json_encode($out);
?>