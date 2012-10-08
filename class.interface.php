<?php

/**
 * permaLink
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/de/addons/permalink.php
 * @copyright 2011-2012 phpManufaktur by Ralf Hertsch
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License (GPL)
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
  if (defined('LEPTON_VERSION')) include (WB_PATH . '/framework/class.secure.php');
}
else {
  $oneback = "../";
  $root = $oneback;
  $level = 1;
  while (($level < 10) && (!file_exists($root . '/framework/class.secure.php'))) {
    $root .= $oneback;
    $level += 1;
  }
  if (file_exists($root . '/framework/class.secure.php')) {
    include ($root . '/framework/class.secure.php');
  }
  else {
    trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
  }
}
// end include class.secure.php

// include GENERAL language file
if (!file_exists(WB_PATH . '/modules/kit_tools/languages/' . LANGUAGE . '.php')) {
  require_once (WB_PATH . '/modules/kit_tools/languages/DE.php'); // Vorgabe: DE
                                                                    // verwenden
}
else {
  require_once (WB_PATH . '/modules/kit_tools/languages/' . LANGUAGE . '.php');
}

if (!file_exists(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/languages/' . LANGUAGE . '.php')) {
  require_once (WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/languages/DE.php'); // Vorgabe:
                                                                                              // DE
                                                                                              // verwenden
}
else {
  require_once (WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/languages/' . LANGUAGE . '.php');
}

if (!class_exists('dbconnectle')) require_once (WB_PATH . '/modules/dbconnect_le/include.php');
if (!class_exists('kitToolsLibrary')) require_once (WB_PATH . '/modules/kit_tools/class.tools.php');

if (!class_exists('dbPermaLink')) require_once (WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/class.permalink.php');

global $kitLibrary;
global $dbPermaLink;
global $permaLink;

if (!is_object($kitLibrary)) $kitLibrary = new kitToolsLibrary();
if (!is_object($dbPermaLink)) $dbPermaLink = new dbPermaLink();
if (!is_object($permaLink)) $permaLink = new permaLink();
class permaLink {
  private $error = '';
  private $message = '';
  protected $template_path = '';
  protected $forbidden_filenames = array(
    'index.php',
    '.htaccess'
  );
  const use_get = 'GET';
  const use_post = 'POST';
  const use_request = 'REQUEST';
  const use_session = 'SESSION';
  public function __construct() {
    global $kitLibrary;
    $this->template_path = WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/htt/';
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

  /**
   * Set $this->message to $message
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

    $where = array(
      dbPermaLink::field_id => $link_id
    );
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

  /**
   * Get the PAGE_ID from the desired WYSIWYG page URL
   *
   * @param STR $url
   * @return INT PAGE_ID or BOOL FALSE on error
   */
  protected function getPageIDfromURL($url) {
    global $database;
    global $kitLibrary;

    $wb_settings = array();
    if (!$kitLibrary->getWBSettings($wb_settings)) return false;

    $dir = str_replace(WB_URL . $wb_settings['pages_directory'], '', dirname($url)) . '/';
    $file = basename($url);
    $file = substr($file, 0, strpos($file, $wb_settings['page_extension']));
    $SQL = sprintf("SELECT page_id FROM %spages WHERE link='%s'", TABLE_PREFIX, $dir . $file);

    $page_id = $database->get_one($SQL);
    if (empty($page_id)) return false;
    return $page_id;
  } // getPageIDfromURL()

  /**
   * Call this function to get the complete URL of a permaLink by the given
   * permaLink ID
   *
   * @param INT $id
   *          permaLink
   * @return STR URL or BOOL FALSE on error
   */
  public function getURLbyPermaLinkID($id) {
    global $dbPermaLink;
    global $kitLibrary;

    $where = array(
      dbPermaLink::field_id => $id
    );
    $pl = array();
    if (!$dbPermaLink->sqlSelectRecord($where, $pl)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPermaLink->getError()));
      return false;
    }
    if (count($pl) < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(pl_error_perma_link_invalid_id, $id)));
      return false;
    }

    $wb_settings = array();
    if (!$kitLibrary->getWBSettings($wb_settings)) return false;

    return WB_URL . $wb_settings['pages_directory'] . $pl[0][dbPermaLink::field_permanent_link];
  } // getURLbyPermaLinkID()

  /**
   * Call this function to create a permanent link for your application
   *
   * @param STR $redirect_url
   *          - the origin URL including all parameters needed
   * @param
   *          REFRENCE STR $perma_link - the link which should be created
   * @param STR $request_by
   *          - username or application which create the permaLink
   * @param STR $request_type
   *          - type of permaLink - 'addon' or 'manual'
   * @param
   *          REFERENCE INT $link_id - ID of the record
   * @param STR $request_use
   *          - type of request - 'GET', 'PUT', 'REQUEST', 'SESSION'
   * @return BOOL - if false you get additional informations by getMessage() or
   *         getError()
   */
  public function createPermaLink($redirect_url, &$perma_link, $request_by, $request_type = dbPermaLink::type_addon, &$link_id = -1, $request_use = self::use_get) {
    global $dbPermaLink;
    global $kitLibrary;
    // Pruefungen durchfuehren
    if (strpos($redirect_url, WB_URL) === false) {
      // ungueltige URL
      $this->setMessage(sprintf(pl_msg_redirect_url_extern, $redirect_url, WB_URL));
      return false;
    }
    // PAGE_ID aus der URL ermitteln
    if (false === ($page_id = $this->getPageIDfromURL($redirect_url))) {
      $this->setMessage(sprintf(pl_msg_page_id_not_found, $redirect_url));
      return false;
    }
    // Parameter auslesen
    $params = array();
    $redirect_url = str_replace('&amp;', '&', $redirect_url);
    parse_str(parse_url($redirect_url, PHP_URL_QUERY), $params);

    $request_str = '';
    foreach ($params as $key => $value) {
      $request_str .= sprintf('$_%s[\'%s\']=\'%s\';', strtoupper($request_use), $key, $value);
    }

    // Perma Link ggf. bereinigen
    $perma_link = strtolower($perma_link);
    if (empty($perma_link)) {
      $this->setMessage(pl_msg_perma_link_empty);
      return false;
    }
    if (strpos($perma_link, WB_URL . PAGES_DIRECTORY) !== false) {
      $perma_link = str_replace(WB_URL . PAGES_DIRECTORY, '', $perma_link);
    }
    if (strpos($perma_link, WB_URL) !== false) {
      $perma_link = str_replace(WB_URL, '', $perma_link);
    }
    $perma_link = str_replace('//', '/', $perma_link);

    // erstes Zeichen ein Slash?
    if ($perma_link[0] != '/') $perma_link = '/' . $perma_link;

    $fa = explode('/', $perma_link);
    $perma_link = '';
    $start = true;
    foreach ($fa as $segment) {
      if ($start) {
        $start = false;
        continue;
      }
      $perma_link .= '/' . page_filename($segment);
    }
    if (strpos($perma_link, '.') == 1) {
      $this->setMessage(pl_msg_perma_link_not_hidden);
      return false;
    }

    $file = substr($perma_link, strrpos($perma_link, '/') + 1);
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
    $where = array(
      dbPermaLink::field_permanent_link => $perma_link
    );
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
      dbPermaLink::field_permanent_link => $perma_link,
      dbPermaLink::field_redirect_url => $redirect_url,
      dbPermaLink::field_request_by => $request_by,
      dbPermaLink::field_request_type => $request_type,
      dbPermaLink::field_request_call => $request_use,
      dbPermaLink::field_status => dbPermaLink::status_active
    );
    $link_id = -1;
    if (!$dbPermaLink->sqlInsertRecord($data, $link_id)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPermaLink->getError()));
      return false;
    }

    // physikalische Seite anlegen
    if (false === ($page_file = file_get_contents($this->template_path . 'page.htt'))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(pl_error_reading_file, $this->template_path . 'page.htt')));
      return false;
    }
    $cfg_file = '';
    $count = substr_count($perma_link, '/');
    if ($count > 1) {
      // pruefen, ob das erforderliche Verzeichnis bereits existiert
      $dir = substr($perma_link, 0, strrpos($perma_link, '/'));
      if (!file_exists(WB_PATH . PAGES_DIRECTORY . $dir)) {
        if (!mkdir(WB_PATH . PAGES_DIRECTORY . $dir, 0755, true)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(pl_error_mkdir, PAGES_DIRECTORY . $dir)));
          return false;
        }
      }
    }
    for($i = 1; $i < $count; $i++) {
      $cfg_file .= '../';
    }
    $cfg_file .= 'config.php';
    $page_file = str_replace(array(
      '{$requests}',
      '{$config_file}',
      '{$page_id}'
    ), array(
      $request_str,
      $cfg_file,
      $page_id
    ), $page_file);
    if (!file_put_contents(WB_PATH . PAGES_DIRECTORY . $perma_link, $page_file)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(pl_error_writing_file, PAGES_DIRECTORY . $perma_link)));
      return false;
    }
    return true;
  } // createPermaLink()

  /**
   * Use this function to change the status of the permaLink
   *
   * @param INT $id
   * @param BOOL $status
   */
  public function updatePermaLink($id, $status) {
    global $dbPermaLink;

    $where = array(
      dbPermaLink::field_id => $id
    );
    $link = array();

    if (!$dbPermaLink->sqlSelectRecord($where, $link)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPermaLink->getError()));
      return false;
    }

    if (count($link) < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $id)));
      return false;
    }

    $link = $link[0];

    if (($status == dbPermaLink::status_active) || ($status == dbPermaLink::status_locked)) {
      // Statusaenderung kann direkt durchgefuehrt werden, keine weiteren
      // Aktionen erforderlich
      $data = array(
        dbPermaLink::field_status => $status
      );
      $where = array(
        dbPermaLink::field_id => $id
      );
      if (!$dbPermaLink->sqlUpdateRecord($data, $where)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPermaLink->getError()));
        return false;
      }
      $this->setMessage(sprintf(pl_msg_perma_link_status_changed, $id));
      return true;
    }
    elseif ($status == dbPermaLink::status_deleted) {
      // permaLink soll geloescht werden
      if (file_exists(WB_PATH . PAGES_DIRECTORY . $link[dbPermaLink::field_permanent_link])) {
        if (!unlink(WB_PATH . PAGES_DIRECTORY . $link[dbPermaLink::field_permanent_link])) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(pl_error_unlink_file, $link[dbPermaLink::field_permanent_link])));
          return false;
        }
      }
      $where = array(
        dbPermaLink::field_id => $id
      );
      if (!$dbPermaLink->sqlDeleteRecord($where)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPermaLink->getError()));
        return false;
      }
      $this->setMessage(sprintf(pl_msg_perma_link_deleted, $link[dbPermaLink::field_permanent_link]));
      return true;
    }
    else {
      // unbekannter Status
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(pl_error_status_unknown, $status)));
      return false;
    }
  } // updatePermaLink()

  /**
   * Delete a permaLink
   *
   * @param MIXED $perma_link
   *          - INT ID or STR permaLink
   * @return BOOL
   */
  public function deletePermaLink($perma_link) {
    global $dbPermaLink;
    if (is_int($perma_link)) {
      return $this->updatePermaLink($perma_link, dbPermaLink::status_deleted);
    }
    // permaLink statt ID verwenden
    $where = array(
      dbPermaLink::field_permanent_link => $perma_link
    );
    $link = array();
    if (!$dbPermaLink->sqlSelectRecord($where, $link)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPermaLink->getError()));
      return false;
    }
    if (count($link) < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(pl_error_perma_link_invalid, $perma_link)));
      return false;
    }
    return $this->updatePermaLink($link[0][dbPermaLink::field_id], dbPermaLink::status_deleted);
  }
} // class permaLink

?>