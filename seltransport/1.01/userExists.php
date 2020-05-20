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

$task->validateParameter('body/isRoot', INTEGER, true, '0, 1');
$task->validateParameter('body/userName', STRING);
$task->validateParameter('body/subscriberId', INTEGER);
$task->validateParameter('body/externalId', STRING);

//check task type
if($taskOut['inputParams']['header']['taskType'] != TASK_TYPE_USR) {
    new errorMsg(REQUEST_WRONG_TASK_TYPE, 'Wrong task type (' . $taskOut['inputParams']['header']['taskType'] . ')', $taskOut);
}

//set task status to transferred
$status = new status($taskOut);
$status->switchStatus(status::CONST_TRANSFERRED, '');

$user = new user($taskOut);
$usrOut = $user->userExists();

//set task status to accepted
$status->switchStatus(status::CONST_ACCEPTED, json_encode($usrOut));

$out = [
    'newToken' => $taskOut['token'],
    'result' => $usrOut
];

echo json_encode($out);
?>