<?php

// check WPMU or WP
if ( function_exists('get_blog_option') ) {
    define ('BWX_WPMU', '1' );
} else {
    define ('BWX_WPMU', '' );
}

// compatibility for WP　and WPMU
function bwx_get_option ( $blog_id , $option_name ) {
//    if ( function_exists('get_blog_option') ) {
    if ( BWX_WPMU ) {
        return get_blog_option ( $blog_id, $option_name );
    } else {
        return get_option ( $option_name );
    }
}

function bwx_update_option ($blog_id , $option_name , $option_value  ){
//    if ( function_exists('update_blog_option') ) {
    if ( BWX_WPMU ) {
        return update_blog_option ( $blog_id, $option_name , $option_value  );
    } else {
        return update_option ( $option_name, $option_value  );
    }
}

function bwx_post_title ($b_id , $p_id ) {
    if ( BWX_WPMU ) {
        global $blog_id;
        if ( $blog_id ==1 ) {
            $post = get_blog_post( $b_id, $p_id);
            return $post->post_title;
        } else {
            return  get_the_title ( $p_id );
        }
    } else {
            return  get_the_title ( $p_id );
    }
}
function bwx_get_meta ($b_id , $p_id , $m_key) {
    if ( BWX_WPMU ) {
    } else {
        return get_post_meta($p_id, $m_key, true );
    }
}

function bwx_blog_title (  $user_id ) {
    if ( BWX_WPMU ) {
        global $blog_id;
// need to add the function
        return "Admin";
    } else {
        $userdata = get_userdata( $user_id );
        return $userdata->user_login;
    }
}

function bwx_option_array ( $option ) {
    $options = explode(";", $option);
    foreach ( $options as $k => $v) {
        $vs = explode(":", $v);
        if ( count($vs) ==2 ) {
            $options[$k] = array ( $vs[0] => $vs[1] );
        }
    }
    return $options;
}

function bwx_array_option ( $array ) {
    $s = '';
    foreach ( $array as $k => $v ){
        if ( is_array ( $v ) && count ( $v ) == 1  ) {
            foreach ( $v as $k1 => $v1 ) {
                $s .= $k1 . ":" . $v1 ;
                $s .= ";";
            }
        } else {
            $s .= $v . ";" ;
        }
    }
    return $s;
}



// order actions
//
function bwx_delete_order( $wpdb,$bwx_table_name,$order_id ) {
    $wpdb->query ("delete from ". $bwx_table_name . " where id = ". $order_id  );
    echo "order ". $order_id . "deleted !";
}

function bwx_display_details ( $wpdb,$bwx_table_name, $order_id ) {
        $BWX_T_F = get_bwx_column ();
        $order_details = $wpdb->get_row("select * from ". $bwx_table_name . " where id = ". $order_id , ARRAY_A);
        echo "<h2>". __('Order Details', 'bwx_payment') ."</h2>";
        echo "<table width='100%' >";
        echo "<tr><td align='right' width='20%'>";
        echo '<form method="post"><input type="submit" name="submit" value="'.__('back','bwx_payment').'"></form>';
        echo '</td><td></td></tr>';
        foreach ( $order_details as $k => $v ) {
//                    echo  "<tr><th align='right' width='20%' >". $bwx_table_fields[$k] . " : </th><td width='70%'>" . $v . "</td></tr>";
          if ( $k == 'blog_id' ) {  $v = bwx_blog_title ( $v ) ; }
          if ( $k == 'post_id' ) {  $v = bwx_post_title ( $blog_id , $v ) ; }
          echo  "<tr><th align='right' width='20%' >". $BWX_T_F[$k]  . " : </th><td width='70%'>" . $v . "</td></tr>";
        }
        echo "<tr></tr>";
// double return icon
        echo "<tr><td align='right' width='20%'>";
        echo '<form method="post"><input type="submit" name="submit" value="'.__('back','bwx_payment').'"></form>';
        echo '</td><td></td></tr>';
        echo "</table>";
        return ;
}

function get_bwx_column () {
        $bwx_table_column = array (
                'id'=> __('Order ID', 'bwx_payment'),
                'blog_id'=> __('Blog ID', 'bwx_payment'),
                'post_id' =>__('Post ID', 'bwx_payment'),
                'product_price' => __('Product Price', 'bwx_payment'),
                'product_qty' =>__('Product Qty', 'bwx_payment'),
                'product_options' =>__('Product Options', 'bwx_payment'),
                'delivery_method' =>__('Delivery Method', 'bwx_payment'),
                'delivery_cost' =>__('Delivery Cost', 'bwx_payment'),
                'total'=> __('Total', 'bwx_payment'),
                'order_date' => __('Order Date', 'bwx_payment'),
                'order_status' => __('Order Status', 'bwx_payment'),
                'shipment_name' => __('Receiver Name', 'bwx_payment'),
                'shipment_address' => __('Shipment Address', 'bwx_payment'),
                'shipment_city' => __('Shipment City', 'bwx_payment'),
                'shipment_country' => __('Shipment Country', 'bwx_payment'),
                'shipment_postcode' => __('Shipment Post Code', 'bwx_payment'),
                'shipment_tel' => __('Shipment Tel', 'bwx_payment'),
                'buyer_name' =>  __('Buyer Name', 'bwx_payment'),
                'buyer_tel' => __('Buyer Tel Number', 'bwx_payment'),
                'buyer_email' => __('Buyer Email', 'bwx_payment'),
                'v_pstatus' => __('Payment Status', 'bwx_payment'),
                'v_pstring' => __('Payment String', 'bwx_payment'),
                'v_pmode' => __('Payment Mode', 'bwx_payment'),
                'v_moneytype' => __('Money Type', 'bwx_payment'),
                'client_ip' => __('Buyer IP Address', 'bwx_payment'),
                'payment_method' =>__('Payment Method', 'bwx_payment')
        );
        return $bwx_table_column ;
}

function get_bwx_brief_column () {
    global $user_level;

    $brief_fields =  array ( 'id', 'blog_id', 'post_id' , 'product_price', 'product_qty','delivery_cost','total',
                        'order_date', 'order_status', 'buyer_name',  'buyer_tel' );
    if( $user_level < 5 ) {
        $brief_fields =  array ( 'id', 'post_id' , 'product_price', 'product_qty','delivery_cost','total',
                        'order_date', 'order_status', 'buyer_name',  'buyer_tel' );
    }
    return $brief_fields;
}

// class
// order table selection filter
// key as the table field 'blog_id','post_id','order_date','order_status'
// value is the selected , 0 is the defalt, select all : where $key like '%'
// Class BWX_Display_Orders
class BWX_Filter {
    var $bwx_filter = array ();
    var $one_page_display =0 ;
    
    function BWX_Filter () {
        global $user_level;
        $this->one_page_display = 0 ;
        $this->bwx_filter = array (
                            'blog_id'=> array (
                                        'default_select'=> __('All User ID','bwx_payment'),
                                        'selected_num'=> 0
                                        ),
                            'post_id'=> array (
                                        'default_select'=> __('All Post ID','bwx_payment'),
                                        'selected_num'=> 0
                                        ),
                            'order_date'=> array (
                                        'default_select'=> __('All Order Date','bwx_payment'),
                                        'selected_num'=> 0
                                        ),
                            'order_status'=> array (
                                        'default_select'=>__('All Order Status','bwx_payment'),
                                        'selected_num'=> 0
                                        )
            );
        if ( $user_level < 5 ) {
            array_shift ( $this->bwx_filter );
        }
    }

// Session['bwx_filter']
// session['bwx_filter']['blog_id'] = array ('%','',''....);
// session['bwx_filter']['post_id'] = array ('%','',''....);
// session['bwx_filter']['order_date'] = array ('%','',''....);
// session['bwx_filter']['order_status'] = array ('%','',''....);
    function ini_bwx_sessions () {
        if ( ! $_SESSION['bwx_filter'] ) {
            global $wpdb,$bwx_table_name,$user_level,$user_ID,$blog_id;

            echo "this is ini the session. ";
            $blog_id_db = '%';
            if (  !BWX_WPMU  && $user_level < 5  ) {
                $blog_id_db = $user_ID;
            }
            if ( BWX_WPMU && $blog_id !=1 ) {
                $blog_id_db = $blog_id ;
            }

//* super users
            $where = '';
//* Author
            if ( ( BWX_WPMU && $blog_id !=1 ) || ( !BWX_WPMU && $user_level < 5 ) ) {
                 $where = ' where `blog_id` like ' . $blog_id_db ;
            }

//**
            foreach ( array_keys( $this->bwx_filter ) as $v  ) {
                $sql = '';
//
                if ( $v == 'order_date' ) {
                    $sql = "SELECT DISTINCT YEAR(order_date) AS yyear, MONTH(order_date) AS mmonth FROM $bwx_table_name  " . $where . " ORDER BY ". $v ." DESC";
                } else {
                    $sql = "SELECT DISTINCT ".$v." FROM $bwx_table_name " . $where . "  ORDER BY ". $v ." DESC";
                }
//*
                $results = $wpdb->get_results( $sql );
                $_SESSION['bwx_filter'][$v][] = '%';
                foreach ( $results as $row ) {
                    if ( $v == 'order_date' ) {
                        $_SESSION['bwx_filter'][$v][] = $row->yyear . "-" . ( (strlen($row->mmonth)== 1)?'0'.$row->mmonth:$row->mmonth ) ;
                    } else {
                        $_SESSION['bwx_filter'][$v][] = $row->$v ;
                    }
                }
            }
//**
        }
    }
// $_GET['blog_id']  ;select name = 'blog_id', value = n is the value of SESSION['bwx_filter']['blog_id'][n]
//
//
    function update_bwx_sessions () {
//        $get_hash ='';
        foreach ( array_keys( $this->bwx_filter ) as $v  ) {
            if ( isset ( $_GET[$v] ) ) {
                $this->bwx_filter[$v]['selected_num'] = $_GET[$v] ;
//                $get_hash .= $_GET[$v];
            }
        }

        $this->one_page_display = 0 ;

        if ( $_GET['one_page_display'] == '1')  $this->one_page_display = 1 ;
        if ( $_GET['one_page_display'] == '0')  $this->one_page_display = 0 ;
//        $get_hash = bwx_hash ($get_hash);
//        return $get_hash;
    }
    
    function one_page_form_string () {
        global $user_level;
        $action_page = '';
        if ( $user_level > 5 ) {
            $action_page = 'Display-Orders-Admin' ;
        } else {
            $action_page = 'Display-Orders-Author' ;
        }
        
        $string = '<form action="" method="get" >';
        $string .= '<input type="hidden" name="page" value="'.$action_page .'" >';
        foreach ( $this->bwx_filter as $k => $v ) {
            $string .= '<input type="hidden" name="'.$k.'"  value="'.$this->bwx_filter[$k]['selected_num'].'" >';
        }
        $string .= '<input  type="hidden" name="one_page_display" value="1" >';
        $string .= '<input  value="'.__('One Page Display','bwx_payment').'" type="submit" >';
        $string .= '</form>';
        return $string;
    }

//**
// $fliter_string ="<form > <select>  ....  </form>"
    function select_form_filter () {
        global $user_level;
        $action_page = 'Display-Orders-Author';

        if ( $user_level > 5 )  $action_page = 'Display-Orders-Admin';
        $string = '<form action="" method="GET">';
        $string .='<input type="hidden" name="page" value="'.$action_page.'" >';
        
        foreach ( array_keys ( $this->bwx_filter ) as $v ) {
            $string .= '<select name="'.$v.'">';
            foreach ( $_SESSION['bwx_filter'][$v] as $num => $db_v ) {
                $selected = '';
                if ( $num == $this->bwx_filter[$v]['selected_num'] ) {
                    $selected = "selected = \"selected\" " ; /* get the option select */
                }
                if ( $num == 0 ) {
                    $string .= '<option value="'.$num.'"  '.$selected.'  >'. $this->bwx_filter[$v]['default_select'] .'</option>';
                } else {
                    if ( $v == 'post_id' )  $db_v = bwx_post_title ( $blog_id , $db_v );
                    if ( $v == 'blog_id' )  $db_v = bwx_blog_title ( $db_v ) ;
                    $string .= '<option value="'.$num.'"  '.$selected.' >'. $db_v .'</option>';
                }
            }
            $string .= '</select>';
        }
        $string .= '<input type="submit" name="filter" value="'. __("filter","bwx_payment").'">';
        $string .= '</form>';
        return $string;
    }
    function where_clause() {

        global $bwx_table_name,$user_level,$user_ID,$blog_id;

        $where = ' where ';
        foreach ( array_keys ( $this->bwx_filter ) as $field ) {
            $n = intval ( $this->bwx_filter[$field]['selected_num'] ) ;
            if ( $field == 'order_date' ) {
                $where .= "  `".$field . "`  like '" . $_SESSION['bwx_filter'][$field][$n] ."%'" ." and "   ;
            } else {
                $where .= "  `".$field . "`  like '" . $_SESSION['bwx_filter'][$field][$n] ."'" ." and "   ;
            }
        }
        $where = substr($where, 0, -4 );

        if ( BWX_WPMU ){
            if ( $blog_id != 1 ) {
                $where .= " and  `blog_id` like " . $blog_id ;
            }
        } else {
            if ( $user_level <5 ) {
                $where .= " and  `blog_id` like " . $user_ID ;
            }
        }

        return $where;
    }
    
    function add_args() {
        $add_args = array ();
        foreach ( $this->bwx_filter as $k =>$v ) {
            $add_args[$k] =  $v['selected_num'];
        }
        $add_args['one_page_display'] = 0 ;
        $add_args['filter'] = true ;
        return $add_args;
    }

//
// $BWX->display_order_brief_rows ( $per_page );

    function display_order_brief_rows ( $per_page ){
    global $wpdb,$bwx_table_name;
    $where = $this->where_clause();

    $bwx_brief = get_bwx_brief_column () ;
    $bwx_table_column = get_bwx_column ();

    $currency = get_option('v_moneytype');
    if ( $currency == 0 ) {
        $currency = __('CNY','bwx_payment');
    } elseif ( $currency == 1 ) {
        $currency = __('USD','bwx_payment');
    }

    $sub_total = 0 ;
    $product_toal = 0;
    $delivery_cost = 0;

    $offset =  $per_page * (  $_GET['paged'] - 1  );
    $limit ="";  /* one page display */
    if ( $this->one_page_display == 0 ) $limit = " limit ". $per_page .  " offset " . $offset ; /* seperate pages display */

    $my_query = "SELECT `". implode ('`,`' ,  $bwx_brief )  ."` FROM $bwx_table_name ". $where . " ORDER BY order_date DESC " . $limit ;
    $myrows = $wpdb->get_results( $my_query );
    if ( $this->one_page_display == 1 ) {
        $num_rows = count ( $myrows );
        echo "<br> " . __('Order Numbers is :','bwx_payment') . $num_rows ;
    }
    echo "<table width=\"100%\" ><tr>";
    foreach ( $bwx_brief as $v ) echo "<th>" . $bwx_table_column[$v] . "</th>" ;
    echo '<th>'.__('delete', 'bwx_payment').'</th><th>'.__('details', 'bwx_payment').'</th></tr>'; /* add two column */
    foreach ( $myrows as $row ) {
        $sub_total = $sub_total + $row->total;
        $product_toal = $product_toal + $row->product_price * $row->product_qty ;
        $delivery_cost = $delivery_cost + $row->delivery_cost ;
        echo "<tr>";
        foreach ( $row as $k => $v ) {
            if ( $k == 'blog_id' ) {  $v = bwx_blog_title ( $v ) ; }
            if ( $k == 'post_id' ) {  $v = bwx_post_title ( $blog_id , $v ) ; }
            if ( $k == 'order_date' ) {  $v = substr ( $v,5,-3 ) ; }
            $app_fix ="";
            if ( $k == 'product_price' || $k == 'delivery_cost' || $k == 'total' )   $app_fix = $currency;
            echo "<td>".$v.$app_fix."</td>";
        }
// add two columns for delete and details
        echo '
        <td width="5%"><form method="post">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="order_id" value="'.$row->id.'">
        <input type="submit" name="submit" value="'. __('delete', 'bwx_payment') . '" onclick="ajax()">
        ';
        wp_nonce_field ( 'bwx_orders' );
        echo '</form></td>
        <td width="5%"><form method="post">
        <input type="hidden" name="action" value="details">
        <input type="hidden" name="order_id" value="'.$row->id.'">
        <input type="submit" name="submit" value="'. __('details', 'bwx_payment') . '">
        ';
        wp_nonce_field ( 'bwx_orders' );
        echo '</form></td>';

        echo "</tr>";
    }
    echo "</table>";

    echo "<br />" . __('Product Total is : ','bwx_payment')  . $product_toal ." ". $currency;
    echo "<br />" . __('Delivery Cost is : ','bwx_payment') . $delivery_cost ." ". $currency ;
    echo "<br />" . __('Total is : ','bwx_payment') . $sub_total ." ". $currency ;
    }
//


} // end of class



// db functions
function bwx_db_validation ( $input_db ) {
    if (  get_magic_quotes_gpc() ) {
        $input_db = stripslashes ( $input_db );
    }
    return mysql_real_escape_string( $input_db );
}

function bwx_insert_db () {
    global $blog_id, $wpdb, $bwx_table_name ;
// verify the input if the field is null
    if ( !$_POST['buyer_name']  ||  ( !$_POST['buyer_tel'] && !$_POST['buyer_email'] ) )
        return  __('Please Input Buyer Information. ','bwx_payment');
//
    $bwx_table_column = get_bwx_column ();
    $data = array ();
    $data_type = array ();
    $data['id'] = 'null' ;
    $data_type['id'] = '%d' ;

    if (  BWX_WPMU  ) {
        $data['blog_id'] = $blog_id ;
    } else {
        $thepost = get_post( $_POST['post_id'] ) ;
        $data['blog_id'] = $thepost->post_author ;
    }
    $data_type['blog_id'] = '%d' ;

    $data['order_status'] = __('Order','bwx_payment') ;
    $data_type['order_status'] = '%s' ;

    $data['product_options'] = '';
    $data_type['product_options'] = '%s' ;

    $data['v_moneytype'] = get_option( 'v_moneytype' );;
    $data_type['v_moneytype'] = '%d' ;


    foreach ( $_POST as $k => $v ) {
        if ( $k != 'from_userinfo' && $k != 'secrete' && $k != 'submit' ) {
            $v = bwx_db_validation ( $v );

            if ( $k == 'post_id' || $k == 'product_price' || $k == 'product_qty' || $k == 'delivery_cost' || $k == 'total' ) {
                $data_type[$k] = '%d'  ;
            } else {
                $data_type[$k]= '%s' ;
            }
            
            if ( $k == 'delivery_method' ) {
                $cost = explode (':', $v );  /*  get delivery cost */
                $data['delivery_cost'] = $cost[1];
                $data_type['delivery_cost'] = '%d'  ;
            }
            
            if ( substr ( $k, 0 , 15 ) == 'product_options' ) {
                $data['product_options'] = $data['product_options'] . $v . ";";
            } else {
                $data[$k] = $v;
            }
        }
    }
//

// check total is correct
    $calculate_total = get_post_custom_values( __('Price','bwx_payment'),  $_POST['post_id'] );
    $calculate_total = intval ( $calculate_total[0] )  * intval ( $_POST['product_qty'] );
    $calculate_total = $calculate_total + $data['delivery_cost'];
    if (  $calculate_total != $_POST['total']  )  return __( 'Totoal Price Check Erorr! ', 'bwx_payment');

//
    $string ='<table>';
    foreach ( $data as $k => $v ) {
        if ( $k != 'id' ) {
            $string .=  '<tr><td align="right" >'. $bwx_table_column[$k] . ': </td><td>' . $v . '</td></tr>';
        }
    }
    $string .='</table>';
//
    $where = 'select order_date from '. $bwx_table_name . ' where ';
    foreach ( $data as $k => $v ) {
        if ( $k == 'client_ip' || $k == 'post_id' || $k == 'total' || $k == 'order_status' || $k == 'blog_id'
        || $k == 'buyer_name' || $k == 'buyer_tel' || $k == 'buyer_email' ) {
            if ( $v != '' )
            $where .= " `" . $k . "`  = " . ( ($data_type[$k] == '%d')?"":"'" ) . $v . ( ($data_type[$k] == '%d')?"":"'" ) ." and " ;
        }
    }
    $where = substr ( $where, 0, -4 ) . " order by order_date desc; " ;
    $row =  $wpdb->get_row("$where");
//
    $systemtime = date("Y-m-d H:i:s");
/* 2010-01-04 23:02:34 , 10 minutes */
// 10 minutes or no record in DB, insert the record
    $order_id = 0;
    if ( ( substr($systemtime,0,15) > substr($row->order_date, 0,15)) || !$row->order_date )  {
        $wpdb->insert( $bwx_table_name, $data , $data_type ) ;
        $order_id = mysql_insert_id();
    } else {
        $string = __('Already Bought!', 'bwx_payment');
    }
    
    if (  $_POST['payment_method'] == __('Internet Payment','bwx_payment') && $order_id ) $string = bwx_payease_submit ( $order_id );

    return $string ;
}


function bwx_payease_submit ( $order_id ) {
// submit to payease
//            $bwx_payment_page_id = bwx_get_option( $blog_id, 'bwx_payment_page' );
//              <form name="payeasesubmit" method="post" action="'. get_permalink( $bwx_payment_page_id ) .'">


            $v_mid = get_option('v_mid');
            $v_moneytype =  get_option('v_moneytype');
            
            $v_ymd = date("Ymd");
            $v_oid = $v_ymd . '-' . $v_mid . '-' . $order_id  ;
            $bwx_payment_page_id = get_option( 'bwx_payment_page' );
            $v_url = get_permalink( $bwx_payment_page_id );
            $v_string = $v_moneytype . $v_ymd . $_POST['total'] . $v_mid . $v_oid . $v_mid . $v_url ;
            $secrete = get_option ('v_secrete');
            $v_md5info = bin2hex ( mhash (MHASH_MD5, $v_string , $secrete )) ;

            $order_string = '<form name="payeasesubmit" method="post" action="http://pay.beijing.com.cn/prs/user_payment.checkit">';
            $order_string .= '
                <input type="hidden" name="v_mid" value ="'.$v_mid.'"  >
                <input type="hidden" name="v_oid" value ="'.$v_oid.'"  >
                <input type="hidden" name="v_rcvname" value ="'.$v_mid.'"  >
                <input type="hidden" name="v_rcvaddr" value ="'.get_option('v_rcvaddr').'"  >
                <input type="hidden" name="v_rcvtel" value ="'.get_option('v_rcvtel').'"  >
                <input type="hidden" name="v_rcvpost" value ="'.get_option('v_rcvpost').'"  >
                <input type="hidden" name="v_amount" value ="'.$_POST['total'].'"  >
                <input type="hidden" name="v_ymd" value ="'.$v_ymd.'"  >
                <input type="hidden" name="v_orderstatus" value ="1"  >
                <input type="hidden" name="v_ordername" value ="'.$_POST["buyer_name"].'"  >
                <input type="hidden" name="v_moneytype" value ="'.$v_moneytype.'"  >
                <input type="hidden" name="v_url" value ="'.$v_url.'"  >
                <input type="hidden" name="v_md5info" value ="'.$v_md5info.'"  >
                </form>
                <script language="JavaScript" type="text/javascript">
                document.payeasesubmit.submit();
                </script>';
            return  $order_string;
//              return " vstring is : " . $v_string ." <br /> md5nfo is : " .$v_md5info ."<br /> secrete is: " .$secrete ;
}

function bwx_get_moneytype () {
    $currency_options = get_option ( 'v_moneytype' );
    $currency = '';
    if ( $currency_options == 0 )   $currency = __('CNY','bwx_payment');
    if ( $currency_options == 1 )   $currency = __('USD','bwx_payment');
    return $currency;
}

// only display author's posts in admin-edit page
function posts_for_current_author($query) {
        global $user_level;

        if($query->is_admin && $user_level < 5) {
                global $user_ID;
                $query->set('author',  $user_ID);
                unset($user_ID);
        }
        unset($user_level);

        return $query;
}
add_filter('pre_get_posts', 'posts_for_current_author');


?>
