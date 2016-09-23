<?php
class Database
{
	
	const SERVER_ADDRESS = "aat29u96vjhoky.cn1xyhblivvi.ap-southeast-1.rds.amazonaws.com:3306";
	const SERVER_USERNAME = "root";
	const SERVER_PASSWORD = "password";
	const SERVER_DB = "innodb";
	private $connection;
	
	private $error_code = "";
	
	const R_DEVICE = 101;
	
	const TB_DEVICE = "register";
	/**
	 * CONSTRUCTOR
	 */
	function __construct() {}
	
	/***************************************************************
	 * Start connection to database
	 ***************************************************************/
	public function connect(){
		
		$this->connection = new mysqli(self::SERVER_ADDRESS, self::SERVER_USERNAME, 
		self::SERVER_PASSWORD, self::SERVER_DB);
		
		// return false on error
		if($this->connection->connect_errno != 0) {
			$this->error_code = $this->connection->connect_errno;
			return false;
		}
		
		// PHP 5.2.9 and 5.3.0
		if(mysqli_connect_error()) {
			$this->error_code = $this->connection->mysqli_connect_errno();
			return false;
		}
		return true;
	}
	
	/***************************************************************
	 * Disconnect from database
	 ***************************************************************/
	 public function disconnect() {
		 if($this->connection != null) {
			 $this->connection->close();
		 }
	 }

	/***************************************************************
	 * Raw query into MySQL
	 ***************************************************************/ 
	public function rawQuery($raw, &$arrResult) {

		if($arrResult = $this->connection->query($raw)) {
			// if no record, return null
			if(mysqli_num_rows($arrResult) <= 0)
				$arrResult = null;

			return true;
		}

		return false;	
	}

	 /***************************************************************
	 * Insert data into database
	 ***************************************************************/
	 public function insert($type, &$arrObj) {
		 
		 $szQuery = "INSERT INTO ".self::getTableName($type);
		 
		 $iCount = 0;
		 
		 $szColumn = '';
		 $szValue = '';
		 
		 foreach($arrObj as $key => $value) {
			 
			 if($iCount == 0) {
				$szColumn = "(";
				$szValue = "(";
			 } else {
				 $szColumn .= ",";
				 $szValue .= ",";
			 }
			 
			 $szColumn .= $key;
			 $szValue .= "'".mysqli_real_escape_string($this->connection, $value)."'";
			 
			 $iCount++;
		 }
		 
		 $szColumn .= ")";
		 $szValue .= ")";
		 
		 $szQuery .= $szColumn." VALUES ".$szValue;

		if($arrResult = $this->connection->query($szQuery)) {
			// if no record, return null
			if(mysqli_num_rows($arrResult) <= 0)
				$arrResult = null;

			return true;
		}
		 
		 return false;		 
	 }

	/***************************************************************
	 * Select data from database
	 ***************************************************************/
	public function select ($type, $arrObj, &$arrResult = null) {
		
		$szQuery = "SELECT * FROM ".self::getTableName($type);

		$iCount = 0;
		
		if(is_array($arrObj)) {
			
			$szQuery .= " WHERE ";
			
			foreach($arrObj as $key => $value) {
				
				if($iCount != 0) {
					$szQuery .= " AND ";
				}

				if(is_array($value)) {

					// comparing date
					if(isset($value["DATE1"]) & isset($value["DATE2"])){
						$szQuery .=  $key." BETWEEN '".$value["DATE1"]."' AND '".$value["DATE2"]."'";
					}

				} else {
					$szQuery .= $key . "=" . "'" . $value . "'";
				}
				
				$iCount++;
			}
		}

		if($arrResult = $this->connection->query($szQuery)) {
			// print_r($arrResult);
			// if no record, return null
			if(mysqli_num_rows($arrResult) <= 0)
				$arrResult = null;

			return true;
		}
		
		return false;
	}
	
	/***************************************************************
	 * Search string from database
	 ***************************************************************/
	 public function search($type, $arrColumn, $arrObj, &$arrResult = null) {
		
		$szColumns = "";
		
		for($i=0; $i < count($arrColumn); $i++) {
				
				if($i != 0) {
					$szColumns .= ",";
				}
				
				$szColumns .= $arrColumn[$i];
		}
		
		// build string
		$szQuery = "SELECT * FROM ".self::getTableName($type)." WHERE MATCH(".$szColumns.") AGAINST(";
		
		// BOOLEAN MODE
		$search = $arrObj['search'];
		// remove non-alphanumeric character
		preg_replace("/[^A-Za-z0-9 ]/", '', $search);
		// remove HTML <space>
		$search_part = preg_split("/ +/", $search);
		// Add operator on each string
		$search_text = "";
		for($i=0; $i<count($search_part); $i++) {
			
			if($i > 0) 
				$search_text .= " ";
			
			$search_text .= "+".$search_part[$i];
		}
		
		// find word that contain this word or more 'apple' 'applesause'
		$szQuery .= "'".$search_text."*' ";
		
		$szQuery .= "IN BOOLEAN MODE) ORDER BY MATCH(".$szColumns.") AGAINST("."'".$search_text."') DESC";
		
		if($arrResult = $this->connection->query($szQuery)) {
			return true;
		}
		
		return false;
	 }
	 
	 /***************************************************************
	 * Get total number of rows based on condition
	 ***************************************************************/
	 public function rowCount($type, $arrObj) {
		
		$szQuery = "SELECT COUNT(*) FROM ".self::getTableName($type)." WHERE ";

		$iCount = 0;
		
		$szColumn = "";
		$szValue = "";
		
		if(is_array($arrObj)) {
			
			foreach($arrObj as $key => $value) {
				
				if($iCount != 0) {
					$szQuery .= " AND ";
				}
				
				$szQuery .= $key."="."'".$value."'";
				
				$iCount++;
			}
		}
		
		if($aCount = $this->connection->query($szQuery)) {
			$data = $aCount->fetch_array();
			return $data[0];
		}
	 }
	 
	/***************************************************************
	 * Update row record
	 ***************************************************************/ 
	public function update($type, $aUpdates, $aPrimary) {
		
		$szQuery = "UPDATE ".self::getTableName($type)." SET ";
		
		$iCount = 0;
		foreach($aUpdates as $key=> $value) {
			
			if($iCount > 0)
				$szQuery .= ",";
			
			$szQuery .= $key;
			$szQuery .= "=";
			$szQuery .= "'".$value."'";
			
			$iCount++;
		}
		
		$szQuery .= " WHERE ";
		
		$iPrimaryCount = 0;
		foreach($aPrimary as $key=>$value) {
			
			if($iPrimaryCount > 0) {
				$szQuery .= " AND "	;
			}
			
			$szQuery .= $key;
			$szQuery .= "=";
			$szQuery .= "'".$value."'";
			
			$iPrimaryCount++;
		}

		if($arrResult = $this->connection->query($szQuery)) {
			return true;
		}
		
		return false;
	}
	 
	 /***
	  * Delete row
	  */
	 public function remove($type, $arrObj) {
			
		$szQuery = "DELETE FROM ".self::getTableName($type)." WHERE ";
		
		$iCount = 0;
		
		if(is_array($arrObj)) {
			
			foreach($arrObj as $key => $value) {
				
				if($iCount != 0) {
					$szQuery .= " AND ";
				}
				
				$szQuery .= $key."="."'".$value."'";
				
				$iCount++;
			}
		}
	
		if($this->connection->query($szQuery)) {
			return true;
		}
		 
		 return false;
	 }
	 
	 /***
	  * Custom query for select
	  * 
	  */
	 public function selectRawQuery ($type, $query, &$arrResult = null, $limit) {
		
		$szQuery = "SELECT ".$query." FROM ".self::getTableName($type);

		if($limit) {
			$szQuery .= " ".$limit;
		}

		if($arrResult = $this->connection->query($szQuery)) {
			// if no record, return null
			if(mysqli_num_rows($arrResult) <= 0)
				$arrResult = null;

			return true;
		}
		
		return false;
	}

	 /***
	  * Return last error from connection
	  *
	  */
	 public function getError() {
		 return $this->connection->error;
	 }
	 
	 /**************************** PRIVATE ***********************************/


	/***************************************************************
	 * Insert data into database
	 * @param $type
	 * @return string
	 */
	 private function getTableName($type){
		 
		 $szName = "";
		 
		 switch($type) {
			 case self::R_DEVICE:
				$szName = self::TB_DEVICE;
			 break;
		 }
		 
		 return $szName;
	 }
	 
	 
}

?>
