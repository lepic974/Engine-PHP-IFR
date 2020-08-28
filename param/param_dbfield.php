<?php
// Definition de l'architecture de la base de données

//---< A >---\\

//---< B >---\\

//---< C >---\\

//---< D >---\\

//---< E >---\\

//---< F >---\\

//---< G >---\\

//---< H >---\\

//---< I >---\\
define('T_IMG', 't_image');
$g_image = array();
$g_image['code'] = 'code';
$g_image['nom'] = 'nom';
$g_image['taille'] = 'taille';
$g_image['mimetype'] = 'mimetype';
$g_image['description'] = 'description';
$g_image['img_blob'] = 'img_blob';
$g_image['id'] = 'id';

define('T_UI','t_interface');
$g_interface = array();
$g_interface['id'] = 'id';
$g_interface['fk_user'] = 'fk_user';
$g_interface['div_interface'] = 'div_interface';
$g_interface['offset_x'] = 'offset_x';
$g_interface['offset_y'] = 'offset_y';
$g_interface['background'] = 'background';

//---< J >---\\

//---< K >---\\

//---< L >---\\
define('T_LANG','t_lang');
$g_lang = array();
$g_lang['id'] = 'id';
$g_lang['lang'] = 'lang';
$g_lang['flag'] = 'flag';

//---< M >---\\

//---< N >---\\

//---< O >---\\

//---< P >---\\
define('T_PARAMETRE','t_parametre');
$g_parametre = array();
$g_parametre['id'] = 'id';
$g_parametre['code'] = 'code';
$g_parametre['value'] = 'value';

//---< Q >---\\

//---< R >---\\

//---< S >---\\

//---< T >---\\

//---< U >---\\
define('T_USER','t_user');
$g_user = array();
$g_user['id'] = 'id';
$g_user['fk_lang'] = 'fk_lang';
$g_user['prenom'] = 'prenom';
$g_user['nom'] = 'nom';
$g_user['login'] = 'login';
$g_user['password'] = 'password';
$g_user['desactiveON'] = 'desactiveON';
$g_user['isAdministrateurON'] = 'isAdministrateurON';
$g_user['adresse_1'] = 'adresse_1';
$g_user['adresse_2'] = 'adresse_2';
$g_user['cp'] = 'cp';
$g_user['ville'] = 'ville';
$g_user['fk_pays'] = 'fk_pays';
$g_user['date_naissance'] = 'date_naissance';
$g_user['tel'] = 'tel';
$g_user['enable_menu'] = 'enable_menu';
$g_user['enable_article'] = 'enable_article';
$g_user['enable_actu'] = 'enable_actu';
$g_user['enable_photo'] = 'enable_photo';
$g_user['enable_event'] = 'enable_event';
$g_user['enable_param'] = 'enable_param';
$g_user['enable_user'] = 'enable_user';

//---< V >---\\

//---< W >---\\

//---< X >---\\

//---< Y >---\\

//---< Z >---\\

?>
