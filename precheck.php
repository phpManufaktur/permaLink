<?php

/**
 * permaLink
 * 
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
 */

// prevent this file from being accessed directly
if (!defined('WB_PATH')) die('invalid call of '.$_SERVER['SCRIPT_NAME']);

// Checking Requirements

$PRECHECK['WB_VERSION'] = array('VERSION' => '2.8', 'OPERATOR' => '>=');
$PRECHECK['PHP_VERSION'] = array('VERSION' => '5.2.0', 'OPERATOR' => '>=');
$PRECHECK['WB_ADDONS'] = array(
	'dbconnect_le'	=> array('VERSION' => '0.64', 'OPERATOR' => '>='),
	'dwoo' => array('VERSION' => '0.10', 'OPERATOR' => '>='),
	'kit_tools' => array('VERSION' => '0.11', 'OPRATOR' => '>='),
);

// auf UTF-8 pruefen
global $database;  
$sql = "SELECT `value` FROM `".TABLE_PREFIX."settings` WHERE `name`='default_charset'";
$result = $database->query($sql);

// auf MySQL 5 pruefen
$vers = mysql_get_client_info();
list($major, $minor) = explode('.', $vers, 2); 

if ($result) {
	$data = $result->fetchRow(MYSQL_ASSOC);
	$PRECHECK['CUSTOM_CHECKS'] = array(
		'Default Charset' => array(
			'REQUIRED' => 'utf-8',
			'ACTUAL' => $data['value'],
			'STATUS' => ($data['value'] === 'utf-8')),
		'MySQL VERSION' => array(
			'REQUIRED' => '>= 5.0',
			'ACTUAL' => $major,
			'STATUS' => ($major >= 5))
	);
}

?>