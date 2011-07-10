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

// prevent illegal access
if (!defined('PERMA_LINK_ID')) die('invalid call of '.$_SERVER['SCRIPT_NAME']);


global $database;

$SQL = sprintf("SELECT pl_redirect_url FROM %smod_perma_link WHERE pl_id='%s'", TABLE_PREFIX,	PERMA_LINK_ID);
$redirect = $database->get_one($SQL);

if ($database->is_error()) die($database->get_error());

if (empty($redirect)) {
	// Datensatz nicht gefunden...
	die('Fatal error: The permaLink for this page does no longer exists! Please contact the webmaster of <a href="'.WB_URL.'">'.WB_URL.'</a>!');	
}
else {
	header("Location: $redirect");
}

?>