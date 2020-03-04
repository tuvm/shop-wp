<div class="wrap">
	<h1>Danh sách đơn hàng</h1>
	
<?php
	// Include WP List Table
	if (!class_exists('WP_List_Table')) {
		require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
	}

	class muahangnhanh extends WP_List_Table {
		//    Contruct
		public function __construct()
		{
			parent::__construct([
				'singular' => __('Mua Hàng Nhanh', 'muahangnhanh'),
				'plural' => __('Mua Hàng Nhanh', 'muahangnhanh'),
				'ajax' => true
			]);
		}
			// Default Setting for column
		function column_default($item, $column_name)
		{
			return $item[$column_name];
		}
		function column_id($item)
        {
            if(current_user_can('manage_options'))
            {
				$arr_params = array('page' => 'muahangnhanh_process', 'id' => $item['id']);
                $actions = array(
                    'edit' => sprintf('<a href="'.add_query_arg( $arr_params ,admin_url('admin.php')).'"><strong>%s</strong></a>', __('Chỉnh sửa', 'muahangnhanh')),
                    'delete' => sprintf('<a class="delete" href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Xóa', 'muahangnhanh')),
                );
            }
            return sprintf('%s %s',
                '<strong>' . $item['id'] . '</strong>',
                $this->row_actions($actions)
            );
        }
		// Checkbox Column
        function column_cb($item)
        {
            return sprintf(
                '<input type="checkbox" name="id[]" value="%s" />',
                $item['id']
            );
        }

        // Show Column
        function get_columns()
        {
            $columns = array(
                'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
				'id' => __('ID', 'muahangnhanh'),
                'madonhang' => __('Mã Đơn Hàng', 'muahangnhanh'),
                'ten' => __('Họ Tên'),
				'sdt' => __('Số Điện Thoại'),
				'email' => __('Email'),
				'sanpham' => __('Sản Phẩm Đặt Mua'),
				'diachi' => __('Địa Chỉ Nhận'),
				'soluong' => __('Số Lượng'),
				'thanhtien'=> __('Thành Tiền'),
            );
            return $columns;
        }
		function get_sortable_columns()
        {
            $sortable_columns = array(
                'madonhang' => array('madonhang', true)
            );
            return $sortable_columns;
        }
		// Bulk action
        function get_bulk_actions()
        {
            $actions = array();
            if(current_user_can('manage_options')) {
                $actions = array(
                    'deletemul' => 'Xóa'
                );
            }
            return $actions;
        }

        // Bulk action
        function process_bulk_action()
        {
            //Security check
            if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {

                $nonce = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
                $action = 'bulk-' . $this->_args['plural'];

                if (!wp_verify_nonce($nonce, $action))
                    wp_die('Nope! Security check failed!');

            }
            if ('deletemul' === $this->current_action()) {
                $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
                if (is_array($ids)) $ids = implode('\',\'', $ids);
                if (!empty($ids)) {
                    global $wpdb;
					$table_name = $wpdb->prefix . 'muahangnhanh';
                    $resultd = $wpdb->query("DELETE FROM $table_name WHERE id IN('$ids')");
                    if ($resultd > 0)
                        echo '<div class="updated below-h2" id="message"><p>' . sprintf(__('<strong>Xóa thành công' . '</strong>. Số kết quả đã xóa: %d', 'muahangnhanh'), $resultd) . '</p></div>';
                    else {
                        echo '<div class="updated below-h2" id="message"><p>' . sprintf(__('Lỗi hệ thống. Vui lòng tải lại trang và thử lại hoặc liên hệ quản trị hệ thống CSDL')) . '</p></div>';
                    }


                }
            }
        }
		// Render column
        function prepare_items()
        {
            global $wpdb;
            $table_name = $wpdb->prefix . 'muahangnhanh'; // ơprefix]_orders

            $per_page = 10; // how much records will be shown per page

            $columns = $this->get_columns();

            $hidden = array();
            // Sortable column
            $sortable = $this->get_sortable_columns();

            // here we configure table headers, defined in our methods
            $this->_column_headers = array($columns, $hidden, $sortable);

            $this->process_bulk_action();
            // will be used in pagination settings
            $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

            // prepare query params, as usual current page, order by and order direction
            $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
            $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'madonhang';
            $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';

            // [REQUIRED] define $items array
            // notice that last argument is ARRAY_A, so we will retrieve array
            $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged * $per_page), ARRAY_A);

            // [REQUIRED] configure pagination
            $this->set_pagination_args(array(
                'total_items' => $total_items, // total items defined above
                'per_page' => $per_page, // per page constant defined at top of method
                'total_pages' => ceil($total_items / $per_page) // calculate pages count
            ));

        }
	}
	$tables = new muahangnhanh();
    // Delete Action
    if ('delete' === $tables->current_action()) {
        if (isset($_GET['id'])) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'muahangnhanh';
            $idd = $_GET['id'];
            $resultd = $wpdb->delete($table_name, array('id' => $idd));
            if ($resultd > 0)
                $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Xóa thành công kết quả <strong> ' . $idd . '</strong>. Số kết quả đã xóa: %d', 'muahangnhanh'), $resultd) . '</p></div>';
            else
                $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Lỗi hệ thống. Vui lòng tải lại trang và thử lại hoặc liên hệ quản trị hệ thống CSDL')) . '</p></div>';
        }
    }
	 $tables->prepare_items();
?>
	<form method="post" action="<?php echo plugins_url('export.php', __FILE__)?>">
        <input type="submit" name="export" value="Export">
	</form>
    <form id="persons-table" method="GET">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $tables->display() ?>
    </form>
    
    <?php
	//date_default_timezone_set("Asia/Ho_Chi_Minh");
	//echo "Today is " . date("ymdHis") . "<br>";
	//echo time();
	?>
</div>
<script>
    jQuery(document).ready(function () {
        jQuery("a.delete").click(function (e) {
            if (!confirm('Bạn có chắc chắn muốn XÓA kết quả? \nViệc xóa dữ liệu sẽ KHÔNG THỂ KHÔI PHỤC, chọn OK nếu bạn muốn xóa')) {
                e.preventDefault();
                return false;
            }
            return true;
        });
        jQuery("#doaction").click(function (e) {
            if (!confirm('Bạn có chắc chắn muốn XÓA tất cả kết quả đã chọn? \nViệc xóa dữ liệu sẽ KHÔNG THỂ KHÔI PHỤC, chọn OK nếu bạn muốn xóa')) {
                e.preventDefault();
                return false;
            }
            return true;
        });
    });
</script>
