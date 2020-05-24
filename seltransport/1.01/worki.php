<?php
// require_once("worki2.php");
//e475950f06971715c399570fc9ba5cb3

//echo password_hash("e475950f06971715c399570fc9ba5cb3",PASSWORD_DEFAULT)

//echo password_verify("e475950f06971715c399570fc9ba5cb3","$2y$10$d.rfp429In7rWgLqVycRwODi0Dy462.ae.0/rgs1IscC7DNyPZtOC")
// echo bin2hex(random_bytes(32))
// echo bin2hex(random_bytes(16))
// echo bin2hex(random_bytes(5))
// echo strtoupper(hash('sha512', 'kiskutya'))
// echo gmdate("Y-m-d H:i:s");

// var_dump(ReadFromINI("DATABASE"))

// $ini=new ini;
// var_dump($ini->ReadFromINI("API_FOLDER"))

// echo dirname(__DIR__)
// echo realpath(__DIR__)

// echo gmdate('Ymd His') . "\n"; // 2014-12-12 07:18:00 UTC

// echo date('YmdHis', time());

// echo strtotime(time());

// define('a', 'A');
// define('b', 'B');
// define('c', 'C');

// $out=a.',' . b. ',' . c;

// $out['A']['B']['C'] = 1;
// $out['A']['B']['D'] = 2;
// var_dump(json_encode($out));
$inputJSON='{"userName": "36303102211","registrationKey": "E14465394CD45E8FFF4AB8C3575A1A2FEA23EDFB19042D30EDE46623C35DCBE467FE987DFE351659320DA180E4B6EC02B646492A97490E9889B6668317C06A80","masterKey": "57CEDBD352E5BA47F8AE0EE1D2FD2E5C9523BDE7B37369D364154CA678A7D1D984A0FC94A5E6F339C8BCC0D73DC4C0C5DEB5E9AFB2E63DF2ED91D1E0C9892577"}';
$outputJSON='';
$p_connectionInfo = array('Database'=>'CUSTOMERPORTAL', 'UID'=>'CP_USER', 'PWD'=>'0heaA4Engt');
$conn = sqlsrv_connect('tcp:selazure.database.windows.net, 1433', $p_connectionInfo);

// $p_sql = '{call IF_1001_GET_TRANSPORT_DATA ( ? )}';
$p_sql = '{call IF_1001_VALIDATE_USER ( ? )}';
$p_params = array(
                     array(&$inputJSON, SQLSRV_PARAM_IN)
                    );

$stmt = sqlsrv_query($conn, $p_sql, $p_params);

// $data = array();

// while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC)){
//   $data = array_merge( $data, array_values($row) );
// }

$data='';
while (sqlsrv_fetch($stmt)) {
    $data .= sqlsrv_get_field($stmt, 0, SQLSRV_PHPTYPE_STRING('UTF-8'));
}

var_dump(json_decode($data, true));

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn); //Close the connnectiokn first


// $out = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC);
// echo json_$data[0];

// for ($i = 0; $i < count($data); $i++) {
//     $res . = $data[$i];
// }

// echo $res;
    // for($i=0; $i<$numRows-1; $i++)
    // {
    //     $out = sqlsrv_fetch_array($stmt);
    // }

// while($outputJSON=sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC)){
//     $out=$outputJSON;
// };
// var_dump($out);

// sqlsrv_free_stmt($stmt);
// sqlsrv_close($conn);

?>