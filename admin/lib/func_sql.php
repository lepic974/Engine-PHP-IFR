<?php



function getParamRestrict($table, $idVal, $fieldName, $restrictFieldList = "", $idFieldName='Id'){
	$getParam = -1;
	$debug=false;
	
	//construction de la partie WHERE de la requete
	$sqlRestriction = '';
	if(is_array($restrictFieldList) && !empty($restrictFieldList)){
		foreach($restrictFieldList as $restrictFieldName=>$restrictValue){
			$sqlRestriction .= " AND `$restrictFieldName`='$restrictValue'";				
		}
	}elseif(!empty($restrictFieldList)){
		$sqlRestriction .= " AND ".$restrictFieldList;
	}
	
	$query = "SELECT `$fieldName` FROM `$table` WHERE `$idFieldName`='$idVal' ".$sqlRestriction;
	$result = query($query, $debug) or die("Query failed on: $query");		
	while ($line = mysqli_fetch_assoc($result)) {
		foreach ($line as $col_value) {
			$getParam = $col_value;
		}
	}		
	return($getParam);
} // end getParamRestrict


function getParam($table, $idVal, $fieldName, $idFieldName='Id' ){
	$getParam = -1;

	  if(!get_magic_quotes_gpc()){
	    $idVal = addslashes($idVal);				
	  }else{
	    $idVal = str_replace("\'","'",$idVal);				
	    $idVal = str_replace("'","\'",$idVal);
	  }
  
	$query = "SELECT `$fieldName` FROM $table WHERE `$idFieldName`='$idVal'";
	$result = query($query);
	if( mysqli_num_rows($result)>0 ){
		while ($line = mysqli_fetch_assoc($result)) {
	
			foreach ($line as $col_value) {
				$getParam = $col_value;
			}
		}
	}	
	return($getParam);
} 
	
function getParams($table, $idVal, $idFieldName='Id'){
	$getParams = -1;
	
	$query = "SELECT * FROM $table WHERE $idFieldName='$idVal'";
	$result = mysqli_query($GLOBALS["___mysqli_ston"], $query) or die("Query failed on: $query");
	while ($line = mysqli_fetch_assoc($result)) {
		$getParams = $line; 
	}
	return($getParams);
}

?>