<?php
/*
	=============================================================
	| Autor: Isaac Bruno Lima da Silva 							|
	| Versão: 1.0 												|
	| contato: 													|
	|	isaac.centenario@gmail.com 								|
	|	isaac.centenario@outlook.com     						|
	|	https://facebook.com/isaac.centenario 					|
	|	https://github.com/isaaccentenario						|
	|	https://bitbucket.org/isaaccentenario					|
	|															|
	=============================================================
*/
class simpleCRUD {
	public $host;
	protected $user;
	protected $password;
	protected $port;
	protected $socket;
	protected $db;
	public $charset;
	public $conn;
	public $error_detail;

	public function __construct($host=null,$user=null,$password=null,$db=null,$charset="utf-8",$port=3306,$socket="") {
		$this->host = $host;
		$this->user = $user;
		$this->password = $password;
		$this->db = $db;
		$this->port = $port;
		$this->socket = $socket;
		$this->charset = $charset;
	}
	public function connect(){
		$connect = new mysqli($this->host,$this->user,$this->password,$this->db, $this->port, !empty($this->socket)? $this->socket : "");
		$this->conn = $connect;
		
		if( mysqli_connect_errno() )
		{
			$this->error_detail = mysqli_connect_errno();
			return false;
		}
		else
		{
			return true;
		}

		$this->conn->set_charset($this->charset); 
		$this->conn->query("SET NAMES '".$this->charset."'");
		$this->conn->query("SET GLOBAL sql_mode='' ");
	}

	public function error() {
		if( $this->conn->error ) $this->error_detail = $this->conn->error; 
		return $this->error_detail;
	}

	public function escape( $string = null ) {
		return $this->conn->real_escape_string( $string );
	}
	
	public function insert($table=null, $data = array() ) {
		if( $table != null && !empty( $data ) )
		{
			$col = "";
			$val = ""; 

			foreach( $data as $key=>$value )
			{
				$value = $this->escape( $value ); 
				$col .= $key.", "; 
				$val .= " '".$value."',"; 
			}

			$col = rtrim( $col, ", " ); 
			$val = rtrim( $val , "," ); 
			
			$query = $this->conn->query( "INSERT INTO $table( " . $col . " ) VALUES ( ". $val ." ) " ); 

			if( $query ):
				return true;
			else:
				return false;
			endif;
		}
		else
		{
			return false; 
		}
	}

	public function update( $table = null , $update = array() , $conditions = array() ) {

		if( $table == null && empty( $update ) && empty ( $conditions) ) 
		{
			return false;
		}
		$new_values = "";
		$cond = "";

		foreach( $update as $key => $value )
		{
			$value = $this->escape( $value ); 
			$new_values .= $key."='".$value."',"; 	
		}
		foreach( $conditions as $key => $value )
		{
			$value = $this->escape( $value ); 
			$cond .= $key."='".$value."' and "; 
		}
		$new_values = rtrim( $new_values, "," ); 
		$cond = rtrim( $cond , " and "); 

		$query = $this->conn->query( "UPDATE $table SET $new_values WHERE $cond" ); 

		if( $this->conn->affected_rows > 0 ):
			return  $this->conn->affected_rows;
		else:
			return false;
		endif;
	}
	
	public function delete( $table = null , $conditions = array() ) {

		if( $table == null && empty ( $conditions) ) 
		{	
			return false;
		}

		$cond = "";

		foreach( $conditions as $key => $value )
		{
			$value = $this->escape( $value ); 
			$cond .= $key."='".$value."' and "; 
		}
		$cond = rtrim( $cond , " and "); 

		$query = $this->conn->query( "DELETE FROM $table WHERE $cond" ); 

		if( $this->conn->affected_rows > 0 ):
			return $this->conn->affected_rows > 0;
		else:
			return false;
		endif;

	}
	public function get( $table = null, $conditions = array(), $options = array() ) {
		$defaults = array(
				"operator" => "AND",
				"orderby" => "",
				"order" => "DESC",
				"limit" => 25
			);
		$s = $options + $defaults; 
		$operator = $s['operator'];
		$cond = "";
		if( !empty( $conditions ) ):
			foreach( $conditions as $key => $value )
				{
					$value = $this->escape( $value ); 
					$cond .= $key."='".$value."' ".$operator." "; 
				}
			$cond = rtrim( $cond , " ".$operator." ");
		else:
			$cond = 1;
		endif;

		if( !empty( $s['orderby'] ) ) :
			$order_sett = "order by ". $s['orderby']. " ".$s['order']; 
		else:
			$order_sett = "";
		endif; 

		$query = $this->conn->query( "SELECT * FROM ".$table." WHERE ".$cond." ". $order_sett." LIMIT ".$s['limit'] );

		$return = array();

		while( $a = $query->fetch_object() )
		{
			$return[] = $a;
		}
		return $return;
	}

	public function query( $query = null ) {
		$query = $this->conn->query( $query ); 
		$result = array();
		while( $a = $query->fetch_object() )
		{
			$result[] = $a;
		}
		return $result;
	}
}