<?php

function getMultiField($table, $fieldList, $restrictFieldList = "", $orderFieldName="", $idFieldName='Id', $ressource=''){
	$list = array();
	//construction de la liste des champs ? recuperer
	if(is_array($fieldList)){
		$nbField = count($fieldList);
		$cpt = 1;
		$field = "$idFieldName, ";
		foreach($fieldList as $fieldName){
			if($cpt == $nbField){
				$field .= ''.$fieldName.'';
			}else{
				$field .= ''.$fieldName.', ';
			}
			$cpt++;
		}
	}elseif($fieldList == '*'){
		$field = '*';
	}else{
		$field = "$idFieldName, ".$fieldList."";
	}
	
	$sqlRestriction = "";
	//construction de la partie WHERE de la requete
	if(is_array($restrictFieldList) && !empty($restrictFieldList)){
		foreach($restrictFieldList as $restrictFieldName=>$restrictValue){
			if($sqlRestriction == ""){
				$sqlRestriction .= " WHERE ";
			}else{
				$sqlRestriction .= " AND ";
			}
			
			if(is_array($restrictValue)){
				$sqlRestriction .= "(";
				$cpt = 1;
				foreach($restrictValue as $value){
					if($cpt > 1){
						$sqlRestriction .= " OR ";
					}
					if(is_string($value)){
						$value = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $value);
						$sqlRestriction .= "".$restrictFieldName." LIKE '".$value."'";
					}else{
						$sqlRestriction .= "".$restrictFieldName."='".$value."'";
					}
					$cpt++;
				}
				if($cpt == 1){
					$sqlRestriction .= " 1=1 ";
				}
				$sqlRestriction .= ")";
			}else{
				if(is_string($restrictValue)){
					$restrictValue = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $restrictValue);
					$sqlRestriction .= "".$restrictFieldName." LIKE '".$restrictValue."'";
				}else{
					$sqlRestriction .= "".$restrictFieldName."='".$restrictValue."'";
				}
			}
		}
	}elseif(!empty($restrictFieldList)){
		if(stripos($restrictFieldList, 'WHERE') === false){
			$sqlRestriction .= " WHERE ".$restrictFieldList;
		}else{
			$sqlRestriction .= $restrictFieldList;
		}
	}
	
	//construction de la partie ORDER BY de la requete
	if(!empty($orderFieldName) && $orderFieldName != ""){
		$sqlRestriction .= " ORDER BY $orderFieldName";
	}else{
		$sqlRestriction .= " ORDER BY $idFieldName";
	}
	
	//construction de la requete complete
	$query = "SELECT $field FROM $table".$sqlRestriction;
	if($field=='*') {
		//dbug($query);
	}
	// recuperation de la connection mysql definie ou celle par defaut
	if(isset($ressource) && !empty($ressource)){
		$result = mysqli_query( $ressource, $query) or die("Query failed on: $query");
	}else{
		$result = mysqli_query($GLOBALS["___mysqli_ston"], $query) or die("Query failed on: $query");
	}
	
	//mise en array du resultat de la requete
	while ($line = mysqli_fetch_assoc($result)) {
		
		$list{$line{$idFieldName}} = $line;
	}
	//dbug($list);
	return ($list);
}

function query_link($query, $link=false){
	if(!empty($query)){
		$result = mysqli_query($link, $query);
		if(!$result){				
			return FALSE;  	   	
		}
		return $result;
	}
}

function query($query){
	if(!empty($query)){
		$result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
		if(!$result){				
			return FALSE;  	   	
		}
		return $result;
	}
}

function sql_update($table,$id,$update,$values){
	foreach($values as $key => $value) $tmp_set[]=$update[$key]."='".addslashes($value)."'";
	$sql="UPDATE ".$table." SET ".implode(', ',$tmp_set)." WHERE id='".$id."' LIMIT 1;";
	return query($sql);
}

function magic_slashe($key,$value) {
	if(!get_magic_quotes_gpc()){
		return $key."='".addslashes($value)."'";
	}else{
		return $key."='".$value."'";
	}
}

function sql_simple_update($table,$id,$r,$transaction_mode=FALSE,$debug=FALSE,$id_name='id'){
	foreach($r as $key => $value)$tmp_set[]=$key."='".addslashes($value)."'";

	$sql="UPDATE ".$table." SET ".implode(', ',$tmp_set)." WHERE $id_name='".$id."' LIMIT 1;";
        //echo "\n".$sql."\n";
	if($transaction_mode)
		return $sql;
	else
		return query($sql,$debug);
}

function sql_simple_update_link($table,$id,$r,$link, $transaction_mode=FALSE,$debug=FALSE,$id_name='id'){
	foreach($r as $key => $value)$tmp_set[]=$key."='".addslashes($value)."'";

	$sql="UPDATE ".$table." SET ".implode(', ',$tmp_set)." WHERE $id_name='".$id."' LIMIT 1;";
        //echo "\n".$sql."\n";
	if($transaction_mode)
		return $sql;
	else
		return mysqli_query($link, $sql);
}

function sql_simple_update_quote($table,$id,$r,$transaction_mode=FALSE,$debug=FALSE){
	foreach($r as $key => $value){
		$tmp_set[]=magic_slashe($key,$value);		
	}

	$sql="UPDATE ".$table." SET ".implode(', ',$tmp_set)." WHERE id='".$id."' LIMIT 1;";

	if($transaction_mode)
		return $sql;
	else
		return query($sql,$debug);
}

function sql_simple_delete($table,$id,$transaction_mode=FALSE,$debug=FALSE){
	$sql="DELETE FROM ".$table." WHERE id='".$id."' LIMIT 1;";	
	if($transaction_mode)
		return $sql;
	else
		return query($sql,$debug);
}

function sql_simpleWhere_delete($table,$fieldname,$fieldvalue,$transaction_mode=FALSE,$debug=FALSE){

	$sql="DELETE FROM ".$table." WHERE ".$fieldname."='".$fieldvalue."' LIMIT 1;";
		
	if($transaction_mode)
		return $sql;
	else
		return query($sql,$debug);
}

function sql_simple_insert($table,$r,$transaction_mode=FALSE,$debug=FALSE){
	foreach($r as $key => $val){
		$insert[]='`'.$key.'`';
		$value[]="'".addslashes($val)."'";
	}
	$sql="INSERT INTO ".$table." (".implode(', ',$insert).") VALUES (".implode(', ',$value).");";
	if($transaction_mode){
		return $sql;
	}else{
		query($sql,$debug);
		return @((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
	}
}

function sql_simple_insert_link($table,$r,$transaction_mode=FALSE,$debug=FALSE,$link){
	foreach($r as $key => $val){
		$insert[]='`'.$key.'`';
		$value[]="'".addslashes($val)."'";
	}
	$sql="INSERT INTO ".$table." (".implode(', ',$insert).") VALUES (".implode(', ',$value).");";
	if($transaction_mode){
		return $sql;
	}else{
		mysqli_query($link, $sql);
		return @((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
	}
}

function sql_simple_replace($table,$r,$transaction_mode=FALSE,$debug=FALSE){
	foreach($r as $key => $val){
		$insert[]='`'.$key.'`';
		$value[]="'".addslashes($val)."'";
	}
	$sql="REPLACE INTO ".$table." (".implode(', ',$insert).") VALUES (".implode(', ',$value).");";
	if($transaction_mode){
		return $sql;
	}
	else{
		query($sql,$debug);
		return @((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
	}
}

function sql_simple_insert_quote($table,$r,$transaction_mode=FALSE,$debug=FALSE){
	foreach($r as $key => $val){
		$insert[]='`'.$key.'`';
		if(!get_magic_quotes_gpc()){
			$value[]="'".addslashes($val)."'";
		}else{
			$value[]="'".$val."'";
		}
	}
	$sql="INSERT INTO ".$table." (".implode(', ',$insert).") VALUE (".implode(', ',$value).");";
	if($transaction_mode){
		return $sql;
	}
	else{
		query($sql,$debug);
		return @((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
	}
}

function sql_insert($table,$insert,$values,$debug=FALSE){
	foreach($values as $key => $value) $values[$key]="'".addslashes($value)."'";
	$sql="INSERT INTO ".$table." (".implode(', ',$insert).") VALUE (".implode(', ',$values).");";
	return insert_query($sql,$debug);
}

function squery($sql,$debug=FALSE,$link=false){
	if($link)
		$result=query_link($sql,$link);
	else
		$result=query($sql,$debug, false,$link);
	if(@mysqli_num_rows($result)==1){
		$r=@mysqli_fetch_row($result);
		return $r[0];
	}
	if(@mysqli_num_rows($result)>1){
		$r=array();
		while($row=@mysqli_fetch_row($result)) $r[]=$row[0];
		return $r;
	}
	return FALSE;
}

function squery_assoc($sql,$indexName,$fieldName,$debug=FALSE,$link=false){
	$result=query($sql,$debug, false,$link);
	if(@mysqli_num_rows($result)>=1){
		$r=array();
		while($row=@mysqli_fetch_array($result)) $r[$row[$indexName]]=$row[$fieldName];
		return $r;
	}
	return FALSE;
}

function squeryArr($sql,$debug=FALSE, $link=false){
	$r = false;
	$result=query($sql,$debug,false,$link);
	if(mysqli_num_rows($result)==1){
		while($row=mysqli_fetch_array($result)) { 
			$r=$row;
		}
		return $r;
	}
	return FALSE;
}
?>