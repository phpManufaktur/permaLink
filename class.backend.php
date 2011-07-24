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
if (!class_exists('Dwoo')) 								require_once(WB_PATH.'/modules/dwoo/include.php');
if (!class_exists('kitToolsLibrary'))   	require_once(WB_PATH.'/modules/kit_tools/class.tools.php');

if (!class_exists('permaLink'))						require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.interface.php');

global $parser;

if (!is_object($parser)) 									$parser = new Dwoo();

class permaLinkBackend extends permaLink {
	
	const request_action							= 'act';
	
	const action_about								= 'abt';
	const action_default							= 'def';
	const action_list									= 'lst';
	const action_edit									= 'edt';
	const action_edit_check						= 'edtc';
		
	private $tab_navigation_array = array(
		self::action_list								=> pl_tab_list,
		self::action_edit								=> pl_tab_edit,
		self::action_about							=> pl_tab_about		
	);
	
	private $page_link 								= '';
	private $img_url									= '';
	
	public function __construct() {
		parent::__construct();
		$this->page_link = ADMIN_URL.'/admintools/tool.php?tool=perma_link';
		$this->img_url = WB_URL. '/modules/'.basename(dirname(__FILE__)).'/images/';
		date_default_timezone_set(tool_cfg_time_zone);
	} // __construct()
	
	/**
   * Return Version of Module
   *
   * @return FLOAT
   */
  public function getVersion() {
    // read info.php into array
    $info_text = file(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/info.php');
    if ($info_text == false) {
      return -1; 
    }
    // walk through array
    foreach ($info_text as $item) {
      if (strpos($item, '$module_version') !== false) {
        // split string $module_version
        $value = explode('=', $item);
        // return floatval
        return floatval(preg_replace('([\'";,\(\)[:space:][:alpha:]])', '', $value[1]));
      } 
    }
    return -1;
  } // getVersion()
  
  public function getTemplate($template, $template_data) {
  	global $parser;
  	try {
  		$result = $parser->get($this->template_path.$template, $template_data); 
  	} catch (Exception $e) {
  		$this->setError(sprintf(tool_error_template_error, $template, $e->getMessage()));
  		return false;
  	}
  	return $result;
  } // getTemplate()
  
  
  /**
   * Verhindert XSS Cross Site Scripting
   * 
   * @param REFERENCE $_REQUEST Array
   * @return $request
   */
	public function xssPrevent(&$request) { 
  	if (is_string($request)) {
	    $request = html_entity_decode($request);
	    $request = strip_tags($request);
	    $request = trim($request);
	    $request = stripslashes($request);
  	}
	  return $request;
  } // xssPrevent()
	
  public function action() {
  	$html_allowed = array();
  	foreach ($_REQUEST as $key => $value) {
  		if (!in_array($key, $html_allowed)) {
  			$_REQUEST[$key] = $this->xssPrevent($value);	  			
  		} 
  	}
    isset($_REQUEST[self::request_action]) ? $action = $_REQUEST[self::request_action] : $action = self::action_default;
        
  	switch ($action):
  	case self::action_edit:
  		$this->show(self::action_edit, $this->dlgEdit());
  		break;
  	case self::action_edit_check:
  		$this->show(self::action_edit_check, $this->checkEdit());
  		break;
  	case self::action_about:
  		$this->show(self::action_about, $this->dlgAbout());
  		break;
  	case self::action_list:
  	case self::action_default:
  	default:
  		$this->show(self::action_list, $this->dlgList());
  		break;
  	endswitch;
  } // action
	
  	
  /**
   * Ausgabe des formatierten Ergebnis mit Navigationsleiste
   * 
   * @param $action - aktives Navigationselement
   * @param $content - Inhalt
   * 
   * @return ECHO RESULT
   */
  public function show($action, $content) {
  	$navigation = array();
  	foreach ($this->tab_navigation_array as $key => $value) {
  		$navigation[] = array(
  			'active' 	=> ($key == $action) ? 1 : 0,
  			'url'			=> sprintf('%s&%s=%s', $this->page_link, self::request_action, $key),
  			'text'		=> $value
  		);
  	}
  	$data = array(
  		'WB_URL'			=> WB_URL,
  		'navigation'	=> $navigation,
  		'error'				=> ($this->isError()) ? 1 : 0,
  		'content'			=> ($this->isError()) ? $this->getError() : $content
  	);
  	echo $this->getTemplate('backend.body.htt', $data);
  } // show()
	
  public function dlgAbout() {
  	$data = array(
  		'version'					=> sprintf('%01.2f', $this->getVersion()),
  		'img_url'					=> $this->img_url.'/perma-link-425x282.jpg',
  		'release_notes'		=> file_get_contents(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/info.txt'),
  	);
  	return $this->getTemplate('backend.about.htt', $data);
  } // dlgAbout()
  
  public function dlgList() {
  	global $dbPermaLink;
  	
  	$SQL = sprintf( "SELECT * FROM %s WHERE %s!='%s' ORDER BY %s ASC",
  									$dbPermaLink->getTableName(),
  									dbPermaLink::field_status,
  									dbPermaLink::status_deleted,
  									dbPermaLink::field_permanent_link);
  	$links = array();
  	if (!$dbPermaLink->sqlExec($SQL, $links)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPermaLink->getError()));
  		return false;
  	}
  	$links_array = array();
  	
  	foreach ($links as $link) {
  		foreach ($dbPermaLink->type_array as $key => $value) {
  			if ($key == $link[dbPermaLink::field_request_type]) {
  				$rt = $value;	
  				break;
  			}
  		}
  		foreach ($dbPermaLink->status_array as $key => $value) {
  			if ($key == $link[dbPermaLink::field_status]) {
  				$status = $value;
  				break;
  			}
  		}
  		$links_array[$link[dbPermaLink::field_id]] = array(
  			'id'							=> $link[dbPermaLink::field_id],
  			'timestamp'				=> $link[dbPermaLink::field_timestamp],
  			'request_by'			=> $link[dbPermaLink::field_request_by],
  			'request_type'		=> $rt,
  			'status'					=> $status,
  			'redirect_url'		=> $link[dbPermaLink::field_redirect_url],
  			'permanent_link'	=> $link[dbPermaLink::field_permanent_link],
  			'edit_link'				=> sprintf(	'%s&%s', $this->page_link,
  																		http_build_query(array(
  																			self::request_action	=> self::action_edit,
  																			dbPermaLink::field_id	=> $link[dbPermaLink::field_id]))) 
  		);
  	}
  	
  	$data = array(
  		'title'				=> pl_title_list,
  		'header'			=> array(
  											'id'							=> pl_th_id,
  											'timestamp'				=> pl_th_timestamp,
  											'request_by'			=> pl_th_request_by,
  											'request_type'		=> pl_th_request_type,
  											'status'					=> pl_th_status,
  											'redirect_url'		=> pl_th_redirect_url,
  											'permanent_link'	=> pl_th_permanent_link
  											),
  		'links'				=> $links_array,
  		'is_intro'		=> ($this->isMessage()) ? 0 : 1,
  		'intro'				=> ($this->isMessage()) ? $this->getMessage() : pl_intro_list  	
  	);
  	return $this->getTemplate('backend.list.htt', $data);
  } // dlgList()
	
  public function dlgEdit() {
  	global $dbPermaLink;
  	global $kitLibrary;
  	
  	$link_id = (isset($_REQUEST[dbPermaLink::field_id])) ? $_REQUEST[dbPermaLink::field_id] : -1;

  	$link = array();
  	if ($link_id < 1) {
  		// Defaults setzen
  		if (!$this->getDefaultDataRecord($link)) {
  			return false;
  		}
  		$link[dbPermaLink::field_request_type] = dbPermaLink::type_manual;
  		$link[dbPermaLink::field_request_by] = $kitLibrary->getDisplayName();
  		$link[dbPermaLink::field_timestamp] = time();
  		$link[dbPermaLink::field_request_call] = dbPermaLink::call_get;
  	}
  	else {
  		if (!$this->getDataRecord($link_id, $link)) {
  			return false;
  		}
  	}
  	foreach ($link as $key => $value) {
  		if (isset($_REQUEST[$key])) $link[$key] = $_REQUEST[$key];
  	}
  
  	$link_array = array(
  		'id'						=> array(	'name'		=> dbPermaLink::field_id,
  															'value'		=> $link_id,
  															'label'		=> pl_label_id,
  															'hint'		=> pl_hint_id),
  		'request_type'	=> array(	'name'		=> dbPermaLink::field_request_type,
  															'value'		=> $link[dbPermaLink::field_request_type],
  															'options'	=> $dbPermaLink->type_array,
  															'enabled'	=> 0,
  															'label'		=> pl_label_request_type,
  															'hint'		=> pl_hint_request_type),
  		'request_by'		=> array(	'name'		=> dbPermaLink::field_request_by,
  															'value'		=> $link[dbPermaLink::field_request_by],
  															'enabled'	=> 0,
  															'label'		=> pl_label_request_by,
  															'hint'		=> pl_hint_request_by),
  		'request_call'	=> array(	'name'		=> dbPermaLink::field_request_call,
  															'value'		=> $link[dbPermaLink::field_request_call],
  															'options'	=> $dbPermaLink->call_aray,
  															'enabled'	=> ($link_id) < 1 ? 1 : 0,
  															'label'		=> pl_label_request_call,
  															'hint'		=> pl_hint_request_call),
  		'status'				=> array(	'name'		=> dbPermaLink::field_status,
  															'value'		=> $link[dbPermaLink::field_status],
  															'options'	=> $dbPermaLink->status_array,
  															'enabled'	=> ($link_id) < 1 ? 0 : 1,
  															'label'		=> pl_label_status,
  															'hint'		=> pl_hint_status),
  		'redirect_url'	=> array(	'name'		=> dbPermaLink::field_redirect_url,
  															'value'		=> $link[dbPermaLink::field_redirect_url],
  															'enabled'	=> ($link_id < 1) ? 1 : 0,
  															'label'		=> pl_label_redirect_url,
  															'hint'		=> pl_hint_redirect_url),
  		'permanent_link'=> array(	'name'		=> dbPermaLink::field_permanent_link,
  															'value'		=> $link[dbPermaLink::field_permanent_link],
  															'label'		=> pl_label_permanent_link,
  															'enabled'	=> ($link_id < 1) ? 1 : 0,
  															'hint'		=> pl_hint_permanent_link),
  		'timestamp' 		=> array(	'name'		=> dbPermaLink::field_timestamp,
  															'value'		=> $link[dbPermaLink::field_timestamp],
  															'label'		=> pl_label_timestamp,
  															'hint'		=> pl_hint_timestamp)		
  	);
  	
  	$form = array(
  		'action'		=> array(	'link'	=> $this->page_link,
  													'name'	=> self::request_action,
  													'value'	=> self::action_edit_check),
  		'btn'				=> array(	'ok'		=> tool_btn_ok,
  													'abort'	=> tool_btn_abort)
  	); 
  	$data = array(
  		'form'				=> $form,
  		'title'				=> pl_title_edit,
  		'link'				=> $link_array,
  		'is_intro'		=> ($this->isMessage()) ? 0 : 1,
  		'intro'				=> ($this->isMessage()) ? $this->getMessage() : pl_intro_edit,
  	);
  	return $this->getTemplate('backend.edit.htt', $data);
  } // dlgEdit()
  
  public function checkEdit() {
  	global $dbPermaLink;
  	
  	$link_id = isset($_REQUEST[dbPermaLink::field_id]) ? $_REQUEST[dbPermaLink::field_id] : -1;
  	
  	$link = array();
  	if ($link_id < 1) {
  		if (!$this->getDefaultDataRecord($link)) {
  			return false;
  		}
  	}
  	else {
  		if (!$this->getDataRecord($link_id, $link)) {
  			return false;
  		}
  	}
    foreach($link as $key => $value) {
    	switch($key):
    	case dbPermaLink::field_status:
    	case dbPermaLink::field_request_by:
    	case dbPermaLink::field_request_type:
    	case dbPermaLink::field_request_call:
    	case dbPermaLink::field_redirect_url:
    	case dbPermaLink::field_permanent_link:
    		if (isset($_REQUEST[$key])) $link[$key] = $_REQUEST[$key];
    		break;
    	default:
    		continue;
    	endswitch;	
    }
    
    if ($link_id < 1) {
    	// neuen permaLink anlegen
    	if (!$this->createPermaLink($link[dbPermaLink::field_redirect_url], $link[dbPermaLink::field_permanent_link], $link[dbPermaLink::field_request_by], $link[dbPermaLink::field_request_type], $link_id, $link[dbPermaLink::field_request_call])) {
    		if ($this->isMessage()) return $this->dlgEdit();
    		return false;
    	}
    	foreach ($link as $key => $value) {
    		unset($_REQUEST[$key]);
    	}	
    	$_REQUEST[dbPermaLink::field_id] = $link_id;
    }
    else {
    	if (!$this->updatePermaLink($link[dbPermaLink::field_id], $link[dbPermaLink::field_status])) {
    		if ($this->isMessage()) return $this->dlgEdit();
    		return false;
    	}
    	if ($link[dbPermaLink::field_status] == dbPermaLink::status_deleted) return $this->dlgList();
    	return $this->dlgEdit();
    }
    return $this->dlgEdit();
  } // dlgEditCheck()
  
} // class permaLinkBackend

?>