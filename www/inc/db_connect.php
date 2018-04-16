<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
i	corresponding variable has type integer
d	corresponding variable has type double
s	corresponding variable has type string
b	corresponding variable is a blob and will be sent in packets
 */

require_once 'functions.php';

class dbconn{
    
    var $mysqli;
    
    
    public function __construct() {
        global $CONFIG;
        $this->user = $CONFIG['dbuser'];
	$this->password = $CONFIG['dbpass'];
	$this->database = $CONFIG['dbname'];
	$this->host = $CONFIG['dbhost'];
    }

    public function connect(){
        $this->mysqli = new mysqli($this->host, $this->user, $this->password, $this->database);
        $this->mysqli->set_charset('utf8');
        return $this->mysqli;
    }

    function fetchAssocStatement($stmt) {

        if($stmt->num_rows>0) {
            $result = array();
            $md = $stmt->result_metadata();
            $params = array();
            while($field = $md->fetch_field()) {
                $params[] = &$result[$field->name];
            }
            call_user_func_array(array($stmt, 'bind_result'), $params);
            if($stmt->fetch()) {
                return $result;
            }
        }
        return null;
    }

    function fetchdbarray($stmt) {
        $stmt->execute();
        $stmt->store_result();
        $result = array();
        while ($row = fetchAssocStatement($stmt)){
            $result[] = $row;
        }
        $stmt->close();
        return $result;
    }
    public function fetcharray($query, $data, $format) {
        // Connect to the database
        try{
	$db = $this->connect();
	//Prepare our query for binding
	$stmt = $db->prepare($query);
        if ($format != ''){
            // Prepend $format onto $values
            array_unshift($data, $format);
            //Dynamically bind values
            call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($data));
        }
	//Execute the query
	$stmt->execute();
        $stmt->store_result();
        $result = array();
        while ($row = $this->fetchAssocStatement($stmt)){
            $result[] = $row;
        }
        $stmt->close();
        }
        catch (Exception $e){
            echo $e->getMessage();
        }
	return $result;
    }

    public function delete($query, $data, $format) {
	$db = $this->connect();
	$stmt = $db->prepare($query);
	array_unshift($data, $format);
	call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($data));
	$stmt->execute();
        $stmt->close();
	return true;
    }

    public function insert($query, $data, $format) {
	$db = $this->connect();
	$stmt = $db->prepare($query);
	array_unshift($data, $format);
	call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($data));
	$stmt->execute();
        $stmt->close();
	return true;
    }
    public function update($query, $data, $format) {
	$db = $this->connect();
	$stmt = $db->prepare($query);
	array_unshift($data, $format);
	call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($data));
	$stmt->execute();
        $stmt->close();
	return true;
    }
    public function varquery($var, $query, $data, $format) {
	try{
	$db = $this->connect();
        $vardb = $db->prepare($var);
        $vardb->execute();
        $vardb->store_result();
	$stmt = $db->prepare($query);
	array_unshift($data, $format);

	call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($data));
	$stmt->execute();
        $stmt->store_result();
        $result = array();
        while ($row = $this->fetchAssocStatement($stmt)){
            $result[] = $row;
        }
        $stmt->close();
        
        }
        catch (Exception $e){
            echo $e->getMessage();
        }
	return $result;
    }
    
    private function ref_values($array) {
	$refs = array();
	foreach ($array as $key => $value) {
            $refs[$key] = &$array[$key]; 
	}
	return $refs; 
    }
}
