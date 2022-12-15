<?php
$GLOBALS['TL_DCA']['tl_product_installer'] = array
(
	// Config
	'config' => array
	(
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary'
			)
		)
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'contao_manager_token' => array
		(
			'sql'                     => "varchar(64) NOT NULL default ''"
		)
	)
);
