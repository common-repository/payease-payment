<?php
/*
Plugin Name: BWX PayEase Payment Gateway
Plugin URI: http://shop.bwxnet.com
Description: A Chinese Payment Gateway and Order System.
Version: 1.5
Author: Forrest Ling
Author URI: http://bwxnet.com/
*/
/*  Copyright Forrest Ling 
*/


global $bwx_db_version;
$bwx_db_version = "1.5" ;

global $wpdb,$bwx_table_name;
$bwx_table_name = $wpdb->base_prefix . "bwx_orders_a3";
//

require_once ('bwx_functions.php');

// for I18n
function bwx_init() {
    $plugin_dir = basename(dirname(__FILE__));
    load_plugin_textdomain( 'bwx_payment', FALSE , $plugin_dir );
}
add_action('init', 'bwx_init');

// install or upgrade db
require_once (dirname(__FILE__) . '/bwx_install.php');
register_activation_hook( __FILE__, 'bwx_db_install');

// add admin page
add_action('admin_menu', 'bwx_payment_add_pages');

function bwx_payment_add_pages() {
//    $cap = array ('author','editor','administrator');
    add_menu_page( __('BWX Payment','bwx_payment'), __('Payment Setting','bwx_payment'), 'administrator', __FILE__ , 'bwx_payment_setting_page');
    add_submenu_page( __FILE__ , __('Orders Page','bwx_payment'), __('Orders','bwx_payment'), 'author', 'Display-Orders-Author', 'bwx_display_orders_page_Author');
    add_submenu_page( __FILE__ , __('Orders Page','bwx_payment'), __('Orders','bwx_payment'), 'administrator', 'Display-Orders-Admin', 'bwx_display_orders_page_Admin');
}

//************************************************************************************************
//     bwx_payment_setting_page
//************************************************************************************************
function bwx_payment_setting_page() {
    global $wpdb;
    global $blog_id ;

//  create bwx_payment pages

    $bwx_payment_page_id = bwx_get_option( $blog_id, 'bwx_payment_page' );

    if ( ! $bwx_payment_page_id || get_post( $bwx_payment_page_id , ARRAY_A ) == NULL /* in case page was deleted*/ ) {
        $bwx_payment_page_id = create_bwx_payment_page ();
        bwx_update_option ($blog_id, 'bwx_payment_page' , $bwx_payment_page_id );
        ?> <div id="message" class="updated fade">
        <p><strong><?php echo __('BWX_Payment_page created ', 'bwx_payment') ;   ?></strong></p></div> <?php
    }
    
    $bwx_userinfo_page_id = bwx_get_option( $blog_id, 'bwx_userinfo_page' );
    if ( ! $bwx_userinfo_page_id || get_post( $bwx_userinfo_page_id , ARRAY_A ) == NULL  ) {
        $bwx_userinfo_page_id = create_bwx_userinfo_page();
        bwx_update_option ($blog_id, 'bwx_userinfo_page' , $bwx_userinfo_page_id );
        ?> <div id="message" class="updated fade">
        <p><strong><?php echo __('BWX_Userinfo_Page created ', 'bwx_payment') ;   ?></strong></p></div> <?php

    }


//  diaply var payease options

    $payease_gateway = array(   'v_mid'=> array ( __('v_mid','bwx_payment'), __('Payease Merchant ID','bwx_payment')),
                                'v_rcvname'=>array(__('v_rcvname','bwx_payment'), __('Payease Name','bwx_payment')),
                                'v_rcvaddr'=>array(__('v_rcvaddr','bwx_payment'), __('Payease Merchant Address','bwx_payment')),
                                'v_rcvtel'=>array(__('v_rcvtel','bwx_payment'), __('Payease Merchant Telephone','bwx_payment')),
                                'v_rcvpost'=>array(__('v_rcvpost','bwx_payment'), __('Payease Merchant Post Code','bwx_payment')),
                                'v_moneytype'=>array(__('v_moneytype','bwx_payment'), __('Currency Type, 0 for Chinese Yuan, 1 for US Dollar','bwx_payment')),
                                'v_secrete'=>array(__('secret','bwx_payment'), __( 'key Payease and you','bwx_payment'))
                              );

//    $payease_gateway = bwx_get_payease_options_array ();
// processing POST
    if ( $_POST[ 'action' ] == 'config') {
          check_admin_referer( 'payease_options' );
          foreach ( $payease_gateway as $key => $value ) {
                if ( $key == 'v_moneytype' && $_POST[ $key ] != '0' && $_POST[ $key ] != '1' ) { /* check money type */
                    bwx_update_option ($blog_id, 'v_moneytype', '0' );
                } else {
                    bwx_update_option ($blog_id, $key, $_POST[ $key ] );
                }
          }
    }
    if ( $_POST[ 'action' ] == 'delivery_option') {
        check_admin_referer( 'payease_options' );
        $delivery_array = bwx_option_array ( $_POST['delivery_option'] );
        bwx_update_option ($blog_id, 'delivery_option', $delivery_array );
    }
    if ( $_POST[ 'action' ] == 'payment_option') {
        check_admin_referer( 'payease_options' );
        $payment_array = array ();
        foreach ( $_POST as $k => $v ) {
            if ( substr ( $k, 0, 14  ) ==  'payment_option' )   $payment_array[] = $v;
        }
//        $payment_array = bwx_option_array ( $_POST['payment_option'] );
        bwx_update_option ($blog_id, 'payment_option', $payment_array );
    }

// display & setup options
    echo "<h2>". __('BWX Configuration Setting', 'bwx_payment') . "</h2>";
    echo "<h3>" . __('Configure Payment Setting' , 'bwx_payment') . "</h3>";
    echo "<table border='0'>";
    echo '<form method="POST">';
            echo '<input type="hidden" name="action" value="config" />';
            foreach (  $payease_gateway as $key =>$value ) {
                $option = bwx_get_option( $blog_id, $key );
                echo "<tr><td>$value[0] : </td><td>" . "<input type='text' name='" .$key. "' value='" . $option . "'  size='30' /> </td><td>  (". $value[1]." )</td></tr>";
            }
            wp_nonce_field ('payease_options');
            echo "<tr><td></td><td><input type='submit' value='".__('Submit', 'bwx_payment')."' /></td></tr>";
    echo "</form></table><br />";
    echo "<p>" . __( 'please set up payease parameters' , 'bwx_payment') . "</p>";

// display & setup payment gateways
//* v1.6   echo bwx_admin_set_gateways ();

//
    echo "<h3>" . __('Configure Delivery Options' , 'bwx_payment') . "</h3>";
    echo "<table border='0'>";
    echo '<form method="POST">';
            echo '<input type="hidden" name="action" value="delivery_option" />';
                $delivery_option = bwx_get_option( $blog_id, 'delivery_option' );   /* option is array */
                $delivery_string = bwx_array_option ( $delivery_option );
                echo "<tr><td>" . __('delivery_option','bwx_payment') . " : </td><td>" . "<input type='text' name='delivery_option' value='".$delivery_string."'  maxlength='256' size='64' /> </td></tr>";
                wp_nonce_field ('payease_options');
            echo "<tr><td></td><td><input type='submit' value='".__('Submit', 'bwx_payment')."' /></td></tr>";
    echo "</form></table><br />";
    echo "<p>". __( 'Format: Address1:cost1;Address2:cost2;... seperate with address and cost with `:`, seperate addresses with `;` ' , 'bwx_payment') . "</p>";
    echo "<p>" . __( 'Please set up delivery option. ' , 'bwx_payment') . "</p>";

//
//    $payment_options = array ( '取货时付款','送货时,货到付款','银行汇款(预付款)','网络付款(预付款)' );
    $payment_options_all = array ( __('Payment on Seller Site','bwx_payment'),__('Payment on the Delivery Site','bwx_payment'),__('Bank Transfer','bwx_payment'),__('Internet Payment','bwx_payment') );

    echo "<h3>" . __('Configure Payment Options' , 'bwx_payment') . "</h3>";
    echo "<table border='0'>";
    echo '<form method="POST">';
            echo '<input type="hidden" name="action" value="payment_option" />';
                $payment_options_sel = bwx_get_option( $blog_id, 'payment_option' );    /* option is array */
//                $payment_string = bwx_array_option ( $payment_option );
                echo "<tr><td>" . __('payment_option','bwx_payment') . " : </td><td>";
                $n = 0;
                foreach ( $payment_options_all as $v ) {
                    echo "<label><input type='checkbox' id='".$n."' name='payment_option".$n++."' value='".$v."' ";
                    if ( in_array ($v, $payment_options_sel ) )  echo " checked =\"yes\" " ;
                    echo " /> $v  <label>";
                }
                echo "</td></tr>";
                wp_nonce_field ('payease_options');
            echo "<tr><td></td><td id=\"payment_gateways_lists\"></td></tr>";
            echo "<tr><td></td><td><input type='submit' value='".__('Submit', 'bwx_payment')."' /></td></tr>";
    echo "</form></table><br />";
    echo "<p>" . __( 'Please set up payment option. ' , 'bwx_payment') . "</p>";
}

// **********************************************************************************
// seting page header jquery tab function
/*    v1.6
function bwx_admin_setpage_header () {
    global $wp_current_filter, $wp_filter ;
    // if the admin set parameter page
    if ( $_SERVER['QUERY_STRING'] == 'page=bwx_payment/bwx_payment.php' ) {
    echo '
    <script type="text/javascript">
jQuery(document).ready(function($) {
    $(function() {
        $("#adminpaymentstab").tabs();
    });
    if ( $("#3").attr("checked") ) {
        $("#payment_gateways_lists").html("<p> gateways list </p>");
    }  else {
        $("#payment_gateways_lists").html("");
    }
});
    </script>
    <style type="text/css">
.ui-tabs-nav {
    margin: 0;
    padding: 0;
    list-style: none;
  zoom: 1;
}
.ui-tabs-nav li {
    padding: 0;
    margin: 0 5px 0 0;
    float: left;
}
.ui-tabs-nav a {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    font-weight: bold;
    color: #999;
  text-decoration: none;
  display: block;
  padding: 5px 15px 3px 15px;
    border: 1px solid #999;
    border-bottom: none;
}
.ui-tabs-selected a {
  color: #000;
  background: #FFC;
    position: relative;
    top: 1px;
}
.ui-tabs-nav a:focus {
  outline: none;
}
.ui-tabs-panel {
    clear: left;
    border: 1px solid #999;
  margin: 0;
    padding: 10px;
    background: #FFC;
    width: 500px;
}
    .ui-tabs .ui-tabs-hide {
     display: none;
    }
    </style>
    ';
    }
}
add_action('admin_head', 'bwx_admin_setpage_header');
*/
//
/* v1.6
function bwx_admin_load_jquery () {
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-tabs');
}
add_action('init', 'bwx_admin_load_jquery');
//
*/
/*   v1.6
function bwx_admin_set_gateways () {
    $bwx_payment_gateways = array (
                 'payease' => array(
                                'gatewayname' => __('Payease','bwx_payment'),
                                'v_mid'=> array ( 'fieldname' =>   __('v_mid','bwx_payment'),
                                                  'comments' =>  __('Payease Merchant ID','bwx_payment')
                                         ),
                                'v_rcvname'=>array( 'fieldname' => __('v_rcvname','bwx_payment'),
                                                  'comments' => __('Payease Name','bwx_payment')
                                         ),
                                'v_rcvaddr'=>array( 'fieldname' => __('v_rcvaddr','bwx_payment'),
                                                  'comments' =>  __('Payease Merchant Address','bwx_payment')
                                        ),
                                'v_rcvtel'=>array(  'fieldname' => __('v_rcvtel','bwx_payment'),
                                                  'comments' =>   __('Payease Merchant Telephone','bwx_payment')
                                         ),
                                'v_rcvpost'=>array( 'fieldname' => __('v_rcvpost','bwx_payment'),
                                                  'comments' =>    __('Payease Merchant Post Code','bwx_payment')
                                        ),
                                'v_moneytype'=>array( 'fieldname' => __('v_moneytype','bwx_payment'),
                                                  'comments' =>  __('Currency Type, 0 for Chinese Yuan, 1 for US Dollar','bwx_payment')
                                        ),
                                'v_secrete'=>array( 'fieldname' => __('secret','bwx_payment'),
                                                  'comments' =>  __( 'key Payease and you','bwx_payment')
                                        )
                              ),
                 'paypal' => array(
                                'gatewayname' => __('Paypal','bwx_payment'),
                                'cmd'=> array ( 'fieldname' => __('cmd','bwx_payment'),
                                                 'comments' =>  __( 'Buy Now button or Donate button','bwx_payment'),
                                                'fieldoption' => array ( '_xclick' =>__('_xclick','bwx_payment'),
                                                                         '_donations' =>__('_donations','bwx_payment')
                                                                )
                                                ),
                                'business'=>array( 'fieldname' => __('business','bwx_payment'),
                                                 'comments' =>  __( 'Seller Email Address','bwx_payment')
                                                )
                              )
                );

    $str = '<div id="adminpaymentstab"><ul>';
    foreach (  $bwx_payment_gateways as $k => $v  ) {
        $str .= '<li><a href="#'. $k .'">'.$v['gatewayname'].'</a></li>';
    }
    $str .= '</ul>';

    foreach (  $bwx_payment_gateways as $k => $v  ) {
        $str .= '<div id="'.$k.'">';
        $str .= '<form method="post" name="'.$k.'" >';
        foreach ( $v as $k1 => $v1 ) {
            if ( $k1 != 'gatewayname' )  {
                $str .= $v1['fieldname'] . ' : ';
                if ( ! $v1['fieldoption'] ) {
                    $str .= '<input name="'.$k1.'" value="" />';
                } else {
                    $str .= '<select name="'.$k1.'">';
                    foreach ( $v1['fieldoption'] as $k2 => $v2 ) {
                        $str .='<option value="'.$k2.'"> '.$v2.' </option>';
                    }
                    $str .= '</select>';
                }
                if ( $v1['comments'] ) $str .=' ('.$v1['comments'].')';
                $str .='<br />'  ;
            }
        }
        $str .= '<input type="submit" name="'.$k.'" value="'.__('Submit','bwx_payment').'" ></form>';
        $str .= '</div>';
    }
    
    $str .= '</div>';
    return $str ;
}
*/


// ********************************************************************************
// functions of page one
// ********************************************************************************

function create_bwx_payment_page () {
    global $user_ID;
    $bwx_payment_page = array(
      'post_title' => __('Payment Return Page', 'bwx_payment'),
      'post_content' => '',
      'post_status' => 'publish',
      'post_type' => 'page',
      'post_author' => $user_ID,
      'menu_order' => 100,
      'comment_status' => 'closed' ,
      'ping_status' => 'closed'
    );
    return wp_insert_post( $bwx_payment_page );
}

function create_bwx_userinfo_page () {
    global $user_ID;
    $bwx_userinfo_page = array(
      'post_title' => __('User Information Page', 'bwx_payment'),
      'post_content' => '',
      'post_status' => 'publish',
      'post_type' => 'page',
      'post_author' => $user_ID,
      'menu_order' => 101,
      'comment_status' => 'closed' ,
      'ping_status' => 'closed',
      'post_parent' => bwx_get_option( $blog_id, 'bwx_payment_page' )
    );
    return wp_insert_post( $bwx_userinfo_page );
}

// *********************  filter the content *******************************
//
function bwx_filter_the_content ( $content ) {
    global $post, $blog_id;
    $my_string ='';

    $bwx_payment_page_id = bwx_get_option( $blog_id, 'bwx_payment_page' );
    $bwx_userinfo_page_id = bwx_get_option( $blog_id, 'bwx_userinfo_page' );

// feedback page
    if (  $bwx_payment_page_id == $post->ID  ) {
        $my_string = bwx_payment_page();
    } elseif ( $bwx_userinfo_page_id == $post->ID ) {
// userinfo page
        $my_string = bwx_userinfo_page();
    } else {
        $my_string = bwx_buy_button ( $post->ID  );
        return '<div style="float:right; width:120px; height:80px; display: inline; align:center; " >'.$my_string.'</div>'.$content ;
    }
    return $content . $my_string;
}
add_filter ('the_content', 'bwx_filter_the_content' );

//*********************************************************************************
//   bwx_payment_page
//*********************************************************************************
function bwx_payment_page(){
    global $wpdb, $bwx_table_name ;
    $my_string =   " <br/> ". __('Thanks for Payment！','bwx_payment') .  "<br/>  " ;

    if ( !$_POST['v_oid'] ) return " no v_oid ";
    $check =  $_POST['v_oid'] . $_POST['v_pstatus'] .$_POST['v_pstring'] . $_POST['v_pmode'] ;
    $secrete = get_option ('v_secrete');
    $v_md5info = bin2hex ( mhash (MHASH_MD5, $check , $secrete )) ;
    if (  $v_md5info != $_POST['v_md5info'] ) return " secrete error !";

    $order_id = explode ( '-', $_POST['v_oid'] );
    $order_id = $order_id[2];
    $sql = " select total, order_status, v_moneytype from $bwx_table_name where id = $order_id ";
    $row =  $wpdb->get_row("$sql");
    if ( $row->total != $_POST['v_amount'] ) return " total error !";
    if ( $row->order_status != __('Order','bwx_payment') ) return " order status error !";
    if ( $row->v_moneytype != $_POST['v_moneytype'] ) return " moneytype error !";

    $order_status = __('Paid','bwx_payment');
    $order_status = bwx_db_validation ( $order_status );

    $v_pstatus =  bwx_db_validation ( htmlspecialchars( $_POST['v_pstatus'] ) );
    $v_pstring =  bwx_db_validation ( htmlspecialchars ($_POST['v_pstring']) );
    $v_pmode   =  bwx_db_validation ( htmlspecialchars( $_POST['v_pmode'] ) );
    $v_pstatus = mb_convert_encoding( $v_pstatus, "UTF-8","GB2312");
    $v_pstring = mb_convert_encoding( $v_pstring, "UTF-8","GB2312");
    $v_pmode = mb_convert_encoding( $v_pmode, "UTF-8","GB2312");

    $wpdb->query("UPDATE $bwx_table_name SET order_status = '". $order_status ."' , v_pstatus = '".$v_pstatus."' , v_pstring = '". $v_pstring ."' , v_pmode = '".$v_pmode. "' WHERE id = $order_id ");
//
    $my_string .= $order_status . "<br />" . $v_pstatus . "<br />" . $v_pstring . "<br />". $v_pmode ;


//
    return "Thanks !" . $my_string;
}

// ******************* bwx buy button ****************************
//
function bwx_buy_button ( $p_id = 0 ) {
    global $blog_id;
    if ( $p_id == 0 ) return '';
    $bwx_userinfo_page_id = bwx_get_option( $blog_id, 'bwx_userinfo_page' );
    $bwx_meta = get_post_custom( $p_id );
// get option_select, shipment_select
    if ( is_numeric ( $bwx_meta[ __('Price','bwx_payment')][0] ) ) {

// print the buy button
        $price = $bwx_meta[ __('Price','bwx_payment')][0];
        $currency = bwx_get_moneytype ();
        $secrete = bin2hex ( mhash (MHASH_MD5, $p_id . $price , 'secret' )) ;
        $product_string  = '<table><tr><td align="center">';
        $product_string .= '<b>' . __('Price', 'bwx_payment') . ':'. $price . $currency .'</b>';
        $product_string .= '</td></tr>';
        $product_string .= '<tr><td align="center" >';
        $product_string .= '<form name="formbuy'.$p_id.'" method="post" action="'. get_permalink( $bwx_userinfo_page_id ) .'">';
        $product_string .= '<input type="hidden" name="from_product_page" value="buy" > ';
        $product_string .= '<input type="hidden" name="secrete" value="'. $secrete .'" > ';
        $product_string .= '<input type="hidden" name="price" id="uprice'.$p_id.'" value="'. $price .'" > ';
        $product_string .= '<input type="hidden" name="product_page_id" value="'. $p_id .'" > ';
        if ( WPLANG == 'zh_CN' ) {
            $buynow_img = "/wp-content/plugins/bwx_payment/buynow.gif";
        } else {
            $buynow_img = "/wp-content/plugins/bwx_payment/buynow_en.gif";
        }
        $product_string .= '<img src="'.get_option('home'). $buynow_img . '" onclick="document.formbuy' .$p_id. '.submit()" >';
        $product_string .= '</form >';
        $product_string .= '</td></tr></table>';
        return $product_string;
    } else {
        return '';
    }
}

// ********************************************************************************
// bwx user info page
// ********************************************************************************
function bwx_userinfo_page () {
    global $post, $wpdb,$blog_id;
    $order_string ='';

// processing the Post self
    if ( $_POST[ 'from_userinfo' ] == 'confirm_order' ) {
        $secrete_check = bin2hex ( mhash (MHASH_MD5, $_POST['post_id'] . $_POST ['product_price'] , 'secret' )) ; /* generate for */
        if ( $secrete_check == $_POST['secrete']  ) {
            $order_string = "<h4>"  . __('Thank You for Your Order !','bwx_payment') . "</h4>" ;

// insert into the DB and submit to payment gateway.
            $order_string .= bwx_insert_db ();
        }  else {
                $order_string .= "check secrete error !";
        }
    } elseif ( $_POST[ 'from_product_page' ] == "buy" ) {
        $order_string .= bwx_buyer_page ();

    } else {
            $order_string .= __('post secrete code error !', 'bwx_payment');
    }
    return $order_string;
}




/********************************  bwx_buyer_page *******************************/
//
function bwx_buyer_page () {
        
// processing the Post from product page
        $check_s = bin2hex ( mhash (MHASH_MD5, $_POST['product_page_id'] . $_POST ['price'], 'secret' )) ;
        if (  $check_s == $_POST['secrete'] )  {
            $secrete = bin2hex ( mhash (MHASH_MD5, $_POST['product_page_id'] . $_POST ['price'], 'secret' )) ; /* generate for */
// form subbmit to DataBase and Payease.
            $order_string .= bwx_ajax_userinfo ();  /* java functions */
            
// product information: img and title and price . global table #1
            $bwx_meta = get_post_custom( $_POST['product_page_id'] );
            $Image =  $bwx_meta[ __('Image','bwx_payment')][0];
            if ( ! $Image ) $Image = get_option('home') . '/wp-content/plugins/bwx_payment/nopic.jpg' ;
            $Price =  $bwx_meta[ __('Price','bwx_payment')][0];
            if ( $Price != $_POST ['price'] ) return  __('Price Error !','bwx_payment');

            $title = bwx_post_title ( $blog_id , $_POST['product_page_id'] );
            $currency = bwx_get_moneytype ();
            $order_string .='<table width="100%" border="0" ><tr>';
            $order_string .='<td><img src="'.$Image.'" height="100" width="100" ></td>';
            $order_string .='<td>'. __('Product Name', 'bwx_payment').'<b>:'. $title.'</b><br/>'.__('Price','bwx_payment') .'<b>:'. $Price. '</b>'.$currency.'</td>';
            $order_string .='</tr></table><br/>';

// form start
            $order_string .= '<form align="left" name="formuserinfo" id="formuserinfo" method="post" onsubmit="return false" >';
// hidden para
            $order_string .= '
                <input type="hidden" name="from_userinfo" value ="confirm_order"  >
                <input type="hidden" name="secrete" value ="'. $secrete .'"  >
                <input type="hidden" name="post_id" value ="'. $_POST['product_page_id'] .'"  >
                <input type="hidden" name="product_price" value ="'. $_POST ['price'] .'"  >
                <input type="hidden" name="client_ip" value ="'. $_SERVER ['REMOTE_ADDR'] .'"  >
                ';
// order information  global table #2
            $order_string .= '<table width="100%" style="border: 1px solid #800000;" >';
// order details
            $alert_userinfo = __('Please input a number for qty.' ,'bwx_payment'); // javascript alter parameter
            $order_string .= '<tr><td colspan="2" align="left" ><h3>  '. __('Order Information', 'bwx_payment') . ' : </h3></td></tr>';
            $order_string .= "<tr><td colspan=\"2\" align=\"left\">".__('Product Information：','bwx_payment')."</td></tr>" ;

            $order_string .='<tr><td colspan="2">';
//            $total =  $_POST ['price']  *  $_POST ['quantity'] ;
            $order_string .= "<table id='tablep' border='0' width='100%' ><tr><th width='50%'>".__('Product Name','bwx_payment')."</th><th width='20%'>".__('Unit Price','bwx_payment')."</th><th width='10%'>".__('Qty','bwx_payment')."</th><th width='20%'>" . __('Subtotal','bwx_payment'). "</th></tr>" ;
            $product_name = bwx_post_title ( $blog_id, $_POST['product_page_id'] );
//            $order_string .= "<tr><td>". $product_name ."</td><td>". $_POST ['price']."</td><td>".$_POST ['quantity']."</td><td>".$total."</td></tr>" ;
            $order_string .= "<tr><td>". $product_name ."</td><td><span id='tduprice'>". $_POST ['price']."</span>".$currency."</td>";
            $order_string .= "<td><input type='text' name='product_qty' id='quantity' value='1' onblur='userinfo(\"".$alert_userinfo."\", \"".$_POST ['price']. "\")' size='3' maxlength='3' > </td>";
//subtotal
            $order_string .= "<td><span id='subtotal'>";
            $order_string .= '<script type="text/javascript">iniuprice("'.$Price.'" );</script>';
            $order_string .= "</span>".$currency."</td></tr>" ;
            $order_string .= "</table>" ;
            $order_string .= "</td></tr>" ;
            
            $order_string .= "<tr><td colspan=\"2\" align=\"left\">".__('Order Options：','bwx_payment')."</td></tr>" ;

// product options

            if ( $bwx_meta[ __('Price','bwx_payment')] ) {
                $n = 0;
                foreach ( $bwx_meta as $options => $lists  ) {
                    if ( $options != __('Price','bwx_payment') && $options != __('Delivery','bwx_payment')
                    && substr($options,0,1) !='_' && $options != __('Image','bwx_payment') ) {
                    $order_string .= '<tr><td width="30%" align="right" >';
                    $order_string .= $options . ' :</td><td width="70%" align="left" ><select name="product_options_'. $n .'" >';
                    $list = explode (';', $lists[0] );
                    foreach ( $list as $k => $v ) {
                        $order_string .= '<option value="'. $options .':'. $v.'">'. $v .'</option>';
                    }
                    $order_string .= '</select>';
                    $order_string .= '</td></tr>';
                    $n++;
                    }
                }
            }

// delivery option :
            $delivery_options = bwx_get_option ( $blog_id, 'delivery_option' );
            $s = '';
            $ini_shipment_fee = 0; $n = 0;
            foreach ( $delivery_options as $k => $v) {
                foreach ( $v as $k1 => $v1 ) {
                    $s .= "<option value='" . $k1 . ":" .$v1."' >" . $k1. " ". __('Shipment Cost','bwx_payment') .":".$v1. " ".$currency . "</option>";
                    if ( $n++ == 0 ) $ini_shipment_fee =  $v1;
                }
            }

            $order_string .= '<tr><td width="30%" align="right" >'. __('Delivery Method', 'bwx_payment') .' :</td>
                <td width="70%" align="left">
                <select name="delivery_method" id="deliveryoption" style="width: 20em;" onblur="userinfo(\''.$alert_userinfo.'\', \''.$_POST ['price'].'\')" >' .$s. '</select>
                </td></tr>
            ';

//payment option :
            $internet_payment_gateway = __('Internet Payment','bwx_payment');
            $payment_options = bwx_get_option ( $blog_id, 'payment_option' );
            $s = '';
            foreach ( $payment_options as  $v) {
                 $s .= "<option value='".$v."'  >" . $v .  "</option>";
            }
            $order_string .= '<tr><td width="30%" align="right" >'. __('Payment Method', 'bwx_payment') .' :</td>
                <td width="70%" align="left">
                <select name="payment_method" id="payment_method" style="width: 20em;"  >' .$s. '</select>
                </td></tr>
            ';
/*
//            $order_string .='<div id="paymenthideshow" style="display:none"><tr><td colspan="2">'. __('payment gateway','bwx_payment') .'</td></tr></div>';
            $order_string .='<tr><td colspan="2">';
            $displaytext = __('Payease Payment Gateway','bwx_payment');
//            $order_string .='<div id="paymenthideshow" ><script type="text/javascript"> paymentdisplay("'.$internet_payment_gateway.'","'.$displaytext .'");</script></div>';
            $order_string .= '<div id="paymenthideshow"><label><input type="radio" checked="checked" />'.$displaytext.'</label></div>';
            $order_string .='</td></tr>';
*/
            $order_string .= "<tr><td colspan=\"2\" align=\"left\">".__('Delivery Cost and Total：','bwx_payment')."</td></tr>" ;
            $order_string .='<tr><td width="30%" align="right" >'.__('Delivery Cost','bwx_payment'). ':</td><td align="left" >';
            $order_string .= '<span id="ShowDeliveryCost">';
            $order_string .= '<script type="text/javascript"> inicost(); </script> ';
            $order_string .= '</span>' . $currency;
            $order_string .="</td></tr>";
            $order_string .='<tr><td width="30%" align="right">'.__('Total','bwx_payment'). ':</td><td align="left" >';
            $order_string .= '<input name="total" type="hidden" id="inputtotal" value="" >';
            $order_string .= '<span id="userinfototal">';
            $order_string .= '<script type="text/javascript">iniprice("'.$Price.'");</script>';
            $order_string .= '</span>' . $currency ;
            $order_string .="</td></tr>";
            $order_string .=  '</table>';
            
            
            
// user information input  global table #3
            $order_string .= '<table width="100%" border="0" >';
            $order_string .= '<tr><td colspan="2" align="left" ><h3> '. __('Please Input Buyer Information', 'bwx_payment') . ' </h3></td></tr>
                <tr><td width="30%" align="left" >'. __('Buyer Name', 'bwx_payment') .' : </td><td width="70%" align="left"> <input type="text" name="buyer_name" id="buyer_name" size="24" maxlength="24" value =""  ></td></tr>
                <tr><td width="30%" align="left" >'.__('Buyer Tel', 'bwx_payment') .': </td><td width="70%" align="left"> <input type="text" name="buyer_tel" id="buyer_tel" size="16" maxlength="16" value =""  ></td></tr>
                <tr><td width="30%" align="left" >'.__('Buyer Email', 'bwx_payment') .': </td><td width="70%" align="left"> <input type="text" name="buyer_email" id="buyer_email" size="24" maxlength="48" value =""  ></td></tr>
                ';

// display shipment input
            $order_string .= '<tr><td colspan="2" align="left" ><h3>  '. __('Please Input Shipment Information', 'bwx_payment') . ' : </h3></td></tr>
                <tr><td colspan="2" align="left" >' . __('Please Advise Delivery Address.','bwx_payment') . '</td></tr>
                <tr><td width="30%" align="left" >'. __('Shipment name', 'bwx_payment') . ' :</td><td width="70%" align="left"> <input type="text" name="shipment_name" size="24" maxlength="24" value =""  ></td></tr>
                <tr><td width="30%" valign="top" align="left" >'. __('Shipment Address', 'bwx_payment') . ':</td><td width="70%" align="left"> <input type="text" name="shipment_address" size="24" maxlength="64" value ="" ></td></tr>
                <tr><td width="30%" align="left" >'. __('Shipment City', 'bwx_payment') . ' : </td><td width="70%" align="left"> <input type="text" name="shipment_city" size="24" maxlength="24" value =""  ></td></tr>
                <tr><td width="30%" align="left" >'. __('Shipment Country', 'bwx_payment') . ' : </td><td width="70%" align="left"> <input type="text" name="shipment_country" size="24" maxlength="24" value =""  ></td></tr>
                <tr><td width="30%" align="left" >'. __('Post Code', 'bwx_payment') . ' : </td><td width="70%" align="left"> <input type="text" name="shipment_postcode" size="12" maxlength="12" value =""  ></td></tr>
                <tr><td width="30%" align="left" >'. __('Shipment Telephone', 'bwx_payment') . ': </td><td width="70%" align="left"><input type="text" name="shipment_tel" size="20" maxlength="20" value =""  ></td></tr>
                ';
//
            $alert1  = __(' Please input the buyer name .', 'bwx_payment') ;
            $alert2  = __(' Please input the buyer tel or buyer email .','bwx_payment');
            $order_string .= '<tr><td width="30%" align="left" >'.__('Confirm Order', 'bwx_payment') .':</td><td width="70%" align="left"> <input type="button"  value="'.__('Submit','bwx_payment').'"  onclick="chkbuyerinfo(\''.$alert1.'\',\''. $alert2. '\', \''.$alert_userinfo. '\' )"  ></td></tr>';
            $order_string .="</table>";
            $order_string .= '</form>';
///            $order_string .='<script type ="text/javascript" > document.getElementById("formuserinfo").onsubmit = chkbuyerinfo();  </script>';

            return $order_string;
        }
}

// submit to payease
//

/*
function display_payease_buy_botton ( $content ) {
    global $post;
    $string = " Post ID is: ". $post->ID;
    $string .= '
    ';
    return $content. $string;
}
add_filter ('the_content', 'display_payease_buy_botton' );
*/

// ********************************************************************************
// admin page: display the order status page
// ********************************************************************************
function bwx_display_orders_page_Admin () {

    display_orders_page ( 'admin' );


}

function bwx_display_orders_page_Author () {

    display_orders_page ( 'author' );
}

// ********************************************************************************
//
function display_orders_page ( $author_or_admin ) {

    global $wpdb,$bwx_table_name,$user_level;
    $BWX = new BWX_Filter();
// process actions
    if (!empty ($_POST['action'] )) {

        check_admin_referer ('bwx_orders');
        switch ( $_POST['action']) {
            case "delete":
                bwx_delete_order( $wpdb, $bwx_table_name, $_POST['order_id'] );
                break;
            case "details":
                bwx_display_details ( $wpdb,$bwx_table_name,$_POST['order_id']);
                return;
                break;
        }
//        $BWX->check_post_actions();
    }

    echo "<h2> " .__('Orders Page.', 'bwx_payment'). $author_or_admin . "</h2>";

    $BWX->ini_bwx_sessions ();

// process form input
    if ( $_GET['filter'] || isset ( $_GET['one_page_display'] ) ) {
        $BWX->update_bwx_sessions();
    }

// display form
    echo '<div class="tablenav">';
    echo '<div class="alignleft">';
    echo $BWX->select_form_filter ();
    echo '</div>'; // end of div class alignleft

// get where clause
    $where = $BWX->where_clause();

// pagination
// get total rows num
    $num_rows = $wpdb->get_var ( "select count(*) from  " . $bwx_table_name  . " " . $where  );
    $per_page = 10 ;
    $max_num_pages = intval( $num_rows / $per_page ) + 1 ;
    if ( $num_rows % $per_page == 0 ) $max_num_pages-- ;

// transfer the filter

    $add_args = $BWX->add_args();

    $page_links = paginate_links( array(
        'base' => add_query_arg ('paged','%#%'),
        'format' => '',
        'prev_text' => __('prev', 'bwx_payment'),
        'next_text' => __('next', 'bwx_payment'),
        'total' => $max_num_pages ,
        'current' => $_GET['paged'],
        'add_args' => $add_args
    ));

    if (!$_GET['paged'] ) $_GET['paged']= 1 ;
    if ( $page_links ) {
    echo '<div class="tablenav-pages">';
    $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' , 'bwx_payment') . '</span>%s',
    number_format_i18n( ( $_GET['paged'] - 1 ) * $per_page + 1 ),
    number_format_i18n( min( $_GET['paged'] * $per_page, $num_rows ) ),
    number_format_i18n( $num_rows ),
    $page_links
    );
    if ( $BWX->one_page_display ==0 ) echo $page_links_text;
    echo '</div></div>';
    }


// sql get rows of orders
//    $BWX_Orders = new BWX_Show_Orders();
    echo "<br />";
 $BWX->display_order_brief_rows ( $per_page );

//
    if ( $BWX->one_page_display ==0 ) {
        echo '<div class="tablenav"><div class="tablenav-pages">'. $page_links_text ;
        echo '</div></div>';
        echo  $BWX->one_page_form_string() ;
    }
}

//
//
function bwx_ajax_userinfo () {
$string = '
<script type="text/javascript">
//<![CDATA[
function userinfo ( alert_userinfo, uprice ) {
    qty =  document.getElementById("quantity").value ;
    if ( isNaN( qty ) ) { alert ( alert_userinfo );  }
    else {
    qty = Math.round(qty);
    total = uprice * qty ;
    subtotalHtml = document.getElementById("subtotal");
    subtotalHtml.innerHTML = total;
    deliveryoption =  document.getElementById("deliveryoption").value ;
    firstposition = deliveryoption.indexOf(":");
    deliverycost = deliveryoption.slice( firstposition+1 );
    total = total + deliverycost *1 ;
    mydeliveryCost = document.getElementById("ShowDeliveryCost");
    mytotal = document.getElementById("userinfototal");
    inputtotal = document.getElementById("inputtotal");
    mydeliveryCost.innerHTML = deliverycost ;
    mytotal.innerHTML = total;
    inputtotal.value = total;
    inputqty = document.getElementById("quantity");
    inputqty.value = qty;
    }

}
function iniuprice (uprice) {
    qty =  document.getElementById("quantity").value ;
    total = uprice * qty ;
    document.write(total);
}
function inicost() {
    deliveryoption =  document.getElementById("deliveryoption").value ;
    firstposition = deliveryoption.indexOf(":");
    deliverycost = deliveryoption.slice( firstposition+1 );
    document.write(deliverycost);
}
function iniprice (uprice) {
    qty =  document.getElementById("quantity").value ;
    total = uprice * qty ;
    deliveryoption =  document.getElementById("deliveryoption").value ;
    firstposition = deliveryoption.indexOf(":");
    deliverycost = deliveryoption.slice( firstposition+1 );
    total = total + deliverycost *1 ;
    document.write(total);
    inputtotal = document.getElementById("inputtotal");
    inputtotal.value = total;
}
function chkbuyerinfo ( alert1, alert2 , alertqty ) {
    var buyername  = document.getElementById("buyer_name");
    var buyertel   = document.getElementById("buyer_tel");
    var buyeremail = document.getElementById("buyer_email");
    if ( buyername.value == "") {
        alert ( alert1 );
        buyername.focus();
        return false;
    }
    if ( buyertel.value == "" && buyeremail.value == "" ) {
        alert ( alert2 );
        buyertel.focus();
        return false;
    }
    qty =  document.getElementById("quantity").value ;
    if ( isNaN( qty ) ) {
        alert ( alertqty );
        qtyposition =  document.getElementById("quantity");
        qtyposition.focus();
        return false;
    }
    document.formuserinfo.submit();
    return true;
}
//]]>
</script>
';
return $string;
}

function bwx_clear_session() {
    unset($_SESSION);
    // you may want to delete the session cookie
    if (isset($_COOKIE[session_name()])) {
      setcookie(session_name(), '', time()-60);
    }
    session_destroy();
}
add_action('wp_logout', 'bwx_clear_session');

function bwx_start_session() {
    session_start(); // for form select
    $_SESSION['time']     = time();
}
//add_action('wp_login', 'bwx_start_session');
add_action('admin_init', 'bwx_start_session');

//************************************************************************
// widget for list authors
//**************************************************************************

class FooWidget extends WP_Widget {
    /** constructor */
    function FooWidget() {
        parent::WP_Widget(false, $name = 'FooWidget');
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        ?>
              <?php echo $before_widget; ?>
                  <?php if ( $title )
                        echo $before_title . $title . $after_title; ?>
    <ul>
    <?php wp_list_authors('show_fullname=1&optioncount=1'); ?>
    </ul>
              <?php echo $after_widget; ?>

        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
        return $new_instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
        $title = esc_attr($instance['title']);
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
        <?php
    }

} // class FooWidget

// register FooWidget widget
add_action('widgets_init', create_function('', 'return register_widget("FooWidget");'));


?>
