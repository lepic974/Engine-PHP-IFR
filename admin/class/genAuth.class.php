<?php
/* ==============================================
	developpement realise par CTH.
============================================== */
?>
<?php
class genAuth{
	/* ----------------- DECLARATION ----------------- */
	
	//declaration variable public
	public $public = '';
	
	//declaration variable private
	private $private;	
	private $session_type;
	
	//declaration variable protected
	protected $protected;	
	protected $redirect_url = '';
	protected $check_retry = 5;
	
	//declaration constante
	const CLASSNAME = 'genAuth';
	
	const SESSION_UNDEFINED = 'undefined';
	const SESSION_AM = USERSESSION;
	
	/* ----------------- CONSTRUCTOR ----------------- */
	/* class constructor */
	function __construct(){
		//echo 'construction objet: ' . $this->name;
		$this->identify_auth();

	}//end of class constructor
	
	/* class destructor */
	function __destruct(){
		//echo 'destruction objet: ' . $this->name;
	
	}//end of class destructor

	
	/* ----------------- METHOD ----------------- */
	
	/*
	 * identification du type de session
	 */
	public function identify_auth(){
		if(isset($_SESSION[USERSESSION]['user'])){
			$this->set_session_type(genAuth::SESSION_AM);
		}else{
			$this->set_session_type(genAuth::SESSION_UNDEFINED);
		}
	}//end of session_auth
	
	/*
	 * verification of authentification
	 */
	public function verif_auth(){
		global $g_patron;
		
		if(isset($_SESSION[USERSESSION]['user'])){
			if(!defined('USER_ID')) define('USER_ID',$_SESSION[USERSESSION]['user']);
			return true;
		}else{
			return false;
		}
	}//end of method verif_auth
	
	/*
	 * load authentification information
	 */
	public function load_auth($login, $pass){
		global $g_user;
		
		$auth = false;

		if(!empty($pass)){ // empty password 
			$login=addslashes($login);
			
			$sql="SELECT * FROM ".T_USER." WHERE {$g_user['login']}='{$login}' AND {$g_user['desactiveON']}=0 AND {$g_user['isAdministrateurON']}=1 LIMIT 1;";
			$result=query($sql);		

			if(!$result)
				return false;

			$r=mysqli_fetch_assoc($result);
			$pass=md5(addslashes($pass));	//md5 encryption
			if( $r[$g_user['password']] == $pass){
				$auth = $r[$g_user['id']];
			}
			
			if($auth){ // set session infos
				$this->set_session_type(genAuth::SESSION_AM);
				$_SESSION[USERSESSION]['user']=$r[$g_user['id']];
				$_SESSION[USERSESSION]['user_id']=$r[$g_user['id']];
				$_SESSION[USERSESSION]['login']=$r[$g_user['login']];
				$_SESSION[USERSESSION]['lang']=$r[$g_user['fk_lang']];
				$_SESSION[USERSESSION]['name']=$r[$g_user['prenom']].' '.$r[$g_user['nom']];
				$_SESSION[USERSESSION]['right']['enable_menu']=$r[$g_user['enable_menu']];
				$_SESSION[USERSESSION]['right']['enable_article']=$r[$g_user['enable_article']];
				$_SESSION[USERSESSION]['right']['enable_actu']=$r[$g_user['enable_actu']];
				$_SESSION[USERSESSION]['right']['enable_photo']=$r[$g_user['enable_photo']];
				$_SESSION[USERSESSION]['right']['enable_event']=$r[$g_user['enable_event']];
				$_SESSION[USERSESSION]['right']['enable_param']=$r[$g_user['enable_param']];
				$_SESSION[USERSESSION]['right']['enable_user']=$r[$g_user['enable_user']];
			}	
		}//end if
		
		return $auth;	
	}// end of method load_auth
	
	/*
	 * unload authentification information
	 */
	public function unload_auth(){		
		unset($_SESSION[USERSESSION]);
		unset($_SESSION['_xgrid']);
		unset($_SESSION['s_orderby_t']);
	}//end of method unload_auth
	
	public function reload_auth($login, $pass){
		$this->load_auth($login, $pass);
	}

	public function redirect(){
		
		if($this->get_redirect_url()){
			header('location:'.$this->get_redirect_url());
			exit;
		}
	}
	
	/* ----------------- ACCESSOR ----------------- */
	public function set_redirect_url($url){
		$this->redirect_url = $url;
	}
	
	public function get_redirect_url(){
		return $this->redirect_url;
	}
	
	public function set_check_retry($num){
		$this->check_retry = $num;
	}
	
	public function get_check_retry(){
		return $this->check_retry;
	}
	
	protected function set_session_type($type){
		$this->session_type = $type;
	}
	
	public function get_session_type(){
		return $this->session_type;
	}

}//end of authentification class


?>