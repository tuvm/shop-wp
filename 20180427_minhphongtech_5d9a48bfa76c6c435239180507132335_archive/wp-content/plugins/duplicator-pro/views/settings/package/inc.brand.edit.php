<?php
/* @var $brand DUP_PRO_Brand_Entity */

$was_updated = false;

//check_admin_referer($nonce_action);
$_REQUEST['action'] = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'new';
$_REQUEST['id']		= isset($_REQUEST['id'])	 ? $_REQUEST['id'] : 0;


switch ($_REQUEST['action']) {

	case 'new':
		$brand = new DUP_PRO_Brand_Entity();
		break;

	case 'default':
		$brand = DUP_PRO_Brand_Entity::get_default_brand();
		break;

	case 'edit':
		$brand = DUP_PRO_Brand_Entity::get_by_id($_REQUEST['id']);
		break;

	case 'save':
		$was_updated	 = true;
		$brand			 = new DUP_PRO_Brand_Entity();
		$brand->name	 = DUP_PRO_U::setVal($_POST['name'], 'New Brand');
		$brand->notes	 = DUP_PRO_U::setVal($_POST['notes'], '');
		$brand->logo	 = DUP_PRO_U::setVal($_POST['logo'], '');
		$brand->save();
		break;
}
?>

<style>
    #dup-storage-form input[type="text"], input[type="password"] { width: 250px;}
	#dup-storage-form input#name {width:100%; max-width: 500px}
	#dup-storage-form input#_local_storage_folder {width:100% !important; max-width: 500px}
	td.dpro-sub-title {padding:0; margin: 0}
	td.dpro-sub-title b{padding:20px 0; margin: 0; display:block; font-size:1.25em;}
	input#max_default_store_files {width:50px !important}
	form#dpro-package-brand-form {padding: 0}
	form#dpro-package-brand-form input[type="text"] { width:350px;}
	form#dpro-package-brand-form .readonly {background:transparent; border:none;}
	textarea#brand-notes {width:350px;}
	textarea#brand-logo {width:500px; height:100px; font-size: 12px}
	textarea#brand-default-logo {width:500px;; height:100px; font-size: 12px}
</style>

<?php
	if ($was_updated) {
		$update_message = 'Brand Saved!';
		echo "<div class='notice notice-success is-dismissible dpro-wpnotice-box'><p>{$update_message}</p></div>";
	}
?>
 <!-- ====================
TOOL-BAR -->
<table class="dpro-edit-toolbar">
	<tr>
		<td></td>
		<td>
			<div class="btnnav">
				<a href="<?php echo $brand_list_url; ?>" class="add-new-h2"> <i class="fa fa-photo"></i> <?php DUP_PRO_U::_e('Brands'); ?></a>
				<?php if ($_REQUEST['action'] == 'new') : ?>
					<span><?php DUP_PRO_U::_e('Add New'); ?></span>
				<?php else: ?>
					<a href="<?php echo $brand_edit_url; ?>&action=new" class="add-new-h2"><?php DUP_PRO_U::_e('Add New'); ?></a>
				<?php endif; ?>
			</div>
		</td>
	</tr>
</table>
<hr class="dpro-edit-toolbar-divider"/>

<form id="dpro-package-brand-form" action="<?php echo $brand_edit_url; ?>" method="post" data-parsley-ui-enabled="true">
    <?php wp_nonce_field($nonce_action); ?>
	<input type="hidden" name="id" id="brand-id" value="<?php echo $brand->id; ?>" />
	<input type="hidden" name="action" id="brand-action" value="<?php echo $_REQUEST['action']; ?>" />

	<?php if ($_REQUEST['action'] == 'default') : ?>
		<table class="provider form-table">
			<tr>
				<th scope="row"><label><?php DUP_PRO_U::_e("Name"); ?></label></th>
				<td><?php echo $brand->name; ?></td>
			</tr>
			<tr">
				<th scope="row"><label><?php DUP_PRO_U::_e("Notes"); ?></label></th>
				<td><?php echo $brand->notes; ?></td>
			</tr>
			<tr>
				<th scope="row"><label><?php DUP_PRO_U::_e("Logo"); ?></label></th>
				<td><textarea id="brand-default-logo" readonly="true"><?php echo $brand->logo; ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label><?php DUP_PRO_U::_e("Mode"); ?></label></th>
				<td><?php echo $brand->get_mode_text(); ?></td>
			</tr>
			<tr>
				<th scope="row"><label><?php DUP_PRO_U::_e("Active"); ?></label></th>
				<td>Yes</td>
			</tr>
		</table>
		<i><?php DUP_PRO_U::_e("The default brand cannot be changed"); ?></i>
		<br/><br/>
	<?php else: ?>
		<table class="provider form-table">
			<tr>
				<th scope="row"><label><?php DUP_PRO_U::_e("Name"); ?></label></th>
				<td><input type="text" name="name" id="brand-name" value="<?php echo $brand->name; ?>" data-parsley-required></td>
			</tr>
			<tr>
				<th scope="row"><label><?php DUP_PRO_U::_e("Notes"); ?></label></th>
				<td><textarea name="notes" id="brand-notes"><?php echo $brand->notes; ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label><?php DUP_PRO_U::_e("Logo"); ?></label></th>
				<td><textarea name="logo" id="brand-logo"><?php echo $brand->logo; ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label><?php DUP_PRO_U::_e("Mode"); ?></label></th>
				<td>
					<select name="mode" id="brand-mode">
						<option>Keep Plugin</option>
						<option>Remove Plugin</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label><?php DUP_PRO_U::_e("Active"); ?></label></th>
				<td>No</td>
			</tr>
		</table>
	<?php endif; ?>

    <br style="clear:both" />
    <button class="button button-primary" type="button" onclick="DupPro.Settings.Brand.Save()"><?php DUP_PRO_U::_e('Save Brand'); ?></button>
</form>

<script>
    jQuery(document).ready(function ($)
	{
		DupPro.Settings.Brand.Save = function() {
			if ($('#dpro-package-brand-form').parsley().validate()) {
				$('#brand-action').val('save');
				$('#dpro-package-brand-form').submit();
			}
        }

		//INIT
		$('#brand-name').focus();
    });
</script>


