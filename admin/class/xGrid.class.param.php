<?php
/**
* @package XGrid
* @author CC <christophe.cautere@nexto.fr>
* @desc allow xGrid project level parameters
*/
xGrid::onNew('setUserProfileId', USER_ID);
xGrid::onNew('enableDatePicker', TRUE);
xGrid::onNew('enableUserProfile', defined('XGRID_ENABLE_PROFILE'));
//Pdf print
xGrid::onNew('enablePdfPrint', defined('XGRID_ENABLE_PRINT_HTML2PDF'));
xGrid::onNew('setPdfEngineInclude', 'class/tcpdf/tcpdf.php');
//Excel export
xGrid::onNew('setExcelExportInclude', 'class/php2excel/');

xGrid::onNew('enableVerboseMode', !defined('IS_PROD'));
xGrid::onNew('setQueryFunction', 'query');

xGrid::onNew('setProfileDefaultTable', true);
xGrid::onNew('enableDbug', isset($_GET['dbug']));
xGrid::onNew('fadedRowBgd', TRUE);
?>