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

//EZ MÉG ITTEN SZAR
// $confirmationTypeString = 'NONE,SMS,EMAIL';
// $confirmationTypeString = USER_CONFIRMATION_NONE . ',' . USER_CONFIRMATION_EMAIL . ',' . USER_CONFIRMATION_SMS;

// $task->validateParameter('body/confirmationType', STRING, true, $confirmationTypeString);
$task->validateParameter('body/confirmationType', STRING);
$task->validateParameter('body/userName', STRING);

if(isset($taskOut['inputParams']['body']['phoneNumber']) == true) {
    $task->validateParameter('body/phoneNumber', STRING, ($taskOut['inputParams']['body']['confirmationType'] == USER_CONFIRMATION_SMS ? true:false));
}

//check task type
if($taskOut['inputParams']['header']['taskType'] != TASK_TYPE_REGISTRATION) {
    new errorMsg(REQUEST_WRONG_TASK_TYPE, 'Wrong task type (' . $taskOut['inputParams']['header']['taskType'] . ')', $taskOut);
}

//set task status to transferred
$status = new status($taskOut);
$status->switchStatus(status::CONST_TRANSFERRED, '');

$user = new user($taskOut);
$regOut = $user->registration();

if($regOut['result'] == 'NOK') {
    echo json_encode($regOut);
    die;
}

switch ($taskOut['inputParams']['body']['confirmationType']) {
    case USER_CONFIRMATION_NONE:
        $regEndOut = $user->registrationEnd($regOut['registrationKey']);
        if($regEndOut['result'] == 'NOK') {
            echo json_encode($regEndOut);
            die;
        }
        break;

    case USER_CONFIRMATION_EMAIL:
        //még nem tudom hogy lesz, meg kell csinálni (VI)
        break;  

    case USER_CONFIRMATION_SMS:
        $inputJSON['inputParams']['header']['interface'] = $taskOut['inputParams']['header']['interface'];
        $inputJSON['inputParams']['body']['provider'] = 'SEEME';
        $inputJSON['inputParams']['body']['phoneNumber'] = $taskOut['inputParams']['body']['userName'];

        $sms = new seeMeSMS($inputJSON, '#' . $regOut['registrationKey']);
        $smsOut = $sms->send();
        break;
}

//set task status to accepted
$status->switchStatus(status::CONST_ACCEPTED, json_encode($regOut));

if(isset($regEndOut) == false) {
    $out = [
        'newToken' => $taskOut['token'],
        'registrationKey' => $regOut['registrationKey']
    ];
}else{
    $out = [
        'newToken' => $taskOut['token'],
        'registrationKey' => $regOut['registrationKey'],
        'masterKey' => $regEndOut['masterKey']
    ];

}

echo json_encode($out);
?>