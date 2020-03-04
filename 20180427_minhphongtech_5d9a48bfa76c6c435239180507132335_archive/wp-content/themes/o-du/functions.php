<?php

add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
function custom_override_checkout_fields( $fields ) {
     unset($fields['billing']['billing_postcode']);
unset($fields['billing']['billing_country']);
 unset($fields['billing']['billing_address_2']);
 unset($fields['billing']['billing_company']);
 unset($fields['billing']['billing_address_1']);
     return $fields;
}

function tp_custom_checkout_fields( $fields ) {$fields['city']['label'] = 'Địa chỉ'; return $fields;
}
add_filter( 'woocommerce_default_address_fields', 'tp_custom_checkout_fields' );



function vantuan_meta_box(){ // add meta box
            add_meta_box('add-game','Thêm thông tin xe','display_add_tt','product');
        }
        add_action('add_meta_boxes','vantuan_meta_box'); // action huck
        function display_add_tt(){
         //tao truong  support android
            global $post;
            $trongtai = get_post_meta( $post->ID, 'trongtai', true );
            $dungtichxilanh = get_post_meta( $post->ID, 'dungtichxilanh', true );
            echo '<label for="trongtai">Trọng tải: </label>';
            echo '<input type="text" name="trongtai" value="'.$trongtai.'"/><br/>';

            echo '<label for="dungtichxilanh">Dung tích xi lanh: </label>';
            echo '<input type="text" name="dungtichxilanh" value="'.$dungtichxilanh.'"/><br/>';
        }
            function  display_save_tt($post_id){ // ham luu thong tin

               $trongtai =sanitize_text_field($_POST[trongtai]);
               update_post_meta( $post_id,'trongtai', $trongtai );

               $dungtichxilanh =sanitize_text_field($_POST[dungtichxilanh]);
               update_post_meta( $post_id,'dungtichxilanh', $dungtichxilanh );
            }
        add_action('save_post','display_save_tt');

 ?>

