<?php

/**
 * permaLink
 * 
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
 * 
 * FOR VERSION- AND RELEASE NOTES PLEASE LOOK AT INFO.TXT!
 */

// try to include LEPTON class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
	include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php'); 
} else {
	$subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));	$dir = $_SERVER['DOCUMENT_ROOT'];
	$inc = false;
	foreach ($subs as $sub) {
		if (empty($sub)) continue; $dir .= '/'.$sub;
		if (file_exists($dir.'/framework/class.secure.php')) { 
			include($dir.'/framework/class.secure.php'); $inc = true;	break; 
		} 
	}
	if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include LEPTON class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}
// end include LEPTON class.secure.php

if (!class_exists('dbconnectle')) 				require_once(WB_PATH.'/modules/dbconnect_le/include.php');

class dbPermaLink extends dbConnectLE {
	
	const field_id							= 'pl_id';
	const field_redirect_url 		= 'pl_redirect_url';
	const field_permanent_link	= 'pl_permanent_link';
	const field_request_type		= 'pl_request_type';
	const field_request_by			= 'pl_request_by';
	const field_request_call		= 'pl_request_call';
	const field_status					= 'pl_status';
	const field_timestamp				= 'pl_timestamp';
	
	const type_addon 						= 1;
	const type_manual						= 2;
	const type_undefined				= 0;
	
	public $type_array = array(
		array('value' => self::type_addon, 			'text' => pl_type_addon),
		array('value' => self::type_manual, 		'text' => pl_type_manual),
		array('value' => self::type_undefined, 'text' => pl_type_undefined)
	);
	
	const call_get							= 'GET';
	const call_request					= 'REQUEST';
	const call_put							= 'PUT';
	const call_session					= 'SESSION';
	
	public $call_aray = array(
		array('value'	=> self::call_get, 			'text' => 'GET'),
		array('value'	=> self::call_put, 			'text' => 'PUT'),
		array('value'	=> self::call_request, 	'text' => 'REQUEST'),
		array('value'	=> self::call_session, 	'text' => 'SESSION')		
	);
	
	const status_active					= 1;
	const status_locked					= 2;
	const status_deleted				= 3; 

	public $status_array = array(
		array('value' => self::status_active,		'text'	=> pl_status_active),
		array('value' => self::status_locked,		'text' => pl_status_locked),
		array('value' => self::status_deleted,	'text'	=> pl_status_deleted)
	);
	
	private $createTables 		= false;
  
  public function __construct($createTables = false) {
  	$this->createTables = $createTables;
  	parent::__construct();
  	$this->setTableName('mod_perma_link');
  	$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
  	$this->addFieldDefinition(self::field_permanent_link, "VARCHAR(512) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_redirect_url, "VARCHAR(512) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_request_type, "TINYINT NOT NULL DEFAULT '".self::type_undefined."'");
  	$this->addFieldDefinition(self::field_request_by, "VARCHAR(128) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_request_call, "VARCHAR(20) NOT NULL DEFAULT '".self::call_get."'");
  	$this->addFieldDefinition(self::field_status, "TINYINT NOT NULL DEFAULT '".self::status_active."'");
  	$this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");	
  	$this->checkFieldDefinitions();
  	// Tabelle erstellen
  	if ($this->createTables) {
  		if (!$this->sqlTableExists()) {
  			if (!$this->sqlCreateTable()) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			}
  		}
  	}
  } // __construct()
	
} // class dbPermaLink

?>