<?php
class Database{
    public $conn;

    function getMainSettings(){
        $server = "tcp:selazure.database.windows.net, 1433";
        $database = "CUSTOMERPORTAL";
        $username = "CP_USER";
        $password = "0heaA4Engt";

        return array(
            "server"=>$server,
            "database"=>$database,
            "username"=>$username,
            "password"=>$password
        );
    }

    public function getConnection($database){
        if($database=="MAIN"){
            $settings=$this->getMainSettings();

            $server=$settings["server"];
            $database=$settings["database"];
            $username=$settings["username"];
            $password=$settings["password"];
        }

        $this->conn = null;

        try{
            $connectionInfo = array( "Database"=>$database, "UID"=>$username, "PWD"=>$password);
            $this->conn = sqlsrv_connect($server, $connectionInfo);

        }catch(exception $e){
            echo "Connection error: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>