<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
devvn_creat_shipping_rates_table();
function devvn_creat_shipping_rates_table(){
	global $wpdb;
	$wpdb->hide_errors();	
	$collate = '';	
	if ( $wpdb->has_cap( 'collation' ) ) {
		$collate = $wpdb->get_charset_collate();
	}
	$table_name = $wpdb->prefix . 'woocommerce_devvn_district_shipping_rates';
	
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "
		  CREATE TABLE $table_name (
		  box_id bigint(20) NOT NULL auto_increment,
		  box_district longtext NOT NULL,
		  box_cost varchar(200) NOT NULL,
		  box_title varchar(200) NOT NULL,
		  shipping_method_id bigint(20) NOT NULL,
		  box_length varchar(200) NOT NULL,
		  box_width varchar(200) NOT NULL,
		  box_height varchar(200) NOT NULL,
		  box_max_weight varchar(200) NOT NULL,
		  box_cost_per_weight_unit varchar(200) NOT NULL,
		  box_cost_percent varchar(200) NOT NULL, 
		  box_advance longtext NOT NULL,
		  box_hasadvance int(2) NOT NULL DEFAULT 0,
		  box_shipdisable int(2) NOT NULL DEFAULT 0,
		  
		  PRIMARY KEY  (box_id)
		) $collate;";
	
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}else{
        if($wpdb->get_var("SHOW COLUMNS FROM $table_name LIKE 'box_cost_percent'") != 'box_cost_percent'){
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN box_length varchar(200) NOT NULL");
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN box_width varchar(200) NOT NULL");
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN box_height varchar(200) NOT NULL");
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN box_max_weight varchar(200) NOT NULL");
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN box_cost_per_weight_unit varchar(200) NOT NULL");
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN box_cost_percent varchar(200) NOT NULL");
        }
		if($wpdb->get_var("SHOW COLUMNS FROM $table_name LIKE 'box_hasadvance'") != 'box_hasadvance'){
			$wpdb->query("ALTER TABLE $table_name ADD COLUMN box_advance longtext NOT NULL");
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN box_hasadvance int(2) NOT NULL DEFAULT 0");
		}
		if($wpdb->get_var("SHOW COLUMNS FROM $table_name LIKE 'box_shipdisable'") != 'box_shipdisable'){
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN box_shipdisable int(2) NOT NULL DEFAULT 0");
		}
	}
}
function get_qh_option($matps = array(), $selected = ''){
	$get_list_quanhuyen = new Woo_Address_Selectbox_Class;
    $option = '';
	if($matps && is_array($matps)) {
	    foreach ($matps as $matp) {
            $matp_old = (isset($matp['location_code']))?$matp['location_code']:'';
            $matp_old = explode(':', $matp_old);
            $matp = (isset($matp_old[1]))?$matp_old[1]:'';
            $maCountry = (isset($matp_old[0]))?$matp_old[0]:'';
            if($maCountry == 'VN') {
                $list_quanhuyen = $get_list_quanhuyen->get_list_district($matp);
                $name_city = $get_list_quanhuyen->get_name_city($matp);
                if (is_array($list_quanhuyen) && !empty($list_quanhuyen)) {
                    $option .= '<optgroup label="'.esc_attr($name_city).'">';
                    $dwas_selected = '';
                    foreach ($list_quanhuyen as $value) {
                        if (is_numeric($selected)) {
                            $dwas_selected = selected($selected, $value['maqh'], false);
                        } elseif (is_serialized($selected)){
                            $dwas_selected_array = maybe_unserialize($selected);
                            if (in_array($value['maqh'], $dwas_selected_array)) {
                                $dwas_selected = 'selected="selected"';
                            }
                        }
                        $option .= '<option value="' . $value['maqh'] . '" ' . $dwas_selected . '>' . $value['name'] . '</option>';
                        $dwas_selected = '';
                    }
                    $option .= '</optgroup>';
                }
            }
        }
    }
	return $option;
}
function devvn_box_shipping_admin_rows( $method ) {
	global $wpdb;
	wp_enqueue_script( 'woocommerce_district_shipping_rate_rows' );

	$instance_id = intval($method->instance_id);		
	$zoneID = $wpdb->get_row( "SELECT zone_id FROM `".$wpdb->prefix."woocommerce_shipping_zone_methods` WHERE `instance_id` = ".$instance_id, ARRAY_A);
	$zoneID = isset($zoneID['zone_id'])?intval($zoneID['zone_id']):'';
	if($zoneID){
		$get_qh = $wpdb->get_results( "SELECT location_code 
										FROM `".$wpdb->prefix."woocommerce_shipping_zone_locations` 
										WHERE `zone_id` = ".$zoneID." 
										AND `location_type` = 'state'
									", ARRAY_A);
		if($get_qh && is_array($get_qh) && !empty($get_qh)){
		?>
		<table id="flat_rate_boxes" class="shippingrows widefat" cellspacing="0" style="position:relative;">
			<thead>
				<tr>
					<th class="check-column"><input type="checkbox"></th>
					<th><?php _e( 'Quận/huyện', 'devvn' ); ?></th>
					<th><?php _e( 'Phí vận chuyển', 'devvn' ); ?></th>
					<th><?php _e( 'Tiêu đề', 'devvn' ); ?></th>				
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th colspan="10"><a href="#" class="add-box button button-primary"><?php _e( 'Thêm quận/huyện', 'devvn' ); ?></a> <a href="#" class="remove button"><?php _e( 'Xóa lựa chọn', 'devvn' ); ?></a></th>
				</tr>
			</tfoot>
			<tbody class="flat_rate_boxes" data-boxes="">
				<?php
				$hasqh = $method->get_boxes();
				if($hasqh && is_array($hasqh) && !empty($hasqh)):				
				$stt = 0;
				foreach ($hasqh as $data):
				?>
				<tr class="flat_rate_box">
					<td class="check-column sort_dwas_td">
						<input type="checkbox" name="select" />
						<input type="hidden" class="box_id" name="box_id[<?php echo $stt;?>]" value="<?php echo $data['box_id'];?>" />
                        <span class="icon_sort_dwas"></span>
					</td>
					<td><select class="select chosen_select" multiple="multiple" name="box_district[<?php echo $stt;?>][]"><?php echo get_qh_option($get_qh,$data['box_district']);?></select></td>
					<td>
                        <input type="number" class="text" name="box_cost[<?php echo $stt;?>]" placeholder="<?php _e( '0', 'devvn' ); ?>" size="4" value="<?php echo $data['box_cost'];?>" />
                        <div class="district_shipping_advance">
                            <label><input type="checkbox" name="shipping_advance[<?php echo $stt;?>]" value="1" class="shipping_advance" <?php checked(1,$data['box_hasadvance'])?>/> Tùy chỉnh điều kiện</label>
                            <div class="dwas_price_list <?php echo (isset($data['box_hasadvance']) && $data['box_hasadvance'])?'dwas_show':'dwas_hidden';?>">
                                <div class="dwas_price_list_box">
                                    <div class="dwas_price_list_tr">
                                        <div class="dwas_price_list_td"><?php _e('Điều kiện giá order >=','devvn');?></div>
                                        <div class="dwas_price_list_td"><?php _e('Giá vận chuyển','devvn');?></div>
                                    </div>
                                    <?php
                                    $condition = maybe_unserialize($data['box_advance']);
                                    if($condition && is_array($condition)):?>
                                        <?php $stt2 = 0; foreach ($condition as $k=>$v){
                                        $dk = isset($v['dk'])?$v['dk']:0;
                                        $price = isset($v['price'])?$v['price']:0;
                                        ?>
                                        <div class="dwas_price_list_tr">
                                            <div class="dwas_price_list_td"><input type="number" class="input_district_condition" name="district_condition[<?php echo $stt;?>][dk_<?php echo $stt2;?>][dk]" min="0" value="<?php echo $dk;?>"></div>
                                            <div class="dwas_price_list_td"><input type="number" class="input_district_condition" name="district_condition[<?php echo $stt;?>][dk_<?php echo $stt2;?>][price]" min="0" value="<?php echo $price;?>"></div>
                                            <div class="dwas_price_list_td"><a href="javascript:void(0)" class="dwas_delete_condition"><?php _e('x','devvn')?></a></div>
                                        </div>
                                        <?php $stt2++;}?>
                                    <?php else:?>
                                        <div class="dwas_price_list_tr">
                                            <div class="dwas_price_list_td"><input type="number" class="input_district_condition" name="district_condition[<?php echo $stt;?>][dk_0][dk]" min="0"></div>
                                            <div class="dwas_price_list_td"><input type="number" class="input_district_condition" name="district_condition[<?php echo $stt;?>][dk_0][price]" min="0"></div>
                                            <div class="dwas_price_list_td"><a href="javascript:void(0)" class="dwas_delete_condition"><?php _e('x','devvn')?></a></div>
                                        </div>
                                    <?php endif;?>
                                </div>
                                <div class="dwas_price_list_tfoot">
                                    <a href="javascript:void(0)" class="dwas_add_condition"><?php _e('Thêm điều kiện','devvn')?></a>
                                </div>
                            </div>
                        </div>
                        <div class="district_shipping_disable">
                            <label><input type="checkbox" name="shipping_disable[<?php echo $stt;?>]" value="1" class="shipping_disable" <?php checked(1,$data['box_shipdisable'])?>/> Không vận chuyển tới đây</label>
                        </div>
                    </td>
					<td><input type="text" class="text" name="box_title[<?php echo $stt;?>]" placeholder="<?php _e( 'Ví dụ', 'devvn' ); ?>" value="<?php echo $data['box_title'];?>" /></td>	
				</tr>
				<?php $stt++;endforeach;endif;?>
			</tbody>
		</table>
		<script type="text/template" id="tmpl-district-rate-box-row-template">
		<tr class="flat_rate_box">
			<td class="check-column sort_dwas_td">
				<input type="checkbox" name="select" />
				<input type="hidden" class="box_id" name="box_id[{{{ data.index }}}]" value="{{{ data.box.box_id }}}" />
                <span class="icon_sort_dwas"></span>
			</td>
			<td><select class="select chosen_select" multiple="multiple" name="box_district[{{{ data.index }}}][]" data-value="{{{ data.box.box_district }}}"><?php echo get_qh_option($get_qh,'');?></select></td>
			<td>
                <input type="text" class="text" name="box_cost[{{{ data.index }}}]" placeholder="<?php _e( '0', 'devvn' ); ?>" size="4" value="{{{ data.box.box_cost }}}" />
                <div class="district_shipping_advance">
                    <label><input type="checkbox" name="shipping_advance[{{{ data.index }}}]" value="1" class="shipping_advance"/> Tùy chỉnh điều kiện</label>
                    <div class="dwas_price_list dwas_hidden">
                        <div class="dwas_price_list_box">
                            <div class="dwas_price_list_tr">
                                <div class="dwas_price_list_td"><?php _e('Điều kiện giá order >=','devvn');?></div>
                                <div class="dwas_price_list_td"><?php _e('Giá vận chuyển','devvn');?></div>
                            </div>
                            <div class="dwas_price_list_tr">
                                <div class="dwas_price_list_td"><input type="number" class="input_district_condition" name="district_condition[{{{ data.index }}}][dk_0][dk]" min="0"></div>
                                <div class="dwas_price_list_td"><input type="number" class="input_district_condition" name="district_condition[{{{ data.index }}}][dk_0][price]" min="0"></div>
                                <div class="dwas_price_list_td"><a href="javascript:void(0)" class="dwas_delete_condition"><?php _e('x','devvn')?></a></div>
                            </div>
                        </div>
                        <div class="dwas_price_list_tfoot">
                            <a href="javascript:void(0)" class="dwas_add_condition"><?php _e('Thêm điều kiện','devvn')?></a>
                        </div>
                    </div>
                </div>
                <div class="district_shipping_disable">
                    <label><input type="checkbox" name="shipping_disable[{{{ data.index }}}]" value="1" class="shipping_disable"/> Không vận chuyển tới đây</label>
                </div>
            </td>
			<td><input type="text" class="text" name="box_title[{{{ data.index }}}]" placeholder="<?php _e( 'Tiêu đề của hình thức vận chuyển', 'devvn' ); ?>" value="{{{ data.box.box_title }}}" /></td>	
		</tr>
		</script>
		<?php
		}
	}
}
function devvn_box_condition_price( $method ){
    $all_price_condition = maybe_unserialize($method->all_price_condition);
    $checked = null;
    $all_condition = array();
    if($all_price_condition && is_array($all_price_condition)){
        $checked = (isset($all_price_condition['checked']) && $all_price_condition['checked'] == 1)?1:0;
        $all_condition = isset($all_price_condition['all_condition'])?$all_price_condition['all_condition']:array();
    }
    ?>
    <div class="district_shipping_advance all_condition_district">
        <label><input type="checkbox" name="all_district_condition_checked" value="1" class="shipping_advance" <?php checked(1,$checked);?>/> Tùy chỉnh điều kiện cho toàn bộ quận/huyện</label>
        <div class="dwas_price_list <?php echo ($checked)?'dwas_show':'dwas_hidden';?>">
            <div class="dwas_price_list_box">
                <div class="dwas_price_list_tr">
                    <div class="dwas_price_list_td"><?php _e('Điều kiện giá order >=','devvn');?></div>
                    <div class="dwas_price_list_td"><?php _e('Giá vận chuyển','devvn');?></div>
                </div>
                <?php if($all_condition){?>
                <?php $stt = 0;foreach ($all_condition as $condition):
                $dk = isset($condition['dk'])?$condition['dk']:'';
                $price = isset($condition['price'])?$condition['price']:'';
                ?>
                <div class="dwas_price_list_tr">
                    <div class="dwas_price_list_td"><input type="number" class="input_district_condition" name="all_district_condition[dk_<?php echo $stt;?>][dk]" min="0" value="<?php echo $dk;?>"></div>
                    <div class="dwas_price_list_td"><input type="number" class="input_district_condition" name="all_district_condition[dk_<?php echo $stt;?>][price]" min="0" value="<?php echo $price;?>"></div>
                    <div class="dwas_price_list_td"><a href="javascript:void(0)" class="dwas_delete_condition"><?php _e('x','devvn')?></a></div>
                </div>
                <?php $stt++;endforeach;?>
                <?php }else{?>
                <div class="dwas_price_list_tr">
                    <div class="dwas_price_list_td"><input type="number" class="input_district_condition" name="all_district_condition[dk_0][dk]" min="0" value=""></div>
                    <div class="dwas_price_list_td"><input type="number" class="input_district_condition" name="all_district_condition[dk_0][price]" min="0" value=""></div>
                    <div class="dwas_price_list_td"><a href="javascript:void(0)" class="dwas_delete_condition"><?php _e('x','devvn')?></a></div>
                </div>
                <?php }?>

            </div>
            <div class="dwas_price_list_tfoot">
                <a href="javascript:void(0)" class="dwas_save_condition"><?php _e('Lưu điều kiện','devvn')?></a>
                <a href="javascript:void(0)" class="dwas_add_condition"><?php _e('Thêm điều kiện','devvn')?></a>
            </div>
        </div>
    </div>
    <?php
}
function devvn_box_shipping_admin_rows_process( $shipping_method_id ) {
	global $wpdb;

	// Clear cache
	$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_wc_ship_%')" );

	// Save rates
	$box_ids          = isset( $_POST['box_id'] ) ? array_map( 'intval', $_POST['box_id'] ) : array();
	$box_districts    = isset( $_POST['box_district'] ) ? array_map( 'wc_clean', $_POST['box_district'] ) : array();
	$box_costs        = isset( $_POST['box_cost'] ) ? array_map( 'wc_clean', $_POST['box_cost'] ) : array();
	$box_titles       = isset( $_POST['box_title'] ) ? array_map( 'wc_clean', $_POST['box_title'] ) : array();
    $box_hasadvances   = isset( $_POST['shipping_advance'] ) ? array_map( 'wc_clean', $_POST['shipping_advance'] ) : array();
    $box_shipdisables   = isset( $_POST['shipping_disable'] ) ? array_map( 'wc_clean', $_POST['shipping_disable'] ) : array();
    $district_conditions   = isset( $_POST['district_condition'] ) ? array_map( 'wc_clean', $_POST['district_condition'] ) : array();

	// Get max key
	$max_key = ( $box_ids ) ? max( array_keys( $box_ids ) ) : 0;

	for ( $i = 0; $i <= $max_key; $i++ ) {

		if ( ! isset( $box_ids[ $i ] )) {
			continue;
		}

		$box_id                   = $box_ids[ $i ];
		$box_district             = isset($box_districts[ $i ])?maybe_serialize($box_districts[ $i ]):'';
		$box_cost                 = isset($box_costs[ $i ])?$box_costs[ $i ]:'';
		$box_title                = isset($box_titles[ $i ])?$box_titles[ $i ]:'';
		$box_length = $box_width  = $box_height = $box_max_weight = $box_cost_per_weight_unit = $box_cost_percent = 0;
        $box_hasadvance           = isset($box_hasadvances[ $i ])?$box_hasadvances[ $i ]:'';
        $box_shipdisable           = isset($box_shipdisables[ $i ])?$box_shipdisables[ $i ]:'';
        $district_condition       = isset($district_conditions[ $i ])?maybe_serialize($district_conditions[ $i ]):'';

		if ( $box_id > 0 ) {

			// Update row
			$wpdb->update(
				$wpdb->prefix . 'woocommerce_devvn_district_shipping_rates',
				array(
					'box_district'           	=> $box_district,
					'box_cost'               	=> $box_cost,
					'box_title'              	=> $box_title,
					'shipping_method_id'     	=> $shipping_method_id,
					'box_length'               	=> $box_length,
					'box_width'                	=> $box_width,
					'box_height'               	=> $box_height,
					'box_max_weight'           	=> $box_max_weight,					
					'box_cost_per_weight_unit' 	=> $box_cost_per_weight_unit,
					'box_cost_percent'         	=> $box_cost_percent,
                    'box_advance'         	    => $district_condition,
                    'box_hasadvance'         	=> $box_hasadvance,
                    'box_shipdisable'         	=> $box_shipdisable,
				),
				array(
					'box_id' => $box_id
				),
				array(
					'%s',
					'%s',
					'%s',
					'%d',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
                    '%s',
                    '%d',
                    '%d',
				),
				array(
					'%d'
				)
			);		

		} else {

			// Insert row
			$result = $wpdb->insert(
				$wpdb->prefix . 'woocommerce_devvn_district_shipping_rates',
				array(
					'box_district'           	=> $box_district,
					'box_cost'               	=> $box_cost,
					'box_title'              	=> $box_title,
					'shipping_method_id'     	=> $shipping_method_id,
					'box_length'               	=> $box_length,
					'box_width'                	=> $box_width,
					'box_height'               	=> $box_height,
					'box_max_weight'           	=> $box_max_weight,
					'box_cost_per_weight_unit' 	=> $box_cost_per_weight_unit,
					'box_cost_percent'         	=> $box_cost_percent,
                    'box_advance'         	    => $district_condition,
                    'box_hasadvance'         	=> $box_hasadvance,
                    'box_shipdisable'         	=> $box_shipdisable,
				),
				array(
					'%s',
					'%s',
					'%s',
					'%d',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
                    '%s',
                    '%d',
                    '%d',
				)
			);
		}
	}
}

add_action('wp_ajax_woocommerce_district_rate_box_delete', 'woocommerce_district_rate_box_delete');
function woocommerce_district_rate_box_delete() {
	check_ajax_referer( 'delete-box', 'security' );

	if ( is_array( $_POST['box_id'] ) ) {
		$box_ids = array_map( 'intval', $_POST['box_id'] );
	} else {
		$box_ids = array( intval( $_POST['box_id'] ) );
	}

	if ( ! empty( $box_ids ) ) {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->prefix}woocommerce_devvn_district_shipping_rates WHERE box_id IN (" . implode( ',', $box_ids ) . ")" );
	}

	die();
}
add_action('wp_ajax_woocommerce_district_rate_array_to_serialize', 'woocommerce_district_rate_array_to_serialize');
function woocommerce_district_rate_array_to_serialize() {
    if(!is_admin()) wp_send_json_error();
    $data_form = isset($_POST['data_form'])?$_POST['data_form']:'';
    $output = array();
    if($data_form) {
        parse_str($data_form, $params);
        $all_district_condition = isset($params['all_district_condition'])?$params['all_district_condition']:array();
        $shipping_advance_checked = isset($params['all_district_condition_checked'])?1:0;
        $output['checked'] = $shipping_advance_checked;
        $output['all_condition'] = $all_district_condition;
        wp_send_json_success(maybe_serialize($output));
    }
    wp_send_json_error();
    die();
}