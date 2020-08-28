<?php

class page{

	private $header = '';
	private $footer = '';
	private $corps  = '';
	
	public function __construct($encapse_corps = true, $titre=''){
		if($encapse_corps){
			$this->build_header($titre);
			$this->build_footer();
		}
	}
	
	private function build_header($titre){
		$this->header = '<body>';

		if(isset($_SESSION[USERSESSION]['user']) && !empty($_SESSION[USERSESSION]['user'])){
			// Menu
            $this->header.= '<table class="main_tab">';
            $this->header.= '   <tr>';
            $this->header.= '       <td class="main_tab_menu">';
			$this->header.= '        	<div id="zone_menu" class="menu">';

			// Utilisateur
            $this->header.= '        		<div class="title_gray_frame" style="text-align:center;margin-bottom:20px;">Utilisateur</div>';

            $tmp_labels[]='<img src="pic/interface/icone_user.png" />';
            $tmp_fields[]=build_input('form_login',$_SESSION[USERSESSION]['name'],'',' size="25" class="input_login" style="width:90%;" autocomplete="off" disabled ');
            $this->header.= '		'.build_formTable($tmp_labels,$tmp_fields);

            $param=array();
            $param['mypic']='CANCEL';
            $param['label']='Déconnexion';
            $param['a'] = 'index.php?to=logout';
            $this->header.= '        		<div style="text-align:center;margin-top:20px;margin-bottom:40px; ">'.ubtn($param).'</div>';



            // Administration
            $this->header.= '        		<hr class="sep_titre" />';

			$this->header.= '        		<div class="title_gray_frame" style="text-align:center;margin-top:20px;margin-bottom:20px; ">Administration</div>';
			$this->header.= '        		<div id="menu">';
			$this->header.= '        			<h3>Site Public</h3>';
			$this->header.= '        			<div style="margin:0px; padding:0px; padding-top:5px;padding-bottom:5px;padding-left:15px;">';
            $this->header.= '        			</div>';
            $this->header.= '        			<h3>Paramêtres</h3>';
            $this->header.= '        			<div style="margin:0px; padding:0px; padding-top:5px;padding-bottom:5px;padding-left:15px;">';
            $this->header.= '        				<table style="width:100%;" cellspacing="0" cellpadding="0" border="0">';
            $this->header.= '        					<tr style="height: 20px;">';
            $this->header.= '        						<td style="width: 20px;">';
            $this->header.= '        							<img src="pic/interface/indent.png" />';
            $this->header.= '        						</td>';
            $this->header.= '        						<td>';
            $this->header.= '        							<a href="index.php?to=param" class="lien_menu">Paramètre Site</a>';
            $this->header.= '        						</td>';
            $this->header.= '        					</tr>';
            $this->header.= '        					<tr style="height: 20px;">';
            $this->header.= '        						<td style="width: 20px;">';
            $this->header.= '        							<img src="pic/interface/indent.png" />';
            $this->header.= '        						</td>';
            $this->header.= '        						<td>';
            $this->header.= '        							<a href="index.php?to=listing_user" class="lien_menu">Lister les Utilisateurs</a>';
            $this->header.= '        						</td>';
            $this->header.= '        					</tr>';
            $this->header.= '        					<tr style="height: 20px;">';
            $this->header.= '        						<td style="width: 20px;">';
            $this->header.= '        							<img src="pic/interface/indent.png" />';
            $this->header.= '        						</td>';
            $this->header.= '        						<td>';
            $this->header.= '        							<a href="index.php?to=user" class="lien_menu">Ajouter un Utilisateur</a>';
            $this->header.= '	        					</td>';
            $this->header.= '        					</tr>';
            $this->header.= '        				</table>';
            $this->header.= '        			</div>';
            $this->header.= '        			<h3>Options Back Office</h3>';
            $this->header.= '        			<div style="margin:0px; padding:0px; padding-top:5px;padding-bottom:5px;padding-left:15px;">';
            $this->header.= '        				<table style="width:100%;" cellspacing="0" cellpadding="0" border="0">';
            $this->header.= '        					<tr style="height: 20px;">';
            $this->header.= '        						<td style="width: 20px;">';
            $this->header.= '        							<img src="pic/interface/indent.png" />';
            $this->header.= '        						</td>';
            $this->header.= '        						<td>';
            $this->header.= '        							<a href="#" onclick="reset_interface();" class="lien_menu">Réinitialiser Interface</a>';
            $this->header.= '	        					</td>';
            $this->header.= '        					</tr>';
            $this->header.= '        					<tr style="height: 20px;">';
            $this->header.= '        						<td style="width: 20px;">';
            $this->header.= '        							<img src="pic/interface/indent.png" />';
            $this->header.= '        						</td>';
            $this->header.= '        						<td>';
            $this->header.= '        							<a href="../" target="_blank" class="lien_menu">Voir le Site</a>';
            $this->header.= '        						</td>';
            $this->header.= '        					</tr>';
            $this->header.= '        					<tr style="height: 50px;">';
            $this->header.= '        						<td style="width: 100%;" colspan="2">';
            $this->header.= '        							<div id="background_drag">Changer background...</div>';
            $this->header.= '        						</td>';
            $this->header.= '        					</tr>';
            $this->header.= '        				</table>';
            $this->header.= '	        		</div>';
            $this->header.= '	        	</div>';
            $this->header.= '	        	<div style="clear:both;"></div>';
            $this->header.= '	        </div>';


            $this->header.= '        </td>';
            $this->header.= '        <td class="main_tab_content">';

			// Main Div
			$this->header.= '        	<div id="main_div" class="main_div">';
			$this->header.= '        		<div class="title_gray_frame_contenu" >'.$titre.'</div>';
			$this->header.= '        		<div class="inner_main_div">';
            $this->header.= '        		    <div class="padding_main_div">';
        }
	}
	
	private function build_footer(){
        $this->footer = '	        	    </div>';
        $this->footer.= '	        	</div>';
        $this->footer.= '	        </div>';
        $this->footer.= '       </td>';
        $this->footer.= '   </tr>';
        $this->footer.= '</table>';
        $this->footer.= '</body>';
	}
	
	public function build_content($html=''){
		$this->corps = $html;
	}
	
	public function show(){
		echo $this->header;
		echo $this->corps;
		echo $this->footer;
	}
	
}

?>