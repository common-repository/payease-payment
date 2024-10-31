<?php

function bwx_db_install () {
    global $wpdb;
    global $bwx_db_version;
    global $blog_id;
    global $bwx_table_name;
    
//1. exit if not admin
//    if (  !is_site_admin() || $blog_id != 1 ) { return ; }

//2. install database;
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

            $sql = "CREATE TABLE " . $table_name . " (
                `id` bigint(20) NOT NULL auto_increment,
                `blog_id` bigint(20) NOT NULL default '1',
                `post_id` bigint(20) NOT NULL default '1',
                `product_price` int(8) NOT NULL default '0',
                `product_qty` int(8) NOT NULL default '0',
                `product_options` varchar(64) NOT NULL default '',
                `delivery_method` varchar(32) NOT NULL default '',
                `delivery_cost` int(8) NOT NULL default '0',
                `total` int(8) NOT NULL default '0',
                `order_date` TIMESTAMP NOT NULL DEFAULT now(),
                `order_status` varchar(16) NOT NULL default '',
                `shipment_name` varchar(24) NOT NULL default '',
                `shipment_address` varchar(64) NOT NULL default '',
                `shipment_city` varchar(24) NOT NULL default '',
                `shipment_country` varchar(24) NOT NULL default '',
                `shipment_postcode` varchar(12) NOT NULL default '',
                `shipment_tel` varchar(20) NOT NULL default '',
                `buyer_name` varchar(24) NOT NULL default '',
                `buyer_tel` varchar(20) NOT NULL default '',
                `buyer_email` varchar(48) NOT NULL default '',
                `v_pstatus` tinyint(4) NOT NULL default '0',
                `v_pstring` varchar(32) NOT NULL default '',
                `v_pmode` varchar(32) NOT NULL default '',
                `v_moneytype` tinyint(4) NOT NULL default '0',
                `client_ip` varchar(15) NOT NULL default '0.0.0.0',
                `payment_method` varchar(32) NOT NULL default '',
                PRIMARY KEY (`id`),
                KEY `product_id` (`blog_id`,`post_id` )
            );";


      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);
      add_option("bwx_db_version", $bwx_db_version );
    }

//3. upgrade database;
    $installed_ver = get_option( "bwx_db_version" );
    if( $installed_ver != $bwx_db_version ) {

            $sql = "CREATE TABLE " . $bwx_table_name . " (
                `id` bigint(20) NOT NULL auto_increment,
                `blog_id` bigint(20) NOT NULL default '1',
                `post_id` bigint(20) NOT NULL default '1',
                `product_price` int(8) NOT NULL default '0',
                `product_qty` int(8) NOT NULL default '0',
                `product_options` varchar(64) NOT NULL default '',
                `delivery_method` varchar(32) NOT NULL default '',
                `delivery_cost` int(8) NOT NULL default '0',
                `total` int(8) NOT NULL default '0',
                `order_date` TIMESTAMP NOT NULL DEFAULT NOW(),
                `order_status` varchar(16) NOT NULL default '',
                `shipment_name` varchar(24) NOT NULL default '',
                `shipment_address` varchar(64) NOT NULL default '',
                `shipment_city` varchar(24) NOT NULL default '',
                `shipment_country` varchar(24) NOT NULL default '',
                `shipment_postcode` varchar(12) NOT NULL default '',
                `shipment_tel` varchar(20) NOT NULL default '',
                `buyer_name` varchar(24) NOT NULL default '',
                `buyer_tel` varchar(20) NOT NULL default '',
                `buyer_email` varchar(48) NOT NULL default '',
                `v_pstatus` tinyint(4) NOT NULL default '0',
                `v_pstring` varchar(32) NOT NULL default '',
                `v_pmode` varchar(32) NOT NULL default '',
                `v_moneytype` tinyint(4) NOT NULL default '0',
                `client_ip` varchar(15) NOT NULL default '0.0.0.0',
                `payment_method` varchar(32) NOT NULL default '',
                PRIMARY KEY (`id`),
                KEY `product_id` (`blog_id`,`post_id` )
            );";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);
      update_option( "bwx_db_version", $bwx_db_version );
    }
}


?>

