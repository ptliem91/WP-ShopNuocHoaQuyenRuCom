<?php
/*
  Plugin Name: Woocommerce Hide Add To Cart Button
  Description: This is Hide Add To Cart Button plugin
  Version: 1.3
  Author: WPProcare
 */
 
 // add jQuery UI
function wppc_jquery_ui() {
    wp_register_style('jquery_ui' , plugin_dir_url(__FILE__). 'css/jquery-ui-timepicker-addon.css');
    wp_enqueue_style('jquery_ui');	
    wp_enqueue_script('jquery-time-picker' ,  plugin_dir_url(__FILE__). 'js/jquery-ui-timepicker-addon.js',  array('jquery' ));
    wp_enqueue_script('custom' ,  plugin_dir_url(__FILE__). 'js/custom_backend.js',  array('jquery' ));	
}
add_action('admin_head', 'wppc_jquery_ui');

// Add Custom Product Fileds
add_action('woocommerce_product_options_general_product_data', 'wppc_woocommerce_custom_product_data_field');
if (!function_exists('wppc_woocommerce_custom_product_data_field')) {
    function wppc_woocommerce_custom_product_data_field() {
        global $woocommerce, $post;
        echo '<div class="options_group">';
        woocommerce_wp_select(
                array(
                    'id' => 'woo_disable_add_to_cart_checkbox',
                    'label' => __('Show/Hide Add to Cart Button', 'woocommerce'),
                    'options' => array(
                        'show_button' => __('Show', 'woocommerce'),
                        'disable_button' => __('Hide', 'woocommerce'),
                    )
                )
        );
        woocommerce_wp_checkbox(
            array(
                'id' => 'woo_disable_add_to_cart_check',
                'label' => __('Check to use datetime', 'woocommerce'),
                'type' => 'checkbox', 
                )
        );
        woocommerce_wp_text_input(
            array(
                'id' => 'woo_disable_add_to_cart_date',
                'label' => __('Expiry date', 'woocommerce'),
                'type' => 'text', 
                )
        );
        echo '</div>';
       
    }
}

// Save Custom Product Fields
add_action('woocommerce_process_product_meta', 'wppc_woo_process_product_meta_fields_save');
add_filter( 'woocommerce_general_settings', 'wppc_add_custom_field_into_all' );
function wppc_add_custom_field_into_all( $settings ) {
    $settings[] = array( 'name' => __( 'Show/Hide all  Add to Cart Button', 'woocommerce' ), 'type' => 'title', 'desc' => '', 'id' => 'woocommerce_settings_all_product' );
                    
    $settings[] = array(
        'title'     => __( 'Show/Hide all Add to Cart Button', 'woocommerce' ),
        'id'        => 'woocommerce_select_field_all_product',
        'css'       => 'min-width:350px;',
        'class'     => 'checkbox',
        'type'      => 'checkbox',
        'desc_tip' =>  false,
    );

    $settings[] = array( 'type' => 'sectionend', 'id' => 'woocommerce_settings_all_product');
    
    return $settings;
    
}

/**
 * Product Meta Fields Save
 * @param type $post_id
 */
if (!function_exists('wppc_woo_process_product_meta_fields_save')) {
    function wppc_woo_process_product_meta_fields_save($post_id) {
    	$woocheckbox=sanitize_text_field($_POST['woo_disable_add_to_cart_checkbox']);
        $woo_disable_add_to_cart_checkbox = isset($woocheckbox) ? $woocheckbox : 'Show';
        update_post_meta($post_id, 'woo_disable_add_to_cart_checkbox', $woo_disable_add_to_cart_checkbox);

        $woocheck=sanitize_text_field($_POST['woo_disable_add_to_cart_check']);
        $woo_disable_add_to_cart_check = isset($woocheck) ? $woocheck : false;
        update_post_meta($post_id, 'woo_disable_add_to_cart_check', $woo_disable_add_to_cart_check);
        
        $woocommerce_text_field = sanitize_text_field($_POST['woo_disable_add_to_cart_date']);
        update_post_meta( $post_id, 'woo_disable_add_to_cart_date', esc_attr( $woocommerce_text_field ) );
    }
}

function wppc_remove() {
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
    add_action( 'woocommerce_after_shop_loop_item', 'wppc_woocommerce_template_loop_add_to_cart',10);
}

add_action( 'init', 'wppc_remove');


if ( ! function_exists( 'wppc_woocommerce_template_loop_add_to_cart' ) ) {
    function wppc_woocommerce_template_loop_add_to_cart( $args = array() ) { 
        global $product;
        $show_hide_option = get_post_meta( $product->id, 'woo_disable_add_to_cart_checkbox', 'false' );
        $check_use_date = get_post_meta( $product->id, 'woo_disable_add_to_cart_check', 'false' );
        $show_hide_datetime = get_post_meta( $product->id, 'woo_disable_add_to_cart_date', 'false' );
        $woocommerce_field_all_product = get_option( 'woocommerce_select_field_all_product' );
        $st_dt = new DateTime($show_hide_datetime); 
        $set_time = $st_dt->format('YmdHis');
        $current_time = current_time('YmdHis');
        if ($woocommerce_field_all_product == 'yes') {

        }else if($woocommerce_field_all_product == 'no'){
            if ( $product ) { 
                if(($show_hide_option == 'show_button') || ($show_hide_option == '')){
                    $defaults = array( 
                        'quantity' => 1,  
                        'class' => implode( ' ', array_filter( array( 
                                'button',  
                                'product_type_' . $product->get_type(),  
                                $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',  
                                $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',  
                    ))),  ); 
                    //$args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product ); 
                    wc_get_template( 'loop/add-to-cart.php', $args ); 
                }
                else if($show_hide_option == 'disable_button' && $check_use_date == 'yes' ){
                    if($set_time >= $current_time){
                        
                    }
                    else if($set_time < $current_time){
                        $defaults = array( 
                        'quantity' => 1,  
                        'class' => implode( ' ', array_filter( array( 
                                'button',  
                                'product_type_' . $product->get_type(),  
                                $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',  
                                $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',  
                    ))),  ); 
                    //$args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product ); 
                    wc_get_template( 'loop/add-to-cart.php', $args ); 
                    }
                }
                else {

                }
            } 
        }
    } 
}

add_action( 'woocommerce_single_product_summary','wppc_add_custom_field_into_single');
function wppc_add_custom_field_into_single(){
    global $product;
    $show_hide_option = get_post_meta( $product->id, 'woo_disable_add_to_cart_checkbox', 'false' );
    $check_use_date = get_post_meta( $product->id, 'woo_disable_add_to_cart_check', 'false' );
    $show_hide_datetime = get_post_meta( $product->id, 'woo_disable_add_to_cart_date', 'false' );
    $woocommerce_field_all_product = get_option( 'woocommerce_select_field_all_product' );
    $st_dt = new DateTime($show_hide_datetime); 
    $set_time = $st_dt->format('YmdHis');
    $current_time = current_time('YmdHis');
    if ($woocommerce_field_all_product == 'yes') {
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
    }else if($woocommerce_field_all_product == 'no'){
        if($show_hide_option == 'show_button'){
        
        }else if($show_hide_option == 'disable_button' && $check_use_date == 'yes'){
            if($set_time>=$current_time){
                remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
            }else{

            }
        }
        else if($show_hide_option == 'disable_button'){
            remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
        }
    }
}

