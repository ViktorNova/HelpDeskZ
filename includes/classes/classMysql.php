<?php
/*******************************************************************************
*  Title: Help Desk Software HelpDeskZ
*  Version: 1.0 from 17th March 2015
*  Author: Evolution Script S.A.C.
*  Website: http://www.helpdeskz.com
********************************************************************************
*  COPYRIGHT AND TRADEMARK NOTICE
*  Copyright 2015 Evolution Script S.A.C.. All Rights Reserved.
*  HelpDeskZ is a registered trademark of Evolution Script S.A.C..

*  The HelpDeskZ may be used and modified free of charge by anyone
*  AS LONG AS COPYRIGHT NOTICES AND ALL THE COMMENTS REMAIN INTACT.
*  By using this code you agree to indemnify Evolution Script S.A.C. from any
*  liability that might arise from it's use.

*  Selling the code for this program, in part or full, without prior
*  written consent is expressly forbidden.

*  Using this code, in part or full, to create derivate work,
*  new scripts or products is expressly forbidden. Obtain permission
*  before redistributing this software over the Internet or in
*  any other medium. In all cases copyright and header must remain intact.
*  This Copyright is in full effect in any country that has International
*  Trade Agreements with the United States of America

*  Removing any of the copyright notices without purchasing a license
*  is expressly forbidden. To remove HelpDeskZ copyright notice you must purchase
*  a license for this script. For more information on how to obtain
*  a license please visit the page below:
*  https://www.helpdeskz.com/contact
*******************************************************************************/
class MySQLDB
{
	var $functions = array(
		'connect'            => 'mysql_connect',
		'pconnect'           => 'mysql_pconnect',
		'select_db'          => 'mysql_select_db',
		'query'              => 'mysql_query',
		'result'             => 'mysql_result',
		'query_unbuffered'   => 'mysql_unbuffered_query',
		'fetch_row'          => 'mysql_fetch_row',
		'fetch_array'        => 'mysql_fetch_array',
		'fetch_field'        => 'mysql_fetch_field',
		'free_result'        => 'mysql_free_result',
		'data_seek'          => 'mysql_data_seek',
		'error'              => 'mysql_error',
		'errno'              => 'mysql_errno',
		'affected_rows'      => 'mysql_affected_rows',
		'num_rows'           => 'mysql_num_rows',
		'num_fields'         => 'mysql_num_fields',
		'field_name'         => 'mysql_field_name',
		'insert_id'          => 'mysql_insert_id',
		'escape_string'      => 'mysql_escape_string',
		'real_escape_string' => 'mysql_real_escape_string',
		'close'              => 'mysql_close',
		'client_encoding'    => 'mysql_client_encoding',
	);

	var $registry = null;
	var $fetchtypes = array(
		DBARRAY_NUM   => MYSQL_NUM,
		DBARRAY_ASSOC => MYSQL_ASSOC,
		DBARRAY_BOTH  => MYSQL_BOTH
	);
	var $database = null;
	
	function connect($db_name, $db_server, $db_user, $db_passwd, $db_prefix){
		$this->tbl_prefix = $db_prefix;
		$this->database = $db_name;
		$this->connection_master = $this->db_connect($db_name, $db_server, $db_user, $db_passwd);
		$this->select_db($this->database);
	}
	function testconnect($db_name, $db_server, $db_user, $db_passwd){
		$link = @$this->functions[connect]($db_server, $db_user, $db_passwd);
		if(!$link){
			return("<strong>Error MySQL DB Conection</strong>. Please contact to site administrator.");
		}else{
			$this->database = $database;
			if(!@$this->functions[select_db]($db_name, $link)){
				return ("<strong>Error MySQL DB Conection</strong> Can not connect to $db_name");
			}else{
				@$this->functions['close']($link);	
			}
		}		
	}
	function db_connect($db_name, $db_server, $db_user, $db_passwd){
		$link = @$this->functions[connect]($db_server, $db_user, $db_passwd);
		if(!$link){
			die("<br /><br /><strong>Error MySQL DB Conection</strong><br>Please contact to site administrator.");
		}
		return $link;
	}
	function select_db($database){
		$this->database = $database;
		if(!@$this->functions[select_db]($this->database, $this->connection_master)){
			die("<br /><br /><strong>Error MySQL DB Conection</strong><br>Please contact to site administrator.");
		}
	}
	function close()
	{
		return @$this->functions['close']($this->connection_master);
	}
	function query($sql, $buffered = true)
	{
		$this->sql =& $sql;
		return $this->execute_query($buffered, $this->connection_master);
	}
	function &execute_query($buffered = true, &$link)
	{
		$this->connection_recent =& $link;
		$this->querycount++;

		if ($queryresult = $this->functions[$buffered ? 'query' : 'query_unbuffered']($this->sql, $link))
		{
			// unset $sql to lower memory .. this isn't an error, so it's not needed
			$this->sql = '';

			return $queryresult;
		}
		else
		{
			// unset $sql to lower memory .. error will have already been thrown
			$this->sql = '';
		}
	}
	function &fetchRow($sql, $type = DBARRAY_ASSOC)
	{
		$this->sql =& $sql;
		$queryresult = $this->execute_query(true, $this->connection_master);
		$returnarray = $this->fetch_array($queryresult, $type);
		$this->free_result($queryresult);
		return $returnarray;
	}
	function fetch_array($queryresult, $type =DBARRAY_ASSOC)
	{
		$result = @$this->functions['fetch_array']($queryresult, $this->fetchtypes["$type"]);
		return $result;
	}
	# Fetch One
	# Devuelve el resultado de un query
	function fetchOne($sql)
	{
		$this->sql =& $sql;
		$queryresult = $this->execute_query(true, $this->connection_master);
		$returnresult = $this->result($queryresult);
		$this->free_result($queryresult);
		return $returnresult;
	}
	# insert
	# Inserta la data en una tabla, los datos debe ser array
	function insert($tbl, $dataArray)
	{
		foreach($dataArray as $k=>$v){
			$keys .= "$k, ";
			$values .= "'".$this->real_escape_string($v)."', ";
		}
		$keys = substr ($keys, 0, strlen($keys) - 2);
		$values = substr ($values, 0, strlen($values) - 2);
		$sql = "INSERT INTO {$tbl}($keys) VALUES({$values})";
		$exeq = $this->query($sql);
		return $exeq;
	}
	# InsertID
	# Deveulve el ultimo id insertado
	function lastInsertId()
	{
		return @$this->functions['insert_id']($this->connection_master);
	}
	# Delete
	# Elimina uno o varios datos de la tabla
	function delete($tbl, $data=null)
	{
		if($data != ''){
			$conditional = "WHERE {$data}";
		}
		$sql = "DELETE FROM {$tbl} {$conditional}";
		$this->query($sql);
	}
	# Update
	# Actualiza uno o varios campos
	function update($tbl, $dataArray, $conditional=null)
	{
		foreach($dataArray as $k=>$v){
			$updsql .= "$k='".$this->real_escape_string($v)."', ";
		}
		$updsql = substr ($updsql, 0, strlen($updsql) - 2);
		if($conditional != ''){
			$updsql.= "WHERE {$conditional}";
		}
		$sql = "UPDATE {$tbl} SET {$updsql}";
		$this->query($sql);
	}
	function result($queryresult){
		$result = @$this->functions['result']($queryresult, $this->fetchtypes["$type"]);
		return $result;
	}
	function free_result($queryresult)
	{
		$this->sql = '';
		return @$this->functions['free_result']($queryresult);
	}
	function escape_string($string)
	{
		if ($this->functions['escape_string'] == $this->functions['real_escape_string'])
		{
			return $this->functions['escape_string']($string, $this->connection_master);
		}
		else
		{
			return $this->functions['escape_string']($string);
		}
	}
	function real_escape_string($string)
	{
		$this->sql = '';
		return @$this->functions['real_escape_string']($string);
	}
}
?>