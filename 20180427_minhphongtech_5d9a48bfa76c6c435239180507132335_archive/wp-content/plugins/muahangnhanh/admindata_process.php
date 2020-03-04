<div class="wrap" >
    <h1>Danh sách đơn hàng</h1>
        <h2><?php _e('Hệ thống Xử lý đơn hàng', 'muahangnhanh') ?>
            <a class="add-new-h2"
               href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=mua-hang-nhanh'); ?>"><?php _e('Quay lại Danh sách', 'muahangnhanh') ?></a>
        </h2>
	<?php
	// BEGIN
	if(isset($_GET['id'])) {
	    global $wpdb;
        $id = $_GET['id'];
		$table_name = $wpdb->prefix . 'muahangnhanh';
		function check($check){
			$check = strip_tags(addslashes($check));
			return $check;
		}
		if (isset($_POST['submit']) && wp_verify_nonce($_POST['nonce'], basename(__FILE__))) {
		    	$sanpham = $_POST['sanpham'];
				$diachi = $_POST['diachi'];
				$soluong = $_POST['soluong'];
				$thanhtien = $soluong*$_POST['price'];
			$wpdb->update($table_name, array(
				'sanpham' =>$sanpham,
				'diachi'=> $diachi,
				'soluong' => $soluong,
				'thanhtien' => $thanhtien,
				),
				array(
					'id' => $id
				),
				array(
				"%s",
				"%s",
				"%s",
				"%s",
				),
				array("%s"));

		    }
		$count_order = $wpdb->get_var("SELECT * FROM $table_name WHERE id = '$id' ");
		if($count_order == 0)  { _e('kết quả bạn chọng không tồn tại','muahangnhanh'); }
		else {
					$item = $wpdb->get_row("SELECT * FROM $table_name WHERE id = '$id'", ARRAY_A, 0);

?>
		<div id="messages"></div>
        <div id="errors"></div>
		<div class="muahangnhanh-panel welcome-panel">
        <form id="form" method="POST">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__)) ?>"/>
            <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
            <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>
			<div class="muahangnhanh-panel-head">
			<h2>Sửa đơn hàng #<?php _e($id, 'muahangnhanh'); ?></h2>
			</div>
			<div class="muahangnhanh-panel-body">
				<label  class="muahangnhanh-label" for="sanpham"><b>Sản Phẩm Đặt Mua:</b></label>
				<textarea id="result" style="width:100%; height: 60px;" class="muahangnhanh-box result w50r" name="sanpham"><?php echo str_replace('\\','',$item[sanpham]) ?></textarea><br />
				<label class="muahangnhanh-label" for="diachi"><b>Địa chỉ nhận hàng:</b></label>
				<textarea id="result" style="width:100%; height: 60px;" class="muahangnhanh-box result w50r" name="diachi"><?php echo str_replace('\\','',$item[diachi]) ?></textarea><br />
				<label class="muahangnhanh-label" for="soluong"><b>Số lượng đặt:</b></label>
				<input type="number" style="width:100%" name="soluong" value="<?php echo $item[soluong]?>"><br />
				<label class="muahangnhanh-label" for="thanhtien"><b>Thành tiền:</b></label>
				<input type="text" style="width: 100%" name="thanhtien" value="<?php echo $item[thanhtien] ?>" disabled ><br />
				<input type="text" hidden name="price" value="<?php echo $item[thanhtien]/$item[soluong] ?>">
				<script>
					var price = jQuery('.muahangnhanh-panel-body').find('input[name="thanhtien"]').val()/jQuery('.muahangnhanh-panel-body').find('input[name="soluong"]').val();
					jQuery('.muahangnhanh-panel-body').find('input[name="soluong"]').change(function(){
						number = jQuery(this).val()*price;
						jQuery('.muahangnhanh-panel-body').find('input[name="thanhtien"]').val(number);
					});
					function addCommas(nStr)
					{
						nStr += '';
						x = nStr.split('.');
						x1 = x[0];
						x2 = x.length > 1 ? '.' + x[1] : '';
						var rgx = /(\d+)(\d{3})/;
						while (rgx.test(x1)) {
							x1 = x1.replace(rgx, '$1' + '.' + '$2');
						}
						return x1 + x2;
					}
				</script>
			</div>
			<div class="muahangnhanh-panel-foot">
			<input type="submit" id="submit" class="button-primary" name="submit" value="<?php _e('Cập nhật', 'muahangnhanh') ?>">
			</div>
        </form>
		<br />
		</div>
<?php		
		}
	}
	else {
		
		_e('<div class="update-message notice inline notice-alt warning-message notice-warning"><p>Bạn chưa chọn kết quả cần chỉnh sửa. Hãy quay lại danh sách và chọn "<b>Chỉnh sửa</b>"!!</p></div>','muahangnhanh');
		
	}
	// END
	?>
	
	</div>