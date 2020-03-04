<?php 
/**
 * Plugin Name: Dat hang nhanh
 * Plugin URI: https://web79.vn
 * Description: This is a plugin that allows us to checkout woo Ajax functionality in WordPress
 * Version: 1.2
 * Author: Dinh Hien
 * Author URI: https://web79.vn
 * License: GPL2
 */
add_action( 'woocommerce_single_product_summary', 'return_policy', 40 );
 
function return_policy() {
    echo do_shortcode('[muahangnhanh]');
}

 
/* Setting database from install plugin */
global $jal_db_version;
$my_plg_db_version = '1.0';
function dinhhien_web79_install() {
    global $wpdb;
    global $my_plg_db_version;
    $table_name = $wpdb->prefix . 'muahangnhanh';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
		madonhang text NOT NULL,
        ten text NOT NULL,
        sdt text(15) NOT NULL,
        email text NOT NULL,
        sanpham text NOT NULL,
        soluong mediumint(5) NOT NULL,
        diachi text NOT NULL,
		thanhtien float NOT NULL,
        PRIMARY KEY  (id),
		UNIQUE KEY id (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    add_option( 'my_plg_db_version', $my_plg_db_version );
}
register_activation_hook( __FILE__, 'dinhhien_web79_install' );

/* 
    -Register active and deactive setting
*/
register_activation_hook(__FILE__, 'muahangnhanh_activated');
function muahangnhanh_activated(){
    $default_setting = array(
        'host'=>'smtp.domain.com',
        'port'=>'25',
        'username'=>'example@doamin.com',
        'password'=>'example@doamin.com',
    );
    add_option('muahangnhanh_options', $default_setting);
}
register_deactivation_hook(__FILE__, 'muahangnhanh_deactivated');
function muahangnhanh_deactivated(){
    delete_option('muahangnhanh_options');
}

/* ------Add option page */
add_action('admin_menu', 'muahangnhanh_option_page');
function muahangnhanh_option_page(){
    // add_options_page($page_title, $menu_title, $capability, $menu_slug, $function)
    add_menu_page('Mua Hàng Nhanh', 'Mua Hàng Nhanh', 'manage_options','mua-hang-nhanh','muahangnhanh_data','',3);
   // add_menu_page('Mua Hang Nhanh Admin Page,', 'Mua Hang Nhanh','manage_options','mua-hang-nhanh','muahangnhanh_setting');
   add_submenu_page('mua-hang-nhanh','SMTP Setting,', 'SMTP Setting','manage_options','smtp-setting','muahangnhanh_setting');
	add_submenu_page('mua-hang-nhanh','Chỉnh sửa đơn hàng','Sửa đơn hàng','edit_posts','muahangnhanh_process','muahangnhanh_process');
}

function muahangnhanh_data(){
	include('admindata.php');
}
function muahangnhanh_process(){
	include('admindata_process.php');
}
function muahangnhanh_setting(){
?>
    <div class="wrap">
        <h1>Mua Hang Nhanh Admin Page</h1>
        <form method="post" action="options.php">
            <!-- settings_fields($option_group) -->
            <?php settings_fields('muahangnhanh_settings'); ?>
            <!--  do_settings_sections($page); -->
            <?php do_settings_sections('muahangnhanh-option-page'); ?>
            <?php submit_button(); ?>
            
        </form>
    </div>
<?php
}

/*
----Setup seting sections-----
*/
add_action('admin_init', 'muahangnhanh_setting_init');
function muahangnhanh_setting_init(){
    //add_settings_section($id, $title, $callback, $page)
    register_setting('muahangnhanh_settings','muahangnhanh_options');
    add_settings_section('muahangnhanh-section', 'Setting send mail for Mua Hang Nhanh', 'muahangnhanh_setting_setup', 'muahangnhanh-option-page');
    
    // Add option fields into section
    // add_settings_field( $id, $title, $callback, $page, $section, $arg)
    add_settings_field('muahangnhanh-host-id','SMTP Host', 'muahangnhanh_TextBox','muahangnhanh-option-page', 'muahangnhanh-section', array('name'=>'host') );
    add_settings_field('muahangnhanh-port-id','SMTP Port', 'muahangnhanh_TextBox','muahangnhanh-option-page', 'muahangnhanh-section', array('name'=>'port') );
    add_settings_field('muahangnhanh-username-id','SMTP User', 'muahangnhanh_TextBox','muahangnhanh-option-page', 'muahangnhanh-section', array('name'=>'username') );
    add_settings_field('muahangnhanh-password-id','SMTP Password', 'muahangnhanh_TextBox','muahangnhanh-option-page', 'muahangnhanh-section', array('name'=>'password') );
    add_settings_field('muahangnhanh-sendfrom-id','SMTP Send From', 'muahangnhanh_TextBox','muahangnhanh-option-page', 'muahangnhanh-section', array('name'=>'sendfrom') );
    add_settings_field('muahangnhanh-noticemail-id','Notice Email', 'muahangnhanh_TextBox','muahangnhanh-option-page', 'muahangnhanh-section', array('name'=>'noticemail') );
}

function muahangnhanh_setting_setup(){
    echo "<p>Set smtp host for send mail";    
}

function muahangnhanh_TextBox($args){
    extract($args);
    $optionArray = (array)get_option('muahangnhanh_options');
    $current_value = $optionArray[$name];
    echo '<input type="'.pass($name).'" name="muahangnhanh_options['.$name.']" value="'.$current_value.'">';
}
function pass($req){
    if($req == 'password') {
        return 'password';
    }
    else {
        return 'text';
    }
}

/*
    wp_mail SMTP
*/
function myphpmailer( PHPMailer $phpmailer ){
    $data = (array)get_option('muahangnhanh_options');
    $phpmailer->IsSMTP();
	$phpmailer->Host = $data['host'];
    $phpmailer->Port = $data['port']; // could be different
    $phpmailer->Username = $data['username']; // if required
    $phpmailer->Password = $data['password']; // if required
    $phpmailer->SMTPAuth = true; // if required
	if($data['port']=='587'){
		$secure = 'tls';
	}
	else if($data['port']=='465') {
		$secure = 'ssl';
	}
    $phpmailer->SMTPSecure = $secure; // enable if required, 'tls' is another possible value
    $phpmailer->CharSet  ='utf-8';
	$phpmailer->SetFrom($data['sendfrom'], get_option('blogname')); // Thông tin người gửi
}
add_action('phpmailer_init', 'myphpmailer');

/*
    Enquene script and style
*/
add_action( 'wp_enqueue_scripts', 'ajax_test_enqueue_scripts' );
function ajax_test_enqueue_scripts() {
	wp_enqueue_script( 'muahangnhanh', plugins_url( '/js/myscript.js', __FILE__ ), array('jquery'), '', true );
	wp_enqueue_style('muahangnhanh', plugins_url('/style/style.css', __FILE__));
}

/*
    Input form
*/
function formmail(){
    global $product;
    echo "
    <div class='form-group'>
        <input type='text' class='form-control' placeholder='Họ tên:' name='name' required>
    </div>
    <div class='form-group'>
      <input type='text' class='form-control' placeholder='Số điện thoại:' name='sdt' required>
    </div>
    <div class='form-group'>
      <input type='email' class='form-control' placeholder='Email của bạn:' name='email' required>
    </div>
    <div class='form-group'>
      <input type='text' class='form-control' placeholder='Địa chỉ nhận hàng:' name='address' required>
    </div>
    <div class='form-group'>
      <input type='number' class='form-control' placeholder='Số lượng mua hàng' name='qty' value='1' required min='1'>
    </div>
    <div class='form-group'>
      <input type='text' class='form-control' disabled name='total' required>
    </div>
    <button type='submit' class='btn btn-default' name='submit'>ĐẶT HÀNG</button><div class='web79loading' style='display:inline-block'></div>

";
}
add_shortcode('formmail','formmail');

/* Main function of plugin */
function muahangnhanh() {
    if( ! is_admin()){
    echo "<div class='clearfix'></div>";
    echo "<a data-popup-open='muahangnhanh' href='#'><div class='detailcall-1'><h3>ĐẶT HÀNG NHANH</h3><span>Giao hàng tận nơi miễn phí nội thành!</span></div></a>
        <div class='popup' data-popup='muahangnhanh'>
            <div class='popup-inner'>
                <div id='contact_form_pop'>
		    	<div class='form-title'>
			      	<h3>Đặt hàng nhanh</h3>
					<p>Giao hàng tân nơi, miễn phí giao hàng toàn quốc</p>
					<hr>
			    </div>
		      	<div class='form-content'>
			      	<div class='cottrai'>";
						//Get product image
							global $product;
							if ( has_post_thumbnail( $product->id ) )  {
				        	   $image = wp_get_attachment_image_src( get_post_thumbnail_id( $loop->post->ID ), 'single-post-thumbnail' );
				        		?> 
				        		<img src="<?php echo $image[0]; ?>">
				  			<?php } 
				   		//get title
							echo "<div class='title-wrapper'>";
						    echo $product->post->post_title;
							echo "</div>";
						//Get price
							$product_id=$product->id;
							$price=$product->get_price_html();
							echo $price;
							$price=$product->price;
							$from = get_option('blogname');
							$to = get_option('admin_email');
							$blog_url = get_site_url();
			echo"<p style='margin-top:10px; font-size:14px; color: black; padding: 0;'>Bạn vui lòng nhập đúng thông tin đặt hàng gồm: Họ tên, SĐT, Email, Địa chỉ để chúng tôi được phục vụ bạn tốt nhất !</p></div><div class='cotphai'>";
			echo do_shortcode('[formmail]');
			echo "</div>
				</div>
	    </div>
                <a class='popup-close' data-popup-close='muahangnhanh' href='#'>x</a>
            </div>
        </div>
        
    <script>".
	 "var price = '{$price}';
	  var from = '{$from}';
	  var blog_url = '{$blog_url}';
	  var to = '{$to}'; "."</script>    
    ";
    }	
}
add_shortcode('muahangnhanh','muahangnhanh');
