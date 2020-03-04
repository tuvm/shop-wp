<?php
defined("ABSPATH") or die("");
require_once(DUPLICATOR_PRO_PLUGIN_PATH.'/classes/entities/class.secure.global.entity.php');

$subtab = isset($_REQUEST['subtab']) ? $_REQUEST['subtab'] : 'general';
$txt_general = DUP_PRO_U::__("General Settings");
$txt_profile = DUP_PRO_U::__("Feature Profiles");
$txt_migrate = DUP_PRO_U::__("Migrate Settings");
$spacer = ' &nbsp;|&nbsp; ';
$url = 'admin.php?page=duplicator-pro-settings';

switch ($subtab) {

//GENERAL TAB
case 'general':
$html = <<<HTML
<div class='dpro-sub-tabs'>
	<b>{$txt_general}</b>{$spacer}
	<a href='{$url}&subtab=profile'>{$txt_profile}</a>{$spacer}
	<a href='{$url}&subtab=migrate'>{$txt_migrate}</a>
 </div>
HTML;
	echo $html;
	include ('inc.general.php');
break;

//PROFILE TAB
case 'profile':
$html = <<<HTML
<div class='dpro-sub-tabs'>
	<a href='{$url}&subtab=general'>{$txt_general}</a>{$spacer}
	<b>{$txt_profile}</b>{$spacer}
	<a href='{$url}&subtab=migrate'>{$txt_migrate}</a>
 </div>
HTML;
	echo $html;
	include ('inc.profile.php');
break;

//MIGRATE TAB
case 'migrate':
$html = <<<HTML
<div class='dpro-sub-tabs'>
	<a href='{$url}&subtab=general'>{$txt_general}</a>{$spacer}
	<a href='{$url}&subtab=profile'>{$txt_profile}</a>{$spacer}
	<b>{$txt_migrate}</b>
 </div>
HTML;
	echo $html;
	include ('inc.migrate.php');
}
