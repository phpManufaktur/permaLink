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

// include GENERAL language file
if(!file_exists(WB_PATH .'/modules/kit_tools/languages/' .LANGUAGE .'.php')) {
	require_once(WB_PATH .'/modules/kit_tools/languages/DE.php'); // Vorgabe: DE verwenden 
}
else {
	require_once(WB_PATH .'/modules/kit_tools/languages/' .LANGUAGE .'.php');
}

if(!file_exists(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php')) {
	require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/DE.php'); // Vorgabe: DE verwenden 
}
else {
	require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php');
}

if (!class_exists('dbconnectle')) 				require_once(WB_PATH.'/modules/dbconnect_le/include.php');
if (!class_exists('kitToolsLibrary'))   	require_once(WB_PATH.'/modules/kit_tools/class.tools.php');

if (!class_exists('dbPermaLink'))					require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.permalink.php');

global $kitLibrary;
global $dbPermaLink;
global $permaLink;

if (!is_object($kitLibrary)) 							$kitLibrary = new kitToolsLibrary();
if (!is_object($dbPermaLink))							$dbPermaLink = new dbPermaLink();	
if (!is_object($permaLink))								$permaLink = new permaLink();	

class permaLink {
	
	private $error										= '';
	private $message									= '';
	protected $template_path					= '';
	protected $forbidden_filenames 		= array('index.php', '.htaccess');
	
	public function __construct() {
		global $kitLibrary;
		$this->template_path = WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/htt/' ;
	} // __construct()
	
	/**
    * Set $this->error to $error
    * 
    * @param STR $error
    */
  protected function setError($error) {
  	$this->error = $error;
  } // setError()

  /**
    * Get Error from $this->error;
    * 
    * @return STR $this->error
    */
  public function getError() {
    return $this->error;
  } // getError()

  /** Set $this->message to $message
    * 
    * @param STR $message
    */
  protected function setMessage($message) {
    $this->message = $message;
  } // setMessage()

  /**
    * Get Message from $this->message;
    * 
    * @return STR $this->message
    */
  public function getMessage() {
    return $this->message;
  } // getMessage()

  /**
    * Check if $this->message is empty
    * 
    * @return BOOL
    */
  public function isMessage() {
    return (bool) !empty($this->message);
  } // isMessage
  
  /**
    * Check if $this->error is empty
    * 
    * @return BOOL
    */
  public function isError() {
    return (bool) !empty($this->error);
  } // isError
	
  protected function getDefaultDataRecord(&$data = array()) {
  	global $dbPermaLink;
  	
  	$data = $dbPermaLink->getFields();
  	$data[dbPermaLink::field_id] = -1;
  	$data[dbPermaLink::field_request_type] = dbPermaLink::type_undefined;
  	$data[dbPermaLink::field_status] = dbPermaLink::status_active;
  	
  	return true;
  } // getDefaultRecord()
  
  protected function getDataRecord($link_id, &$data = array()) {
  	global $dbPermaLink;
  	
  	$where = array(dbPermaLink::field_id => $link_id);
  	$data = array();
  	if (!$dbPermaLink->sqlSelectRecord($where, $data)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPermaLink->getError()));
  		return false;
  	}
  	if (count($data) < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $link_id)));
  		return false;
  	}
  	$data = $data[0];
  	return true;
  } // getRecord()
  
  public function createPermaLink($redirect_url, $perma_link, $request_by, $request_type=dbPermaLink::type_addon, &$link_id=-1) {
  	global $dbPermaLink;
  	global $kitLibrary;
  	// Pruefungen durchfuehren
  	$redirect_url = strtolower($redirect_url);
  	if (strpos($redirect_url, WB_URL) === false) {
  		// ungueltige URL
  		$this->setMessage(sprintf(pl_msg_redirect_url_extern, $redirect_url, WB_URL));
  		return false;
  	}
  	// Perma Link ggf. bereinigen
  	$perma_link = strtolower($perma_link);
  	if (strpos($perma_link, WB_URL.PAGES_DIRECTORY) !== false) {
  		$perma_link = str_replace(WB_URL.PAGES_DIRECTORY, '', $perma_link);
  	}
  	if (strpos($perma_link, WB_URL) !== false) {
  		$perma_link = str_replace(WB_URL, '', $perma_link);
  	}
  	$perma_link = str_replace('//', '/', $perma_link);
  	
  	// erstes Zeichen ein Slash?
  	if (strpos($perma_link, '/') != 0) $perma_link = '/'.$perma_link;
  	
  	$fa = explode('/', $perma_link);
  	$perma_link = '';
  	$start = true;
  	foreach ($fa as $segment) {
  		if ($start) {
  			$start = false;
  			continue;
  		}
  		$perma_link .= '/'.media_filename($segment);
  	}
  	
  	$file = substr($perma_link, strrpos($perma_link, '/')+1);
  	if (in_array($file, $this->forbidden_filenames)) {
  		$this->setMessage(sprintf(pl_msg_forbidden_filename, $file));
  		return false;
  	}
  	
  	$ext = substr($file, strrpos($file, '.'));
  	$settings = array();
		$kitLibrary->getWBSettings($settings);
		if ($ext != $settings['page_extension']) {
			$this->setMessage(sprintf(pl_msg_page_extension_invalid, $settings['page_extension']));
			return false;
		}
  	
  	// Pruefen, ob der permaLink bereits verwendet wird
  	$where = array(dbPermaLink::field_permanent_link => $perma_link);
  	$link = array();
  	if (!$dbPermaLink->sqlSelectRecord($where, $link)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPermaLink->getError()));
  		return false;
  	}
  	if (count($link) > 0) {
  		$this->setMessage(sprintf(pl_msg_perma_link_already_exists, $perma_link, $link[0][dbPermaLink::field_id]));
  		return false;
  	}
  	
  	// ok - Datensatz anlegen
  	$data = array(
  		dbPermaLink::field_permanent_link	=> $perma_link,
  		dbPermaLink::field_redirect_url		=> $redirect_url,
  		dbPermaLink::field_request_by			=> $request_by,
  		dbPermaLink::field_request_type		=> $request_type,
  		dbPermaLink::field_status					=> dbPermaLink::status_active
  	);
  	$link_id = -1;
  	if (!$dbPermaLink->sqlInsertRecord($data, $link_id)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPermaLink->getError()));
  		return false;
  	}
  	
  	// physikalische Seite anlegen
  	if (false === ($page_file = file_get_contents($this->template_path.'page.htt'))) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(pl_error_reading_file, $this->template_path.'page.htt')));
  		return false;
  	}
  	$cfg_file = '';
  	$count = substr_count($perma_link, '/');
  	if ($count > 1) {
  		// pruefen, ob das erforderliche Verzeichnis bereits existiert
  		$dir = substr($perma_link, 0, strrpos($perma_link, '/'));
  		if (!file_exists(WB_PATH.PAGES_DIRECTORY.$dir)) {
  			if (!mkdir(WB_PATH.PAGES_DIRECTORY.$dir, 0755, true)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(pl_error_mkdir, PAGES_DIRECTORY.$dir)));
  				return false;
  			}
  		}
  	}
  	for ($i = 0; $i < $count; $i++) {
  		$cfg_file .= '../';
  	}
  	$cfg_file .= 'config.php';
  	$page_file = str_replace(array('{$link_id}', '{$config_file}'), array($link_id, $cfg_file), $page_file);
  	if (!file_put_contents(WB_PATH.PAGES_DIRECTORY.$perma_link, $page_file)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(pl_error_writing_file, PAGES_DIRECTORY.$perma_link)));
  		return false;
  	}
  	return true;
  } // createPermaLink()
  
} // class permaLink

?>