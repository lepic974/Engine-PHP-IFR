/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */
CKEDITOR.stylesSet.add( 'my_styles', [
	                                      // Block-level styles.
	                                      { name: 'Titre', element: 'div', attributes: { 'class': 'zorglub_titre' } },
	                                      { name: 'Sous Titre',  element: 'div', attributes: { 'class': 'zorglub_sous_titre' } },
	                                      { name: 'Chapeau',  element: 'div', attributes: { 'class': 'zorglub_chapeau' } },
	                                      { name: 'Contenu',  element: 'p', attributes: { 'class': 'zorglub_contenu' } },
	                                      { name: 'Couleur Bleu',  element: 'span', attributes: { 'class': 'zorglub_couleur_bleu' } },
	                                  ]);

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For the complete reference:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	
	config.stylesSet = 'my_styles';
	
	config.extraPlugins = 'youtube';
	
	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbarGroups = [
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'links' },
		{ name: 'insert' },
		{ name: 'forms' },
		{ name: 'tools' },
		{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'others' },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align' ] },
		{ name: 'styles' },
		{ name: 'colors' },
		{ name: 'Youtube' },   
		{ name: 'about' }
	];

	// Remove some buttons, provided by the standard plugins, which we don't
	// need to have in the Standard(s) toolbar.
	config.removeButtons = 'Underline,Subscript,Superscript';

	// Se the most common block elements.
	config.format_tags = 'p;h1;h2;h3;pre';

	// Make dialogs simpler.
	config.removeDialogTabs = ''; // 'image:advanced;link:advanced';
	
	

};
