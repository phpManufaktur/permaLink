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

define('pl_error_mkdir',										'<p>Das Verzeichnis <b>%s</b> konnte nicht angelegt werden.</p>');
define('pl_error_reading_file',							'<p>Die Datei <b>%s</b> konnte nicht eingelesen werden!</p>');
define('pl_error_writing_file',							'<p>Die Datei <b>%s</b> konnte nicht geschrieben werden!</p>');

define('pl_hint_id',												'');
define('pl_hint_request_type',							'');
define('pl_hint_request_by',								'');
define('pl_hint_status',										'');
define('pl_hint_redirect_url',							'');
define('pl_hint_permanent_link',						'');
define('pl_hint_timestamp',									'');

define('pl_intro_edit',											'Bearbeiten Sie den PermaLink wie gewünscht.');

define('pl_label_id',												'ID');
define('pl_label_request_type',							'Request Typ');
define('pl_label_request_by',								'Request durch');
define('pl_label_status',										'Status');
define('pl_label_redirect_url',							'Redirect URL');
define('pl_label_permanent_link',						'Permanent Link');
define('pl_label_timestamp',								'Letzte Änderung');

define('pl_msg_forbidden_filename',					'<p>Der Dateiname <b>%s</b> ist nicht zulässig!</p>');
define('pl_msg_page_extension_invalid',			'<p>Für den permaLink ist nur die Dateiendung <b>%s</b> erlaubt!</p>');
define('pl_msg_perma_link_already_exists',	'<p>Der permaLink <b>%s</b> wird bereits verwendet (<b>ID: %05d</b>)!</p>');
define('pl_msg_redirect_url_extern',				'<p>Die Redirect URL (<b>%s</b>) muss auf eine existierende Seite innerhalb der Domain <b>%s</b> verweisen.</p>');

define('pl_status_active',									'Aktiv');
define('pl_status_locked',									'Gesperrt');
define('pl_status_deleted',									'Gelöscht');

define('pl_tab_list',												'Übersicht');
define('pl_tab_edit',												'Bearbeiten');
define('pl_tab_about',											'?');

define('pl_title_edit',											'PermaLink bearbeiten');

define('pl_type_addon',											'Addon/Modul');
define('pl_type_manual',										'Manuell');
define('pl_type_undefined',									'- nicht definiert -');

?>