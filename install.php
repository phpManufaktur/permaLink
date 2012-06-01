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

// include language file for flexTable
if (!file_exists(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/languages/' . LANGUAGE . '.php')) {
  require_once (WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/languages/DE.php'); // Vorgabe:
                                                                                      // DE
                                                                                      // verwenden
}
else {
  require_once (WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/languages/' . LANGUAGE . '.php');
}

require_once (WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/class.permalink.php');

global $admin;

$tables = array(
  'dbPermaLink'
);
$error = '';

foreach ($tables as $table) {
  $create = null;
  $create = new $table();
  if (!$create->sqlTableExists()) {
    if (!$create->sqlCreateTable()) {
      $error .= sprintf('[INSTALLATION %s] %s', $table, $create->getError());
    }
  }
}

// Prompt Errors
if (!empty($error)) {
  $admin->print_error($error);
}

?>