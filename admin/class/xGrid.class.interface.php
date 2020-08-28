<?php
/**
* @package XGrid
* @author CC <christophe.cautere@nexto.fr>
* @desc xgrid interface element abstraction layer
* @comment allow better per project integration
* @version 1.0, xx/200x
*/

$interface=array();

#PARAM
$interface['param_user']=USER_ID;

#BUTTONS
$ubtn=array();
$ubtn['alt']='+';
$ubtn['mypic']='ADD';
$ubtn['class']='xgrid_add_profile';
$ubtn['xgrid']=true;
$interface['button_add_profile']=ubtn($ubtn);

$ubtn=array();
$ubtn['alt']='X';
$ubtn['mypic']='SUPPR';
$ubtn['class']='xgrid_remove_profile';
$ubtn['xgrid']=true;
$interface['button_remove_profile']=ubtn($ubtn);

$ubtn=array();
$ubtn['alt']='Edit';
$ubtn['mypic']='EDITPEN';
$ubtn['class']='xgrid_edit_profile';
$ubtn['xgrid']=true;
$interface['button_edit_profile']=ubtn($ubtn);

$ubtn=array();
$ubtn['mypic']='RIGHT';
$ubtn['label']='Ajouter';
$ubtn['xgrid']=true;
$interface['button_add']=ubtn($ubtn);

$ubtn=array();
$ubtn['mypic']='GOLAST';
$ubtn['label']='Tous';
$ubtn['xgrid']=true;
$interface['button_add_all']=ubtn($ubtn);


$ubtn=array();
$ubtn['mypic']='LEFT';
$ubtn['label']='Supprimer';
$ubtn['xgrid']=true;
$interface['button_rem']=ubtn($ubtn);

$ubtn=array();
$ubtn['mypic']='GOFIRST';
$ubtn['label']='Tous';
$ubtn['xgrid']=true;
$interface['button_rem_all']=ubtn($ubtn);

$ubtn=array();
$ubtn['mypic']='EXPORT';
$ubtn['label']='.CSV';
$ubtn['xgrid']=true;
$interface['button_export_csv']=ubtn($ubtn);

$ubtn=array();
$ubtn['mypic']='EXPORT';
$ubtn['label']='.XLS';
$ubtn['xgrid']=true;
$interface['button_export_xls']=ubtn($ubtn);


$ubtn=array();
$ubtn['alt']='X';
$ubtn['mypic']='FILTERDEL';
$ubtn['class']='xgrid_filter_off';
$ubtn['xgrid']=true;
$interface['button_filter_off']=ubtn($ubtn);

$ubtn=array();
$ubtn['alt']='Filtre';
$ubtn['mypic']='EDIT';
$ubtn['class']='xgrid_filter_on';
$ubtn['xgrid']=true;
$interface['button_filter_on']=ubtn($ubtn);

$ubtn=array();
$ubtn['alt']='F';
$ubtn['mypic']='EDIT';
$ubtn['xgrid']=true;
$interface['button_filter_toggle']=ubtn($ubtn);

$ubtn=array();
$ubtn['alt']='F';
$ubtn['mypic']='EDIT';
$ubtn['class']='xgrid_filter_choose';
$ubtn['xgrid']=true;
$interface['button_filter_choose']=ubtn($ubtn);

$ubtn=array();
$ubtn['mypic']='OK';
$ubtn['class']='xgrid_filter_yes';
$ubtn['xgrid']=true;
$interface['button_filter_yes']=ubtn($ubtn);

$ubtn=array();
$ubtn['mypic']='ADD';
$ubtn['label']='Ajouter';
$ubtn['class']='xgrid_editable_new';
$ubtn['xgrid']=true;
$interface['button_editable_new']=ubtn($ubtn);

$ubtn=array();
$ubtn['mypic']='CANCEL';
$ubtn['class']='xgrid_filter_no';
$ubtn['xgrid']=true;
$interface['button_filter_no']=ubtn($ubtn);

$ubtn=array();
$ubtn['label']='Valider';
$ubtn['mypic']='OK';
$ubtn['class']='xgrid_apply';
$ubtn['submit']=TRUE;
$ubtn['xgrid']=true;
$interface['button_filter_apply']=ubtn($ubtn);

$ubtn=array();
$ubtn['alt']='|<';
$ubtn['mypic']='GOFIRST';
$ubtn['class']='xgrid_page_go_first';
$ubtn['xgrid']=true;
$interface['button_page_go_first']=ubtn($ubtn);

$ubtn=array();
$ubtn['alt']='<';
$ubtn['mypic']='LEFT';
$ubtn['class']='xgrid_page_go_prev';
$ubtn['xgrid']=true;
$interface['button_page_go_prev']=ubtn($ubtn);

$ubtn=array();
$ubtn['alt']='>';
$ubtn['mypic']='RIGHT';
$ubtn['class']='xgrid_page_go_next';
$ubtn['xgrid']=true;
$interface['button_page_go_next']=ubtn($ubtn);

$ubtn=array();
$ubtn['alt']='>|';
$ubtn['mypic']='GOLAST';
$ubtn['class']='xgrid_page_go_last';
$ubtn['xgrid']=true;
$interface['button_page_go_last']=ubtn($ubtn);

$ubtn=array();
$ubtn['mypic']='PRINT';
$ubtn['label']='.PDF';
$ubtn['xgrid']=true;
$interface['button_print_pdf']=ubtn($ubtn);

$ubtn=array();
$ubtn['mypic']='PRINT';
$ubtn['label']='Ecran';
$ubtn['xgrid']=true;
$interface['button_print_html']=ubtn($ubtn);


#ICON
$interface['icon_calendar']='<img style="cursor: pointer; vertical-align: bottom;" src="pic/js_calendar/cal.gif" class="xgrid_filter_date_select" />';
$interface['icon_yes']=mypic('YES');
$interface['icon_yesalt']=mypic('YESALT');
$interface['icon_sort_asc']=mypic('SORTASCNE','',' class="xgrid_sort_asc" align="absmiddle"');
$interface['icon_sort_desc']=mypic('SORTDESCNE','',' class="xgrid_sort_desc" align="absmiddle"');

#TEXTS
$interface['text_allRows']='Toutes les lignes';
$interface['text_visibleRows']='Lignes visibles';
$interface['text_noResultAndFilter']='Aucun résultat, le filtre appliqué est peut être trop restrictif';
$interface['text_noResult']='Aucun résultat';
$interface['text_singleResult']='Un seul résultat trouvé et affiché';
$interface['text_resultFromTo']='Résultats %0% à %1%'; //@var %0%/%1% currently viewing start/end result rows
$interface['text_resultTotal']='affichés sur %0%'; //@var %0% total number of result rows
$interface['text_execTime']='Exécuté en %0% ms';

#WORDS
$interface['word_columns']='Colonnes';
$interface['word_export']='Exporter';
$interface['word_filter']='Filtrer';
$interface['word_format']='Format';
$interface['word_legend']='Légende';
$interface['word_print']='Imprimer';
$interface['word_switch']='Visibilité';
$interface['word_rows']='Lignes';
$interface['word_profiles']='Profils';
?>