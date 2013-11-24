<?php

/**
 * permaLink
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link https://phpmanufaktur.de/perma_link
 * @copyright 2011-2013 phpManufaktur by Ralf Hertsch
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
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

if (!file_exists(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/languages/' . LANGUAGE . '.php')) {
  require_once (WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/languages/DE.php'); // Vorgabe:
                                                                                      // DE
                                                                                      // verwenden
}
else {
  require_once (WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/languages/' . LANGUAGE . '.php');
}

global $admin;

$error = '';

// Prompt Errors
if (!empty($error)) {
  $admin->print_error($error);
}

?>