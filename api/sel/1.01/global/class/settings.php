<?php
class Settings{
    public $stmt;

    public function getSettings($conn, $p_id){
        if( $conn ) {
         //Read tomtom params from database
         try{
            $sql = 'SELECT * FROM MAP_Settings WHERE selid='.strval($p_id);
            $this->stmt = sqlsrv_query($conn,$sql);
               
            }catch(exception $e){
                echo "Read settings from server failed: " . $e->getMessage();
            }
    
            return $this->stmt;
        }
    }
}
?>