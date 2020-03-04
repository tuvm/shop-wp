<?php
/* @var $global DUP_PRO_Global_Entity */
defined("ABSPATH") or die("");

$max_execution_time			= ini_get("max_execution_time");
$max_execution_time			= empty($max_execution_time) ? 30 : $max_execution_time;
$max_worker_cap_in_sec		= (int) (0.7 * (float) $max_execution_time);
$is_zip_available			= (DUP_PRO_Zip_U::getShellExecZipPath() != null);
$is_shellexec_on			= DUP_PRO_Shell_U::isShellExecEnabled();
$phpdump_chunkopts			= array("20", "100", "500", "1000", "2000");
$user_id = get_current_user_id();
$package_ui_created = is_numeric(get_user_meta($user_id,'duplicator_pro_created_format',true)) ? get_user_meta($user_id,'duplicator_pro_created_format',true) : 1; //Old option was $global->package_ui_created

$_REQUEST['_package_mysqldump_path'] = isset($_REQUEST['_package_mysqldump_path']) ? DUP_PRO_U::safePath($_REQUEST['_package_mysqldump_path']) : '';

//SAVE RESULTS
if (isset($_POST['action']) && $_POST['action'] == 'save') {
	check_admin_referer($nonce_action);

	//DATABASE
	$enable_mysqldump					 = isset($_REQUEST['_package_dbmode']) && $_REQUEST['_package_dbmode'] == 'mysql' ? "1" : "0";
	$global->package_mysqldump			 = $enable_mysqldump ? 1 : 0;
	$global->package_phpdump_qrylimit	 = isset($_REQUEST['_package_phpdump_qrylimit']) ? (int) $_REQUEST['_package_phpdump_qrylimit'] : 100;
	$global->package_mysqldump_path		 = trim($_REQUEST['_package_mysqldump_path']);

	//ARCHIVE SETTINGS
	DUP_PRO_U::initStorageDirectory();
	$global->archive_compression = isset($_REQUEST['archive_compression']) ? (bool) $_REQUEST['archive_compression'] : true;
	$prelim_build_mode = (int) $_REQUEST['archive_build_mode'];
	
	// Something has changed which invalidates Shell exec so move it to ZA
	$global->archive_build_mode = (!$is_zip_available && ($prelim_build_mode == DUP_PRO_Archive_Build_Mode::Shell_Exec))
		? DUP_PRO_Archive_Build_Mode::ZipArchive
		: $prelim_build_mode;

	$global->ziparchive_mode		= isset($_REQUEST['ziparchive_mode']) ? (int) $_REQUEST['ziparchive_mode'] : 0;
	$global->ziparchive_validation	= isset($_REQUEST['ziparchive_validation']);
	if (isset($_REQUEST['ziparchive_chunk_size_in_mb'])) {
		$global->ziparchive_chunk_size_in_mb = (int) $_REQUEST['ziparchive_chunk_size_in_mb'];
	}

	//SCHEULED SETTINGS
	$global->archive_compression_schedule = isset($_REQUEST['archive_compression_schedule']) ? (bool) $_REQUEST['archive_compression_schedule'] : true;
	$prelim_build_mode_schedule = (int) $_REQUEST['archive_build_mode_schedule'];

	// Something has changed which invalidates Shell exec so move it to ZA
	$global->archive_build_mode_schedule = (!$is_zip_available && ($prelim_build_mode_schedule == DUP_PRO_Archive_Build_Mode::Shell_Exec))
		? DUP_PRO_Archive_Build_Mode::ZipArchive
		: $prelim_build_mode_schedule;


	//PROCESSING
	$global->max_package_runtime_in_min	 = (int) $_REQUEST['max_package_runtime_in_min'];
	$global->server_load_reduction		 = (int) $_REQUEST['server_load_reduction'];
	$global->php_max_worker_time_in_sec	 = $_REQUEST['php_max_worker_time_in_sec'];

	$action_updated = $global->save();
    $sglobal->save();
	$global->adjust_settings_for_system();
}

$mysqlDumpPath				= DUP_PRO_DB::getMySqlDumpPath();
$mysqlDumpFound				= ($mysqlDumpPath) ? true : false;


class DUP_PRO_UI_Settings_General_Basic
{

    public static function getShellZipMessage($hasShellZip = false)
    {
		if ($hasShellZip) {
			DUP_PRO_U::_e('The "Shell Zip" mode allows Duplicator to use the servers internal zip command. <br/>');
			DUP_PRO_U::_e('When available this mode is recommended over the PHP "ZipArchive" mode.');
		} else {
			DUP_PRO_U::_e("<i style='color:maroon'><i class='fa fa-exclamation-triangle'></i> This server is not configured for the Shell Zip engine - please use "
				. "a different engine mode.  Shell Zip is <a href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-package-030-q' target='_blank'>recommended</a> when available. "
				. "For a list of supported hosting providers <a href='https://snapcreek.com/wordpress-hosting/' target='_blank'>click here</a>.</i>");

			//Show possible solutions for some linux setups
			$problem_fixes	= DUP_PRO_Zip_U::getShellExecZipProblems();
			if (count($problem_fixes) > 0 && ((strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN'))) {
				$shell_tooltip	= ' ';
				$shell_tooltip .= DUP_PRO_U::__("To make 'Shell Zip' available, ask your host to:<br/>");
				$i = 1;
				foreach ($problem_fixes as $problem_fix) {
					$shell_tooltip .= "{$i}. {$problem_fix->fix}<br/>";
					$i++;
				}
				$shell_tooltip .= '<br/>';
				echo "{$shell_tooltip}";
			}
		}
    }
}
?>

<?php if ($action_updated) : ?>
	<div class="notice notice-success is-dismissible dpro-wpnotice-box"><p><?php echo $action_response; ?></p></div>
	<br/>
<?php endif; ?>


<form id="dup-settings-form" action="<?php echo self_admin_url('admin.php?page=' . DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG); ?>" method="post" data-parsley-validate>
<?php wp_nonce_field($nonce_action); ?>
<input type="hidden" name="action" value="save">
<input type="hidden" name="page"   value="<?php echo DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG ?>">
<input type="hidden" name="tab"   value="package">

<!-- ===============================
DATABASE -->
<h3 class="title"><?php DUP_PRO_U::_e("Database") ?> </h3>
<hr size="1" />
<table class="form-table">
<tr>
	<th scope="row"><label><?php DUP_PRO_U::_e("SQL Script"); ?></label></th>
	<td>

		<div class="engine-radio <?php echo ($is_shellexec_on) ? '' : 'engine-radio-disabled'; ?>">
			<input type="radio" name="_package_dbmode" value="mysql" id="package_mysqldump" <?php echo DUP_PRO_UI::echoChecked($global->package_mysqldump); ?>  onclick="DupPro.UI.SetDBEngineMode();" />
			<label for="package_mysqldump"><?php DUP_PRO_U::_e("Mysqldump"); ?> <small><?php DUP_PRO_U::_e("(recommended)"); ?></small></label> &nbsp; &nbsp; &nbsp;
		</div>

		<div class="engine-radio">
			<input type="radio" name="_package_dbmode" id="package_phpdump" value="php" <?php echo DUP_PRO_UI::echoChecked(!$global->package_mysqldump); ?>  onclick="DupPro.UI.SetDBEngineMode();"  />
			<label for="package_phpdump"><?php DUP_PRO_U::_e("PHP Code"); ?></label>
		</div>
		<br style="clear:both"/><br/>

		<!-- SHELL EXEC  -->
		<div class="engine-sub-opts" id="dbengine-details-1" style="display:none">

			<!-- MYSQLDUMP IN-ACTIVE -->
			<?php if (! $is_shellexec_on) : ?>

				<div class="dup-feature-notfound">
					<?php
						DUP_PRO_U::_e("In order to use mysqldump the PHP function shell_exec needs to be enabled. This server currently does not allow "
							. "<a href='http://php.net/manual/en/function.shell-exec.php' target='_blank'>shell_exec</a> to run. ");
						DUP_PRO_U::_e("Please contact your host or server admin to enable this feature. For a list of approved providers that support shell_exec ");
						echo "<a href='https://snapcreek.com/wordpress-hosting/' target='_blank'>" . DUP_PRO_U::__("click here") . "</a>.  The 'PHP Code' setting will be used "
							. "until this issue is resolved by your hosting provider.";
					?>
				</div><br/>

			<!-- MYSQLDUMP ACTIVE -->
			<?php else : ?>

					<?php if ( $mysqlDumpFound) : ?>
						<div class="dup-feature-found">
							<i class="fa fa-check-circle"></i>
							<?php DUP_PRO_U::_e("Successfully Found:"); ?> &nbsp;
							<i><?php echo $mysqlDumpPath ?></i>
						</div><br/>
					<?php else : ?>
						<div class="dup-feature-notfound">
							<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
							<?php
								DUP_PRO_U::_e('The mysqldump program was not found at its default location or the custom path below.  Please enter a valid path where mysqldump can run. ');
								DUP_PRO_U::_e("If the problem persist contact your server admin for the correct path. For a list of approved providers that support mysqldump ");
								echo "<a href='https://snapcreek.com/wordpress-hosting/' target='_blank'>" . DUP_PRO_U::__("click here") . "</a>.";
							?>
						</div><br/>
					<?php endif; ?>

					<label><?php DUP_PRO_U::_e("Custom Path"); ?></label>
					<i class="fa fa-question-circle"
						data-tooltip-title="<?php DUP_PRO_U::_e("mysqldump"); ?>"
						data-tooltip="<?php DUP_PRO_U::_e('An optional path to the mysqldump program.  Add a custom path if the path to mysqldump is not properly detected or needs to be changed.   For all paths including Windows use a forward slash.'); ?>"></i>
					<br/>
					<input class="wide-input" type="text" name="_package_mysqldump_path" id="_package_mysqldump_path" value="<?php echo $global->package_mysqldump_path; ?>"  placeholder="<?php DUP_PRO_U::_e("/usr/bin/mypath/mysqldump.exe"); ?>" />
					<br/>
			<?php endif; ?>
		</div>

		<!-- PHP OPTION -->
		<div class="engine-sub-opts" id="dbengine-details-2" style="display:none">
			<label for="_package_phpdump_qrylimit"><?php DUP_PRO_U::_e("Query Limit Size"); ?></label>
			<i style="margin-right:7px" class="fa fa-question-circle"
			   data-tooltip-title="<?php DUP_PRO_U::_e("PHP Query Limit Size"); ?>"
			   data-tooltip="<?php DUP_PRO_U::_e('A higher limit size will speed up the database build time, however it will use more memory.  If your host has memory caps start off low.'); ?>"></i>
			<select name="_package_phpdump_qrylimit" id="_package_phpdump_qrylimit">
				<?php
					foreach ($phpdump_chunkopts as $value) {
						$selected = ( $global->package_phpdump_qrylimit == $value ? "selected='selected'" : '' );
						echo "<option {$selected} value='{$value}'>".number_format($value).'</option>';
					}
				?>
			</select>
		</div>
	</td>
</tr>
</table>


<!-- ===========================
ARCHIVE -->
<h3 class="title"><?php DUP_PRO_U::_e("Archive") ?> </h3>
<hr size="1" />
<table class="form-table" style="margin-bottom: -15px">
<tr>
	<th scope="row">
		<label><?php DUP_PRO_U::_e("Build Setting"); ?></label>
	</th>
	<td>
		<span id="archive-build-manual-icon"><i class="fa fa-gear"></i> </span>
		<span id="archive-build-schedule-icon"><i class="fa fa-clock-o"></i></span>
		<select onchange="DupPro.UI.SetBuildType(this)">
			<option value="1"><?php DUP_PRO_U::_e("Manual Builds"); ?></option>
			<option value="2"><?php DUP_PRO_U::_e("Scheduled Builds"); ?></option>
		</select>
		<i style="margin-right:7px;" class="fa fa-question-circle"
			data-tooltip-title="<?php DUP_PRO_U::_e("Build Setting:"); ?>"
			data-tooltip="<?php DUP_PRO_U::_e('Manual and scheduled builds use separate settings for how they build the archive file.  Manual builds are created through the packages '
				. 'screen and scheduled builds are managed through the schedules menu.'); ?>"></i>
	</td>
</tr>
</table>


<!-- ===========================
MANUAL ENGINE MODE -->
<table class="form-table" id="archive-build-manual">
<tr>
	<th scope="row">
		<label><?php DUP_PRO_U::_e("Compression"); ?></label>
	</th>
	<td>
		<input type="radio" name="archive_compression" id="archive_compression_off" value="0" <?php echo DUP_PRO_UI::echoChecked($global->archive_compression == false); ?> />
		<label for="archive_compression_off"><?php DUP_PRO_U::_e("Off"); ?></label> &nbsp;
		<input type="radio" name="archive_compression"  id="archive_compression_on" value="1" <?php echo DUP_PRO_UI::echoChecked($global->archive_compression == true); ?>  />
		<label for="archive_compression_on"><?php DUP_PRO_U::_e("On"); ?></label>
		<i style="margin-right:7px;" class="fa fa-question-circle"
			data-tooltip-title="<?php DUP_PRO_U::_e("Shell Exec Archive Compression:"); ?>"
			data-tooltip="<?php DUP_PRO_U::_e('Controls archive compression. This setting can be toggled when using ZipArchive on PHP 7+, Shell Zip or DupArchive.'); ?>"></i>
	</td>
</tr>
<tr>
	<th scope="row"><label><?php DUP_PRO_U::_e("Manual Engine"); ?></label></th>
	<td>
		<div class="engine-radio <?php echo ($is_zip_available) ? '' : 'engine-radio-disabled'; ?>">
			<input onclick="DupPro.UI.SetArchiveOptionStates();" type="radio" name="archive_build_mode" id="archive_build_mode1"
				   value="<?php echo DUP_PRO_Archive_Build_Mode::Shell_Exec; ?>"
				   <?php echo DUP_PRO_UI::echoChecked($global->archive_build_mode == DUP_PRO_Archive_Build_Mode::Shell_Exec); ?> />
			<label for="archive_build_mode1"><?php DUP_PRO_U::_e("Shell Zip"); ?></label>
		</div>

		<div class="engine-radio">
			<input onclick="DupPro.UI.SetArchiveOptionStates();" type="radio" name="archive_build_mode" id="archive_build_mode2"  value="<?php echo DUP_PRO_Archive_Build_Mode::ZipArchive; ?>" <?php echo DUP_PRO_UI::echoChecked($global->archive_build_mode == DUP_PRO_Archive_Build_Mode::ZipArchive); ?> />
			<label for="archive_build_mode2"><?php DUP_PRO_U::_e("ZipArchive"); ?></label>
		</div>

				<div class="engine-radio">
			<input onclick="DupPro.UI.SetArchiveOptionStates();" type="radio" name="archive_build_mode" id="archive_build_mode3"  value="<?php echo DUP_PRO_Archive_Build_Mode::DupArchive; ?>" <?php echo DUP_PRO_UI::echoChecked($global->archive_build_mode == DUP_PRO_Archive_Build_Mode::DupArchive); ?> />
			<label for="archive_build_mode3"><?php DUP_PRO_U::_e("DupArchive"); ?></label> &nbsp; &nbsp;
		</div>

		<br style="clear:both"/>

		<!-- SHELL EXEC  -->
		<div class="engine-sub-opts" id="engine-details-1" style="display:none">
			<p class="description">
				<?php DUP_PRO_UI_Settings_General_Basic::getShellZipMessage($is_zip_available);	?>
			</p>
		</div>

		<!-- ZIP ARCHIVE  -->
		<div class="engine-sub-opts" id="engine-details-2" style="display:none; padding-top:20px">
			<label>Mode:</label>
			<select  name="ziparchive_mode" id="ziparchive_mode"  onchange="DupPro.UI.setZipArchiveMode();">
				<option <?php echo DUP_PRO_UI::echoSelected($global->ziparchive_mode == DUP_PRO_ZipArchive_Mode::Multithreaded); ?> value="<?php echo DUP_PRO_ZipArchive_Mode::Multithreaded ?>">
					<?php DUP_PRO_U::_e("Multi-Threaded"); ?>
				</option>
				<option <?php echo DUP_PRO_UI::echoSelected($global->ziparchive_mode == DUP_PRO_ZipArchive_Mode::SingleThread); ?> value="<?php echo DUP_PRO_ZipArchive_Mode::SingleThread ?>">
					<?php DUP_PRO_U::_e("Single-Threaded"); ?>
				</option>
			</select>

			<div id="dpro-ziparchive-mode-st">
				<input type="checkbox" id="ziparchive_validation" name="ziparchive_validation" <?php echo DUP_PRO_UI::echoChecked($global->ziparchive_validation); ?>>
				<label for="ziparchive_validation">Enable file validation</label>
			</div>

			<div id="dpro-ziparchive-mode-mt">
				<label><?php DUP_PRO_U::_e("Buffer Size"); ?></label>
				<input style="width:40px;"
					   data-parsley-required data-parsley-errors-container="#ziparchive_chunk_size_error_container" data-parsley-min="5" data-parsley-type="number"
					   type="text" name="ziparchive_chunk_size_in_mb" id="ziparchive_chunk_size_in_mb" value="<?php echo $global->ziparchive_chunk_size_in_mb; ?>" />
				<label><?php DUP_PRO_U::_e('MB'); ?></label>
				<i style="margin-right:7px" class="fa fa-question-circle"
					data-tooltip-title="<?php DUP_PRO_U::_e("PHP ZipArchive"); ?>"
					data-tooltip="<?php DUP_PRO_U::_e('The buffer size only applies to multi-threaded requests.  The buffer indicates how large an archive will get before a close is registered with the ZipArchive call.  Higher values are faster but can be more unstable based on the hosts max_execution time.'); ?>"></i>
				<div id="ziparchive_chunk_size_error_container" class="duplicator-error-container"></div>
			</div>
		</div>

		<!-- DUPARCHIVE -->
		<div class="engine-sub-opts" id="engine-details-3" style="display:none">
			<p class="description">
				<?php DUP_PRO_U::_e('Creates a custom archive format (archive.daf).<br/>  This option is recommended for large sites or sites on constrained servers.'); ?>
			</p>
		</div>

	</td>
</tr>
</table>


<!-- ===========================
SCHEDULE ENGINE MODE -->
<table class="form-table" id="archive-build-schedule">
	<tr>
		<th scope="row">
			<label><?php DUP_PRO_U::_e("Compression"); ?></label>
		</th>
		<td>
			<input type="radio" name="archive_compression_schedule" id="archive_compression_off_schedule" value="0" 
				<?php echo DUP_PRO_UI::echoChecked($global->archive_compression_schedule == false); ?> />
			<label for="archive_compression_off_schedule"><?php DUP_PRO_U::_e("Off"); ?></label> &nbsp;

			<input type="radio" name="archive_compression_schedule"  id="archive_compression_on_schedule" value="1" 
				<?php echo DUP_PRO_UI::echoChecked($global->archive_compression_schedule == true); ?>  />
			<label for="archive_compression_on_schedule"><?php DUP_PRO_U::_e("On"); ?></label>

			<i style="margin-right:7px;" class="fa fa-question-circle"
				data-tooltip-title="<?php DUP_PRO_U::_e("Shell Exec Archive Compression:"); ?>"
				data-tooltip="<?php DUP_PRO_U::_e('Controls archive compression. This setting can be toggled when using ZipArchive on a PHP 7+ system or Shell Exec Zip.'); ?>"></i>
		</td>
	</tr>
	<tr>
		<th scope="row"><label><?php DUP_PRO_U::_e("Schedule Engine"); ?></label></th>
		<td>

			<?php if ($global->profile_alpha) : ?>
				<!-- TURN ON WHEN ALPHA READY
				<div class="engine-radio">
					<input onclick="DupPro.UI.SetArchiveOptionStates_Schedule();" type="radio" name="archive_build_mode_schedule" id="archive_build_mode3_schedule"
						   value="<?php echo DUP_PRO_Archive_Build_Mode::DupArchive; ?>" <?php echo DUP_PRO_UI::echoChecked($global->archive_build_mode_schedule == DUP_PRO_Archive_Build_Mode::DupArchive); ?> />
					<label for="archive_build_mode3_schedule">
						<?php DUP_PRO_U::_e("DupArchive"); ?>
					</label>
				</div> -->
			<?php endif; ?>

			<div class="engine-radio <?php echo ($is_zip_available) ? '' : 'engine-radio-disabled'; ?>">
				<input onclick="DupPro.UI.SetArchiveOptionStates_Schedule();" type="radio" name="archive_build_mode_schedule" id="archive_build_mode1_schedule"
					   value="<?php echo DUP_PRO_Archive_Build_Mode::Shell_Exec; ?>"
					   <?php echo DUP_PRO_UI::echoChecked($global->archive_build_mode_schedule == DUP_PRO_Archive_Build_Mode::Shell_Exec); ?> />
				<label for="archive_build_mode1_schedule"><?php DUP_PRO_U::_e("Shell Zip"); ?></label>
			</div>

			<div class="engine-radio">
				<input onclick="DupPro.UI.SetArchiveOptionStates_Schedule();" type="radio" name="archive_build_mode_schedule" id="archive_build_mode2_schedule"  
					   value="<?php echo DUP_PRO_Archive_Build_Mode::ZipArchive; ?>" <?php echo DUP_PRO_UI::echoChecked($global->archive_build_mode_schedule == DUP_PRO_Archive_Build_Mode::ZipArchive); ?> />
				<label for="archive_build_mode2_schedule"><?php DUP_PRO_U::_e("ZipArchive"); ?></label>
			</div>

			<br style="clear:both"/>

			<!-- SHELL EXEC  -->
			<div class="engine-sub-opts" id="engine-details-1_schedule" style="display:none">
				<p class="description" >
					<?php DUP_PRO_UI_Settings_General_Basic::getShellZipMessage($is_zip_available);	?>
				</p>
			</div>

			<!-- ZIP ARCHIVE  -->
			<div class="engine-sub-opts" id="engine-details-2_schedule" style="display:none; line-height:22px">
				<?php
					DUP_PRO_U::_e("Settings: ");
					echo (($global->ziparchive_mode == DUP_PRO_ZipArchive_Mode::Multithreaded)) ? DUP_PRO_U::__("Multi-Thread") : DUP_PRO_U::__("Single-Thread");
				?>

				<span id="dpro-ziparchive-mode-st_schedule">
					<!-- Silence -->
				</span>

				<span id="dpro-ziparchive-mode-mt_schedule">
					<?php echo $global->ziparchive_chunk_size_in_mb . DUP_PRO_U::__('MB buffer'); ?>
				</span><br/>
				<small><i><?php	DUP_PRO_U::_e('Note: ZipArchive settings are inherited from "Manual Builds".');?></i></small>
			</div>

			<!-- DUPARCHIVE: Turn on when alpha ready
			<div class="engine-sub-opts" id="engine-details-3_schedule" style="display:none">
				<p class="description">
					<?php
						DUP_PRO_U::_e("<i style='color:maroon'><i class='fa fa-exclamation-triangle'></i> <b>Note:</b> This option is currently in Alpha testing. "
							. "Please do NOT use this setting for scheduled backups on production servers!</i>");
					?>
				</p>
			</div>-->
		</td>
	</tr>
</table>


<!-- ===============================
PROCESSING -->
<h3 class="title"><?php DUP_PRO_U::_e("Processing") ?> </h3>
<hr size="1" />
<table class="form-table">
<tr>
	<th scope="row"><label><?php DUP_PRO_U::_e("Server Throttle"); ?></label></th>
	<td>
		<input type="radio" name="server_load_reduction" value="<?php echo DUP_PRO_Email_Build_Mode::No_Emails; ?>" <?php echo DUP_PRO_UI::echoChecked($global->server_load_reduction == DUP_PRO_Server_Load_Reduction::None); ?> />
		<label for="server_load_reduction"><?php DUP_PRO_U::_e("Off"); ?></label> &nbsp;
		<input type="radio" name="server_load_reduction" value="<?php echo DUP_PRO_Server_Load_Reduction::A_Bit; ?>" <?php echo DUP_PRO_UI::echoChecked($global->server_load_reduction == DUP_PRO_Server_Load_Reduction::A_Bit); ?> />
		<label for="server_load_reduction"><?php DUP_PRO_U::_e("Low"); ?></label> &nbsp;
		<input type="radio" name="server_load_reduction"  value="<?php echo DUP_PRO_Server_Load_Reduction::More; ?>" <?php echo DUP_PRO_UI::echoChecked($global->server_load_reduction == DUP_PRO_Server_Load_Reduction::More); ?> />
		<label for="server_load_reduction"><?php DUP_PRO_U::_e("Medium"); ?></label> &nbsp;
		<input type="radio" name="server_load_reduction"  value="<?php echo DUP_PRO_Server_Load_Reduction::A_Lot ?>" <?php echo DUP_PRO_UI::echoChecked($global->server_load_reduction == DUP_PRO_Server_Load_Reduction::A_Lot); ?> />
		<label for="server_load_reduction"><?php DUP_PRO_U::_e("High"); ?></label> &nbsp;
		<p class="description"><?php  DUP_PRO_U::_e("Throttle to prevent resource complaints on budget hosts. The higher the value the slower the backup.");  ?></p>
	</td>
</tr>
<tr valign="top">
	<th scope="row"><label><?php DUP_PRO_U::_e("Max Build Time"); ?></label></th>
	<td>
		<input style="float:left;display:block;margin-right:6px;" data-parsley-required data-parsley-errors-container="#max_package_runtime_in_min_error_container" data-parsley-min="0" data-parsley-type="number" class="narrow-input" type="text" name="max_package_runtime_in_min" id="max_package_runtime_in_min" value="<?php echo $global->max_package_runtime_in_min; ?>" />
		<p style="margin-left:4px;"><?php DUP_PRO_U::_e('Minutes'); ?></p>
		<div id="max_package_runtime_in_min_error_container" class="duplicator-error-container"></div>
		<p class="description">  <?php DUP_PRO_U::_e('Max build and storage time until package is auto-cancelled. Set to 0 for no limit.'); ?>  </p>
	</td>
</tr>
<tr valign="top">
	<th scope="row"><label><?php DUP_PRO_U::_e("Max Worker Time"); ?></label></th>
	<td>
		<input style="float:left;display:block;margin-right:6px;" data-parsley-required data-parsley-errors-container="#php_max_worker_time_in_sec_error_container" data-parsley-min="10" data-parsley-type="number" class="narrow-input" type="text" name="php_max_worker_time_in_sec" id="php_max_worker_time_in_sec" value="<?php echo $global->php_max_worker_time_in_sec; ?>" />
		<p style="margin-left:4px;"><?php DUP_PRO_U::_e('Seconds'); ?></p>
		<div id="php_max_worker_time_in_sec_error_container" class="duplicator-error-container"></div>
		<p class="description">
			<?php
			DUP_PRO_U::_e("Lower is more reliable but slower. Recommended max is $max_worker_cap_in_sec sec based on PHP setting 'max_execution_time'.");
			?>
		</p>
	</td>
</tr>
</table>

<p class="submit dpro-save-submit">
	<input type="submit" name="submit" id="submit" class="button-primary" value="<?php DUP_PRO_U::_e('Save Package Settings') ?>" style="display: inline-block;" />
</p>
</form>

<script>
jQuery(document).ready(function ($)
{

	DupPro.UI.SetDBEngineMode = function()
	{
		var isMysqlDump	= $('#package_mysqldump').is(':checked');
		var isPHPMode	= $('#package_phpdump').is(':checked');

		$('#dbengine-details-1, #dbengine-details-2').hide();
		switch (true) {
			case isMysqlDump : $('#dbengine-details-1').show(); break;
			case isPHPMode	 : $('#dbengine-details-2').show(); break;
		}
	}

	DupPro.UI.SetBuildType = function(select)
	{
		var value = $(select).val();
		console.log(value);
		$('#archive-build-manual-icon, #archive-build-schedule-icon').hide();
		$('#archive-build-manual, #archive-build-schedule').hide();
		switch (value) {
			case "1" : 
				$('#archive-build-manual-icon, #archive-build-manual').show();
				DupPro.UI.SetArchiveOptionStates();
				break;
			case "2" :
				$('#archive-build-schedule-icon,  #archive-build-schedule').show();
				DupPro.UI.SetArchiveOptionStates_Schedule();
			break;
		}
	}

	DupPro.UI.setZipArchiveMode = function ()
	{
		$('#dpro-ziparchive-mode-st, #dpro-ziparchive-mode-mt').hide();
		if ($('#ziparchive_mode').val() == 0) {
			$('#dpro-ziparchive-mode-mt').show();
		} else {
			$('#dpro-ziparchive-mode-st').show();
		}
	}

	DupPro.UI.SetArchiveOptionStates = function()
	{
		var php70 = <?php DUP_PRO_UI::echoBoolean(DUP_PRO_U::PHP70()); ?>;
		var isShellZipSelected   = $('#archive_build_mode1').is(':checked');
		var isZipArchiveSelected = $('#archive_build_mode2').is(':checked');
		var isDupArchiveSelected = $('#archive_build_mode3').is(':checked');

		if(isShellZipSelected || isDupArchiveSelected) {
			$("[name='archive_compression']").prop('disabled', false);
			$("[name='ziparchive_mode']").prop('disabled', true);
		} else {
			$("[name='ziparchive_mode']").prop('disabled', false);
			if(php70) {
				 $("[name='archive_compression']").prop('disabled', false);
			 } else {
				 $('#archive_compression_on').prop('checked', true);
				$("[name='archive_compression']").prop('disabled', true);
			}
		}

		$('#engine-details-1, #engine-details-2, #engine-details-3').hide();
		switch (true) {
			case isShellZipSelected		: $('#engine-details-1').show(); break;
			case isZipArchiveSelected	: $('#engine-details-2').show(); break;
			case isDupArchiveSelected	: $('#engine-details-3').show(); break;
		}
		DupPro.UI.setZipArchiveMode();
	}



	//INIT
    DupPro.UI.SetArchiveOptionStates();
	DupPro.UI.SetDBEngineMode();


	//SCHEDULE ENGINE:
	//TODO: Remove once DupArchive is solid
	DupPro.UI.SetArchiveOptionStates_Schedule = function()
	{
		var php70 = <?php DUP_PRO_UI::echoBoolean(DUP_PRO_U::PHP70()); ?>;
		var isShellZipSelected   = $('#archive_build_mode1_schedule').is(':checked');
		var isZipArchiveSelected = $('#archive_build_mode2_schedule').is(':checked');
		var isDupArchiveSelected = $('#archive_build_mode3_schedule').is(':checked');

		if(isShellZipSelected || isDupArchiveSelected) {
			$("[name='archive_compression_schedule']").prop('disabled', false);
		} else {
			if(php70) {
				 $("[name='archive_compression_schedule']").prop('disabled', false);
			 } else {
				 $('#archive_compression_on_schedule').prop('checked', true);
				$("[name='archive_compression_schedule']").prop('disabled', true);
			}
		}

		$('#engine-details-1_schedule, #engine-details-2_schedule, #engine-details-3_schedule').hide();
		switch (true) {
			case isShellZipSelected		: $('#engine-details-1_schedule').show(); break;
			case isZipArchiveSelected	: $('#engine-details-2_schedule').show(); break;
			case isDupArchiveSelected	: $('#engine-details-3_schedule').show(); break;
		}


		$('#dpro-ziparchive-mode-st_schedule, #dpro-ziparchive-mode-mt_schedule').hide();
		if ($('#ziparchive_mode').val() == 0) {
			$('#dpro-ziparchive-mode-mt_schedule').show();
		} else {
			$('#dpro-ziparchive-mode-st_schedule').show();
		}
	}

	DupPro.UI.SetArchiveOptionStates_Schedule();



});
</script>
