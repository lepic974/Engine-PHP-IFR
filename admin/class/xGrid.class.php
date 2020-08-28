<?php
/**
* @package XGrid
* @author CT <christophe.thibault@gmail.com>, CC <christophe.cauter@nexto.fr>
* @desc xgrid project ; the class inclusion also act as self xgrid $_POST processing with header redirection;
* thus this class MUST be included prior any html output
* @comment allow specific project integration
* @version 1.0, xx/200x
*/

if(isset($_POST['xgrid_id']) AND isset($_POST['xgrid_json'])){
	//id has some naming restrictions
	if(!preg_match('/^[a-z\d_]{3,28}$/i', $_POST['xgrid_id'])) { 
		echo 'xGrid says : my id "'.$_POST['xgrid_id'].'" has invalid characters or is too short.';
		exit();
	} 
	$_SESSION['_xgrid'][$_POST['xgrid_id']]=$_POST['xgrid_json'];
	unset($_POST['xgrid_json']);
	$_SESSION['_xgrid_post']=$_POST;
	$_SESSION['_xgrid_files']=$_FILES;
	// xGrid will redirect transparently to same location, simulating $_POST and $_FILES as if they were directly sent.
	// this step is required avoiding the refresh (F5) problem
	if(!headers_sent()){
		header("Location: ".html_entity_decode(htmlspecialchars($_SERVER["REQUEST_URI"])));
	}else{
		echo 'xGrid says : there must be no html output prior this point! header already sent!';
	}
	exit();	
}
if(!empty($_SESSION['_xgrid_post'])){
	$_POST=$_SESSION['_xgrid_post'];
	unset($_SESSION['_xgrid_post']);
	$_FILES=$_SESSION['_xgrid_files'];
	unset($_SESSION['_xgrid_files']);
}
function erp_json_decode(&$json){
	if(function_exists('json_decode')) return json_decode($json);
}
function erp_json_encode(&$json){
	if(function_exists('json_encode')) return json_encode($json);
}

$__xGridGlobal=array(); //reserved

/*
 * @desc select query based search engine
 * @param string $xgridId unique id to store json into session
 * @param string $sql select mysql query
 * @version 1.0 01/2008
 * @author C.C christophe.cautere@nexto.fr
 * @comment
 * this server side xgrid class works in concurrence with jquery.xgrid (client side)
 * result table is built this way:
 * -on construct, base select query is set, default parameter layer is applied, json is loaded from session
 * -on methods parameter layer is built over default parameter layer
 * -on build, json parameter layer is stacked up, select query is built, count query is built, header is built, body is built
 */


/*
Changelog

October 2008:
	added this::formatBeforeReplace() @desc process type formatting before applying replace @see this::typeFormat() , this::applyReplace()
	added this::noFormat() @desc disable value type formatting @see this::typeFormat()
	improved this::defaultFilter(), now accept array of values allowing to set default filter for date from/to
	added getRelativeMktime() @desc get mktime based on a time relative to now
	added defaultDateFromFilter(),defaultDateToFilter() @desc allow default "from/to" filter to be easily set for dates, using this::getRelativeMktime()
Decembre 2008:
	added this::verboseMode() @desc allow preventive debug, detecting build time inconsistency. has global parameter default
	added setFormAction() @desc allow to define another post destination (usefull for ajax constructor)
January 2009:
	added this::setColSequence() @desc specified aliases will be displayed in array order, followed by other aliases
	added this::superSwap() @desc allow build time swap AND query time sort/filter, using temporary memory table
February 2009:
	the two most important methods buildSqlInit() and concatCorpse() are now in deep commented
June 2009:
	added this::simpleReplace() @desc shorthand for replace
	added this::disableTh() @desc hide label, disable sort, disable filter
	added this::addPost() @desc add hidden fields to be posted with xGrid
	added this::buildUsingTemplate() @desc build xGrid using template
	added this::addTemplateRow() @desc allow to define a template row to be used in this::buildUsingTemplate()
	added this::firstSort() @desc set first sort order for a collumn
	changed this::verboseMode() to this::enableVerboseMode()
August 2009:
	added this::emptyIfNoResult()
*/




class xGrid{
	private $val=array(); //@var array [$key][]=(object) ( do=>action ..., param=>value)
	private $cell=array(); //@var array [$key][]=(object) ( do=>action ..., param=>value)
	
	private $enabled=array(); //@var array [something]=>TRUE. contain the result of the lastest this::enableSomething() call;
	private $disabled=array(); //@var array @see this::enabled
	private $param=array(); //@var array [something]=>value. contain the result of the lastest this::setSomething() call;
	private $alias=array(); //To be used instead of multiple private properties related to alias management
	private $interface=array(); //@var array html elements of interface @see xGrid.class.interface.php
	private $build=array(); //@var array('hidden', 'wrap', 'footer', 'header') //TODO allow easy override
	
	private $renderTo='screen'; //@var screen | pdf | csv | xls
	
	//private $c;
	private $enableFilter=array(); //@var array alias=>true enable column filter
	private $allowAllFilter=FALSE; //@var bool true enable all available column filter
	private $disableFilter=array(); //@var array alias=>true override this::allowAllFilter for this alias
	private $enableSort=array(); //@var array alias=>true enable sort
	private $allowAllSort=FALSE; //var bool enable sort on click on every column header
	private $disableSort=array(); //@var array alias=>true override this::allowAllSort for this alias
	
	private $disableProfile=FALSE;
	
	private $sql;
	private $sqlCount;
	//private $sqlSplit=array();
	private $sqlNumRows; //@var int total number of rows in query, only in count mode
	
	private $countMode=TRUE; //@var bool count number of results, slower but allow detailled page numbering
	private $pageMode=TRUE;
	private $pageCount; //@var int last page num know in count mode
	private $ajaxMode=FALSE; //@var bool will try to post on itself by default
	
	private $pageCurrent=1;
	
	private $x;
	private $resultPerPage=20;
	private $numRows;
	private $result; //@var ressource mysql result, internal use only
	
	
	
	
	private $isHidden; //@var array list of hidden columns @see this::hide()
	private $isFiltered;
	private $filterInput;
	private $filterType;
	private $filterTitle=array(); //@var array filter human readable
	
	private $isSwitchable=array(); //@var array list of switchable alias
	
	private $isSorted;
	
	private $defaultSort=array(); //@var array list of default ORDER BY section, $defaultSort[alias]=>ASC|DESC
	
	private $isBool; //@var bool define column as boolean and apply following formating: green check for yes and dark cross for no
	private $isBoolAlt; //@var bool like isBool but with alternate display, red check for yes and blank for no
	private $boolFalseHtml; //@var array custom per column html replacement for boolean with FALSE value
	private $boolTrueHtml; //@var array custom per column html replacement for boolean with TRUE value
	
	private $swap; //@var array $swap[alias][value], simplest value replacement
	
	private $tdClass=array();
	
	private $cellAttr=array(); //@var array [alias][attr][][value]
	private $rowAttr=array(); //@var array [attr][][value]
	private $rowAttrSwap=array(); //@var array [attr][alias]

	private $isDate; //@see this::isDate(), this::concatCorpse()
	private $dateFormat; //@see this::isDate(), this::concatCorpse()
	
	private $isNumeric;
	private $isInt;
	private $isFloat;
	private $isDec;
	private $decimals;
	private $isCurrency;
	private $showTotal;
	private $total;
	private $isCumulative;
	private $cumul;
	
	private $userVisibleCol=array(); //@var array, visible columns based on user selection
	private $visibleCol=array(); //@var array, internal use only for optimisation purpose
	private $keyList=array(); //@var array, internal use, only aliased selected fields
	private $orderBy=array();
	private $where=array();
	
	//sql where constructor
	public $sqlWhere;
	
	//intab filters
	private $intabFilter=array(); //@var array []=>alias
	private $comboTable=array(); //@var array alias=>array(value=>label)
	private $comboTableIsMulti=array(); //@var array alias=>bool
	private $comboGlueType=array();	//@var array for OR 
	
	private $prependForm=''; //prepend html after opening form tag
	private $appendForm=''; //append html before closing form tag
	private $disableFormWrap=FALSE; //disable from tag wrapping, allowing custom form wrapping
	
	private $th=array(); //@var array columns label
	private $thShort=array(); //@var array columns short label
	private $json; //@var object json user parameters (filters, sort...)
	private $jsonRO; //@var object json readonly hidden data
	private $xgridId; //@var string xgrid id, based on md5($this->sql) for unique parameter set on each xgrid
	private $aliasMap=array(); //@var array alias mapping for filter in query's "where" section
	
	private $callFunc=array(); //@see this::callFunc(), will execute php_user_func()
	private $callFuncArray=array(); // @see this::callFuncArray(), will execute php_user_func_array()
	
	private $jsonWhere=array();
	private $class='jqxGrid';
	
	private $hideOverflow; //@var array hide culumn overflow hiding
	
	private $replace;
	private $caseReplace;
	private $caseBackground;
	private $highlight=array();
	
	private $title;
	
	private $dblclick; //@var string alias used as dblclick action can be associated with a replace
	
	
	private $paramTable=array(); //@var array code=>label table
	private $paramReplace=array(); //@var array alias=>true
	
	private $sqlExecTime=0;
	private $buildExecTime=0;
	
	private $appendFooter; //@var html append a footer to last row
	private $colFooter=array(); //@var array key=>html append a footer to column
	
	private $splitter; //@var string alias as splitter
	private $splitterValueTracker; //@var string internal value tracker to trigger splitter row
	private $splitterAppend;
	
	private $alternateRowBgd=FALSE; //@var bool alternate row background
	private $fadedRowBgd=FALSE; //@var use faded row background
	
	
	

	
	private $rowJSON=array();
	
	public $dbug=FALSE;
	public $autoTh=FALSE;
	public $bgcolor0='#fff';
	public $bgcolor1;
	
	private $wideMode=TRUE;
	
	private $allowEdit=array();
	private $defaultProfile=FALSE; //if true, apply potential default filter @see this::defaultFilter()
	
	//---< Multiple Connection for different DB >---\\
	private $mysqlDB = '';
	private $mysqlLink = '';
	
	private $noFormat=array();
	private $formatBeforeReplace=array();
	
	// xGrid edit mode
	private $editableAjax;
	private $editableRowId;
	private $isEditable;
	private $isButton;
	private $isDelete;
	private $allowEditableNew;
	private $cellEditParam=array(); //@var add additional posted param (alias=value) on editable field ajax post
	
	
	private $formAction; // url force form action
	private $aliasError=array(); //filled if unknow alias encountered
	private $appendVerbose='';
	
	private $colSequence; //@param array defined alias will be displayed in sequence followed by other aliases
	
	private $superSwap=array(); //@param array swap alias part of select by a joined memory table
	private $tableAliasList=array(); //@param array [alias]=>tableName auto filled during query parsing
	
	private $resultArrayEnabled=TRUE; //collect each row cells into this::resultArray 
	private $resultArray=array(); //[row_idx][alias]=value
	
	private $templateRows=array(); //@see this::buildUsingTemplate()
	private $templateRow; //@see this::buildUsingTemplate()
	private $templateRowSelector;
	
	private $firstSort=array();
	
	private $buildPDF=FALSE; //@see this::buildPDF()
	
	private $renderToScreen=TRUE;
	private $renderToFile=FALSE;
	
	private $glueGather=array(); //@see this::initJson()
		
	//Excel export
	private $excelExportWS; // Excel export worksheet
	private $excelExportCC; // Excel export current column in worksheet
	private $excelExportCR; // Excel export current row in worksheet
	
	
	private $emptyIfNoResult=FALSE; //@var bool on TRUE, will return empty string on empty result set (instead of empty xGrid html wrap)
	
	/*
	 * @desc init xGrid with a unique id and a mysql query
	 * @param string $xgridId shall be unique to have distinct session settings
	 * @param string $sql must be a valid mysql SELECT query using aliases
	 * @comment on construct:
	 * -we apply the predefined parameters (this::onNew())
	 * -we collect some query infos prior any execution
	 * -we initiate|refresh persistance of JSON through php session
	 * -we get some infos from JSON needed at this point
	 * -every else will be done at building point
	 */
	public function __construct($xgridId, $sql){
		//get xGrid project level parameters
		require dirname(__FILE__).'/xGrid.class.param.php';		
		//execute old style project level parameters
		if(isset($param)){ // defined in xGrid.class.param.php
			if(isset($param['enable_print_html2pdf'])) $this->enablePdfPrint($param['enable_print_html2pdf']);
			if(isset($param['enable_profile'])) $this->enableUserProfile($param['enable_profile']);
			if(isset($param['enable_calendar'])) $this->enableDatePicker($param['enable_calendar']);
			if(isset($param['user'])) $this->setUserProfileId($param['user']);
			if(isset($param['queryFunction'])) $this->setQueryFunction($param['queryFunction']);
			if(isset($param['defaultVerboseMode'])) $this->enableVerboseMode($param['defaultVerboseMode']);
		}
		//execute project level parameters
		$this->runOnNew();
		
		//define default interface (that will be sent to client side)
		$this->setDefaultInterface();
		
		//default build parameters
		$this->build['wrap']=TRUE;
		$this->build['hidden']=TRUE;
		$this->build['header']=TRUE;
		$this->build['title']=TRUE; //depends on header
		$this->build['tabs']=TRUE; //depends on header
		$this->build['tab_print']=TRUE; //depends on tabs
		$this->build['footer']=TRUE;
		$this->build['csv']=FALSE;
		$this->build['print']=FALSE;
		
		//suck informations from the select expression list
		//this way we "know" at this point the query expression prior execution (SELECT field[ AS alias], field[ AS alias], ... )
		#SELECT>
			//TODO remove only tailing ;
			//clean sql, remove ;
			$this->sql=trim(str_replace(';','',$sql));
			
			//get root level query for chunking
			$this->blankedSql=$this->getSqlBlankedBracket($this->sql);
			
			//isolate select section
			$select=substr($this->sql, stripos($this->blankedSql,'SELECT')+6,stripos($this->blankedSql,' FROM ')-6 );
			
			//removing field escaping
			$select=str_replace('`','',$select);		
			
			#FIELDS>
				//split selected fields with their optional alias, differentiating comas types. add ending coma to process last field
				$a=str_split($select.',');
				$deep=0; //subquery deep
				$selectSplit=array();
				
				$cursor=0; //parsing cursor
				$cursorOffset=0; //parsing cursor splitting offset
				//parse each char
				foreach($a as &$c){
					switch($c){
						//differentiate coma
						case ",":
							//coma is field separator if subquery deep is 0 (root)
							if($deep==0){
								//store field/alias couple, field can thus be a subquery minus ending coma
								$selectSplit[]=trim(substr($select,$cursorOffset+1,$cursor-$cursorOffset-1));
								$cursorOffset=$cursor;
							}
						break;
						//increase subquery deep level
						case '(':
							$deep++;
						break;
						//decrease subquery deep level
						case ')':
							$deep--;
						break;
					}
					//inc current parsing cursor
					$cursor++;
				}
			#<FIELDS
			#ALIASES>
				foreach($selectSplit as &$field){
					$asPos=stripos($this->getSqlBlankedBracket($field), ' as ');
					if($asPos>0){ //alias found
						$alias=substr($field, $asPos+4);
						$this->keyList[]=$alias;
						$this->aliasMap[$alias]=substr($field, 0, $asPos); // alias => database fieldname|subquery
					}else{
						$alias=$field;
						$dotPos=strripos($this->getSqlBlankedBracket($alias), '.');
						if($dotPos>0) $alias=substr($alias,$dotPos+1); //dot found, field name without prefix must be keept to match mysql_fetch_assoc array
						if(!isset($this->aliasMap[$alias])){
							$this->aliasMap[$alias]=$alias; // database fieldname => database fieldname
							$this->keyList[]=$alias;
						}
					}
				}
			#<ALIASES
			#SELECT*>
				//No alias mode
				if(isset($this->aliasMap['*'])){
					$from=substr($this->sql, stripos($this->blankedSql,' FROM ')+6);
					if(strpos($from,' ')>0){
						$from=trim(substr($from, 0,strpos($from,' ')));
					}
					$sql="EXPLAIN $from";
					
					$result=$this->query($sql);
					while($r=mysqli_fetch_assoc($result)){
						$key=$r['Field'];
						$this->keyList[]=$key;
						$this->aliasMap[$key]=$key;
						$this->th($key,$key);
						
						$type=$r['Type'];
						$split=explode(' ',$type);
						$type=$split[0];
						if($type=='tinyint(1)') $this->isBool($key);
						$split=explode( '(',$type);
						$type=$split[0];
						
						if($type=='int') $this->isInt($key);
						if($type=='decimal') $this->isDec($key);
						if($type=='bigint') $this->isDate($key);
					}
				}
			#<SELECT*
		#<SELECT
		$this->xgridId=$xgridId;
		
		//xGrid persistance is made through a JSON encoded object stored in session
		//at this point we do init (if needed) then read the current JSON
		#PERSISTANCE>
			if(empty($_SESSION['_xgrid'][$this->xgridId])){ //doesnt exists in session
				$tmp_var = stripcslashes(('{}'));
				$this->json=erp_json_decode($tmp_var);
			}else{
				$tmp_var = stripcslashes( $_SESSION['_xgrid'][$this->xgridId] );
				$this->json=erp_json_decode($tmp_var);
				if(!is_object($this->json)){ //not valid
					$this->json=erp_json_decode(stripcslashes(('{}')));
				}
			}
		#<PERSISTANCE
		//on first init use default profile
		if(!count((array)$this->json)){
			$this->defaultProfile=TRUE;
		}
		//get the current page number the user is asking
		$this->pageCurrent=$this->jsonGetCurrentPage();
		
		//init the xGrid object that will be used later to build the dynamic conditionnal section that will be appended to query
		$this->sqlWhere=new xGridWhere();		
		
		//get a copy of former alias map, because dynamic alias change may occur later @see this::superSwap()
		$this->sourceAliasMap=$this->aliasMap;
	}
	
	/*
	 * @desc reset specified xGrid to first load state
	 * @param bool $bool
	 */
	public static function reset($xGridId){
		unset($_SESSION['_xgrid'][$xGridId]);		
	}
	
/*
	 * @desc manage this::param
	 * @var string $param
	 * [@var mixed $value]
	 * @comment use "$this->param('something');" to get value, or "$this->param('something', value);" to set value
	 */
	private function param(){
		$args=func_get_args();
		$param=$args[0];
		if(isset($args[1])){
			$value=$args[1];
			$this->param[$param]=$value;			
		}else{
			if(isset($this->param[$param])){
				return $this->param[$param];
			}else{
				return null;
			}
		}
	}
	
	/*
	 * @desc manage this::enabled, on FALSE unset
	 * @var string $param
	 * [@var bool $bool]
	 * @comment use "$this->enabled('something');" to get value, or "$this->enabled('something', TRUE|FALSE);" to set value
	 */
	private function enabled(){
		$args=func_get_args();
		$param=$args[0];
		if(isset($args[1])){
			$bool=(bool)$args[1];
			if($bool){
				$this->enabled[$param]=TRUE;
			}else{
				unset($this->enabled[$param]);
			}		
		}else{
			return isset($this->enabled[$param]);
		}
	}
	
	/*
	 * @desc manage this::disabled, on FALSE unset
	 * @var string $paramp
	 * [@var bool $bool]
	 * @comment use "$this->disabled('something');" to get value, or "$this->disabled('something', TRUE|FALSE);" to set value
	 */
	private function disabled(){
		$args=func_get_args();
		$param=$args[0];
		if(isset($args[1])){
			$bool=(bool)$args[1];
			if($bool){
				$this->disabled[$param]=TRUE;
			}else{
				unset($this->disabled[$param]);
			}		
		}else{
			return isset($this->disabled[$param]);
		}
	}
	
	/*
	 * @desc public alias for this::param()
	 */
	public function getParam($param){
		return $this->param($param);
	}
	
	/*
	 * @desc public alias for this::enabled()
	 */
	public function isEnabled($param){
		return $this->enabled($param);
	}
	
	/*
	 * @desc public alias for this::disabled()
	 */
	public function isDisabled($param){
		return $this->disabled($param);
	}
	
	
	/*
	 * @desc allow to call xgrid methods on construct
	 * @param string xgrid::methodName
	 * [@param mixed xgrid::methodName(arg)]
	 */
	public static function onNew(){
		global $__xGridGlobal;
		$__xGridGlobal['onNew'][]=func_get_args();
	}
	
	/*
	 * @desc execute this::onNew
	 */
	private function runOnNew(){
		global $__xGridGlobal;
		if(empty($__xGridGlobal['onNew'])) return TRUE;		
		foreach($__xGridGlobal['onNew'] as $onNew){
			$methodName=$onNew[0];
			unset($onNew[0]);
			$argList=$onNew;
			call_user_func_array(array('xGrid',$methodName), $argList);
		}
	}
	
	public function addParamTable($index, $paramTable){
		$this->paramTable[$index]=$paramTable;
	}
	public function paramReplace($key, $paramTableIndex=''){
		$this->paramReplace[$key]=(empty($paramTableIndex) ? $key:$paramTableIndex);
	}
	
	/*
	 * @desc split select expression into pratical chunks
	 * @param string $sql query
	 * @comment 
	 * first step:clean the query by removing all potential keywords
	 * second step: split into chunks using keyword list
	 */
	private function getSqlChunk($sql){
		
		//First step
		$a=str_split($sql);
		$i=0;
		$open=FALSE;
		foreach($a as &$c){
			switch($c){
				
				case "'":
				case '`':
					$open=!$open;
				break;
				
				case '(':
				
					$i++;
				break;
				case ')':
					$i--;
				break;
				default:
					if($i>0 OR $open) $c=' ';
			}
		}
		$sqlClean=implode('',$a);

		//Second step
		$chunk=array();
		$keyword=array('select'=>'SELECT ', 'from'=>' FROM ', 'where'=>' WHERE ', 'groupby'=>' GROUP BY ', 'orderby'=>' ORDER BY ', 'limit'=>' LIMIT ');
		$pos=array();
		$len=array();
		$sqlLen=strlen($sqlClean);
		$keywordParse=array_reverse($keyword);
		$endLen=0;
		foreach($keywordParse as $key => $pattern){
			$pos[$key]=stripos($sqlClean,$pattern);	
			$len[$key]=($pos[$key]===FALSE ? 0:$sqlLen-$pos[$key]-$endLen);
			$endLen+=$len[$key];
			$chunk[$key]=trim(substr($sql, $pos[$key], $len[$key]));
		}
		return array_reverse($chunk);
	}
	
	/*
	 * @desc init superswap query modifier
	 * @var reference $sqlChunk
	 * @comment will dynamically swap values using a memory table, thus the swapped values are available to filter/sort
	 */
	private function initSuperSwap(&$sqlChunk){
		
		if(empty($this->superSwap)) return FALSE;
		
		//clean "from chunk", then split join sections
		$chunks=explode(';', str_ireplace(array('LEFT JOIN ','INNER JOIN ', 'STRAIGHT_JOIN '), ';', preg_replace ("/\s+/", " ", trim(substr($sqlChunk['from'],4))) ));
		$this->tableAliasList=array();
		foreach($chunks as $chunk){
			//we dont need the condition/index trailing section
			$chunk=str_ireplace(array(' ON ',' USING ', ' USE INDEX ', ' IGNORE INDEX '), ';', $chunk);
			$chunk=explode(';',$chunk);
			$chunk=trim($chunk[0]);
			$split=explode(' ', $chunk);
			$table=$split[0];
			//each join section has a table name but optional alias, there is 3 possible cases:
			$alias=$table; //table
			if(count($split)==2) $alias=$split[1]; // table alias
			if(count($split)==3) $alias=$split[2]; // table AS alias
			//fill table list
			$this->tableAliasList[$alias]=$table;				
		}
	

		foreach($this->superSwap AS $key=>$tableData){
			$table=explode('.',$this->sourceAliasMap[$key]);
			$field=$table[1];
			$table=$table[0];
			
			
			$table=$this->tableAliasList[$table];
			$sql="SHOW COLUMNS FROM ".$table." WHERE Field='$field';";
			$result=$this->query($sql);
			$r=mysqli_fetch_assoc($result);
			$typeBefore=$r['Type'];

			//guess swapAfter type
			$isFullNumeric=TRUE; // only decimals
			$isFullInt=TRUE; //only int
			$maxLength=0; // varchar(x)
			
			$sqls=array();
			$mTable='m_xgrid_swap_'.$key;
			foreach($tableData AS $beforeSwap =>$afterSwap){
				if(!is_numeric($afterSwap)){
					$isFullNumeric=FALSE;
					$maxLength=max($maxLength, strlen($afterSwap));
				}else{
					if(!is_integer($afterSwap)) $isFullInt=FALSE;
				}
				$sqls[]="INSERT INTO $mTable (beforeswap, afterswap) VALUES ('$beforeSwap','$afterSwap');";
			}
			if($isFullNumeric){
				if($isFullInt) $typeAfter='bigint(11)';
				else $typeAfter='decimal(20,8)';
			}else{
				$typeAfter='varchar('.$maxLength.')';
			}

			$sqls=array_merge(array("DROP TABLE IF EXISTS $mTable;","
			CREATE ".($this->dbug?'':'TEMPORARY ')."TABLE `$mTable` (
			  `beforeswap` $typeBefore NOT NULL default '',
			  `afterswap` $typeAfter default NULL,
			  PRIMARY KEY  (`beforeswap`)
			) ENGINE=MEMORY DEFAULT CHARSET=utf8;"), $sqls);
		}
		$this->tquery($sqls);

		$sql=" LEFT JOIN $mTable ON $mTable.beforeswap=".$this->sourceAliasMap[$key];
		
		$mx=new mxSql(implode(' ', $sqlChunk));
		
		$mx->setExprByAlias($key, $this->aliasMap[$key].' AS '.$key);
		$mx->rebuildSelect();
		$sqlChunk['select']=$mx->getSelect();
		$sqlChunk['from'].=$sql;

		return TRUE;
	}
	
	
	/*
	 * @desc TO BE COMPLETED, temporary simple filtering
	 
	private function where($key, $string, $type){
		if(is_numeric($string)){
			$wrapper="";
		}else{
			$wrapper="'";
			if(is_array($string)){				
				foreach($string as &$value){
					if(!is_numeric($value))	mysql_real_escape_string($value);
				}
			}else{
				$string=mysql_real_escape_string($string);
			}
			
		}

		switch($type){
			case 'IN':
				foreach($string as &$value) $value=$wrapper.$value.$wrapper;
				$where=" $key IN (".implode(',',$string).")";
				$w=new xGridWhere();
				$w->alias=$key;
				$w->value=$string;
				$w->operator='IN';
				$this->sqlWhere->add($w);
			break;
			case 'contain':			
				$where=" $key LIKE '%$string%'";
				$w=new xGridWhere();
				$w->alias=$key;
				$w->value=$string;
				$w->operator='%';
				$this->sqlWhere->add($w);
				
			break;
			case '=':
				$where="$key={$wrapper}$string{$wrapper}";
				$w=new xGridWhere();
				$w->alias=$key;
				$w->value=$string;
				$w->operator='=';
				$this->sqlWhere->add($w);
			break;
			case '>':
				$where="$key>{$wrapper}$string{$wrapper}";
			break;
			case '<':
				$where="$key<{$wrapper}$string{$wrapper}";
			break;
		}
		if(isset($this->where[$key])){
			$this->where[$key]="( {$this->where[$key]} AND $where )";
		}else{
			$this->where[$key]=$where;	
		}
		
		
	}
	
	*/
	
	
	/*
	 * @desc use json as parameter layer over oncall parameters
	 * @comment also build filter description title
	 */	
	private function initJsontitle(){
		#JSON_PROC>
		//var_export($this->json);
		foreach($this->json as $key => $option){
			//get title of field
			if (isset($this->th[$key])){
				$th=$this->th[$key];
			}else{
				$th=$key;
			}
			//alias level
			if(is_object($option) AND in_array($key, $this->keyList) ){
				//alias option level
				foreach($option as $type=>$val){
					switch($type){
						case 'combo':
							$filterTitle='<span class="xgrid_alias xgrid_filter_off_combo"  rel="'.$key.'">';
							$filterTitle.=$th.' est ';
							$filterTitle.=(count($val)>1?'parmi ':'');
							$label=array();
							foreach($val as $value) $label[]=$this->comboTable[$key][$value];
							$filterTitle.='{'.implode(', ',$label).'}';
							$filterTitle.='</span>';	
							$this->filterTitle[]=$filterTitle;
						break;
						case 'filter':
							if($val===''){
								unset($option->filter);
								break;
							}
							
							
								
								if(isset($this->isDate[$key]) ){
									
									if(!empty($val->from) AND !empty($val->to)){
										$this->filterTitle[]='<span class="xgrid_alias xgrid_filter_off_date"  rel="'.$key.'">'.$th.' du ['.htmlentities($val->from).'] au ['.htmlentities($val->to).']'.'</span>';	
									}else{
										if(!empty($val->from)){
											$this->filterTitle[]='<span class="xgrid_alias xgrid_filter_off_date"  rel="'.$key.'">'.$th.' à partir du ['.htmlentities($val->from).']'.'</span>';								
										}
										if(!empty($val->to)){
											$this->filterTitle[]='<span class="xgrid_alias xgrid_filter_off_date"  rel="'.$key.'">'.$th.' jusqu\'au ['.htmlentities($val->to).']'.'</span>';
										}
									}
									
								}elseif(isset($this->isBool[$key]) ){								
									$this->filterTitle[]='<span class="xgrid_alias xgrid_filter_off_bool"  rel="'.$key.'">'.($val==1?"est":"n'est pas").' '.$th.'</span>';										
								}else{
									$this->filterTitle[]='<span class="xgrid_alias xgrid_filter_off_fulltext"  rel="'.$key.'">'.$th.' contient {'.htmlentities($val).'}</span>';
								}
						break;
					}
				}
			}
		}
		#<JSON_PROC
	}
	
	
	
	/*
	 * @desc use json as parameter layer over oncall parameters
	 * @comment also build filter description title
	 */	
	private function initJson(){
		
		#PROFILE>
			//remove profile
			if(isset($this->json->_temp->removeProfile)){
				$profile_num=(int)$this->json->_temp->removeProfile;
				$sqls=array();
				$sql="DELETE FROM t_xgrid_user_profile WHERE `user`='".$this->getParam('userProfileId')."'";
				$sql.=" AND xgrid='".$this->xgridId."'";
				$sql.=" AND num=$profile_num LIMIT 1;";
				$sqls[]=$sql;
				$this->tquery($sqls);
			}
			//save profile
			if(isset($this->json->_temp->addProfile)){
				$profile_name=mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $this->json->_temp->addProfile);
				//do not save temp and page numbering data
				unset($this->json->_temp);
				unset($this->json->_page_count);
				unset($this->json->_page);
				$sqls=array();
				$sqls[]="SET @next_num=1+(SELECT IFNULL(max(num),0) FROM t_xgrid_user_profile WHERE `user`='".$this->getParam('userProfileId')."' AND `xgrid`='".$this->xgridId."')";
				$sql="INSERT INTO t_xgrid_user_profile (`user`, xgrid, num, `name`, json)";
				$sql.=" VALUES('".$this->getParam('userProfileId')."', '".$this->xgridId."', @next_num, '$profile_name', '".erp_json_encode($this->json)."')";
				$sqls[]=$sql;
				$this->tquery($sqls);
			}
			//replace profile
			if(isset($this->json->_temp->replaceProfile)){
				$profile_num=(int)$this->json->_temp->replaceProfile;
				//do not save temp and page numbering data
				unset($this->json->_temp);
				unset($this->json->_page_count);
				unset($this->json->_page);
				$sqls=array();
				$sqls[]="UPDATE t_xgrid_user_profile SET json='".erp_json_encode($this->json)."' WHERE user='".$this->getParam('userProfileId')."' AND xgrid='".$this->xgridId."' AND num='".$profile_num."';";
				$this->tquery($sqls);
			}
			//replace profile and set it default profile
			if(isset($this->json->_temp->replaceProfileDefault)){
				$profile_num=(int)$this->json->_temp->replaceProfileDefault;
				//do not save temp and page numbering data
				unset($this->json->_temp);
				unset($this->json->_page_count);
				unset($this->json->_page);
				$sqls=array();
				$sql="UPDATE t_xgrid_user_profile SET isDefault=0";
				$sql.=" WHERE user = '".$this->getParam('userProfileId')."' AND xgrid = '".$this->xgridId."' AND isDefault = 1";
				$sqls[]=$sql;
				$sqls[]="UPDATE t_xgrid_user_profile SET json='".erp_json_encode($this->json)."', isDefault = 1 WHERE user='".$this->getParam('userProfileId')."' AND xgrid='".$this->xgridId."' AND num='".$profile_num."';";
				$this->tquery($sqls);
			}
			//save default profile
			if(isset($this->json->_temp->saveProfileDefault)){
				$profile_number=$this->json->_temp->saveProfileDefault;
				$defaultValue=$this->json->_temp->saveProfileDefaultValue;
				//do not save temp and page numbering data
				unset($this->json->_temp);
				unset($this->json->page_count);
				unset($this->json->_page);
				$previousValue = $this->squery("SELECT isDefault FROM t_xgrid_user_profile WHERE user = '".$this->getParam('userProfileId')."' AND xgrid = '".$this->xgridId."' AND num = '".$profile_number."';");
				if($previousValue != $defaultValue){
					$sqls=array();
					if($defaultValue){
						$sql="UPDATE t_xgrid_user_profile SET isDefault=0";
						$sql.=" WHERE user = '".$this->getParam('userProfileId')."' AND xgrid = '".$this->xgridId."' AND isDefault = 1;";
						$sqls[]=$sql;
					}
					if($profile_number != 0){
						$sql="UPDATE t_xgrid_user_profile SET isDefault=".(int)$defaultValue;
						$sql.=" WHERE user = '".$this->getParam('userProfileId')."' AND xgrid = '".$this->xgridId."' AND num = '".$profile_number."';";
						$sqls[]=$sql;
					}
					$this->tquery($sqls);
				}
			}
			//apply profile
			if(isset($this->json->_temp->profile)){
				$user_profile_num=(int)$this->json->_temp->profile;
				if($user_profile_num>0){
					$sql="SELECT json FROM t_xgrid_user_profile WHERE `user`='".$this->getParam('userProfileId')."' AND `xgrid`='".$this->xgridId."' AND num=$user_profile_num LIMIT 1;";
					$result=$this->query($sql);
					if(mysqli_num_rows($result)){
						$r=mysqli_fetch_row($result);
						$json=&$r[0];
						$this->json=erp_json_decode($json);
						$_SESSION['_xgrid'][$this->xgridId]=erp_json_encode($this->json);
					}
				}else{			
					$json='{}';
					$this->json=erp_json_decode($json);
					$_SESSION['_xgrid'][$this->xgridId]=erp_json_encode($this->json);
					$this->defaultProfile=TRUE; //used nelow for potential default filter
				}
			}else{
				$json = erp_json_encode($this->json);
				if($this->param('profileDefaultTable') && strlen($json)<=2){
					// apply default profile if exists
					$sql = "SELECT json FROM t_xgrid_user_profile WHERE `user`='".$this->getParam('userProfileId')."' AND `xgrid`='".$this->xgridId."'; "; //AND `isDefault`=1;
					//dbug($sql);
					$result=$this->query($sql);
					if($result){
						if(mysqli_num_rows($result)){
							$r=mysqli_fetch_row($result);
							$json=&$r[0];
							$this->json=erp_json_decode($json);
							$_SESSION['_xgrid'][$this->xgridId]=erp_json_encode($this->json);
						}
					}
				}
			}
		#<PROFILE
		
		#DEFAULT_FILTER>
			if(!empty($this->defaultFilter)){			
				foreach($this->defaultFilter AS $alias => $value){
					//user filter for this alias always override default
					if(!empty($alias) AND !isset($this->json->{$alias})){
						if(is_array($value)){
							foreach($value as $p => $v)
								$this->json->{$alias}->filter->$p=$v;
						}else{
							$this->json->{$alias}->filter=$value;
						}
						
					}				
				}
			}		
		#<DEFAULT_FILTER
		
		#JSON_PROC>
			if(count($this->glueGather)){
				$common=new xGridWhere();
				$glueGatherList=array();
				foreach($this->glueGather as $key => $glueGather){
					$this_xGridWhere=new xGridWhere();
					$this_xGridWhere->setGroupGlue($glueGather['glueType']);
					$glueGatherList[$key]=$this_xGridWhere;
				}
			}
			foreach($this->json as $key => $option){
				//alias level
				if(is_object($option) AND in_array($key, $this->keyList) ){
					//alias option level
					foreach($option as $type=>$val){
						switch($type){
							case 'combo':							
								$w=new xGridWhere();
								$w->alias=$key;
								$w->field=$this->aliasMap[$key];
								/*
								if(isset($this->comboGlueType[$key]) AND $this->comboGlueType[$key]){
									$w->setGlue($this->comboGlueType[$key]);
								}
*/
								if(count($val)>0){
									$this->filterInput[$key]=$val;
									//"egal" instead of "in"
									if(count($val)==1){
										$w->value=$val[0];
										$w->operator='=';
									}else{
										$w->value=$val;
										$w->operator='IN';
									}
									if(count($this->glueGather)){
										$glueGatherKey=$this->getGlueGather($key);
										if($glueGatherKey===false){
											$common->add($w);
										}else{
											$glueGatherList[$glueGatherKey]->add($w);
										}
									}else{
										$this->sqlWhere->add($w);
									}
								}else{
									unset($option->combo);
								}
							break;
							case 'filter':											
								if($val===''){
									unset($option->filter);
									break;
								}		
											
									$this->isFiltered[$key]=TRUE;
									$this->filterInput[$key]=$val;								
									if(isset($this->isDate[$key]) ){
										if(!empty($val->from)){
											//$this->where($this->aliasMap[$key], date2mktime($val->from)-1, '>');
											$w=new xGridWhere();
											$w->alias=$key;
											$w->field=$this->aliasMap[$key];
											$w->value=date2mktime($val->from)-1;
											$w->operator='>';
											if(count($this->glueGather)){
												$glueGatherKey=$this->getGlueGather($key);
												if($glueGatherKey===false){
													$common->add($w);
												}else{
													$glueGatherList[$glueGatherKey]->add($w);
												}
											}else{
												$this->sqlWhere->add($w);
											}
										}
										if(!empty($val->to)){
											//$this->where($this->aliasMap[$key], date2mktime($val->to)+86400, '<');
											$w=new xGridWhere();
											$w->alias=$key;
											$w->field=$this->aliasMap[$key];
											$w->value=date2mktime($val->to)+86400;
											$w->operator='<';
											if(count($this->glueGather)){
												$glueGatherKey=$this->getGlueGather($key);
												if($glueGatherKey===false){
													$common->add($w);
												}else{
													$glueGatherList[$glueGatherKey]->add($w);
												}
											}else{
												$this->sqlWhere->add($w);
											}
										}									
									}elseif(isset($this->isBool[$key]) ){
										
										//$this->where($this->aliasMap[$key],$val, '=');
										$w=new xGridWhere();
										$w->alias=$key;
										$w->field=$this->aliasMap[$key];
										$w->value=$val;
										$w->operator='=';
										if(count($this->glueGather)){
											$glueGatherKey=$this->getGlueGather($key);
											if($glueGatherKey===false){
												$common->add($w);
											}else{
												$glueGatherList[$glueGatherKey]->add($w);
											}
										}else{
											$this->sqlWhere->add($w);
										}
										
									}elseif(isset($this->isNumeric[$key]) ){
										$val=str_replace(',','.',$val);
										$val = preg_replace("/[^0-9.<!=>]/","",$val);
										$list = preg_split('/([<!=>]*[0-9.]+)/', $val, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
										
										foreach($list as $operatorVal){
											$split = preg_split('/([0-9.]+)/', $operatorVal, -1, PREG_SPLIT_DELIM_CAPTURE);
											$operator=$split[0];
											$val=$split[1];
											
											switch($operator){
												case '=':
												case '!=':
												case '<':
												case '>';
												case '>=';
												case '<=';
													//keep operator
												break;
												case '=>';
													$operator='>=';
												break;
												case '=<';
													$operator='<=';
												break;
												default:
													//unknow | invalid | empty operator, compare as a string
													$operator='%';
											}
											$w=new xGridWhere();
											$w->alias=$key;
											$w->field=$this->aliasMap[$key];
											$w->value=$val;
											$w->operator=$operator;
											if(count($this->glueGather)){
												$glueGatherKey=$this->getGlueGather($key);
												if($glueGatherKey===false){
													$common->add($w);
												}else{
													$glueGatherList[$glueGatherKey]->add($w);
												}
											}else{
												$this->sqlWhere->add($w);
											}
										}
	
										
									}else{											
										//$this->whereLike($this->aliasMap[$key],$val);
										if(substr($val,0,1)=='~'){
											$operator="~";
											$val=substr($val,1);
										}elseif(strpos($val,'%')!==FALSE || strpos($val,'_')!==FALSE){
											$operator="JOKER";
										}else{
											$operator="%";
										}
										
										$w=new xGridWhere();
										$w->alias=$key;
										$w->field=$this->aliasMap[$key];
										$w->value=addslashes(stripslashes($val));
										$w->operator=$operator;
										if(count($this->glueGather)){
											$glueGatherKey=$this->getGlueGather($key);
											if($glueGatherKey===false){
												$common->add($w);
											}else{
												$glueGatherList[$glueGatherKey]->add($w);
												
											}
										}else{
											$this->sqlWhere->add($w);
										}
									}
									
								
									
								
								
							break;
							case 'sort':
								$this->isSorted=TRUE;
								$this->orderBy($key, ($val==-1? 'DESC':'ASC'));
							break;
							case 'switch_show':
								unset($this->isHidden[$key]);
							break;
							case 'switch_hide':
								$this->isHidden[$key]=TRUE;
							break;
						}
					}
				}
			}
			//temp section
			if(count($this->glueGather)){
				if($common->isGroup()){
					$this->sqlWhere->add($common);
				}
				foreach($glueGatherList as $this_xGridWhere){
					if($this_xGridWhere->isGroup()){
						$this->sqlWhere->add($this_xGridWhere);
					}
				}
			}
			
			//$this->renderTo=='csv' $this->renderTo=='xls'
			$do=(isset($this->json->_temp->do) ?  $this->json->_temp->do:'');
			switch($do){
				case 'print_html':
					$this->renderTo='html';
				break;
				case 'print_pdf':
					$this->renderTo='pdf';
				break;
				case 'export_csv':
					$this->renderTo='csv';
				break;
				case 'export_xls':
					$this->renderTo='xls';
				break;
			}
			//export asked, column set not empty
			if(($this->renderTo=='xls' OR $this->renderTo=='csv') AND !empty($this->json->_temp->alias)){
				//only csv supported for now
				$this->renderToFile();
				$this->build['csv']=TRUE;
				$this->build['wrap']=FALSE;
				$this->build['hidden']=FALSE;
				$this->build['header']=FALSE;
				$this->build['footer']=FALSE;
				$this->userVisibleCol=$this->json->_temp->alias;
				//export whole result set
				if(isset($this->json->_temp->allrows)){
					$this->pageMode=FALSE;
				}			
			}
			
			//print asked, column set not empty
			if($this->getParam('pdfMode') OR ( ($this->renderTo=='html' OR $this->renderTo=='pdf') AND !empty($this->json->_temp->alias)) ){
				
				$this->renderToFile();
				$this->build['print']=TRUE;
				$this->enableResultArray(); // PDF do not use html output but an array data source
				$this->dbug=FALSE; // Avoid sending HTML header
				$this->build['wrap']=FALSE;
				$this->build['hidden']=FALSE;
				$this->build['tabs']=FALSE;
				$this->build['footer']=FALSE;
				if($this->getParam('pdfMode')){
					//$this->pageMode=FALSE;
				}else{
					$this->userVisibleCol=$this->json->_temp->alias;
					$this->param['aliasWidth']=$this->json->_temp->aliasWidth;
					//export whole result set
					if(isset($this->json->_temp->allrows)){
						$this->pageMode=FALSE;
					}
				}
						
			}
			
			//get pdf as a string
			//if($this->getParam(''))
			
			unset($this->json->_temp);
			$_SESSION['_xgrid'][$this->xgridId]=erp_json_encode($this->json);
		#<JSON_PROC
		
		//get number of rows per page, must be set after potential profile load
		$this->resultPerPage=$this->jsonGetPageRows();		
	}
	
	private function jsonGetCurrentPage(){
		
		if(isset($this->json->_page)) $this->pageCurrent=(int)$this->json->_page;
		if($this->pageCurrent<1) $this->pageCurrent=1;
		return $this->pageCurrent;
	}
	private function jsonGetPageRows(){
		if(isset($this->json->_page_rows)) return (int)$this->json->_page_rows;
		return $this->resultPerPage;
	}
	private function jsonSetCurrentPage($page){
		$this->json->_page=$page;
	}
	private function jsonSetPageCount($count){
		$this->json->_page_count=$count;
	}
	private function jsonSetPageRows($rows){
		$this->json->_page_rows=$rows;
	}
	

	/*
	 * @desc
	 */
	private function concatHeaderTitleMini(&$html){
		#TITLE>
				$html.='<tr class="trH0">';
				$html.='<td colspan="'.count($this->visibleCol).'">';
				

				if(!empty($this->title)){
					//title
					$html.='<span class="title">'.ucfirst($this->title).'</span>';
				}
	
				$html.='</td>';
				$html.='</tr>';
			#<TITLE
	}
	
	
	/*
	 * @desc
	 */
	private function concatHeaderTitle(&$html){
		#TITLE>
				$html.='<tr class="trH0">';
				$html.='<td colspan="'.count($this->visibleCol).'">';
				
				$html.='<table style="width:100%;margin:0px;padding:0px;">';
				$html.='<tr>';
				$html.='<td>';
				if(!empty($this->title)){
					//title
					$html.='<span class="title">'.ucfirst($this->title).'</span>&nbsp; -&nbsp;&nbsp;';
				}
				
				//$html.='</td>';
				//$html.='<td align="left">';
				if($this->enabled('userProfile') AND ! $this->disableProfile){
					#PROFILE>
						
						
						$html.=$this->interface['word_profiles'].':';
						$html.=$this->interface['button_add_profile'].$this->interface['button_edit_profile'];
						//profile
						$html.='<select class="xgrid_select_profile"><option value=""></option><option value="0" alt="0">Par défaut</option>';
						if($this->param('profileDefaultTable')){
							$sql="SELECT num, name, isDefault FROM t_xgrid_user_profile WHERE `user`='".$this->getParam('userProfileId')."' AND `xgrid`='".$this->xgridId."'";
						}else{
							$sql="SELECT num, name, null FROM t_xgrid_user_profile WHERE `user`='".$this->getParam('userProfileId')."' AND `xgrid`='".$this->xgridId."'";
						}
						$result=$this->query($sql);
						if(mysqli_num_rows($result)){
							while($r=mysqli_fetch_row($result)){
								$html.='<option value="'.$r[0].'" alt="'.$r[2].'">'.$r[1].'</option>';
							}
						}
						$html.='</select>';
					#<PROFILE
				}
				//$html.='</td><td align="right" style="width:80px;">';
				$html.=' ';
				//refresh
				$html.=$this->interface['button_filter_apply'];
				$html.='</td>';
				$html.='<td align="right">';
				//result summary
				$html.=' '.$this->buildRangeSentence().'';
				$html.='</td>';
				$html.='</tr>';
				if(!empty($this->filterTitle)){
				$html.='<tr><td colspan="2">';
				//filter summary
				$html.='Filtres actifs: '.implode(' ',$this->filterTitle);
				$html.='</td></tr>';
				}
				$html.='</table>';		
					
				
				$html.='</td>';
				$html.='</tr>';
			#<TITLE
	}
	/*
	 * @desc
	 */
	private function concatHeaderFilter(&$html){
		if(!empty($this->enableFilter) OR $this->allowAllFilter){
			$html.='<tr class="trH2">';
			foreach($this->visibleCol as $key){
				$jqClass='xgrid_alias';
				if( ( isset($this->enableFilter[$key]) OR $this->allowAllFilter ) AND !isset($this->disableFilter[$key]) ){
					if( isset($this->isBool[$key]) ){
						$jqClass.=' xgrid_filter_bool_radio';
					}elseif(isset($this->isDate[$key]) ){
						$jqClass.=' xgrid_filter_date';
					}else{
						$jqClass.=' xgrid_filter_fulltext';
					}
				}
				//filter buttons are client side generated
				$html.='<td class="'.$jqClass.'" rel="'.$key.'"';
				if(isset($this->isNumeric[$key])){
					$html.=' style="text-align:right;"';
				}elseif(isset($this->isBool[$key])){
					$html.=' style="text-align:center;"';
				}
				$html.='>';	
				$html.='</td>';					
			}
			$html.='</tr>';
		}
	}
	/*
	 * @desc
	 */
	private function concatHeaderSort(&$html){
		$html.='<tr class="trH1">';
		foreach($this->visibleCol as $key){
			$html.='<td class="xgrid_alias" rel="'.$key.'"';
			if(isset($this->isNumeric[$key])){
				$html.=' style="text-align:right;"';
			}
			$html.='>';
			
			if(isset($this->th[$key])){
				$th=(isset($this->thShort[$key]) ? $this->thShort[$key]:$this->th[$key]);
				$th=ucfirst($th);
				if( ( isset($this->enableSort[$key]) OR $this->allowAllSort ) AND !isset($this->disableSort[$key]) ){
					$html.='<span class="xgrid_sort" alt="'.( (isset($this->firstSort[$key]) AND $this->firstSort[$key]=='DESC')  ? 1 : -1).'">'.$th;

					//if(!isset($this->isFiltered[$key]) OR !$this->isFiltered[$key]) $ubtn['style']='display:none;';
					$ascDisplay=' style="display:none;"';
					$descDisplay=' style="display:none;"';
					if(isset($this->json->$key->sort)){
						if($this->json->$key->sort==1) $ascDisplay='';
						else $descDisplay='';
					}
					$html.='<span'.$ascDisplay.'>'.$this->interface['icon_sort_asc'].'</span>';
					$html.='<span'.$descDisplay.'>'.$this->interface['icon_sort_desc'].'</span>';

					//$html.=mypic('SORTASCNE','',' class="xgrid_sort_asc" align="absmiddle"'.$ascDisplay);
					//$html.=mypic('SORTDESCNE','',' class="xgrid_sort_desc" align="absmiddle"'.$descDisplay);
					
					$html.='</span>';
				}else{
					$html.=$th;
				}
				
				
				
			}
			//				$html.='<br/><span class="xgrid_switch_off" style="font-size:6px;">x</span>';			
			$html.='</td>';
		}
		$html.='</tr>';
	}
	
	
	
	/*
	 * @desc
	 */
	private function concatHeader(&$html){
		$html.='<thead>';
		if($this->build['title']){
			$this->concatHeaderTitle($html);
		}
		$this->concatHeaderFilter($html);
		$this->concatHeaderSort($html);		
		$html.='</thead>';
	}
	
	
	
	
	
	
	
	
	
	
	
	/*
	 * @desc blank anything within bracket
	 * @todo take into account where x=y when y contains '(' or ')'
	 */
	private function getSqlBlankedBracket($sql){
		$deep=0; //subquery deep, 0=root

		$blankedSql='';
		$len=strlen($sql);
		//parse each char	
		for($i=0;$i<$len;$i++){
			$c=substr($sql,$i,1);
			switch($c){
				//increase subquery deep level
				case '(':
					$deep++;
					$blankedSql.=$c;
				break;
				//decrease subquery deep level
				case ')':
					$deep--;
					$blankedSql.=$c;
				break;
				default:
					if($deep==0){
						$blankedSql.=$c;
					}else{
						$blankedSql.=' ';
					}
			}
			
		}
		return $blankedSql;
	}
	
	/*
	 * @desc default interface
	 * @param array $override allow to override default interface
	 * @comment
	 * allow (most) of xGrid.class.interface to be ignored
	 */
	public function setDefaultInterface($override=array()){
		//xGrid hard coded default
		if(!isset($this->interface['classDefaultLoaded'])){
			$this->interface['classDefaultLoaded']=TRUE;
			$this->interface=array();
			$this->interface['button_print']='<input type="button" value="OK"/>';
			$this->interface['button_export_csv']='<input type="button" value=".CSV"/>';
			$this->interface['button_export_xls']='<input type="button" value=".XLS"/>';
			$this->interface['button_print_html']='<input type="button" value=".PDF"/>';
			$this->interface['button_print_pdf']='<input type="button" value="Screen"/>';
		}
		//project hard coded default
		if(!isset($this->interface['projectDefaultLoaded'])){
			$interfaceFile=dirname(__FILE__).'/xGrid.class.interface.php';
			if(is_file($interfaceFile)){
				$this->interface['projectDefaultLoaded']=TRUE;
				require $interfaceFile; //get $interface
				foreach($interface AS $option=>$value) $this->interface[$option]=$value;			
			}
		}
		//runtime default
		if(!empty($ovverride)){
			$this->interface['runtimeDefaultLoaded']=TRUE;
			foreach($override AS $option=>$value) $this->interface[$option]=$value;
		}		
	}
	
	
	/*
	 * @desc build html corpse section (result rows)
	 * @var reference $html
	 * @comment this is the second most important xgrid method, as it parse the database query and apply html formatting.
	 * 
	 * html formatting build time modifier process is summarized below:
	 * 
<tbody>
	align cell based on cell type
	alternateRowBgd XOR caseBackground
	add 'tr' class to each row
	lineNumber=0
	<tr loop>
		lineNumber++
		rowAttrSwap
		<tr splitter>
			<valModifier>
				...
			</valModifier>
		</tr splitter>
		<total>
			isCumulative
			showTotal
		</total>
		<callModifier>
			callFunc
			callFuncArray
		</callModifier>
		<tr open>
			rowJSON
			dblclick
			alternateRowBgd +> $cumulativeRowAttr
			caseBackground +> $cumulativeRowAttr
			$cumulativeRowAttr
		</tr open>
		<td loop>
			<td open>
				cellAttr
			</td open>
			<valModifier>
				paramReplace
				caseReplace
				<applyReplace>
					...
				</applyReplace>
				replace
				<applyReplace>
					<replace loop>
						formatBeforeReplace THEN typeFormat
						replace
					</replace loop>
				</applyReplace>
				swap
				noFormat XOR typeFormat
				<typeFormat>
					isDate
					isDec
					isCurrency
					isFloat
				</typeFormat>
				allowEdit (experimental)
				isEditableCombo (experimental)
				hideOverflow
			</valModifier>
			<td close />
		</td loop>
	<tr close />
	<tr foot>
		showTotal
		colFooter
	</tr foot>
</tbody>
	 */
	public function concatCorpse(&$html){
		$html.='<tbody>';
		// display generated query error in verbose mode
		if(!$this->result OR !$this->numRows) return '';
		
		//tdClass, constant collumn class
		/*
		$tdClass=array();
		foreach($this->visibleCol as $key){
			if(!empty($this->tdClass[$key]))
				$tdClass[$key]=implode(' ',$this->tdClass[$key]);		
		}

		//tdStyle
		$tdStyle=array();
		foreach($this->visibleCol as $key){
			$tdStyle[$key]='';
			if(isset($this->isBool[$key])){
				$tdStyle[$key]='text-align:center;';
			}elseif(isset($this->isNumeric[$key])){
				$tdStyle[$key]='text-align:right;';
			}
		}
		*/
		/*

		 */
		
		// Optimization avoiding looping aliases on empty option set
		#OPTIMIZATION>
			$hasCallFunc=(!empty($this->callFunc) OR !empty($this->callFuncArray));
			$hasTotal=(!empty($this->showTotal) OR !empty($this->isCumulative));
		#<OPTIMIZATION
		
		#ATTR_INIT>
		//automatic type css style
		foreach($this->visibleCol as $key){
			if(isset($this->isBool[$key])){
				$this->cellAttr($key, 'style', 'text-align:center;');
			}elseif(isset($this->isNumeric[$key])){
				$this->cellAttr($key, 'style', 'text-align:right;');
			}
		}
		#<ATTR_INIT
		
		//alternate row background and case background are exclusive
		if(!empty($this->caseBackground)){
			$this->alternateRowBgd=FALSE;
		}
		//force default row class
		$this->rowAttr['class'][]='tr';
		
		$lineNumber=0;
		while($r=mysqli_fetch_assoc($this->result)){
			$lineNumber++;			
			#ROWATTRSWAP>
				if(!empty($this->rowAttrSwap)){
					foreach($this->rowAttrSwap AS $attrName =>$key){
						unset($this->rowAttr[$attrName]);
						$this->rowAttr[$attrName][]=$r[$key];
					}
				}
			#<ROWATTRSWAP
			#SPLITTER>
				if(!empty($this->splitter) AND $this->renderTo=='screen'){
					if($this->splitterValueTracker!=$r[$this->splitter]){
						$rSplitter=$r;
						$this->splitterValueTracker=$rSplitter[$this->splitter];
						$html.='<tr class="tr splitter">';
						$html.='<td colspan="'.(count($this->visibleCol)).'">';					
						$html.=$this->valModifier($this->splitter, $rSplitter);
						$html.='</td>';
						$html.='</tr>';
					}
				}
			#<SPLITTER
			if($hasTotal){
				#TOTAL>
				foreach($this->keyList as $key){
					
					if(isset($this->isCumulative[$key])){
						#cumul
						$this->cumul[$key]=$this->cumul[$key]+$r[$key];
						$r[$key]=$this->cumul[$key];
					}
					if(isset($this->showTotal[$key])){
						#total
						$this->total[$key]+=$r[$key];
					}
				}
				#<TOTAL
			}
			if($hasCallFunc){
				#CALLFUNC>
					foreach($this->keyList as $key){
						$this->valCallModifier($key, $r);
					}
				#<CALLFUNC
			}
			$cellArray=array();
			#TR_OPEN>
				$html.='<tr';
					$cumulativeRowAttr=$this->rowAttr;
					//rowJSON
					if(!empty($this->rowJSON)){
						$rowJSON=array();
						foreach($this->rowJSON as $index=>$alias){
							$rowJSON[$index]=$r[$alias];
						}
						$html.=' json=\''.json_encode($rowJSON).'\'';
					}
					//dblclick
					if(!empty($this->dblclick)){
						$key=$this->dblclick;
						$js='';
						if(isset($this->replace[$key])){
							$js=$this->applyReplace($this->replace[$key], $r);
						}
						$html.=' ondblclick="'.$js.'"';
					}
				
				
	
				if($this->alternateRowBgd){
					if($this->fadedRowBgd){
						$cumulativeRowAttr['class'][]='faded faded_alt_'.($lineNumber%2==0 ? 1:0);
					}else{
						$cumulativeRowAttr['class'][]='plain plain_alt_'.($lineNumber%2==0 ? 1:0);
						//$html.=' class="altbgd altbgd'.($lineNumber%2==0 ? 1:0).'"';
					}
				}else{
					if(!empty($this->caseBackground)){
						foreach($this->caseBackground as &$case){
							if(empty($case['key']) OR $r[$case['key']]==$case['value']){
								if($this->fadedRowBgd){
									$cumulativeRowAttr['class'][]='faded faded_c_'.$case['color'];
								}else{
									$cumulativeRowAttr['style'][]='background:'.$case['color'].';';
								}
								
								//$html.=' style="background:'.$case['color'].';"';
								break;
							}
						}
					}
				}
				/*
				if(!empty($this->caseBackground)){
					foreach($this->caseBackground as &$case){
						if($r[$case['key']]==$case['value']){
							$cumulativeRowAttr['style'][]="background:'.$case['color'].';";
							//$html.=' style="background:'.$case['color'].';"';
							break;
						}
					}
				}else{
					if(!empty($this->bgcolor1)){
						//style="background:'.($lineNumber%2==0 ? $this->bgcolor1:$this->bgcolor0).';" 
						$html.=' class="altbgd altbgd'.($lineNumber%2==0 ? 1:0).'"';
					}
				}
*/
				if(!empty($this->editableRowId)){
					$cumulativeRowAttr['xGridEditId'][]=$r[$this->editableRowId];
				}

				if(!empty($cumulativeRowAttr)){
					foreach($cumulativeRowAttr AS $attr => $valueArray){
						switch($attr){
							case 'class':
								$html.=' class="'.implode(' ',$valueArray).'"';
							break;
							case 'style':
								$html.=' style="'.implode('',$valueArray).'"';
							break;
							default:
								$html.=' '.$attr.'="'.implode('',$valueArray).'"';
						}
					}
				}
				$html.='>';
			#<TR_OPEN
			
			

			/*
			$rCell=$r;
			foreach($this->visibleCol as $key){
				$rCell[$key]=$this->valModifier($key, $r);
				//if($hasCallFunc) $this->valCallModifier($key, $rCell);
			}
			*/
			foreach($this->visibleCol as $key){
				#TD_OPEN>
					$html.='<td';
						if(!empty($this->cellAttr[$key])){
							foreach($this->cellAttr[$key] AS $attr => $valueArray){
								switch($attr){
									case 'class':
										$html.=' class="'.implode(' ',$valueArray).'"';
									break;
									case 'style':
										$html.=' style="'.implode('',$valueArray).'"';
									break;
									default:
										$html.=' '.$attr.'="'.implode('',$valueArray).'"';
								}
							}
						}
					if(isset($this->isEditable[$key]) OR isset($this->isButton[$key])){
						$html.=' xGridAlias="'.$key.'"';
					}
					$html.='>';
				#<TD_OPEN
				
				
				if(isset($this->isEditable[$key]) AND $this->renderToScreen){
					#EDITABLE>
						$cellHtml="";
						if(isset($this->cellEditParam[$key])){
							$cellEditParam=array();
							foreach($this->cellEditParam[$key] as $keyAsParam){
								$cellEditParam[$keyAsParam]=$r[$keyAsParam];
							}
							$cellHtml.='<textarea '.($this->dbug ? '':' style="display:none;"').' class="cellEditParam">'.json_encode($cellEditParam).'</textarea>';
						}
						if(isset($this->isBool[$key])){
							$cellHtml.='<input type="checkbox" onchange="xGridEdit(this);" '.($r[$key] ? 'checked="checked"':'').'/>';
						}elseif(isset($this->isNumeric[$key])){
							$cellHtml.='<input type="numeric" onclick="$(this).select();" onchange="xGridEdit(this);" value="'.$r[$key].'" style="width:50px;text-align:right;"/>';
						}else{
							$cellHtml.='<input type="text" onclick="xGridEOF(this)" value="'.htmlspecialchars($r[$key]).'" style="width:100%;"/>';
						}
					#<EDITABLE
				}else{
					$cellHtml=$this->valModifier($key, $r);
				}
				if(isset($this->isButton[$key])){
					if(isset($this->isDelete[$key])){
						$cellHtml='<div onclick="xGridEditDelete(this);">'.$cellHtml.'</div>';
					}else{
						$cellHtml='<div onclick="xGridEditBtn(this);">'.$cellHtml.'</div>';
					}					
				}
				if($this->enabled('resultArray')) $cellArray[$key]=$cellHtml;
				$html.=$cellHtml;
				#TD_CLOSE
				$html.='</td>';
				if($this->renderTo=='csv'){
					$html.=';';
				}
				if($this->renderTo=='xls'){				
					//$this->excelExportWS->write($this->excelExportCR, $this->excelExportCC, utf8_decode(strip_tags($cellHtml)));
					if(isset($this->isNumeric[$key])){
						$this->excelExportWS->write($this->excelExportCR, $this->excelExportCC, utf8_decode(strip_tags($cellHtml)), $this->excelExportFormatNum);
					}else{
						$this->excelExportWS->write($this->excelExportCR, $this->excelExportCC, utf8_decode(strip_tags($cellHtml)));
					}
					$this->excelExportCC++;
				}
			}
			#TR_CLOSE
			$html.='</tr>';
			if($this->enabled('resultArray'))	$this->resultArray[]=$cellArray;
			if($this->renderTo=='csv'){
				$html.="\n";
			}
			if($this->renderTo=='xls'){
				$this->excelExportCR++;
				$this->excelExportCC=0;
			}			
		}

		if( ( $hasTotal OR !empty($this->colFooter)) AND $this->renderTo=='screen'){
			
		
			$html.='<tr style="white-space:nowrap;border-bottom:1px solid #777;border-left:1px solid #777;border-right:1px solid #777;font-weight:bold;">';
				foreach($this->visibleCol as $key){
					//$html.='<td'.(!empty($tdStyle[$key]) ? ' style="'.(isset($this->showTotal[$key])?'border-top:2px solid black;':'').$tdStyle[$key].'"':'').'>';
					$html.='<td style="'.(isset($this->showTotal[$key])?'border-top:2px solid black;text-align:right;':'').'">';
					if(isset($this->showTotal[$key])){
						$val=$this->total[$key];
						if(isset($this->isDec[$key])) $val=round($val, $this->decimals[$key]);
						if(isset($this->isCurrency[$key])) $val=number_format($val, $this->decimals[$key],'.',' ');
						$html.='&sum;&nbsp;=&nbsp;'.$val;
						
					}
					if(!empty($this->colFooter[$key])){
						$html.=$this->colFooter[$key];						
					}
					$html.='</td>';
				}
			$html.='</tr>';
		}
		
		if($this->allowEditableNew){
			$html.='<tr style="border-top:2px solid black; background:#ccc">';
			$html.='<td colspan="'.count($this->visibleCol).'">Nouvel enregistrement : ';
			$html.='</td>';
			$html.='</tr>';
			$html.='<tr style="background:#ccc">';
				foreach($this->visibleCol as $key){
					$html.='<td';
						if(!empty($this->cellAttr[$key])){
							foreach($this->cellAttr[$key] AS $attr => $valueArray){
								switch($attr){
									case 'class':
										$html.=' class="'.implode(' ',$valueArray).'"';
									break;
									case 'style':
										$html.=' style="'.implode('',$valueArray).'"';
									break;
									default:
										$html.=' '.$attr.'="'.implode('',$valueArray).'"';
								}
							}
						}
						
					if(isset($this->isEditable[$key])){
						$html.=' xGridAlias="'.$key.'"';
					}
					$html.='>';
					 
						if(isset($this->isEditable[$key])){
							if(isset($this->isBool[$key])){
								$html.='<input type="checkbox" onchange="xGridEdit(this);" '.(0 ? 'checked="checked"':'').'/>';
							}elseif(isset($this->isNumeric[$key])){
								$html.='<input type="numeric" onclick="$(this).select();" onchange="xGridEdit(this);" value="'.''.'" style="width:50px;text-align:right;"/>';
							}else{
								$html.='<input type="text" onclick="xGridEOF(this)" value="'.''.'" style="width:100%;"/>';
							}
						}
					$html.='</td>';
				}
			$html.='</tr>';
			$html.='<tr style="text-align:right; background:#ccc">';
			$html.='<td colspan="'.count($this->visibleCol).'">'.$this->interface['button_editable_new'];
			$html.='</td>';
			$html.='</tr>';
		}
		
		
		
		$html.='</tbody>';
		
		
		
		if(!empty($this->aliasError)){
			$aliasErrorList='';
			foreach($this->aliasError as $alias=>$void) $aliasErrorList.=$alias.' |';
			$this->appendVerbose('Warning, unknow alias : '.$aliasErrorList);
		}
	}
	
	
	/*
	 * @desc
	 */
	private function valCallModifier($key, &$r){
		if(isset($this->callFunc[$key])){
			//php function name to call
			$funcName=$this->callFunc[$key]['func'];
			$funcParam=array();
			
			//hard coded values
			if(isset($this->callFunc[$key]['paramValue'])){
				foreach($this->callFunc[$key]['paramValue'] as $paramKey =>$paramValue){
					$funcParam[$paramKey]=$paramValue;
				}
			}
			
			//values from other aliases values
			if(isset($this->callFunc[$key]['paramAlias'])){
				foreach($this->callFunc[$key]['paramAlias'] as $paramKey =>$paramAlias){
					$funcParam[$paramKey]=$r[$paramAlias];
				}
			}

			//return call_user_func($funcName, $funcParam);
			//put function result in val
			if(strpos($funcName,'::')>0) $funcName=explode('::',$funcName); //<php 5.3 compatibility
			$r[$key]=call_user_func($funcName, $funcParam);
		}
		//simple func call
		if(isset($this->callFuncArray[$key])){
			$aliasArray=$this->callFuncArray[$key]['aliasArray'];
			// alias name -> alias value
			foreach($aliasArray as &$alias){
				$alias=$r[$alias];
			}	
			//put function result in val
			$funcName=$this->callFuncArray[$key]['func'];
			if(strpos($funcName,'::')>0) $funcName=explode('::',$funcName); //<php 5.3 compatibility
			//return call_user_func_array($func, $aliasArray);
			$r[$key]=call_user_func_array($funcName, $aliasArray);
		}
		
	
	}
		
	
	
	/*
	 * @desc cell value modifier
	 * @param string $key alias
	 * @param string &$r
	 */
	private function valModifier($alias, &$r){
		$val=$r[$alias];
		$this->scheduledValModifier($alias, $val, $r);
		
		if(isset($this->paramReplace[$alias])){
			if(isset($this->paramTable[$this->paramReplace[$alias]][$val]))
				$val=$this->paramTable[ $this->paramReplace[$alias] ][$val];
		}elseif(isset($this->caseReplace[$alias])){ //@see this::caseReplace
			$caseReplace=$this->caseReplace[$alias];
			foreach($caseReplace->alias as $i => $caseKey){
				//default condition reached, apply replace and break;
				if(empty($caseKey)){
					$val=$this->applyReplace($caseReplace->xGridReplace[$i], $r);
					break;
				}
				$caseValue=$caseReplace->value[$i];
				$caseMatch=FALSE;
				//condition logical operator
				switch($caseReplace->operator[$i]){
					case '=':
						$caseMatch=($r[$caseKey]==$caseValue);
					break;					
					case '!=':
						$caseMatch=($r[$caseKey]!=$caseValue);
					break;
				}
				//case condition is true, apply replace and break
				if($caseMatch){							
					$val=$this->applyReplace($caseReplace->xGridReplace[$i], $r);
					break;
				}				
			}
			
		}elseif(isset($this->replace[$alias])){ //@see this::replace()
			$val=$this->applyReplace($this->replace[$alias], $r);
		}		
		
		if(isset($this->swap[$alias])){ //@see this::swap()
			if(isset($this->swap[$alias][$val])) $val=$this->swap[$alias][$val];
		}
		
		if(!isset($this->noFormat[$alias])) $this->typeFormat($alias, $val);
		
		
		
		if(isset($this->allowEdit[$alias])){
			$val='<input value="'.$val.'" class="'.$this->allowEdit[$alias].'"/>';
		}
	
		if(isset($this->c->$alias) AND isset($this->c->$alias->isEditableCombo)){
			$val.='<span class="isEditableCombo" combo="'.$alias.'" value="'.$r[$this->c->$alias->isEditableComboAliasAsValue].'">V</span>';
		}
		
		
		
		
		if(isset($this->hideOverflow[$alias])){
			$val='<div class="width" style="height:16px;">'.$val.'</div>';
		}
		
		
		return $val;
		//$r[$alias]=$val;
	}
	
	/*
	 * @desc scheduled val modifier
	 */
	private function scheduledValModifier($key, &$val, &$r){	
		if(!empty($this->val[$key])){
			foreach($this->val[$key] as $task){
				
				// does modifier have a condition?
				if(isset($task->ifKey) AND !$r[$task->ifKey]){					
					$execute=FALSE;
				}else{
					$execute=TRUE;
				}
				// execute modifier
				if($execute){
					switch($task->do){
						case 'append':
							$val.=$task->string;
						break;
						case 'prepend':
							$val=$task->string.$val;
						break;
						case 'nl2br':
							$val=nl2br($val);
						break;
					}
				}
			}
		}
	}
	
	
	/*
	 * @desc
	 */
	private function typeFormat($alias, &$val){
		if(isset($this->isDate[$alias])){		
			if($val) $val=date($this->dateFormat[$alias],$val);
			else $val = '';
		}				
		if(isset($this->isDec[$alias])) $val=round($val, $this->decimals[$alias]);		
		if(isset($this->isCurrency[$alias])) $val=number_format($val, $this->decimals[$alias],'.',' ');
		if(isset($this->isFloat[$alias])) $val=(float)$val;
	}
	
	private function getmicrotime() {
	    list($usec, $sec) = explode(" ",microtime());
	    return ((float)$usec + (float)$sec);
	}
	
	
	private function benchmark(){
		if(isset($this->benchmarkStep)){
			return ($this->benchmarkStep) ? round(($this->getmicrotime() - $this->benchmarkStep)*1000) : 0;
		}
		$this->benchmarkStep=$this->getmicrotime();
		return 0;
	}
	
	/*
	 * @desc set custom html to be used client side
	 * @see xGrid.class.interface.php
	 * @version 1.1 using JSON instead of plain HTML (IE compatibility, cleaner processing)
	 */
	private function buildTemplate(){
		$html='';
		// [html class]=>index of interface element
		$tmpIndex=array('xgrid_filter_on'=>'button_filter_on', 'xgrid_filter_off'=>'button_filter_off'
		, 'xgrid_filter_choose'=>'button_filter_choose', 'xgrid_filter_yes'=>'button_filter_yes'
		, 'xgrid_filter_no'=>'button_filter_no', 'xgrid_filter_apply'=>'button_filter_apply'
		, 'xgrid_filter_choose'=>'button_filter_choose', 'xgrid_filter_yes'=>'button_filter_yes');
		if($this->enabled('datePicker'))	$tmpIndex['xgrid_filter_date_select']='icon_calendar';
		//get html in place of idx
		foreach ($tmpIndex as $class=>&$idx)	$idx=$this->interface[$idx];
		//json encode hidden container
		$html.='<textarea class="xgrid_HTML_JSON" style="display:none;">'.json_encode($tmpIndex).'</textarea>';
		return $html;
	}
	
	
	/*
	 * @desc internal database query override
	 */
	private function query($sql){
		if($this->getParam('queryFunction')){
			return call_user_func($this->getParam('queryFunction'), $sql);
		}else{
			return mysqli_query($GLOBALS["___mysqli_ston"], $sql);
		}
	}
	
	/*
	 * @desc internal database transactionned queries override
	 */
	private function tquery($queries){
		$this->query('SET AUTOCOMMIT=0');
		$this->query('BEGIN');
		$sql='';
		$transaction_ok=TRUE;
		
		foreach($queries as $query){
			$query=trim($query);
	
			$sql.="<br/>".$query.( substr($query,strlen($query)-1,1)==';' ? '':';');
			if(!$this->query($query)){
				$transaction_ok=FALSE;
				$this->query('ROLLBACK');
				$this->appendVerbose('Erreur de transaction:<br/><pre>'.print_r($queries).'</pre>');
				break;
			}
			
		}
		if($transaction_ok)	$this->query('COMMIT');
		$this->query('SET AUTOCOMMIT=1');
	}
	
	/*
	 * @desc internal database simple query
	 */
	private function squery($sql,$debug=FALSE,$link=false){
		$result=$this->query($sql);
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
	
	/*
	 * @desc construct sql to be run, using base sql then applying both build time modifiers and json modifier.
	 * @param bool $linkOCS DEPRECATED???
	 * @comment this is the first most important xgrid method, as it prepare the database query
	 * 
	 * sql section modificators (in this order) are:
	 * -superswap will update SELECT section (whole alias replacement) and concat FROM section (inner join memory table for alias replacement)
	 * -empty WHERE section will at least worth 1=1 restriction for concat purpose fix
	 * -restriction engine, json filled, will concat WHERE section.
	 * -default sort will set ORDER BY section
	 * -json sort will suppress default sort and concat ORDER BY section
	 * -page numbering will force LIMIT section to the current page result wondow
	 * 
	 * var defined within:
	 * -$this->sqlCount is sql query used to count total number of result rows (if count mode)
	 * -$this->sqlNumRows is total number of result rows (if count mode)
	 * -$this->pageCount is total number of distinct number result pages
	 * -$this->pageCurrent is only updated if client side asked page is either too high (>page count) or too low (<1)
	 * -$this->visibleCol will be set using default visibility json visibility
	 * -$this->visibleCol will be sorted taking into account custom alias order then SELECT section alias order
	 * 
	 * data sent to json are:
	 * -jsonSetPageCount() so that client side knows total number of result pages
	 * -jsonSetCurrentPage() so that client side knows current page num
	 */
	private function buildSqlInit($linkOCS = false){
		
		//split query into section types (select, from ...)
		$sqlChunk=$this->getSqlChunk($this->sql);
		
		#SUPERSWAP>
			$this->initSuperSwap($sqlChunk);		
		#<SUPERSWAP
		
		//where concat fix
		if(empty($sqlChunk['where'])) $sqlChunk['where']='WHERE 1=1';
		//if(!empty($this->where)){			$sqlChunk['where'].=' AND '.implode(' AND ',$this->where);		}
		
		//use restriction "where" engine
		$this->sqlChunkWhere='';
		if( $this->sqlWhere->isGroup() ){		
			$this->sqlChunkWhere=$this->sqlWhere->build();	
			$sqlChunk['where'].=$this->sqlChunkWhere;			
		}
		//apply default sort if not json overloaded
		if(!empty($this->defaultSort) AND empty($this->orderBy)){
			$this->orderBy=$this->defaultSort;
		}
		//apply sort
		if(!empty($this->orderBy)){
			$orderBy=array();
			foreach($this->orderBy as $key=>$way){
				$orderBy[]=$key.' '.$way;
				@$this->json->$key->sort=($way=='ASC' ? 1:-1);
			}
			$sqlChunk['orderby']="ORDER BY ".implode(',',$orderBy);
		}
		//count mode
		if($this->countMode){
			//copy splitted query but limit, then transform it into a count query
			$sqlChunkCount=$sqlChunk;	

			$sqlChunkCount['select']='SELECT COUNT(*)';
			$sqlChunkCount['orderby']='';
			$sqlChunkCount['limit']='';
			
			
			$groupby=trim($sqlChunkCount['groupby']);
			if(!empty($groupby)){
				//replace alias by field, as select section doesnt contain anymore alias definition
				$groupby=str_ireplace('GROUP BY ','',$groupby);
				$groupbyKeyList=explode(',',$groupby);
				
				foreach($groupbyKeyList as &$key){
					$key=trim($key);
					if(isset($this->aliasMap[$key])) $key=$this->aliasMap[$key];
				}
			
				$groupby='GROUP BY '.implode(', ', $groupbyKeyList);
				$sqlChunkCount['groupby']=$groupby;
			
				$this->sqlCount=implode(' ',$sqlChunkCount);
				$result=$this->query($this->sqlCount);
				$this->sqlNumRows=mysqli_num_rows($result);
			}else{
				//simple count
				$this->sqlCount=implode(' ',$sqlChunkCount);			

				if($this->mysqlLink!='')
					$this->sqlNumRows=$this->squery($this->sqlCount,false,$this->mysqlLink);
				else{
					if($this->getParam('queryFunction')){
						$result=call_user_func($this->getParam('queryFunction'), $this->sqlCount);
					}else{
						$result=mysqli_query($GLOBALS["___mysqli_ston"], $this->sqlCount);
					}
					if(!$result){
						$this->appendVerbose(mysqli_error($GLOBALS["___mysqli_ston"]).'<br/>COUNT query=><br/>'.$this->sqlCount);
						$this->sqlNumRows=0;
					}else{
						$resultR=mysqli_fetch_row($result);
						$this->sqlNumRows=$resultR[0];
					}
				}					
			}
			
			
			//number of result pages
			$this->pageCount=ceil($this->sqlNumRows/$this->resultPerPage);
			$this->jsonSetPageCount($this->pageCount);	
			//if current pageis too high after, by example, a more restrictive filter, then go to last page
			$this->pageCurrent=($this->pageCurrent>$this->pageCount ? $this->pageCount:$this->pageCurrent);
			if($this->pageCurrent<1) $this->pageCurrent=1;
			
		}
		$this->jsonSetCurrentPage($this->pageCurrent);
		
		$sqlChunk['limit']="";
		//page numbering
		if($this->pageMode){
			$sqlChunk['limit']=" LIMIT ".($this->pageCurrent-1)*$this->resultPerPage.",".$this->resultPerPage;
		}
		//$sqlChunk['select']="SELECT SQL_CALC_FOUND_ROWS ".substr($sqlChunk['select'], 6);

		
	
		
		$this->sql=implode(' ',$sqlChunk);
		
		//hide
		foreach($this->keyList as $key){
			if(!isset($this->isHidden[$key]))
			$this->visibleCol[]=$key;
		}
		//apply user selection
		if(!empty($this->userVisibleCol)){
			$this->visibleCol=array_intersect($this->visibleCol,$this->userVisibleCol);
		}
		//apply col display sequence
		if(!empty($this->colSequence)){
			$newSeq=array();
			//init new seq with valid aliases in sequence apearence order
			foreach($this->colSequence as $key){				
				if(in_array($key, $this->visibleCol)) $newSeq[]=$key;
			}
			//fill up with remaining aliases
			foreach($this->visibleCol as $key){
				if(!in_array($key, $newSeq)) $newSeq[]=$key;
			}
			$this->visibleCol=$newSeq;
		}
	}
	
	
	/*
	 * @desc build result range sentence
	 */
	private function buildRangeSentence(){
		$html='';
		if($this->sqlNumRows==0 AND $this->countMode){
			$html.=$this->interface['text_noResultAndFilter'];
		}elseif($this->sqlNumRows==1){
			$html.=$this->interface['text_singleResult'];
		}else{
			$firstRow=($this->pageCurrent-1)*$this->resultPerPage;
			$html.='<span>';
			//we display currently showed start/end result rows
			$html.=str_replace('%0%', (1+$firstRow), str_replace('%1%', ($firstRow+$this->numRows), $this->interface['text_resultFromTo']) );
			//count mode and without page numbering, we display total number of result rows
			if($this->countMode AND $this->pageMode){
				$html.=' '.str_replace('%0%', $this->sqlNumRows ,$this->interface['text_resultTotal']);
			}
			$html.='</span>';
		}
		return $html;
	}
	
	/*
	 * @desc build xGRid footer append
	 */
	private function concatAppendFooter(&$html){
		if($this->enabled('verboseMode') AND !empty($this->appendVerbose))	 $this->appendFooter($this->buildVerbose());
		if(!empty($this->appendFooter)){
			$html.='<tr class="trH2" style="border-top:2px solid #333;border-bottom:2px solid #fff;text-align:left;">';
			$html.='<td colspan="'.(count($this->visibleCol)).'">';
			$html.=$this->appendFooter;
			$html.='</td>';
			$html.='</tr>';
		}
	}
	/*
	 * @desc build xGrid footer
	 * @see this::build()
	 */
	public function concatFooter(&$html){
		if(!$this->pageMode) return FALSE;
		
		$html.='<tr class="trH2" style="border-top:2px solid #333;border-bottom:2px solid #fff;text-align:left;">';
		$html.='<td colspan="'.(count($this->visibleCol)).'">';
		
		$html.='<table style="margin:0px;padding:0px;width:100%"><tr style="vertical-align:top;">';
		$html.='<td style="white-space:nowrap;">';
			//count mode, we know total number of pages
			if($this->countMode){
				
				if($this->sqlNumRows==0){
					//$html.='Aucun résultat, le filtre appliqué est peut être trop restrictif.';
				}elseif($this->sqlNumRows==1){
					//$html.='Un seul résultat trouvé et affiché.';
				}else{
					//$firstRow=($this->pageCurrent-1)*$this->resultPerPage;
					//$html.='Résultats '.(1+$firstRow).' à '.($firstRow+$this->numRows).' affichés sur '.$this->sqlNumRows.' résultats.';
					//$html.='<div align="right">'.$this->buildRangeSentence().'.</div>';
					
					$this->jsonSetPageRows($this->resultPerPage);
					
					if($this->pageCount>1){
						$html.=$this->buildPageNav();
					}
				}
			//limit mode, we dont know total number of pages
			}else{
				//$html.=$this->buildRangeSentence();
				//$firstRow=($this->pageCurrent-1)*$this->resultPerPage;
				//$html.='Résultats '.(1+$firstRow).' à '.($firstRow+$this->numRows).'.';
				$html.='. Afficher&nbsp;:&nbsp;<input class="xgrid_page_rows" style="width:20px;" value="'.$this->resultPerPage.'"/>&nbsp;';
				$html.='résultats&nbsp;par&nbsp;page.';
				$this->jsonSetPageRows($this->resultPerPage);
				
				
				$html.=$this->buildPageNav();
				}
		

		if($this->dbug) $html.='<span style="color:#555;">'.str_replace('%0%', $this->sqlExecTime ,$this->interface['text_execTime']).'</span>';
		$html.='</td>';
		$html.='<td style="text-align:right;white-space:normal;">';
		$html.=$this->buildRangeSentence();
		$html.='. Afficher&nbsp;:&nbsp;<input class="xgrid_page_rows" style="width:20px;" value="'.$this->resultPerPage.'"/>&nbsp;';
		$html.='résultats&nbsp;par&nbsp;page.';
		$html.=$this->interface['button_filter_apply'];
		$html.='</td>';
		$html.='</tr>';

		$html.='</table>';
		if(!empty($this->caseBackground)){
			#LEGEND>
				$html.='<div align="right"><table style="margin:0px;padding:0px;"><tr style="vertical-align:top;">';
				$html.='<td></td><td style="text-align:right;">'.$this->interface['word_legend'].' : ';
				$html.='</td>';
				
				$html.='<td style="border-left:1px solid #bbb;white-space:normal;">';
				//$html.='<div style="">Légende</div>';
				$i=0;
				foreach($this->caseBackground as &$case){	
					if(!empty($case['label'])){
						/*
						$html.='<div style="margin-bottom:2px;"><div style="border:1px solid #333;float:left;width: 12px;height:12px;';
						if($this->fadedRowBgd){
							$html.='" class="faded faded_c_'.$case['color'].'"';
						}else{
							$html.='background:'.$case['color'].';"';
						}					
						$html.='></div><div style="padding-left:4px;height:12px;">&nbsp;'.$case['label'].'</div></div>';
						$i++;
						*/
						$html.=' <span  style="border:1px solid #333;font-size:8px;';
						if($this->fadedRowBgd){
							$html.='" class="faded faded_c_'.$case['color'].'"';
						}else{
							$html.='background:'.$case['color'].';"';
						}					
						$html.='>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="padding-left:4px;padding-right:12px;height:12px;">&nbsp;'.str_replace(' ','&nbsp;', $case['label']).'</span>';
					}
				}
				
				$html.='</td>';
				$html.='</tr>';
				$html.='</table></div>';
			#<LEGEND
		}
		
		
		
		$html.='</td>';
		$html.='</tr>';
	}
	
	/*
	 * @desc build xgrid hidden informations
	 */
	private function concatHidden(&$html){
		#HIDDEN>
			//xgrid id
			if($this->dbug){
				$html.='<input value="'.$this->xgridId.'" name="xgrid_id"/>';
				$html.='<div style="border:1px dotted #777;background:#777; color:white">JSON dbug:</div>';
			}else{
				$html.='<input type="hidden" value="'.$this->xgridId.'" name="xgrid_id"/>';
			}
			if(!empty($this->editableAjax)){
				$html.='<input type="hidden" value="'.$this->editableAjax.'" class="xgrid_editable_ajax"/>';
			}
			//json container
			$html.='<textarea name="xgrid_json" class="xgridJSON" '.($this->dbug ? 'style="width:400px;height:200px;"':'style="display:none;"').'>'.erp_json_encode($this->json).'</textarea>';
	
			//json readonly container
			if(!isset($this->jsonRO))
				$this->jsonRO = new stdClass();
			$this->jsonRO->th=$this->th;
			$html.='<textarea class="xgridROJSON" disabled="disabled" '.($this->dbug ? 'style="width:400px;height:200px;"':'style="display:none;"').'>'.erp_json_encode($this->jsonRO).'</textarea>';
			
			/* html override default visual elements for jquery*/
			$html.=$this->buildTemplate();
			
			
		#<HIDDEN
	}
	
	/*
	 * @desc concat query debug information
	 */
	private function concatDbug(&$html){
		if($this->dbug){				
			$chunkList=$this->getSqlChunk($this->sqlCount);
			$html.='<div style="border:1px dotted #eee;background:#777; color:white">SQL COUNT dbug:</div>';
			foreach($chunkList AS $chunk){
				if(!empty($chunk)){
					$html.='<div style="border-left: 1px solid #777; border-right:1px solid #777;border-bottom:1px dotted #777">'.htmlentities($chunk).'</div>';
				}
			}				
			$chunkList=$this->getSqlChunk($this->sql);
			$html.='<div style="border:1px dotted #eee;background:#777; color:white">SQL dbug:</div>';
			foreach($chunkList AS $chunk){
				if(!empty($chunk)){
					$html.='<div style="border-left: 1px solid #777; border-right:1px solid #777;border-bottom:1px dotted #777">'.htmlentities($chunk).'</div>';
				}
			}
			flush();
		}
	}
	
	/*
	 * @desc return true if key exists else return false and add verbose notice
	 */
	private function keyExistsOrVerbose($key){
		if(in_array($key,$this->keyList)) return TRUE;
		$this->aliasError[$key]=TRUE;
		return FALSE;
	}
	
	/*
	 * @desc build xgrid tabs
	 */
	private function concatTabs(&$html){
		$tab=array();
		$tabContent=array();
		#FILTER>
		if(!empty($this->intabFilter)){
			//tab filter trigger, rel is target
			$tab[]='<td><div class="xgrid_tab '.$this->class.'Tab" rel="xgridTabFilter">'.$this->interface['word_filter'].'</div></td>';
			$content='<td>';
			//tab filter content
			$content.='<div class="xgridTabFilter '.$this->class.'TabContent" style="display:none;"><table>';
			
				foreach($this->intabFilter as $key){
					//container is alias name specifier
					$jqClass='xgrid_alias';
					
					//determine filter type based on alias declared type or declared filter type
					//class name will be used client side to manage filters behaviours (type based)
					if( isset($this->isBool[$key]) ){
						$jqClass.=' xgrid_filter_bool_radio';
					}elseif(isset($this->isDate[$key]) ){
						$jqClass.=' xgrid_filter_date';
					}else{
						if(isset($this->comboTable[$key])){
							$jqClass.=' xgrid_filter_combo'.($this->comboTableIsMulti[$key] ? '_multi':'');
						}else{
							$jqClass.=' xgrid_filter_fulltext';
						}
					}
					//a filter row
					$content.='<tr style="vertical-align:top;">';
					//filter label
					$content.='<td style="white-space:nowrap;">'.$this->th[$key].'&nbsp;:</td>';
					//filter container, classes determine client side behaviours
					$content.='<td style="white-space:nowrap;">';
					if(!empty($this->comboTable[$key]) AND $this->comboTableIsMulti[$key]){ //visibility toggler
						$content.='<span class="xgrid_filter_toggle">'.$this->interface['button_filter_toggle'].'</span>';
					}
					$content.='<div class="'.$jqClass.'" rel="'.$key.'">';
					//filter is client side generated
					//combo is server side generated
					if(!empty($this->comboTable[$key])){
						$content.='<span class="xgrid_filter_toggled" style="display:none;">';
						if($this->comboTableIsMulti[$key]){
							//multi selection combo, using a two-panes selection system
							$content.='<table class="xgrid_filter_combo"><tr>';
								//left pane (to be selected)
								$content.='<td><select style="width:160px;height:120px;" multiple="multiple">';
								//populate combo
								foreach ($this->comboTable[$key] as $value=>$label) $content.='<option value="'.$value.'">'.$label.'</option>';
								$content.='</select><div>';
								
								$content.='<span class="xgrid_combo_add_all" style="float:right;">'.$this->interface['button_add_all'].'</span>';
								$content.='<span class="xgrid_combo_add">'.$this->interface['button_add'].'</span>';
								$content.='<div></td>';
								//right pane (selection)
								$content.='<td><select style="width:160px;height:120px;" multiple="multiple">';
								$content.='</select><br/>';
								$content.='<span class="xgrid_combo_rem" style="float:right;">'.$this->interface['button_rem'].'</span>';
								$content.='<span class="xgrid_combo_rem_all">'.$this->interface['button_rem_all'].'</span>';
								
								$content.='</td>';
							$content.='</tr></table>';
						}else{
							//single selection combo
							$content.='<select>';
							//add blank option to combo (as no selection)
							$content.='<option></option>';
							//populate combo
							foreach ($this->comboTable[$key] as $value=>$label) $content.='<option value="'.$value.'">'.$label.'</option>';
							$content.='</select>';
						}
						$content.='</span>';
					}
					$content.='</div>';
					$content.='</td></tr>';					
				}			
			$content.='</table>';
			$content.='<br />';
			$content.='<div align="center">'.$this->interface['button_filter_apply'].'</div>';
			
			$content.='</div>';
			
			$content.='</td>';
			//add tab to tab row (cell wrapped)
			$tabContent[]=$content;
		}
		#<FILTER
		
		if(!empty($this->isSwitchable)){
			#SWITCH>
				$tab[]='<td><div class="xgrid_tab '.$this->class.'Tab" rel="xgrid_switch">'.$this->interface['word_switch'].'</div></td>';
				$content='';
				$content.='<td><div class="xgrid_switch '.$this->class.'TabContent" style="display:none;">';
				$content.='<table><tr style="vertical-align:top;">';
				$content.='<td>';
				$content.='<b>'.$this->interface['word_columns'].'</b>:<br/>';
				foreach($this->isSwitchable as $key=>$void){
					if($this->keyExistsOrVerbose($key)){						
						$label=trim($this->th[$key]);
						//$content.='<span class="xgrid_alias" rel="'.$key.'">';
						//class="xgrid_alias" value="'.$key.'"
						$content.='<input  class="xgrid_alias" rel="'.$key.'" type="checkbox" '.(!isset($this->isHidden[$key])?' checked="checked"':'').'/>'.$label.'<br/>';
						//$content.='</span>';
					}
				}
				/*
				$content.='<b>'.$this->interface['word_format'].'</b>:<br/>';
				$content.=$this->interface['button_print']; 
*/				
				$content.='</td>';
				$content.='<td>';
				/*
				$content.='<b>'.$this->interface['word_rows'].'</b>:<br/>';
				$content.='<input type="radio" value="0" name="'.$this->xgridId.'__print_radio" checked="checked"/>'.$this->interface['text_visibleRows'];
				$content.='<br/>';
				$content.='<input type="radio" value="1" name="'.$this->xgridId.'__print_radio"/>'.$this->interface['text_allRows'];
				
*/
				$content.='</td>';
				$content.='</tr></table>';							
				$content.='<br />';
				$content.='<div align="center">'.$this->interface['button_filter_apply'].'</div>';
				$content.='</div>';
				$content.='</td>';
				$tabContent[]=$content;	
			#<SWITCH
		}		
			
		#EXPORT>
			$tab[]='<td><div class="xgrid_tab '.$this->class.'Tab" rel="xgrid_export">'.$this->interface['word_export'].'</div></td>';
			$content='';
			$content.='<td><div class="xgrid_export '.$this->class.'TabContent" style="display:none;">';
			$content.='<table><tr style="vertical-align:top;">';
			$content.='<td>';
			$content.='<b>'.$this->interface['word_columns'].'</b>:<br/>';
			foreach($this->visibleCol as $key){
				$label=trim($this->th[$key]);
				if(!empty($label)){ //empty label are nor more selectable
					$content.='<input type="checkbox" class="xgrid_alias" value="'.$key.'"'.(!empty($label)?' checked="checked"':'').'/>'.$label.'<br/>';
				}
			}
			$content.='<b>'.$this->interface['word_format'].'</b>:<br/>';
			$content.='<table><tr><td class="xgrid_submit_button" rel="export_csv">'.$this->interface['button_export_csv'].'</td>';
			if($this->enabled('excelExport')){
				$content.='<td class="xgrid_submit_button" rel="export_xls">'.$this->interface['button_export_xls'].'</td>';
			}			
			$content.='</tr></table></td>';
			$content.='<td>';
			$content.='<b>'.$this->interface['word_rows'].'</b>:<br/>';
			$content.='<input type="radio" value="0" name="'.$this->xgridId.'__export_radio" checked="checked"/>'.$this->interface['text_visibleRows'];
			$content.='<br/>';
			$content.='<input type="radio" value="1" name="'.$this->xgridId.'__export_radio"/>'.$this->interface['text_allRows'];
			$content.='</td>';
			$content.='</tr></table>';
			$content.='</div></td>';
			$tabContent[]=$content;		
		#<EXPORT
		if($this->build['tab_print'] AND $this->enabled('pdfPrint')){
			#PRINT>
				$tab[]='<td><div class="xgrid_tab '.$this->class.'Tab" rel="xgrid_print">'.$this->interface['word_print'].'</div></td>';
				$content='';
				$content.='<td><div class="xgrid_print '.$this->class.'TabContent" style="display:none;">';
				$content.='<table><tr style="vertical-align:top;">';
				$content.='<td>';
				$content.='<b>'.$this->interface['word_columns'].'</b>:<br/>';
				foreach($this->visibleCol as $key){
					$label=trim($this->th[$key]);
					if(!empty($label)){ //empty label are nor more selectable
						$content.='<input type="checkbox" class="xgrid_alias" value="'.$key.'"'.(!empty($label)?' checked="checked"':'').'/>'.$label.'<br/>';
					}
				}
				$content.='<b>'.$this->interface['word_format'].'</b>:<br/>';
				
				$content.='<table><tr>';
				//$content.='<td class="xgrid_submit_button" rel="print_html">'.$this->interface['button_print_html'].'</td>';
				if($this->enabled('pdfPrint')){
					$content.='<td class="xgrid_submit_button" rel="print_pdf">'.$this->interface['button_print_pdf'].'</td>';
				}
				$content.='</tr></table>';
				//$content.='<span class="xgrid_submit_button">'.$this->interface['button_print'].'</span>';
				$content.='</td>';
				$content.='<td>';
				$content.='<b>'.$this->interface['word_rows'].'</b>:<br/>';
				$content.='<input type="radio" value="0" name="'.$this->xgridId.'__print_radio" checked="checked"/>'.$this->interface['text_visibleRows'];
				$content.='<br/>';
				$content.='<input type="radio" value="1" name="'.$this->xgridId.'__print_radio"/>'.$this->interface['text_allRows'];
				$content.='</td>';
				$content.='</tr></table>';
				$content.='</div></td>';
				$tabContent[]=$content;	
			#<PRINT
		}		
		
		//empty tab, other tabs auto-reshrink hack
		$tab[]='<td width="100%"></td>';
		//tab content
		$tabContent[]='<td></td>';
		
		
		
		if(!empty($tab)){
		$html.='<div class="'.$this->class.'TabWrapper"><table style="border-collapse: collapse;">';
		$html.='<tr style="vertical-align:top;">'.implode('', $tab).'</tr>';
		$html.='<tr style="vertical-align:top;">'.implode('', $tabContent).'</tr>';		
		$html.='</table></div>';
		}
	}
	
	/*
	 * @desc build xGrid in simplified listing mode (header if not empty and corpse)
	 * @comment count mode is FALSE
	 */
	public function buildListing(){
		$this->build['title']=FALSE;
		$this->countMode(FALSE);
		$this->buildSqlInit();
		$html='';
		#QUERY>
			$this->result=$this->query($this->sql);
			//number of returned result rows within page window
			$this->numRows=mysqli_num_rows($this->result);
		#<QUERY
		#BUILD>
			//$this->concatHidden($html);
			$this->concatDbug($html);
					$html.='<table class="'.$this->class.'" '.($this->wideMode? 'style="width:100%"':'').'>';
					if(!empty($this->th)) $this->concatHeader($html);
					$this->concatAppendFooter($html);
					$this->concatCorpse($html);
					$html.='</table>';				
		#<BUILD
		return $html;
	}
	
		/*
	 * @desc build xGrid automatically hiding empty sections
	 */
	public function buildMini(){
		$this->countMode(FALSE);
		$this->buildSqlInit();
		$html='';
		#QUERY>
			$this->result=$this->query($this->sql);
			//number of returned result rows within page window
			$this->numRows=mysqli_num_rows($this->result);
		#<QUERY
		#BUILD>
			//$this->concatHidden($html);
			$this->concatDbug($html);
				$html.='<div class="'.$this->class.'Wrapper">';
					$html.='<table class="'.$this->class.'" '.($this->wideMode? 'style="width:100%"':'').'>';
					//header section
					if(!empty($this->title)) $this->concatHeaderTitleMini($html);
					if($this->numRows>0){
						if(!empty($this->th)) $this->concatHeaderSort($html);
						$this->concatCorpse($html);
					}
					$this->concatAppendFooter($html);
					$html.='</table></div>';				
		#<BUILD
		return $html;
	}
	
	
	/*
	 * @desc set header row template
	 * @param string$row
	 */
	public function addTemplateRowTh($row){		
		$this->templateRowTh=$row;
	}
	
	/*
	 * @desc set row(s) template
	 * @param string|array $row
	 */
	public function addTemplateRow($row){
		if(!is_array($row)) $row=array($row);
		
		$this->templateRows=array_merge($this->templateRows,$row);
	}
	
	/*
	 * @desc use an alias value to pick the matching template row idx
	 */
	public function setTemplateRowSelector($key){
		$this->templateRowSelector=$key;
	}
	
	/*
	 * @desc build xGrid using template
	 */
	public function buildUsingTemplate(){
		//auto build a defaut template rows if needed
		if(empty($this->templateRows)){
			$templateRow='<tr>';
			foreach($this->visibleCol as $key){
				$templateRow.="<td>%$key%</td>";
			}
			$templateRow.='</tr>';
			$this->templateRows=array($templateRow);
		}
		
		$this->build['title']=FALSE;
		$this->build['th']=FALSE;
		$this->countMode(FALSE);
		$this->buildSqlInit();
		$html='';
		#QUERY>
			$this->result=$this->query($this->sql);
			//number of returned result rows within page window
			$this->numRows=mysqli_num_rows($this->result);
		#<QUERY
		#BUILD>
			$this->concatHeaderSortHtml($html);
			$this->concatCorpseHtml($html);
		#<BUILD
		return $html;
	}
	
	/*
	 * @desc  @see this::buildUsingTemplate()
	 */
	private function concatHeaderSortHtml(&$html){
		if(empty($this->templateRowTh)) return FALSE;
		foreach($this->visibleCol as $key){
			$currentRowHtml=$this->templateRowTh;
			if(isset($this->th[$key])){
				$currentRowHtml=str_replace("%$key%", ucfirst($this->th[$key]), $currentRowHtml);
			}			
		}
		$html.=$currentRowHtml;
	}
	
	
/*
 * @desc @see this::buildUsingTemplate()
 */	
public function concatCorpseHtml(&$html){
		// Optimization avoiding looping aliases on empty option set
		#OPTIMIZATION>
			$hasCallFunc=(!empty($this->callFunc) OR !empty($this->callFuncArray));
			$hasTotal=(!empty($this->showTotal) OR !empty($this->isCumulative));
		#<OPTIMIZATION
		
		$lineNumber=0;
		while($r=mysqli_fetch_assoc($this->result)){
			
			$lineNumber++;			

			if($hasCallFunc){
				#CALLFUNC>
					foreach($this->keyList as $key){
						$this->valCallModifier($key, $r);
					}
				#<CALLFUNC
			}
			$cellArray=array();
			$currentRowHtml=$this->templateRows[0]; //default is first row
			if(!empty($this->templateRowSelector) AND isset($this->templateRows[$r[$this->templateRowSelector]]) ){
				$currentRowHtml=$this->templateRows[$r[$this->templateRowSelector]];
			}
			foreach($this->visibleCol as $key){
				$cellHtml=$this->valModifier($key, $r);
				if(isset($this->isButton[$key])){
					if(isset($this->isDelete[$key])){
						$cellHtml='<div onclick="xGridEditDelete(this);">'.$cellHtml.'</div>';
					}else{
						$cellHtml='<div onclick="xGridEditBtn(this);">'.$cellHtml.'</div>';
					}					
				}				
				if($this->enabled('resultArray')) $cellArray[$key]=$cellHtml;
				$currentRowHtml=str_replace("%$key%", $cellHtml, $currentRowHtml);
			}
			$html.=$currentRowHtml;
		}
			
	}
	
	/*
	 * @desc build xGrid sql
	 * @comment alternative of this::build() when only built query is needed
	 */
	public function buildSql(){
		$this->countMode(FALSE);
		$this->buildSqlInit();	
		return $this->getBuiltSql();
	}
	
	/*
	 * @desc build xGrid
	 */
	public function build(){
		$this->initJson();
		$this->initJsontitle();
		$this->buildSqlInit();
		$this->ajaxPost=($this->ajaxMode AND isset($_POST['xgrid_id']) AND $_POST['xgrid_id']==$this->xgridId);
		
		//if($this->autoTh || $this->dbug){	
			foreach($this->aliasMap as $key=>&$void){
				//display alias name as th, do not override existing th
				if(!isset($this->th[$key])) $this->th[$key]=$key;
			}
		//}
		
		
		#QUERY>		
			$this->benchmark();
			if($this->mysqlLink!=''){
				$this->sqlNumRows=$this->squery($this->sqlCount,false,$this->mysqlLink);
				$this->result=mysqli_query($this->mysqlLink, $this->sql);
			}else{
				
				if($this->getParam('queryFunction')){
					$this->result=call_user_func($this->getParam('queryFunction'), $this->sql);
					//dbug_sql("SELECT FOUND_ROWS();");
				}else{
					$this->result=mysqli_query($GLOBALS["___mysqli_ston"], $this->sql);
					
				}				
				if(!$this->result){
					$this->appendVerbose(mysqli_error($GLOBALS["___mysqli_ston"]).'<br/>SELECT QUERY=><br/>'.$this->sql);												
				}		
			}
			$this->sqlExecTime=$this->benchmark();
			//number of returned result rows within page window
			$this->numRows=0;
			if($this->result) $this->numRows=mysqli_num_rows($this->result);
		#<QUERY
		
		$html='';
		
		//excel export init
		if($this->renderTo=='xls'){
			require_once $this->getParam('excelExportInclude')."Workbook.php";
			require_once $this->getParam('excelExportInclude')."Worksheet.php"; 
			$fname = tempnam("/tmp", mktime()."_export.xls");
			//$workbook = &new writeexcel_workbook($fname);
			$workbook = new Spreadsheet_Excel_Writer_Workbook($fname);
			$this->excelExportWS =&$workbook->addworksheet($this->title);
			//$this->excelExportWS->setInputEncoding('ISO-8859-1');
			$heading =$workbook->addformat(array('bold' => 1));
			$headingNum =$workbook->addformat(array('align' => 'right', 'bold' => 1));
			$this->excelExportFormatNum=$workbook->addformat(array('align' => 'right'));
			foreach($this->visibleCol as $key){
				$this->excelExportWS->write($this->excelExportCR, $this->excelExportCC, utf8_decode($this->th[$key]), (isset($this->isNumeric[$key]) ? $headingNum:$heading) );
				$this->excelExportCC++;
			}
			$this->excelExportCR++;
			$this->excelExportCC=0;			
		}
		#BUILD>
			$html.=$this->prependForm;
			$this->concatDbug($html);
			//xgrid wrapper
			if($this->build['wrap']) $html.='<div class="xgrid" rel="'.($this->ajaxMode?'ajax':'post').'">'; 
				//hidden section
				if($this->build['hidden']) $this->concatHidden($html);
				//tabs section
				if($this->build['header'] AND $this->build['tabs']) $this->concatTabs($html);
				//table wrapper
				if($this->build['wrap']) $html.='<div class="'.$this->class.'Wrapper">';
					$html.='<table class="'.$this->class.'" style="width:100%;" border="0" cellspacing="0" cellpadding="0">';
					//header section
					if($this->build['header']) $this->concatHeader($html);
					//corpse section
					$this->concatCorpse($html);
					//footer section
					if(!$this->build['csv'] && !$this->build['print']) $this->concatAppendFooter($html);
					if($this->build['footer']) $this->concatFooter($html);
					$html.='</table>';				
				//close table wrapper
				if($this->build['wrap']) $html.='</div>'; 
			//close xgrid wrapper
			if($this->build['wrap']) $html.='</div>'; 
			$html.=$this->appendForm;
			//form wrapper
			if($this->build['wrap'] AND !$this->disableFormWrap){
				$name='form';
				if(!empty($this->formAction)){
					$action=$this->formAction;
				}else{
					$action=$_SERVER["REQUEST_URI"];
				}
				if($this->ajaxPost){
					
				}else{				
					$html='<form name="'.$name.'" id="'.$name.'" method="post" action="'.$action.'">'.$html.'</form>';
				}
					//wrap_into_form($html);
			}
		#<BUILD
			//csv export
		if($this->renderTo=='csv'){
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false);
			header("Content-Type: text/csv");
			header("content-disposition: attachment;filename=dataExport.csv");
			echo utf8_decode(html_entity_decode(strip_tags($html)));
			exit();
		}
			//xls export
		if($this->renderTo=='xls'){
			$workbook->close();		
			//$worksheet1->set_column(0, 3, 15);
			
			
  			//header("Content-type: application/x-msexcel; charset=UTF-8");
			header("Content-Type: application/x-msexcel; name=\"export.xls\"");
			header("Content-Disposition: inline; filename=\"export.xls\"");
			$fh=fopen($fname, "rb");
			fpassthru($fh);
			unlink($fname); 
			
			exit();
			
			
			
		}elseif($this->build['print']){
			//header("Content-Type: application/force-download;");
			/*
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: $taille");
			header("Content-Disposition: attachment; filename=\"$file\"");
			header("Expires: 0");
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");
			*/


			//echo $html;exit();
			$pdf='';
			//$pdf.='<style> table{border-collapse:collapse;} .trH1 tD{background:#eee;} td{border:1px solid black;}</style>';
			$pdf.='<style> table{border-collapse:collapse;} .trH1 tD{background:#eee;} td{border:1px solid #555;} td, th{font-size:6px;font-family:arial}</style>';
			
			$param=array();
			$html=strip_tags($html, '<table><tr><th><td>');
			//$param['fast_mode']=TRUE;
			$param['pdfname']=$this->title.' '.$this->buildRangeSentence(); //.' '.implode('',$this->filterTitle);
			if($this->getParam('pdfEngineInclude')){
				return $this->xGrid2TCPdf();
			}else{
				$this->html2pdf($pdf.$html, $param); //deprecated html2pdf engine
			}
			
			
			
			
			exit();
		}else{
			
			if(1==0){
				//TODO: clean is corrupting JSON textarea => JS error in third screen
				//$html="\n".'<!-- BOF xGrid:'.$this->xgridId.' --!>'."\n".$this->cleanHtmlCode($html)."\n".'<!-- EOF xGrid:'.$this->xgridId.' --!>'."\n";
				//$html="\n".'<!-- BOF xGrid:'.$this->xgridId.' --!>'."\n".$html."\n".'<!-- EOF xGrid:'.$this->xgridId.' --!>'."\n";
				$html="\n".$html."\n".'<!-- EOF xGrid:'.$this->xgridId.' --!>'."\n";
			}
			if(!$this->getBuiltRowCount() AND $this->emptyIfNoResult) return '';
			if($this->ajaxPost) {echo $html;exit();}
			return $html;
		}		
	}
	
	/*
	 * @desc get built rows, 0 on empty result set
	 * @comment relevant only after a build
	 */
	public function getBuiltRowCount(){
		return $this->numRows;
	}
	/*
	 * @desc on TRUE, will return empty string on empty result set (instead of empty xGrid html wrap)
	 * @param bool $bool default TRUE
	 */
	public function emptyIfNoResult($bool=TRUE){
		$this->emptyIfNoResult=$bool;
	}
	
	/*
	 * @desc build as PDF
	 */
	public function buildPDF(){
		$this->param('pdfMode','download');
		return $this->build();
	}
	
	/*
	 * @desc build and get PDF as a string
	 * @return string PDF
	 */
	public function getPDF(){
		$this->param('pdfMode','string');
		return $this->build();
	}
	
	
	/*
	 * @desc get cleansed filename
	 * @param string $string
	 * @return string
	 */	
	private static function getCleanFilename($string,$toUpper=true,$SlashException = true){
		$string=strip_tags($string);
		$string = html_entity_decode($string);		
		
		$remplace = array('à'=>'a','á'=>'a','â'=>'a','ã'=>'a','ä'=>'a','å'=>'a','ò'=>'o','ó'=>'o','ô'=>'o','õ'=>'o','ö'=>'o','è'=>'e',
	                      'é'=>'e','ê'=>'e','ë'=>'e','ì'=>'i','í'=>'i','î'=>'i','ï'=>'i','ù'=>'u','ú'=>'u','û'=>'u','ü'=>'u','ÿ'=>'y',
	                      'ñ'=>'n','ç'=>'c','ø'=>'0');
	    $string = strtr(trim(mb_strtolower($string,'utf-8')),$remplace); 
		if($toUpper) $string = mb_strtoupper($string,'utf-8');
		$string = str_replace(" ","_",$string);	
		if($SlashException) {
			$string = preg_replace('/[^A-Z_0-9.@-]/', '_', $string);
		}
		return $string;
	}	
	/*
	 * @desc generate PDF using TCPDF engine
	 */
	private function xGrid2TCPdf(){
		
		if(empty($this->userVisibleCol)) $this->userVisibleCol=$this->visibleCol;
		
		$zoom=5;
		$zoomFontSize=array(1=>1,2=>2, 3=>3, 4=>4, 5=>5, 6=>6, 7=>7, 8=>8, 9=>9, 10=>10);
		$zoomCellHeight=array(1=>0.5, 2=>1, 3=>1.5, 4=>2, 5=>2.5, 6=>3, 7=>3.5, 8=>4, 9=>4.5, 10=>5);
		$zoomLineWidth=array(1=>0.1, 2=>0.1, 3=>0.1, 4=>0.1, 5=>0.2, 6=>0.2, 7=>0.2, 8=>0.3, 9=>0.3, 10=>0.3);
		
		
		require $this->getParam('pdfEngineInclude');
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 
		//$pdf->setPrintHeader(false);
		//$pdf->setPrintFooter(false);
		//$pdf->SetTitle($this->title);
		//$pdf->SetHeaderData(null,null, $this->title);
		$pdf->SetHeaderData('', '', $this->title);
		$pdf->setHeaderFont(Array('helvetica', '', $zoomFontSize[$zoom]));
		$pdf->setFooterFont(Array('helvetica', '', $zoomFontSize[$zoom])); 
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER); 
		$pdf->SetLeftMargin(3);
		$pdf->SetRightMargin(3);
		$pdf->SetFont('helvetica', '', $zoomFontSize[$zoom]);
		$pdf->AddPage();
		
		#FASTMODE>
			$pdf->SetDrawColor(128, 128, 128);
	        $pdf->SetLineWidth($zoomLineWidth[$zoom]);
	        
	        if($this->getParam('aliasWidth')){
		 		$tmpAliasWidthArray=(array)$this->getParam('aliasWidth');
	        }else{
	        	foreach($this->userVisibleCol as $alias) $tmpAliasWidthArray[$alias]=100; //arbitrary width
	        }
	        
			foreach($this->userVisibleCol as $alias){
				$onscreenAliasWidthArray[$alias]=$tmpAliasWidthArray[$alias];
			}
			
	        $onscreenPageWidth=array_sum($onscreenAliasWidthArray);
	        $pdfPageWidth=$pdf->getPageWidth()-6; //minus print margins
	        $pdfAliasWidthArray=array();
	        foreach($onscreenAliasWidthArray as $alias => $onscreenAliasWidth){
	        	$pdfAliasWidthArray[$alias]=$onscreenAliasWidth/$onscreenPageWidth*$pdfPageWidth;
	        }
	        //Set auto alignement
	        $cellAlign=array();
	        foreach($this->userVisibleCol as $alias) $cellAlign[$alias]=(isset($this->isNumeric[$alias])? 'R':'L');
	        
	        #TH>
		        $pdf->SetFillColor(100, 100, 100);
		        $pdf->SetTextColor(255);
		        
		        $pdf->SetFont('', 'B');
		        
		        $row=array();
		        foreach($this->visibleCol as $key) $row[$key]=ucfirst($this->th[$key]);
		        
		        foreach($row as $alias=>&$cell){        		
		        	$pdf->Cell($pdfAliasWidthArray[$alias], $zoomCellHeight[$zoom], strip_tags($cell), 'LRT', 0, $cellAlign[$alias], 1, '',1);
		        }
		        $pdf->Ln();
			#<TH
	
	        $pdf->SetFillColor(224, 235, 255);
	        $pdf->SetTextColor(0);
	        $pdf->SetFont('');
	        $fill = 0;
	        $border="LR";
	        $count=1;
	        $nbRows=count($this->resultArray);
	        foreach($this->resultArray as &$row) {
	        	if($nbRows==$count){
	        		$border="LRB";
	        	}
	        	foreach($row as $alias=>&$cell){        		
	        		$pdf->Cell($pdfAliasWidthArray[$alias], $zoomCellHeight[$zoom], strip_tags($cell), $border, 0, $cellAlign[$alias], $fill, '',1);
	        	}
	            $pdf->Ln();
	            $fill=!$fill;
	            $count++;
	        }
        #<FASTMODE
        switch($this->getParam('pdfMode') ){
        	case 'string':
        		return $pdf->Output('xgrid.pdf', 'S');
        	break;
        	default:
        		$pdf->Output('xgrid.pdf', 'D');   
        	break;
        }      
	}
	
	/*
	 * @desc
	 */
	private function pdfRow(&$pdf){
		
	}
	
	
	/*
	 * @desc html2pdf, deprecated (2 slow)
	 */	
	private static function html2pdf($html_body, $param=array()) {	
		$html='<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
			<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
	
	
		$html.='</head>'.$html_body;
		unset($html_body);
		$html.='</html>';

		include_once('class/pdfClass.php');	
		$path_to_pdf='';
		$base_path='';
		$outputType='download';
	
		if(isset($param['outputType'])) $outputType=$param['outputType']; 
		if(isset($param['pdfname'])) $path_to_pdf=xGrid::getCleanFilename($param['pdfname'],($param['outputType']!='file'),($param['outputType']!='file'));
	
		$pipeline = PipelineFactory::create_default_pipeline('', '');
		
		$pipeline->fetchers[] = new MyFetcherMemory($html, $base_path);
		
		  // Override destination to local file
		  //$pipeline->destination = new MyDestinationFile($path_to_pdf);
		  //$pipeline->destination = new DestinationBrowser('temp.pdf');
		  	switch ($outputType) {
		 		case 'browser':
		 			$pipeline->destination = new DestinationBrowser($path_to_pdf);
		   		break;
		 		case 'download':
		   			$pipeline->destination = new MyDestinationDownload($path_to_pdf);   			
		   		break;
		 		case 'file':
		   			//$pipeline->destination = new DestinationFile($filename, 'File saved as: <a href="%link%">%name%</a>');   			
		   			//$_SESSION['justGeneratedPDFFile'] = null;
		 			$pipeline->destination = new MyDestinationFile($path_to_pdf);
		   			//$_SESSION['justGeneratedPDFFile'] = $path_to_pdf;
		   			echo $path_to_pdf;
	   		break;
			};
	
		 $baseurl = '';
		 $media =& Media::predefined('A4');
	  	// $media->set_landscape(isset($param['landscape']));
	  	 $media->set_landscape(TRUE);
		  
		 $media->set_margins(array('left'   => 3,
		                            'right'  => 3,
		                            'top'    => 10,
		                            'bottom' => 10));
	
		  
		
		  global $g_config;
		  $g_config = array(
		  					
		                    'cssmedia'     => 'screen',
		                    'scalepoints'  => '1',
		                    'renderimages' => TRUE,
		                    'renderlinks'  => TRUE,
		                    'renderfields' => FALSE,
		                    'mode'         => 'html',
		                    'encoding'     => 'utf-8',
		                    'debugbox'     => FALSE,
		                    'pdfversion'   => '1.4',
		                  	'smartpagebreak' => 1,
		                    'output'       => 0
		                    );
		
		  $pipeline->configure($g_config);
		  $pipeline->process_batch(array($baseurl), $media);
		  return $path_to_pdf;
	}	
		


	/*
	 * @desc build xGrid
	 */
	public function buildFilter(){
		$this->initJson();
		$this->initJsontitle();
		$this->buildSqlInit();
		$this->ajaxPost=($this->ajaxMode AND isset($_POST['xgrid_id']) AND $_POST['xgrid_id']==$this->xgridId);
		
		//if($this->autoTh || $this->dbug){
			foreach($this->aliasMap as $key => &$void){
				//display alias name as th, do not override existing th
				if(!isset($this->th[$key])) $this->th[$key]=$key;
			}
		//}
		
		
		#QUERY>
			$this->benchmark();
			if($this->mysqlLink!='')
				$this->sqlNumRows=$this->squery($this->sqlCount,false,$this->mysqlLink);
			else{
				$this->result=mysqli_query($GLOBALS["___mysqli_ston"], $this->sql);
			}
			$this->sqlExecTime=$this->benchmark();
			//number of returned result rows within page window
			$this->numRows=mysqli_num_rows($this->result);

		#<QUERY
		
		$html='';
		
		#BUILD>
			$html.=$this->prependForm;
			//xgrid wrapper
			if($this->build['wrap']) $html.='<div class="xgrid" rel="'.($this->ajaxMode?'ajax':'post').'">'; 
				//hidden section
				if($this->build['hidden']) $this->concatHidden($html);
				//tabs section
				if($this->build['header'] AND $this->build['tabs']) $this->concatTabs($html);
				//table wrapper
				if($this->build['wrap']) $html.='<div class="'.$this->class.'Wrapper">';
					$html.='<table class="'.$this->class.'" style="width:100%">';
					//header section
					if($this->build['header']){
						$html.='<thead>';
						if($this->build['title']){
							$this->concatHeaderTitle($html);
						}
						$this->concatHeaderFilter($html);
						//$this->concatHeaderSort($html);		
						$html.='</thead>';
						
					}
					//footer section
					$this->concatAppendFooter($html);
					//if($this->build['footer']) $this->concatFooter($html);
					$html.='</table>';				
				//close table wrapper
				if($this->build['wrap']) $html.='</div>'; 
			//close xgrid wrapper
			if($this->build['wrap']) $html.='</div>'; 
			$html.=$this->appendForm;
			//form wrapper
			if($this->build['wrap'] AND !$this->disableFormWrap){
				$name='form';
				if(!empty($this->formAction)){
					$action=$this->formAction;
				}else{
					$action=$_SERVER["REQUEST_URI"];
				}
				if($this->ajaxPost){
					
				}else{
					
				
					$html='<form name="'.$name.'" id="'.$name.'" method="post" action="'.$action.'">'.$html.'</form>';
				}
					//wrap_into_form($html);
			}
		#<BUILD
		
		if($this->build['csv']){
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false);
			header("Content-Type: text/csv");
			header("content-disposition: attachment;filename=dataExport.csv");
			echo utf8_decode(html_entity_decode(strip_tags($html)));
			exit();
		}elseif($this->build['print']){
			//echo $html;exit();
			$pdf='';
			$pdf.='<style> table{border-collapse:collapse;} .trH1 tD{background:#eee;} td{border:1px solid black;}</style>';
			$param=array();
			$param['pdfname']=$this->title.' '.$this->buildRangeSentence(); //.' '.implode('',$this->filterTitle);
			html2pdf($pdf.$html, $param);
			exit();
		}else{
			
			if($this->ajaxPost) {echo $html;exit();}
			return $html;
		}
		
	}
	
	private function buildPageNav(){
		$html='';
		$html.='<div class="xgrid_page_nav">';
		$html.='<table><tr><td style="width:80px;">';
		if($this->pageCurrent>1){			
			$html.=$this->interface['button_page_go_first'];			
			$html.=$this->interface['button_page_go_prev'];			
		}
		$html.='</td><td>';
		$html.='Page&nbsp;<input class="xgrid_page_num" style="width:30px;" value="'.$this->pageCurrent.'"/>';
		$html.='</td><td style="width:80px;">';
		

		if(!$this->countMode OR $this->pageCurrent<$this->pageCount){
			$html.=$this->interface['button_page_go_next'];		
			if($this->countMode){	
				$html.=$this->interface['button_page_go_last'];
			}
		}
		$html.='</td><td>';
		if($this->countMode){
			$html.=' sur&nbsp;'.$this->pageCount.'&nbsp;pages&nbsp;en&nbsp;tout.';
		}
		$html.='</td></tr></table>';
		$html.='</div>';
		
		return $html;
	}
	
	public function setPage($page){
		$this->pageCurrent=(int)$page;		
		if($this->pageCurrent<1) $this->pageCurrent=1;
	}
	
	public function whereLike($key, $string){
		$this->where($key, $string, 'contain');
	}
	
	/*
	 * @desc allow column to be filtrered, will display type based filter in sub-header zone
	 * @param string $key query select alias
	 */
	public function allowFilter($key){
		$this->enableFilter[$key]=TRUE;
	}
	
	/*
	 * @desc allow all columns to be filtrered, will display type based filter in sub-header zone
	 */
	public function allowAllFilter(){
		$this->allowAllFilter=TRUE;
	}
	
	/*
	 * @desc disable column to be filtrered
	 * @param string $key query select alias
	 */
	public function disableFilter($key){
		$this->disableFilter[$key]=TRUE;
	}
	
	/*
	 * @desc allow column to be sorted, will display sort icon next to header label and allow click-to-sort on header cell
	 * @param string $key query select alias
	 */
	public function enableSort($key){
		$this->enableSort[$key]=TRUE;
	}
	
	/*
	 * @desc this::enableSort() alias
	 * @param string $key query select alias
	 */
	public function allowSort($key){
		$this->enableSort($key);
	}
	
	/*
	 * @desc allow all columns to be sorted, will display sort icon next to header label and allow click-to-sort on header cell
	 */
	public function allowAllSort(){
		$this->allowAllSort=TRUE;
	}
	
	/*
	 * @desc disable column to be sorted
	 * @param string $key query select alias
	 */
	public function disableSort($key){
		$this->disableSort[$key]=TRUE;
	}
	

	/*
	 * @desc allow collumn labelling
	 * @param string $key query select alias
	 */
	public function th($key, $val=''){
		$this->th[$key]=(empty($val)? $key:$val);
	}
	
	/*
	 * @desc allow collumn labelling (short label version)
	 * @param string $key query select alias
	 */
	public function thShort($key, $val=''){
		$this->thShort[$key]=(empty($val)? $key:$val);
	}
	
	/*
	 * @desc allow collumn hiding, the value is still useable in query
	 * @param string query select alias
	 */
	public function hide(){
		$keys_arg= func_get_args();
		foreach($keys_arg as &$keys){
			if(!is_array($keys)) $keys=array($keys);
			foreach($keys as $key) $this->isHidden[$key]=TRUE;
		}
	}
	
	/*
	 * @desc disable colomn head (hide label, disable sorting, disable filtering)
	 * @param string|array alias
	 */
	public function disableTh($alias){
		$keys_arg= func_get_args();
		foreach($keys_arg as &$keys){
			if(!is_array($keys)) $keys=array($keys);
			foreach($keys as $key){
				$this->th($key,' ');
				$this->disableFilter($key);
				$this->disableSort($key);
			}
		}
	}
	
	/*
	 * @desc allow collumn visibility switch, with "off" as default status, visibility toggle done in "visibility" tab
	 * @param string|array alias
	 */
	public function switchOff($keys){
		$keys_arg= func_get_args();
		foreach($keys_arg as &$keys){
			if(!is_array($keys)) $keys=array($keys);
			foreach($keys as $key){
				$this->isSwitchable[$key]=TRUE;
				$this->isHidden[$key]=TRUE;
			}
		}
	}
	
	/*
	 * @desc allow collumn visibility switch, with "on" as default status, visibility toggle done in "visibility" tab
	 * @param string|array alias
	 */
	public function switchOn($keys){
		$keys_arg= func_get_args();
		foreach($keys_arg as &$keys){
			if(!is_array($keys)) $keys=array($keys);
			foreach($keys as $key){
				$this->isSwitchable[$key]=TRUE;
			}
		}
	}
	
	/*
	 * @desc allow collumn overflow auto hide
	 * @param string $key query select alias
	 */
	public function hideOverflow($key){
		$this->hideOverflow[$key]=TRUE;
	}
	
	
	/*
	 * @desc set title
	 * @param string $title
	 */
	public function setTitle($title){
		$this->title=$title;
	}
	
	/*
	 * @desc this::setTitle() alias
	 */
	public function title($title){
		$this->setTitle($title);
	}
	
	
	
	/*
	 * @desc define column type as boolean
	 * @param string $key query select alias
	 * @comment boolean raw values displays (0||1) are replaced by default or custom html
	 */
	public function isBool($key){
		$this->isBool[$key]=TRUE;
		$this->swap[$key][0]='';
		$this->swap[$key][1]=$this->interface['icon_yes'];
	}
		
	public function isBoolAlt($key){
		$this->isBool[$key]=TRUE;
		$this->swap[$key][0]='';
		$this->swap[$key][1]=$this->interface['icon_yesalt'];
	}
	/*
	 * @desc simple replace per value basis
	 * @param string $key query select alias
	 * @param mixed $value value to replace
	 * @param string $html value replacement string
	 */
	public function swap($key,$value,$html){
		$this->swap[$key][$value]=$html;
	}
	
	/*
	 * @desc define column type as date
	 * @param string $key query select alias
	 * @comment date must be in unixtime format, displayed as dd/mm/yyyy format by default
	 */
	public function isDate($key, $format='d/m/Y'){
		$this->isDate[$key]=TRUE;				
		$this->dateFormat[$key]=$format;				
	}
	public function isInt($key){
		$this->isInt[$key]=TRUE;
		$this->isNumeric[$key]=TRUE;
	}
	public function isFloat($key){
		$this->isFloat[$key]=TRUE;
		$this->isNumeric[$key]=TRUE;
	}
	

	/*
	 * @desc set ajax mode
	 * @param bool $bool default TRUE
	 */
	public function ajaxMode($bool=TRUE){
		$this->ajaxMode=(bool)$bool;
	}
	
	
	
	/*
	 * @desc set count mode
	 * @param bool $bool default TRUE
	 */
	public function countMode($bool=TRUE){
		$this->countMode=(bool)$bool;
	}
	
	
	/*
	 * @desc define column type as decimal
	 * @param string $key query select alias
	 * @param int $decimals optional default is 2
	 */
	public function isDec($key, $decimals=2){
		$this->isNumeric[$key]=TRUE;
		$this->isDec[$key]=TRUE;
		$this->decimals[$key]=$decimals;		
	}
	/*
	 * @desc define column type as currency, will apply currency formatting
	 * @param string $key query select alias
	 * @param int $decimals optional default is 2
	 */
	public function isCurrency($key, $decimals=2){		
		$this->isNumeric[$key]=TRUE;
		$this->isCurrency[$key]=TRUE;
		$this->isDec[$key]=TRUE;
		$this->decimals[$key]=$decimals;		
	}
	
	/*
	 * @desc show cumulative of a column preceding rows
	 */
	public function isCumulative($key){
		$this->isCumulative[$key]=TRUE;
		$this->cumul[$key]=0;
	}
	
/*
	 * @desc show total in result footer row
	 */
	public function showTotal($key){
		$this->showTotal[$key]=TRUE;
		$this->total[$key]=0;
	}
	
	
	/**
	 * Replace using XgridReplace object
	 *
	 * @param string $key
	 * @param XGridReplace $replace
	 */
	public function replace($key, $replace){
		$this->replace[$key]=$replace;
	}
	
	/*
	 * @desc replace shorthand @see this::replace()
	 * @param string $alias
	 * @param string $subject
	 * @param mixed [$replace] replace using alias (use self alias by default)
	 * @param mixed [$search] search string to replace (use %0% by default)
	 */
	public function simpleReplace($alias, $subject, $replace='', $search='%0%'){
		if(!is_array($search)) $search=array($search);
		if(empty($replace)) $replace=$alias;
		if(!is_array($replace)) $replace=array($replace);
		$xGridReplace=new xGridReplace($search, $replace, $subject);
		$this->replace($alias, $xGridReplace);
	}
	
	/*
	 * @desc assign a conditional replace
	 * @param string $key query select alias
	 * @param object $case
	 * @see xGridCase()
	 */
	public function caseReplace($key, $case){
		$this->caseReplace[$key]=$case;
	}
	private function applyReplace(&$replace, &$r){
		$subject=$replace->subject;
		if(!empty($replace)){
			if(is_array($replace->search)){
				foreach($replace->search as $i=>$search){
					$alias=$replace->replace[$i];
					if(array_key_exists($alias,$r)){
						$val=$r[$alias];
						if(isset($this->formatBeforeReplace[$alias])) $this->typeFormat($alias, $val);						
						$subject=str_replace($search, $val, $subject);
					}else{
						//unknow alias (verbose mode)
						$this->aliasError[$alias]=TRUE;
					}			
				}
			}else{
				$alias=$replace->replace;
				$val= $r[$replace->replace];
				if(isset($this->formatBeforeReplace[$alias])) $this->typeFormat($alias, $val);	
				$subject=str_replace($replace->search , $val, $subject);
			}
		}
		return $subject;
	}
	
	public function caseBackground($key, $valueList, $colorList, $labelList=''){
		$i=count($this->caseBackground);
		if(!is_array($valueList)) $valueList=array($valueList);
		if(!is_array($colorList)) $colorList=array($colorList);
		if(!is_array($labelList)) $labelList=array($labelList);
		$j=$i+count($valueList);
		for($i;$i<$j;$i++){
			$this->caseBackground[$i]['key']=$key;
			$this->caseBackground[$i]['value']=current($valueList);next($valueList);
			$this->caseBackground[$i]['color']=current($colorList);next($colorList);
			$this->caseBackground[$i]['label']=current($labelList);next($labelList);
		}
	}
	
	public function small($key){
		$this->tdClass[$key][]='small';
		
	}
	
	/*
	 * @desc track alias value then add splitter row on change
	 * @param string $key query select alias
	 */
	public function splitter($key){
		$this->splitter=$key;
		$this->splitterValueTracker='';
	}
	
	/*
	 * @desc append alias to splitter row
	
	public function splitterAppend($key){
		$this->splitterAppend=$key;
	}
	 */
	private function orderBy($key, $way='ASC'){
		$this->orderBy[$key]=$way;
	}
	
	/*
	 * @desc set default alias to be sorted
	 * @param string $key query select alias
	 * @param ASC|DESC $way
	 */
	public function defaultSort($key, $way='ASC'){
		$this->defaultSort[$key]=$way;
	}
	
	/*
	 * @desc allow default filter to be set
	 * @param string $key query select alias
	 * @param string $value
	 */
	public function defaultFilter($key, $value){
		$this->defaultFilter[$key]=$value;
	}
	
	
	/*
	 * @desc allow default filter to be easily set for dates
	 * @param string $key query select alias
	 * @param mktime|enum|empty $from can be mktime past/future relative to now
	 * @param bool $rounded true by default, round current mktime to start of choosed unit
	 * @comment enum eg: ( minus sign is optional, N is Now based on current mktime)
	 * 
	 * 
	 * 1day = one day before before MM/DD/YY 00:00
	 * +1day = one day after before MM/DD/YY 23:59
	 * 5day = five days before before MM/DD/YY 00:00
	 * +6month = half a year after MM/00/YY 00:00
	 * -2year = two years before 00/00/YY 00:00
	 * 
	 * not implemented yet -3hour = 3 hours before MM/DD/YY HH:00
	 * not implemented yet 3week = 3 weeks before today
	 * not implemented yet : today = today (NN/NN/NN at 00:00)
	 */
	public function defaultDateFromFilter($key, $from='', $rounded=TRUE){
		$now=mktime();
		if(!empty($from) AND !is_numeric($from)) $from=$this->getRelativeMktime($from, $rounded);
		if(!empty($from)) $now=$from;
		$this->defaultFilter($key, array('from'=>date('d/m/Y',$now)) );
		$this->isDate($key);
	}
	
	
	/*
	 * @desc allow default filter to be easily set for dates
	 * @param string $key query select alias
	 * @param mktime|enum|empty $to can be mktime past/future relative to now
	 * @param bool $rounded true by default, round current mktime to start of choosed unit
	 * @see this::defaultDateFromFilter()
	 */	
	public function defaultDateToFilter($key, $to='', $rounded=TRUE){
		$now=mktime();
		if(!empty($to) AND !is_numeric($to)) $to=$this->getRelativeMktime($to, $rounded);
		if(!empty($to)) $now=$to;
		$this->defaultFilter($key, array('to'=>date('d/m/Y',$to)) );
	}
	
	/*
	 * @desc allow default filter to be easily set for dates
	 * @param string $key query select alias
	 * @param mktime|enum|empty $to can be mktime past/future relative to now
	 * @param bool $rounded true by default, round current mktime to start of choosed unit
	 * @see this::defaultDateFromFilter()
	 */	
	public function defaultDateBetweenFilter($key, $from='', $to='', $rounded=TRUE){
		$now=mktime();
		if(!empty($from) AND !is_numeric($from)) $from=$this->getRelativeMktime($from, $rounded);
		if(empty($from)) $from=$now;
		
		if(!empty($to) AND !is_numeric($to)) $to=$this->getRelativeMktime($to, $rounded);
		if(empty($to)) $to=$now;
		
		$this->defaultFilter($key, array('to'=>date('d/m/Y',$to),'from'=>date('d/m/Y',$from)));
		$this->isDate($key);
	}
	
	/*
	 * @desc get mktime based on a time relative to now
	 * @param string $t
	 * @param bool $unitRound true by default, will round current mktime based on unit choosed
	 * @return mktime
	 */
	private function getRelativeMktime($t, $unitRound=TRUE){
		//$thisHour=date('H');
		$thisDay=date('d');
		$thisMonth=date('m');
		$thisYear=date('Y');
		//$thisRoundedHour=($unitRound ? 0 : $thisHour); // 00 or current hour
		$thisRoundedDay=($unitRound ? 1 : $thisDay); // 1st of month or current day
		$thisRoundedMonth=($unitRound ? 1 : $thisMonth); // january or current month
		
		//cleanse
		$t=trim(strtolower($t));		
		//past or future?
		$sign=(substr($t,0,1)=='+' ? '+':'-');		
		//remove sign
		if(in_array(substr($t,0,1), array('+','-'))) $t=substr($t,1);
		//isolate unit
		
		$unitArray=array('day', 'hour', 'month', 'year');
		foreach($unitArray as $unit){
			$pos=strpos($t, $unit);
			if($pos>0){
				$nUnit=substr($t,0,$pos);
				break; // we got our unit we can go on
			}
		}
		switch($unit){
			case 'day':
				return mktime(0,0,0,$thisMonth, $thisDay-$nUnit, $thisYear);
			break;
			case 'month':
				return mktime(0,0,0,$thisMonth-$nUnit, $thisRoundedDay, $thisYear);
			break;
			case 'year':
				return mktime(0,0,0,$thisRoundedMonth, $thisRoundedDay, $thisYear-$nUnit);
			break;
		}
		
	}
	
	
	/*
	 * @desc set alias as js ondblclick action
	 * @param string $key query select alias
	 */
	public function jsOnDblClick($key, $x_replace=''){
		$this->isHidden[$key]=TRUE; //autohide
		$this->dblclick=$key;
		if(is_object($x_replace))
			$this->replace('dblclick', $x_replace);
	}
	
	/*
	 * @desc set another xgrid global class
	 */
	public function setClass($class){
		$this->class=$class;
	}
	
	/*
	 * @desc concat another class to current xgrid global class
	 */
	public function addClass($class){
		$this->class=$this->class.' '.$class;
	}
	
	/*
	 * @desc set page mode
	 * @param bool $pageMode default is TRUE
	 */
	public function setPageMode($pageMode=TRUE){
		$this->pageMode=$pageMode;
	}
	
	/*
	 * @desc append html to footer, inside a blank colspanned row
	 * @param string $html
	 */
	public function appendFooter($html){
		$this->appendFooter.=$html;
	}
	
	/*
	 * @desc append html to verbose
	 * @param string $html
	 */
	public function appendVerbose($html){
		if(!empty($this->appendVerbose)) $this->appendVerbose.='<hr style="border: 1px solid red;margin:2px;" />';
		$this->appendVerbose.=$html;
	}
	
	/*
	 * @desc build verbose html
	 */
	private function buildVerbose(){
		$html='<div style="background:red;color:white;">Verbose mode log :</div><div style="border: 2px solid red;white-space:normal;">'.$this->appendVerbose.'</div>';
		return $html;
	}
	
	/*
	 * @desc append html to footer, inside a blank colspanned row
	 * @param string $key query select alias
	 * @param string $html
	 */
	public function colFooter($key, $html){
		$this->colFooter[$key]=$html;
	}
	
	/*
	 * @desc set any attribute for td tag
	* @param string $key query select alias
	 */
	public function cellAttr($key, $attr, $value){
		$this->cellAttr[$key][$attr][]=$value;
	}
	
	/*
	 * @desc set class attribute
	* @param string $key query select alias
	* @param string $value attribute value
	* @see this::cellAttr()
	 */
	public function cellClass($key, $value){
		$this->cellAttr($key, 'class', $value);
	}
	
	/*
	 * @desc set style attribute
	* @param string $key query select alias
	* @param string $value attribute value
	* @see this::cellAttr()
	 */
	public function cellStyle($key, $value){
		$this->cellAttr($key, 'style', $value);
	}
	
	/*
	 * @desc set style width attribute
	* @param string $key query select alias
	* @param string $value width value in px
	* @see this::cellAttr()
	 */
	public function cellWidth($key, $value){
		$this->cellAttr($key, 'style', 'width:'.$value.'px');
	}
	
	
	
	/*
	 * @desc set any attribute for tr tag
	 */
	public function rowAttr($attr, $value){
		$this->rowAttr[$attr][]=$value;
	}
	
	/*
	 * @desc set any attribute for tr tag using a column value
	 */
	public function rowAttrSwap($attr, $alias){
		$this->rowAttrSwap[$attr]=$alias;
	}
	
	/*
	 * @desc fill each row json attribute with specified aliases (json encoded)
	 * @param array $keyList index=>alias
	 */
	public function rowJSON($keyList){
		$this->rowJSON=$keyList;
	}
	
	/*
	 * @desc set highlight style to alias column
	* @param string $key query select alias
	 */
	public function highlight($key){
		$this->cellClass($key, 'highlight');
	}
	
	/*
	 * @desc set alternate row background
	 * @param bool $bool
	 */
	public function alternateRowBgd($bool=TRUE){
		$this->alternateRowBgd=(bool)$bool;
	}
	/*
	 * @desc set faded row background
	 * @param bool $bool
	 */
	public function fadedRowBgd($bool){
		$this->fadedRowBgd=(bool)$bool;
	}
	

		/*
	 * @desc add global filter
	* @param string $key query select alias
	 */
	public function addFilter($key){
		$this->intabFilter[]=$key;
	}
	
	/*
	 * @desc add global multi combo filter
	* @param string $key query select alias
	* @param array $comboTable value=>label
	* @param bool $multi is multi combo
	* @param bool $glue AND|OR
	 */
	public function addComboFilter($key, $comboTable, $multi=FALSE){ //, $glue=FALSE
		$this->intabFilter[]=$key;
		$this->comboTable[$key]=$comboTable;
		$this->comboTableIsMulti[$key]=$multi;
		/*
		if($glue){
			$this->comboGlueType[$key] = $glue;
		}
		*/
	}
	
	/*
	 * @desc add sql where conditionnal group
	 * @param string $glueType glue type ('AND','OR')
	 * @param array $keyList array of group keys
	 */
	public function glueGather($glueType,$keyList){
		$glueGather=array();
		$glueGather['glueType']=$glueType;
		$glueGather['keyList']=$keyList;
		$this->glueGather[]=$glueGather;
	}
	
	/*
	 * @desc return glueGather index if the field is in a conditionnal group, or false if the field is not in a group
	 * @param string $key query select alias
	 */
	public function getGlueGather($key){
		foreach($this->glueGather as $glueGatherKey=>$glueGather){
			foreach($glueGather['keyList'] as $parseKey){
				if($key==$parseKey){
					return $glueGatherKey;
					
				}
			}
		}
		return false;
	}
	
	/*
	 * @desc return true if alias is currently used in a filter
	 * @param string $alias
	 * @param string $operator optional
	 */
	public function issetAliasFilter($alias, $operator=''){
		return $this->sqlWhere->issetAlias($alias, $operator);
	}
	
	
	/*
	 * @desc return alias current combo filter array
	 * @param string $alias
	 */
	public function getAliasComboFilter($alias){	
		if(!empty($this->json->$alias->combo)){
			return $this->json->$alias->combo;
		}else{
			return array();
		}
	}
	
	
	/*
	 * @desc disable print tab
	 */
	public function noPrint(){
		$this->build['tab_print']=FALSE;
	}
	
	
	
	/*
	 * @desc will trigger php call_user_function_array()
	 * @param string $alias query value will be replaced by function return
	 * @param string $func function or static
	 * @param string|array $aliasArray []=>alias each alias value will be passed to function in this order
	 */
	public function callFuncArray($alias, $func, $aliasArray=array()){
		if(!is_array($aliasArray)) $aliasArray=array($aliasArray);
		$this->callFuncArray[$alias]['func']=$func;
		$this->callFuncArray[$alias]['aliasArray']=$aliasArray;
	}

	/*
	 * @desc call php func
	 * @param string $alias
	 * @param string $func php func name
	 * @param array $paramAlias optional paramKey=>paramValue from another alias value
	 * @param array $paramValue optional paramKey=>paramValue from hard coded value
	 */
	public function callFunc($alias, $func, $paramAlias=array(), $paramValue=array()){
		$this->callFunc[$alias]['func']=$func;
		$this->addFuncParamAlias($alias, $paramAlias);
		$this->addFuncParamValue($alias, $paramValue);
	}
	
	/*
	 * @desc
	 */
	public function addFuncParamAlias($alias, $keyAlias=array()){
		if(empty($keyAlias)) return FALSE;
		foreach($keyAlias as $paramKey => $paramAliasValue){
			$this->callFunc[$alias]['paramAlias'][$paramKey]=$paramAliasValue;
		}
	}
	
	/*
	 * @desc 
	 */
	public function addFuncParamValue($alias, $keyValue=array()){
		if(empty($keyValue)) return FALSE;
		foreach($keyValue as $paramKey => $paramValue){
			$this->callFunc[$alias]['paramValue'][$paramKey]=$paramValue;
		}
	}
	
	/*
	 * @disable header build
	
	public function disableHeader(){
		$this->build['header']=FALSE;
	}
	 */
	
	
	
	
	/*
	 * @get query , will only work once xgrid was built
	 */
	public function getBuiltSql(){
		return $this->sql;
	}
	
	/*
	 * @get query , will only work once xgrid was built
	 */
	public function getBuiltWhere(){
		return $this->sqlChunkWhere;
	}
	
	
	/*
	 * @desc set wide mode
	 * @var bool $bool
	 */
	public function setWideMode($bool){
		$this->wideMode=(bool)$bool;
	}
	
	
	/*
	 * @desc append html before closing form tag
	 * @param string $html
	 */
	public function appendForm($html){
		$this->appendForm=$html;
	}
	
	/*
	 * @desc prepend html after opening form tag
	 * @param string $html
	 */
	public function prependForm($html){
		$this->prependForm=$html;
	}
	
	/*
	 * @desc disable from tag wrapping, allowing custom form wrapping
	 * @param bool $bool
	 */
	public function disableFormWrap($bool){
		$this->disableFormWrap=(bool)$bool;
	}
	
	
	/*
	 * @desc under dev do not use
	 */
	public function allowJqEdit($alias, $jq=''){
		$this->allowEdit[$alias]=$jq;
	}
	
	public function setResultPerPage($line){
		$this->resultPerPage=(int)$line;
	}
	
	/*
	 * @desc for multiple DB connection
	 * @param string $db : DB Name
	 */
	public function setMysqlLink($link){
		$this->mysqlLink = $link;
	}
	
	
	

	
	/*
	 * @desc
	 */
	public function initEditableCombo($idx,$combo){
		$this->jsonRO->editableCombo[$idx]=$combo;
	}
	
	/*
	 * @desc
	 */
	public function isEditableCombo($alias, $combo, $aliasAsValue, $aliasAsLabel=''){
		$this->jsonRO->editableCombo[$alias]=$combo;
		$this->c->$alias->isEditableCombo=TRUE;
		$this->c->$alias->isEditableComboAliasAsValue=$aliasAsValue;
		$this->c->$alias->isEditableComboAliasAsLabel=(empty($aliasAsLabel)?$alias:$aliasAsLabel);
	}
	
	/*
	 * @desc 
	 */
	public function getActiveFilter(){
		return $this->filterInput;
	}
	
	
	/*
	 * @desc disable value type formatting @see this::typeFormat()
	* @param string $alias
	 */
	public function noFormat($alias){
		$this->noFormat[$alias]=TRUE;
	}
	
	/*
	 * @desc process type formatting before applying replace @see this::typeFormat() , this::applyReplace()
	* @param string $alias
	 */
	public function formatBeforeReplace($alias){
		$this->formatBeforeReplace[$alias]=TRUE;
	}
	
	
	/*
	 * @desc disable profile management
	 */
	public function disableProfile(){
		$this->disableProfile=TRUE;
	}
	
	
	
	/*
	 * @desc force form action url
	 */
	public function setFormAction($url){
		$this->formAction=$url;
	}
	
	/*
	 * @desc set column display sequence
	 * @var array $seq
	 * @comment specified aliases will be displayed in array order, followed by other aliases
	 */
	public function setColSequence($seq=array()){
		if(!is_array($seq)) $seq=array($seq);
		$this->colSequence=$seq;
	}
	
	
	
	/*
	 * @desc swap alias by auto-generated memory table join
	 * @param string $alias
	 * @var array swap couples
	 */
	public function superSwap($alias, $couples){
		$this->superSwap[$alias]=$couples;
		//required prior to json filter
		$this->aliasMap[$alias]='m_xgrid_swap_'.$alias.'.afterswap';
	}
	
		
	//Function to seperate multiple tags one line
	private function fix_newlines_for_clean_html($fixthistext)
	{
		$fixthistext_array = explode("\n", $fixthistext);
		foreach ($fixthistext_array as $unfixedtextkey => $unfixedtextvalue)
		{
			//Makes sure empty lines are ignores
			if (!preg_match("/^(\s)*$/", $unfixedtextvalue))
			{
				$fixedtextvalue = preg_replace("/>(\s|\t)*</U", ">\n<", $unfixedtextvalue);
				$fixedtext_array[$unfixedtextkey] = $fixedtextvalue;
			}
		}
		return implode("\n", $fixedtext_array);
	}
	
	private function cleanHtmlCode($uncleanhtml)
	{
		//Set wanted indentation
		$indent = "  ";
	
	
		//Uses previous function to seperate tags
		$fixed_uncleanhtml = $this->fix_newlines_for_clean_html($uncleanhtml);
		$uncleanhtml_array = explode("\n", $fixed_uncleanhtml);
		//Sets no indentation
		$indentlevel = 0;
		foreach ($uncleanhtml_array as $uncleanhtml_key => $currentuncleanhtml)
		{
			//Removes all indentation
			$currentuncleanhtml = preg_replace("/\t+/", "", $currentuncleanhtml);
			$currentuncleanhtml = preg_replace("/^\s+/", "", $currentuncleanhtml);
			
			$replaceindent = "";
			
			//Sets the indentation from current indentlevel
			for ($o = 0; $o < $indentlevel; $o++)
			{
				$replaceindent .= $indent;
			}
			
			//If self-closing tag, simply apply indent
			if (preg_match("/<(.+)\/>/", $currentuncleanhtml))
			{ 
				$cleanhtml_array[$uncleanhtml_key] = $replaceindent.$currentuncleanhtml;
			}
			//If doctype declaration, simply apply indent
			else if (preg_match("/<!(.*)>/", $currentuncleanhtml))
			{ 
				$cleanhtml_array[$uncleanhtml_key] = $replaceindent.$currentuncleanhtml;
			}
			//If opening AND closing tag on same line, simply apply indent
			else if (preg_match("/<[^\/](.*)>/", $currentuncleanhtml) && preg_match("/<\/(.*)>/", $currentuncleanhtml))
			{ 
				$cleanhtml_array[$uncleanhtml_key] = $replaceindent.$currentuncleanhtml;
			}
			//If closing HTML tag or closing JavaScript clams, decrease indentation and then apply the new level
			else if (preg_match("/<\/(.*)>/", $currentuncleanhtml) || preg_match("/^(\s|\t)*\}{1}(\s|\t)*$/", $currentuncleanhtml))
			{
				$indentlevel--;
				$replaceindent = "";
				for ($o = 0; $o < $indentlevel; $o++)
				{
					$replaceindent .= $indent;
				}
				
				$cleanhtml_array[$uncleanhtml_key] = $replaceindent.$currentuncleanhtml;
			}
			//If opening HTML tag AND not a stand-alone tag, or opening JavaScript clams, increase indentation and then apply new level
			else if ((preg_match("/<[^\/](.*)>/", $currentuncleanhtml) && !preg_match("/<(link|meta|base|br|img|hr)(.*)>/", $currentuncleanhtml)) || preg_match("/^(\s|\t)*\{{1}(\s|\t)*$/", $currentuncleanhtml))
			{
				$cleanhtml_array[$uncleanhtml_key] = $replaceindent.$currentuncleanhtml;
				
				$indentlevel++;
				$replaceindent = "";
				for ($o = 0; $o < $indentlevel; $o++)
				{
					$replaceindent .= $indent;
				}
			}
			else
			//Else, only apply indentation
			{$cleanhtml_array[$uncleanhtml_key] = $replaceindent.$currentuncleanhtml;}
		}
		//Return single string seperated by newline
		return implode("\n", $cleanhtml_array);	
	}
	
	/*
	 * @desc enable result array.
	 * @var bool $bool
	 */
	public function enableResultArray($bool=TRUE){
		$this->enabled('resultArray',$bool);
	}
	/*
	 * @desc get result array. result array must be explicitly enabled first with this::enableResultArray()
	 */
	public function getResultArray(){
		return $this->resultArray;
	}
	

	/*
	 * @desc enable pdf print
	 * @var bool $bool default TRUE
	 */
	public function enablePdfPrint($bool=TRUE){
		$this->enabled('pdfPrint',$bool);
	}
	
	/*
	 * @desc disable pdf print
	 * @var bool $bool default TRUE
	 */
	public function disablePdfPrint($bool=TRUE){
		$this->disabled('pdfPrint',$bool);
	}
	
	/*
	 * @desc set pdf engine include
	 * @param string $param
	 */
	public function setPdfEngineInclude($param){
		$this->param('pdfEngineInclude',$param);
		$this->enabled('pdfEngine', TRUE);
	}
	
	/*
	 * @desc enable excel export
	 * @var bool $bool default TRUE
	 */
	public function enableExcelExport($bool=TRUE){
		$this->enabled('excelExport',$bool);
	}
	
	/*
	 * @desc disable excel export
	 * @var bool $bool default TRUE
	 */
	public function disableExcelExport($bool=TRUE){
		$this->disabled('excelExport',$bool);
	}
	
	/*
	 * @desc set pdf engine include
	 * @param string $param
	 */
	public function setExcelExportInclude($param){
		$this->param('excelExportInclude',$param);
		$this->enableExcelExport(TRUE);
	}
	
	/*
	 * @desc enable user profile
	 * @var bool $bool default TRUE
	 */
	public function enableUserProfile($bool=TRUE){
		$this->enabled('userProfile',$bool);
	}	
	/*
	 * @desc set user profile id
	 * @param string $param
	 */
	public function setUserProfileId($param){
		$this->param('userProfileId',$param);
		$this->enabled('userProfile', TRUE);
	}
	/*
	 * @desc enable date picker
	 * @var bool $bool default TRUE
	 */
	public function enableDatePicker($bool=TRUE){
		$this->enabled('datePicker',$bool);
	}	
	/*
	 * @desc enable verbose mode
	 * @var bool $bool default TRUE
	 */
	public function enableVerboseMode($bool=TRUE){
		$this->enabled('verboseMode',$bool);
	}	
	/*
	 * @desc enable verbose mode
	 * @var bool $bool default TRUE
	 */
	public function enableDbug($bool=TRUE){
		$this->enabled('dbug',$bool);
		$this->dbug=$bool; //TODO use enabled('dbug') instead
	}
	
	/*
	 * @desc set query function used to query mysql
	 * @param string $param
	 */
	public function setQueryFunction($param){
		$this->param('queryFunction',$param);
	}
	/*
	 * @desc
	 */
	public function setProfileDefaultTable($bool=TRUE){
		$this->param('profileDefaultTable',$bool);
	}
	
	/*
	 * @desc add hidden fields to be posted with xGrid
	 */
	public function addPost($post){
		foreach($post as $name=>$value)	$this->appendForm('<input type="hidden" name="'.$name.'" value="'.$value.'"/>');
	}
	
	/*
	 * @desc set first sort order
	 * @param string $key
	 */
	public function firstSort($key, $order='DESC'){
		$this->firstSort[$key]=$order;
		
	}
	
	/*
	 * @desc define render output to file
	 */
	private function renderToFile(){
		$this->renderToScreen=FALSE;
		$this->renderToFile=TRUE;
	}
	
	
	
	/*
	 * @desc val appending
	 * @param string $key
	 * @param string $string
	 */
	public function valAppend($key, $string){
		$this->val[$key][]=(object) array ('do'=>'append', 'string' => $string);
	}
	
	/*
	 * @desc conditional val appending
	 * @param string $key
	 * @param string $string
	 * @param string $ifKey
	 */
	public function valAppendIf($key, $string, $ifKey){
		$this->val[$key][]=(object) array ('do'=>'append', 'string' => $string, 'ifKey' => $ifKey);
	}
	
	/*
	 * @desc val prepending
	 * @param string $key
	 * @param string $string
	 */
	public function valPrepend($key, $string){
		$this->val[$key][]=(object) array ('do'=>'prepend', 'string' => $string);
	}
	
	/*
	 * @desc conditional val prepending
	 * @param string $key
	 * @param string $string
	 * @param string $ifKey
	 */
	public function valPrependIf($key, $string, $ifKey){
		$this->val[$key][]=(object) array ('do'=>'prepend', 'string' => $string, 'ifKey' => $ifKey);
	}
	
	/*
	 * @desc nl2bt
	 * @param string $key
	 */
	public function valNl2br($key){
		$this->val[$key][]=(object) array ('do'=>'nl2br');
	}

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	#EDITMODE>
		#BUILD>
		/*
		 * @desc deprecated method alias, use this::enableCellEdit() instead
		 */
		public function isEditable($idx){
			$this->isEditable[$idx]=TRUE;
		}
		
		/*
		 * @desc deprecated method alias, use this::enableCellEdit() instead
		 */
		public function enableEdit(){
			$idxList=func_get_args();
			foreach($idxList as $idx){
				$this->isEditable[$idx]=TRUE;
			}
		}
		
		/*
		 * @desc experimental, do not use
		 * @param string|array $keyList
		 * @see this::wasEdited(), this::getEditValue()
		 */
		public function enableCellEdit($keyList){
			if(!is_array($keyList)) $keyList=array($keyList);
			foreach($keyList as $key){
				$this->isEditable[$key]=TRUE;
			}
		}
		
		/*
		 * @desc experimental, ask CC
		 * @param string $key
		 * @param string|array $keyListAsParamList
		 */
		public function setCellEditParam($key, $keyListAsParamList){
			$this->cellEditParam[$key]=$keyListAsParamList;
		}
		
		/*
		 * @desc experimental, do not use
		 */
		public function isButton($idx){
			$this->isButton[$idx]=TRUE;
		}
		
		/*
		 * @desc experimental, do not use
		 */
		public function isDelete($idx){
			$this->isDelete[$idx]=TRUE;
			$this->isButton[$idx]=TRUE;
		}
		
		/*
		 * @desc set ajax callback destination in edit mode
		 */
		public function setEditableAjax($to){
			$this->editableAjax=$to;
		}
		
		/*
		 * @desc use a row id based on an alias in edit mode
		 */
		public function setEditableRowId($alias){
			$this->editableRowId=$alias;
		}
	
		/*
		 * @desc DO NOT USE allow editable add row
		 */
		public function allowEditableNew(){
			$this->allowEditableNew=TRUE;
		}
		#<BUILD
		#PROC>
		/*
		 * @desc is there a xGrid edit to process?
		 * @comment dont forget to include xGrid and avoid any html output at this point as it's AJAX. sit back and relax.
		 * @see this::enableCellEdit()
		 * @return bool
		 */
		public static function wasEdited(){
			return ( isset($_POST['_xGridEdit']) AND isset($_POST['_xGridId']) );	
		}
		/*
		 * @desc get edited field value.
		 * @comment its the user's input you get here.right, you guessed it clever boy.
		 * @see this::enableCellEdit()
		 * @return mixed|NULL
		 */
		public static function getEditValue(){
			return (isset($_POST['_value']) ? $_POST['_value']:NULL);	
		}
		/*
		 * @desc get edited field custom row id
		 * @comment this one was lonely without comment, here it is.
		 * @see this::enableCellEdit(), this::editableRowId()
		 * @return mixed|NULL
		 */
		public static function getEditRowId(){
			return (isset($_POST['_rowId']) ? $_POST['_rowId']:NULL);	
		}
		
		/*
		 * @desc get edited xGrid id
		 * @comment TO USE in case there are multiple xGrid with editable field per screen, we dont want to mess up the whole thing, okay?
		 * @see this::enableCellEdit()
		 * @return string|NULL
		 */
		public static function getEditXGridId(){
			return (isset($_POST['_xGridId']) ? $_POST['_xGridId']:NULL);	
		}		

		/*
		 * @desc get edited field alias
		 * @comment TO USE in case there are multiple edit fields per row, we dont want to mix'em, don't we?
		 * @see this::enableCellEdit()
		 * @return mixed|NULL
		 */
		public static function getEditAlias(){
			return (isset($_POST['_alias']) ? $_POST['_alias']:NULL);	
		}
		/*
		 * @desc get edited field additional param
		 * @param string 
		 * @comment TO USE in case a single row id (getEditRowId) is not enough, or simply if we need more stuff on the other side of the tunnel
		 * @see this::setCellEditParam()
		 * @return mixed|NULL
		 */
		public static function getEditParam($param){
			return (isset($_POST[$param]) ? $_POST[$param]:NULL);	
		}
		
		#<PROC
	#<EDITMODE
	
}



















































































































/*
 * @desc chainable conditions or group of conditions
 */
class xGridWhere{
	private $sub=array();
	private $glue='AND';
	private $groupGlue='AND';

	public $alias='';
	public $field;
	public $operator='=';
	public $value;
	
	
	public function __construct(){
		
	}
	public function isGroup(){
		return !empty($this->sub);
	}
	public function add($sub){
		$this->sub[]=$sub;
	}
	public function setGlue($glue){
		$this->glue = $glue;
	}
	public function setGroupGlue($glue){
		$this->groupGlue=$glue;
	}
	public function build(){
		$html=' '.$this->glue.' ';
		if($this->isGroup()){
			if($this->groupGlue==='AND')
				$html.='(1=1';
			else $html.='(0=1';
			foreach($this->sub as &$sub){
				$sub->setGlue($this->groupGlue);
				$html.=$sub->build();
			}
			$html.=')';
		}else{
			$wrapper="'";
			switch($this->operator){
				case '!=':
				case '=':
				case '<':
				case '>':
				case '<=':
				case '>=':
					if(is_numeric($this->value)){
						$html.=$this->field.$this->operator.$this->value;
					}else{
						$html.=$this->field.$this->operator."'".$this->value."'";
					}
					
				break;
				case '%':
					$html.=$this->field." LIKE '%".$this->value."%'";
				break;
				case 'JOKER':
					$html.=$this->field." LIKE '".$this->value."'";
				break;
				case '~':
					$html.=$this->field." SOUNDS LIKE '%".$this->value."%'";
				break;
				case 'IN':
					foreach($this->value as &$val) $val=$wrapper.$val.$wrapper;
					$html.=$this->field." IN (".implode(',', $this->value).")";
				break;
			}			
		}
		return $html;
	}
	public function issetAlias($alias, $operator=''){
		if($this->isGroup()){
			foreach($this->sub as &$sub){
				if($sub->issetAlias($alias, $operator)) return TRUE;
			}
		}else{
			if($this->alias==$alias AND (empty($operator)? TRUE:($this->operator==$operator) ) ) return TRUE;
			return FALSE;
		}
	}
}
	

class xGridReplace{
	public $type='replace';
	public $search;
	public $replace;
	public $subject;
	public $count;
	public function __construct($search=array(), $replace=array(), $subject='', $count=FALSE){
		$this->search=$search;
		$this->replace=$replace;
		$this->subject=$subject;
		$this->count=$count;
	}
}

class xGridCase{
	public $type='case';
	public $alias=array();
	public $value=array();
	public $xGridReplace=array();
	public $operator=array();
	public function __construct(){
		
	}
	/*
	 * @desc add a conditional replace
	 * @param string $alias 
	 * @param string $value replace if
	 * @param string $xGridReplace replace by
	 * @param string $operator default is '='
	 */
	public function add($alias, $value, $xGridReplace, $operator='='){
		$this->alias[]=$alias;
		$this->value[]=$value;
		$this->xGridReplace[]=$xGridReplace;
		$this->operator[]=$operator;
	}
	
	
	
}


/*
 * @desc html formating directive indent
 */
class sourceIndent{
	private $deep=0; //@var int current deep , current tabulation =( deep * t )
	private $n="\n"; //@var string line break
	private $t="\t"; //@var string tabulation
	/*
	 * @desc
	 */
	public function __construct(){
		
	}
	/*
	 * @desc set n (line break)
	 */
	public function setN($str){
		$this->n=$str;
	}
	/*
	 * @desc set t (tabulation)
	 */
	public function setT($str){
		$this->t=$str;
	}
	
	/*
	 * @desc insert line break using current tabulation
	 */
	public function br(){
		$html='';
		if($this->deep>0) $html.=str_repeat($this->n, $this->deep);
		return $html.$this->n;
	} 
	/*
	 * @desc insert line break, after decreasing deep (higher then br)
	 */
	public function hbr(){
		$this->deep--;
		$this->br();
	}
	/*
	 * @desc insert line break, after increasing deep (deeper then br)
	 */
	public function dbr(){
		$this->deep++;
		$this->br();
	}
}



/*
 * @desc mysql query parser
 * @param string $sql
 */
 
class mxSql{
	private $sql; //@var string raw query
	private $select=''; //@var string SELECT section
	private $from=''; //@var string FROM section
	private $where=''; //@var string WHERE section
	private $groupby=''; //@var string GROUP BY section
	//TODO private $having=''; //@var string HAVING section
	private $orderby=''; //@var string ORDER BY section
	private $limit=''; //@var string LIMIT section
	
	private $expr=array(); //@var array []=>expression SELECT expression (a column with optional alias)
	private $alias=array(); //@var array [alias]=>expressionIndex SELECT expression (a column with optional alias)
	
	
	public function __construct($sql){
		$this->sql=$sql;
		$this->getChunk();
		$this->parseSelect();
	}
	
	
	/*
	 * @desc populate expr and expr aliases
	 */
	public function parseSelect(){
		
		$select=substr($this->select, stripos($this->select,'SELECT')+6);
		#A>
		//split selected fields with their optional alias, differentiating comas types. add ending coma to process last field
		$a=str_split($select.',');
		$deep=0; //subquery deep
		$selectSplit=array();
		
		$cursor=0; //parsing cursor
		$cursorOffset=0; //parsing cursor splitting offset
		//parse each char
		foreach($a as &$c){
			switch($c){
				//differentiate coma
				case ",":
					//coma is field separator if subquery deep is 0 (root)
					if($deep==0){
						//store field/alias couple, field can thus be a subquery minus ending coma
						$this->expr[]=trim(substr($select,$cursorOffset+1,$cursor-$cursorOffset-1));
						$cursorOffset=$cursor;
					}
				break;
				//increase subquery deep level
				case '(':
					$deep++;
				break;
				//decrease subquery deep level
				case ')':
					$deep--;
				break;
			}
			//inc current parsing cursor
			$cursor++;
		}
		#<A
		#B>
		//$selectSplit=explode(',',$select);
		foreach($this->expr as $idx=>&$field){
			$asPos=stripos($this->getSqlBlankedBracket($field), ' as ');
			if($asPos>0){ //alias found
				$alias=substr($field, $asPos+4);
				$this->alias[$alias]=$idx;
			}
		}
		#<B
	}
	
	
/*
	 * @desc blank anything within bracket
	 * @todo take into account where x=y when y contains '(' or ')'
	 */
	private function getSqlBlankedBracket($sql){
		$deep=0; //subquery deep, 0=root

		$blankedSql='';
		$len=strlen($sql);
		//parse each char	
		for($i=0;$i<$len;$i++){
			$c=substr($sql,$i,1);
			switch($c){
				//increase subquery deep level
				case '(':
					$deep++;
					$blankedSql.=$c;
				break;
				//decrease subquery deep level
				case ')':
					$deep--;
					$blankedSql.=$c;
				break;
				default:
					if($deep==0){
						$blankedSql.=$c;
					}else{
						$blankedSql.=' ';
					}
			}
			
		}
		return $blankedSql;
	}
	
	/*
	 * @desc flatten to root level
	 * @param string $str
	 * @return string
	 */
	private function flattenRootLevel($str){
		//split selected fields with their optional alias, differentiating comas types. add ending coma to process last field
		$a=str_split($str.',');
		$deep=0; //subquery deep
		$selectSplit=array();
		foreach($a as &$c){
			
		}
		
		
		$cursor=0; //parsing cursor
		$cursorOffset=0; //parsing cursor splitting offset
		//parse each char
		foreach($a as &$c){
			switch($c){
				//differentiate coma
				case ",":
					//coma is field separator if subquery deep is 0 (root)
					if($deep==0){
						//store field/alias couple, field can thus be a subquery minus ending coma
						$selectSplit[]=trim(substr($str,$cursorOffset+1,$cursor-$cursorOffset-1));
						$cursorOffset=$cursor;
					}
				break;
				//increase subquery deep level
				case '(':
					$deep++;
				break;
				//decrease subquery deep level
				case ')':
					$deep--;
				break;
			}
			//inc current parsing cursor
			$cursor++;
		}
		return implode('',$a);
	}
	
	/*
	 * @desc remove query variable
	 * @param string $str
	 * @return string
	 */
	private function blankVar($str){
		$a=str_split($str);
		$i=0;
		$open=FALSE;
		foreach($a as &$c){
			switch($c){
				
				case "'":
				case '`':
					$open=!$open;
				break;
				
				case '(':
				
					$i++;
				break;
				case ')':
					$i--;
				break;
				default:
					if($i>0 OR $open) $c=' ';
			}
		}
		return implode('',$a);
	}
	
	/*
	 * @desc split select expression into pratical chunks
	 * @param string $sql query
	 * @comment 
	 * first step: blank "false" keywords
	 * second step: split into chunks using keyword list
	 */
	private function getChunk(){		
		//First step		
		$sqlClean=$this->blankVar($this->sql);
		//Second step
		//$chunk=array();
		$keyword=array('select'=>'SELECT ', 'from'=>' FROM ', 'where'=>' WHERE ', 'groupby'=>' GROUP BY ', 'orderby'=>' ORDER BY ', 'limit'=>' LIMIT ');
		$pos=array();
		$len=array();
		$sqlLen=strlen($sqlClean);
		$keywordParse=array_reverse($keyword);
		$endLen=0;
		foreach($keywordParse as $key => $pattern){
			$pos[$key]=stripos($sqlClean,$pattern);	
			$len[$key]=($pos[$key]===FALSE ? 0:$sqlLen-$pos[$key]-$endLen);
			$endLen+=$len[$key];
			$this->{$key}=trim(substr($this->sql, $pos[$key], $len[$key]));
			//$chunk[$key]=trim(substr($this->sql, $pos[$key], $len[$key]));
		}
	}
	
	
	/*
	 * @desc set expr by alias
	 * @param string $alias
	 * @param string $str new expr value
	 */
	public function setExprByAlias($alias, $str){
		if(!isset($this->alias[$alias])) return FALSE;
		$this->expr[$this->alias[$alias]]=$str;
		return TRUE;
	}	
	/*
	 * @desc rebuild select chunk from expr
	 */
	public function rebuildSelect(){
		$this->select="SELECT ".implode(', ',$this->expr);
	}
	/*
	 * @desc get select section
	 */
	public function getSelect(){
		return $this->select;
	}
	

}










































?>