<?php

class uploadFile{
	//declaration variable public
	public $public;
	public $mkdir_mode = 0777;
	public $mkdir_recursive = true;
	public $input_name= 'file';
	
	//declaration variable private
	private $private;
	private $upload_dir = '';		
	private $upload_tempFilename = '';
	private $upload_tempFilepath = '';
	private $upload_Filename = '';
	private $upload_Filedata = '';
	private $upload_error = '';		
	
	//declaration variable protected
	protected $protected;				
	
	//declaration constante
	const CLASSNAME = 'uploadFile';	
	
	function __construct($input_field=''){
		if(!empty($input_field)) $this->input_name = $input_field;
		//echo 'construction objet: ' . $this->name;
		if((isset($_FILES[$this->input_name]))){
			if(($_FILES[$this->input_name]['error'] == UPLOAD_ERR_OK)){
				$this->upload_Filedata = $_FILES[$this->input_name]; 
				$this->upload_tempFilename = basename($_FILES[$this->input_name]['tmp_name']);
				$this->upload_tempFilepath = $_FILES[$this->input_name]['tmp_name'];
				$this->upload_Filename = $this->removeaccents($_FILES[$this->input_name]['name']);
			}else{
				//retour si une erreur c'est produite
				switch ($_FILES[$this->input_name]['error']){
					case UPLOAD_ERR_INI_SIZE:
						$this->upload_error .= ' [UPLOAD init] Le fichier excede la taille fixee par le serveur ('.ini_get("upload_max_filesize").')';
						break;
					case UPLOAD_ERR_FORM_SIZE:
						$this->upload_error .= ' [UPLOAD init] Le fichier excede la taille fixee par cette page ('.$_POST['MAX_FILE_SIZE'].')';
						break;
					case UPLOAD_ERR_PARTIAL:
						$this->upload_error .= ' [UPLOAD init] Le fichier n\'a ete que partiellement charge';
						break;
					case UPLOAD_ERR_NO_FILE:
						$this->upload_error .= ' [UPLOAD init] Aucun fichier telecharge';
						break;
					case UPLOAD_ERR_NO_TMP_DIR:
						$this->upload_error .= ' [UPLOAD init] Le dossier de fichier temporaire n\'est pas definit';
					default:
						$this->upload_error .= ' [UPLOAD init] Erreur non definit';
				}//end switch
				$this->upload_tempFilename = -1;
			}//end else
		}//end if

	}//end of class constructor
	
	/* class destructor */
	function __destruct(){
		//echo 'destruction objet: ' . $this->name;
	
	}//end of class destructor


	
	/* ----------------- METHOD ----------------- */				
	public function setDir($directory){
		if(!empty($directory)){
			if(!is_dir($directory)){
				mkdir($directory, $this->mkdir_mode, $this->mkdir_recursive);
				if(!is_dir($directory)){ 							// si le dossier n'a pas été créé
					$directory = str_replace('/', '\\', $directory); //modifie le separateur de dossier (compatibilite windows)
					mkdir($directory, $this->mkdir_mode, $this->mkdir_recursive);
				}
				if(!is_dir($directory)){
					$this->upload_error .= ' <br/>[UPLOAD dir] Le dossier n a pas pu etre crée';
				}
			}
			$this->upload_dir = $directory;
		}else{
			$this->upload_error .= ' <br/>[UPLOAD dir] Le dossier  est pas definit';
		}
	}
	
	public function setUnique($pref = ''){
		$uniqueId = time() % 100000000;
		$this->upload_Filename = $pref.$uniqueId."_".$this->upload_Filename;
	}
	
	public function setInput_name($name){		
		$this->input_name = $name;		
	}
	
	public function getInput_name(){		
		return $this->input_name;
	}	
 	
	public function removeaccents($string){
		$string = html_entity_decode($string);
		$search	= 'çñÄÂÀÁäâàáËÊÈÉéèëêÏÎÌÍïîìíÖÔÒÓöôòóÜÛÙÚüûùúµ';
		$replace	= 'cnaaaaaaaeeeeeeeeeiiiiiiiioooooooouuuuuuuuu';
		$string = strtr(trim(strtolower($string)), $search, $replace);
		$string = preg_replace('/[^a-z_0-9.@-]/', '', $string);

		return $string;
	}	
 	
	public function getTempFile(){
		if(($_FILES[$this->input_name]['error'] == UPLOAD_ERR_OK)){		
				//fichier temporaire
				$this->upload_tempFilename = basename($_FILES[$this->input_name]['tmp_name']);
				$this->upload_tempFilepath = $_FILES[$this->input_name]['tmp_name'];
				//fichier source
				$this->upload_Filedata = $_FILES[$this->input_name]; 	
				$this->upload_Filename = $this->removeaccents($_FILES[$this->input_name]['name']);
		}else{
			//retour si une erreur c'est produite
			switch ($_FILES[$this->input_name]['error']){
				case UPLOAD_ERR_INI_SIZE:
					$this->upload_error .= ' Le fichier excede la taille fixee par le serveur ('.ini_get("upload_max_filesize").')';
					break;
				case UPLOAD_ERR_FORM_SIZE:
					$this->upload_error .= ' Le fichier excede la taille fixee par cette page ('.$_POST['MAX_FILE_SIZE'].')';
					break;
				case UPLOAD_ERR_PARTIAL:
					$this->upload_error .= ' Le fichier n\'a ete que partiellement charge';
					break;
				case UPLOAD_ERR_NO_FILE:
					$this->upload_error .= ' Aucun fichier telecharge';
					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$this->upload_error .= ' Le dossier de fichier temporaire n\'est pas definit';
				default:
					$this->upload_error .= ' Erreur non definit';
			}//end switch
			$this->upload_tempFilename = -1;
		}//end else
		
		return $this->upload_tempFilename;
	}
	
	public function moveFile($destination=''){
		$res = false;
		if(!empty($this->upload_tempFilename) && !empty($this->upload_Filename)){
			if(!empty($destination)){
				//$destination = str_replace('/', '\\', $destination); //modifie le separateur de dossier (compatibilite windows)
				if(is_dir($destination)){
					if(substr($destination, -1, 1) != '/'){
						$this->upload_dir = $destination.'/';
					}
					$res = move_uploaded_file($this->upload_tempFilepath, $this->upload_dir.$this->upload_Filename);
					if(!$res){ 
						$this->upload_error .= ' <br/>[UPLOAD MOVE] copie non effectuee ('.$this->upload_tempFilepath.$this->upload_tempFilename.' ==> '.$this->upload_dir.$this->upload_Filename.')';
					}
				}else{
					$this->upload_error .= ' <br/>[UPLOAD MOVE] le dossier destination n est pas valide ('.$destination.')';
				}
			}elseif(!empty($this->upload_dir)){				
				//$this->upload_dir = str_replace('/', '\\', $this->upload_dir); //modifie le separateur de dossier (compatibilite windows)
				if(substr($this->upload_dir, -1, 1) != '/'){
					$this->upload_dir = $this->upload_dir.'/';
				}
				//echo "<br>move from : ".$this->upload_tempFilepath;
				//echo "<br>move to : ".$this->upload_dir.$this->upload_Filename;
				$res = move_uploaded_file($this->upload_tempFilepath,$this->upload_dir.$this->upload_Filename);
				//echo "<br> Res : ".$res;
			}else{
				$this->upload_error .= ' <br/>[UPLOAD MOVE] le dossier destination n est pas definit';
			}//end elseifelse

		}else{			
			$this->upload_error .= ' <br/>[UPLOAD MOVE] le fichier source ou le fichier destination n est pas valide';
			$res = false;
		}//end else
		if(!$res){ 
			$this->upload_error .= ' <br/>[UPLOAD MOVE] le fichier n est pas telecharge';
		}
		return $res;
	}//end of method moveFile
	
	public function getError(){
		return $this->upload_error;
	}
	
	
	public function setDirMod($chmod){
		
		if(strlen($chmod) != 4 && is_numeric($chmod)){
			$this->mkdir_mode = $chmod;
		}elseif(strlen($chmod) == 3 && is_numeric($chmod)){
			$this->mkdir_mode = '0'.$chmod;
		}else{
			$this->upload_error .= ' [UPLOAD dir mode] le parametre passe n est pas valide ('.$chmod.')(format attendu: 0000 <-> 0777)';
		}
		return $this->mkdir_mode;
		
	}//end of method setDirMod
	
	
	/*
	* Methode: recupere le mode actuel pour la creation des dossiers
	*
	*	@return  valeur du mode
	*/
	public function getDirMod(){
	
		return $this->mkdir_mode;
	}//end of method getDirMod
	
	/*
	* Methode: recuperation du parametre serveur retournant la taille maximun d'upload
	*
	*	@return taille maximun du fichier d'upload
	*
	*/
	public function getServerUploadMaxSize(){
		return ini_get('upload_max_filesize');
	}//end of method getServerUploadMaxSize
	
	/*
	* Methode: modification du parametre serveur sur la taille maximun d'upload
	*
	*	@param int $size : taille max d'upload
	*
	*	@return taille maximun du fichier d'upload
	*
	*/
	public function setServerUploadMaxSize($size){
		ini_set('upload_max_filesize', (int)$size);
		return ini_get('upload_max_filesize');
	}//end of method setServerUploadMaxSize
	
	/*
	* Recupere le nom du fichier uploader
	*
	*	@return le nom du fichier uploader ou -1 si les donnees sur le fichier sont manquantes
	*/
	public function getFilename(){
		if(!empty($this->upload_Filename)){
			return $this->upload_Filename;
		}else{
			$this->upload_error .= ' <br/>[UPLOAD ] nom du fichier introuvable';
			return -1;
		}
	}//end of method getFilename
	
	/*
	* Recupere le nom du fichier uploader
	*
	*	@return le nom du fichier uploader ou -1 si les donnees sur le fichier sont manquantes
	*/
	public function getFileData(){
		if(is_array($this->upload_Filedata)){
			return $this->upload_Filedata;
		}else{
			$this->upload_error .= ' <br/>[UPLOAD ] informations introuvables';
			return -1;
		}
	}//end of method getFilename
	
	/*
	* Recupere le type du fichier uploader
	*
	*	@return le type du fichier uploader ou -1 si les donnees sur le fichier sont manquantes
	*/
	public function getFileType(){
		if(is_array($this->upload_Filedata) && !empty($this->upload_Filedata['type'])){
			return $this->upload_Filedata['type'];
		}else{
			$this->upload_error .= ' <br/>[UPLOAD ] informations introuvables';
		}
	}//end of method getFileType
	
	/*
	* Recupere la taille du fichier uploader
	*
	*	@return la taille du fichier uploader ou -1 si les donnees sur le fichier sont manquantes
	*/
	public function getFileSize(){
		if(is_array($this->upload_Filedata) && !empty($this->upload_Filedata['size'])){
			return $this->upload_Filedata['size'];
		}else{
			$this->upload_error .= ' <br/>[UPLOAD ] informations introuvables';
		}
	}//end of method getFileSize
	
	/*
	* Recupere le chemin du dossier de stockage des fichiers temporaires
	*
	*	@return le chemin du dossier de stockage des fichiers temporaires
	*/
	public function getTempDir(){
		if(isset($this->upload_tempFilepath) && !empty($this->upload_tempFilepath)){
			return dirname($this->upload_tempFilepath);
		}else{
			return ini_get('upload_tmp_dir');
		}
	}//end of method 
	
	/*
	* Recupere le chemin du dossier de stockage des fichiers temporaires
	*
	*	@return le chemin du dossier de stockage des fichiers temporaires
	*/
	public function setTempDir($tempDir){
	
		if(!empty($tempDir) && is_dir($tempDir)){
			ini_set('upload_tmp_dir', $tempDir);
			return ini_get('upload_tmp_dir');
		}else{
			return -1;
		}
	}//end of method 
	
	
}//end of class uploadFile

?>
