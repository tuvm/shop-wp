<?php
defined("ABSPATH") or die("");
DUP_PRO_U::hasCapability('manage_options');

global $wpdb;
$global  = DUP_PRO_Global_Entity::get_instance();

//COMMON HEADER DISPLAY
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/assets/js/javascript.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/views/inc.header.php');

if ($global->profile_alpha) {
	$current_tab = isset($_REQUEST['tab']) ? esc_html($_REQUEST['tab']) : 'migrate';
} else {
	$current_tab = isset($_REQUEST['tab']) ? esc_html($_REQUEST['tab']) : 'diagnostics';
}
?>

<style>
	div.dpro-sub-tabs {padding: 10px 0 10px 0; font-size: 14px}
</style>

<div class="wrap">
    <?php duplicator_pro_header(DUP_PRO_U::__("Tools")) ?>

    <h2 class="nav-tab-wrapper">
		<?php if ($global->profile_alpha) : ?>
			<a href="?page=duplicator-pro-tools&tab=migrate" class="nav-tab <?php echo ($current_tab == 'migrate') ? 'nav-tab-active' : '' ?>"> <?php DUP_PRO_U::_e('Migration'); ?></a>
		<?php endif;?>
        <a href="?page=duplicator-pro-tools&tab=diagnostics" class="nav-tab <?php echo ($current_tab == 'diagnostics') ? 'nav-tab-active' : '' ?>"> <?php DUP_PRO_U::_e('Diagnostics'); ?></a>
    </h2> 	

    <?php
    switch ($current_tab)
    {
		case 'migrate': include('migrate.php');
            break;
        case 'diagnostics': include('diagnostics/main.php');
            break;
    }
    ?>
</div>
