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

$module_directory = 'perma_link';
$module_name = 'permaLink';
$module_function = 'tool';
$module_version = '0.16';
$module_status = 'Stable';
$module_platform = '2.8';
$module_author = 'Ralf Hertsch, Berlin (Germany)';
$module_license = 'MIT License (MIT)';
$module_description = 'Permanent Links for WebsiteBaker, LEPTON CMS and BlackCat CMS';
$module_home = 'https://phpmanufaktur.de/perma_link';
$module_guid = '2DDC6704-498E-4EC9-BE30-2693A4B9D3A3';
