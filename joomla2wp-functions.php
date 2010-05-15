<?php
/*
Plugin Name: Joomla to WP Migrator
Plugin URI: http://www.it-gnoth.de/wordpress/wordpress-plugins
Description: migrates all posts from Joomla tables to WP tables
Version: 1.0.1
Author: Christian Gnoth
Author URI: http://www.it-gnoth.de
*/

//  ini_set('display_errors', 1); 
//  error_reporting(E_ALL);

include_once( dirname(__FILE__) . '/joomla2wp-mig.php');
include_once( dirname(__FILE__) . '/joomla2wp-admin.php');

define('JTWPDIR', (basename(dirname(__FILE__)) != 'plugins') ? basename(dirname(__FILE__))."/" :  '' );

register_activation_hook(   __FILE__,'joomla2wp_install');
register_deactivation_hook( __FILE__,'joomla2wp_deinstall');

//  load CSS for plugin option page
//	add_action('admin_init','gn_load_css');
//  second way to load a css file for the admin area
//	wp_admin_css( 'name', true );
add_action( 'admin_head', 'j2wp_load_css');
add_action( 'admin_menu', 'joomla2wp_admin_actions');
add_action( 'admin_init', 'register_j2wp_options' );


// relative path to WP_PLUGIN_DIR where the translation files will sit:
$plugin_path = plugin_basename( dirname( __FILE__ ) .'/languages' );
load_plugin_textdomain( 'joomla2wp', '', $plugin_path );



////////////////////////////////////////////////////////////////////////////////
// load plugin wp-admin css
////////////////////////////////////////////////////////////////////////////////
function j2wp_load_css()
{
	$plugin_option_css_url = WP_PLUGIN_URL . '/joomla_migrator';
	
	echo 	"\n\n";
	echo 	'<!-- Joomla to Wordpress Converter - Plugin Option CSS -->' . "\n";
	echo 	'<link rel="stylesheet" type="text/css" href="' . $plugin_option_css_url . '/css/plugin-option.css" />';
	echo 	"\n\n";
	
	return;
}

////////////////////////////////////////////////////////////////////////////////
// register plugin options
////////////////////////////////////////////////////////////////////////////////
function register_j2wp_options()
{
  //  add options
  add_option( 'joomla2wp', 'j2wp_mysql_srv', 'localhost' );
  add_option( 'joomla2wp', 'j2wp_mysql_usr' );
  add_option( 'joomla2wp', 'j2wp_mysql_pswd' );
  add_option( 'joomla2wp', 'j2wp_joomla_db_name' );
  add_option( 'joomla2wp', 'j2wp_joomla_tb_prefix', 'jos_' );
  add_option( 'joomla2wp', 'j2wp_joomla_web_url' );
  add_option( 'joomla2wp', 'j2wp_wp_db_name' );
  add_option( 'joomla2wp', 'j2wp_wp_tb_prefix', 'wp_' );
  add_option( 'joomla2wp', 'j2wp_wp_web_url' );
//  register_setting( 'joomla2wp', 'j2wp_mysql_usr', 'localhost' );
//  register_setting( 'joomla2wp', 'j2wp_mysql_srv', 'localhost' );

  return;  
}

////////////////////////////////////////////////////////////////////////////////
// plugin admin options page
////////////////////////////////////////////////////////////////////////////////
function joomla2wp_menu()
{
  global $wpdb;
                      
  if ( isset( $_POST['j2wp_options_update'] ) )
  {
		//  call update function
		update_j2wp_options();
	}
	
  if ( isset( $_POST['do_mig_btn'] ) )
  {
    //  call to migration script
    //  include 'joomla2wp_mig.php';
  
    joomla2wp_do_mig();
    echo '<div id="message" class="updated fade">';
    echo '<strong>Migration done </strong>.</div>';
  }

  if ( isset( $_POST['get_cat_btn'] ) )
  {
    $cat_name = 'Mortgage Glossary';
    echo $cat_name . ': ' . get_cat_ID($cat_name) . '<br />' . "\n";
    $cat_name = 'Daily Mortgage Updates';
    echo $cat_name . ': ' . get_cat_ID($cat_name) . '<br />' . "\n";
    $cat_name = 'FHA Mortgages';
    echo $cat_name . ': ' . get_cat_ID($cat_name) . '<br />' . "\n";
    $cat_name = "Today's Mortgage Rates";
    echo $cat_name . ': ' . get_cat_ID($cat_name) . '<br />' . "\n";
    $cat_name = 'Mortgage News';
    echo $cat_name . ': ' . get_cat_ID($cat_name) . '<br />' . "\n";
    $cat_name = 'Credit 101';
    echo $cat_name . ': ' . get_cat_ID($cat_name) . '<br />' . "\n";
    $cat_name = 'Mortgage 101';
    echo $cat_name . ': ' . get_cat_ID($cat_name) . '<br />' . "\n";
    $cat_name = 'Mortgage Programs';
    echo $cat_name . ': ' . get_cat_ID($cat_name) . '<br />' . "\n";
    $cat_name = 'Credit Optimization';
    echo $cat_name . ': ' . get_cat_ID($cat_name) . '<br />' . "\n";
    $cat_name = 'Credit Disputes';
    echo $cat_name . ': ' . get_cat_ID($cat_name) . '<br />' . "\n";
    $cat_name = 'Support Team';
    echo $cat_name . ': ' . get_cat_ID($cat_name) . '<br />' . "\n";
  
    echo '<div id="message" class="updated fade">';
    echo '<strong>List done </strong>.</div>';
  }	

  if ( isset( $_POST['change_urls_btn'] ) )
  {
    joomla2wp_change_urls();
    echo '<div id="message" class="updated fade">';
    echo '<strong>URLs changed ! </strong>.</div>';
  }
	
  joomla2wp_print_option_page();
}
         
function joomla2wp_get_options()
{
  global  $j2wp_mysql_srv,
          $j2wp_mysql_usr,
          $j2wp_mysql_pswd,
          $j2wp_joomla_db_name,
          $j2wp_joomla_tb_prefix,
          $j2wp_joomla_web_url,
          $j2wp_wp_db_name,
          $j2wp_wp_tb_prefix,
          $j2wp_wp_web_url;

  $j2wp_mysql_srv       = get_option("j2wp_mysql_srv");
  $j2wp_mysql_usr       = get_option("j2wp_mysql_usr");
  $j2wp_mysql_pswd      = get_option("j2wp_mysql_pswd");
  $j2wp_joomla_db_name  =	get_option('j2wp_joomla_db_name');
  $j2wp_joomla_tb_prefix  =	get_option('j2wp_joomla_tb_prefix');
  $j2wp_joomla_web_url  =	get_option('j2wp_joomla_web_url');
  $j2wp_wp_db_name      =	get_option('j2wp_wp_db_name');
  $j2wp_wp_tb_prefix    =	get_option('j2wp_wp_tb_prefix');
  $j2wp_wp_web_url      =	get_option('j2wp_wp_web_url');

  return;
}

function update_j2wp_options()
{
	//  write Mysql Server if changed
	if  ( $_POST['new_j2wp_mysql_srv'] != get_option( 'j2wp_mysql_srv' ) )
	{
		$j2wp_mysql_srv = $_POST['new_j2wp_mysql_srv'];
	}
	//  write Mysql User if changed
	if  ( $_POST['new_j2wp_mysql_usr'] != get_option( 'j2wp_mysql_usr' ) )
	{
		$j2wp_mysql_srv = $_POST['new_j2wp_mysql_usr'];
	}
	//  write Mysql User Password if changed
	if  ( $_POST['new_j2wp_mysql_pswd'] != get_option( 'j2wp_mysql_pswd' ) )
	{
		$j2wp_mysql_pswd = $_POST['new_j2wp_mysql_pswd'];
	}

	update_option( 'j2wp_mysql_srv', $_POST['new_j2wp_mysql_srv'] );
	update_option( 'j2wp_mysql_usr', $_POST['new_j2wp_mysql_usr'] );
	update_option( 'j2wp_mysql_pswd', $_POST['new_j2wp_mysql_pswd'] );
	update_option( 'j2wp_joomla_db_name', $_POST['new_j2wp_joomla_db_name'] );
	update_option( 'j2wp_joomla_tb_prefix', $_POST['new_j2wp_joomla_tb_prefix'] );
	update_option( 'j2wp_joomla_web_url', $_POST['new_j2wp_joomla_web_url'] );
	update_option( 'j2wp_wp_db_name', $_POST['new_j2wp_wp_db_name'] );
	update_option( 'j2wp_wp_tb_prefix', $_POST['new_j2wp_wp_tb_prefix'] );
	update_option( 'j2wp_wp_web_url', $_POST['new_j2wp_wp_web_url'] );

  echo '<div id="message" class="updated fade">';
  echo '<strong>Options updated !</strong></div>' . "\n";
	
	return;
}

function joomla2wp_admin_actions()
{
  add_options_page("Joomla2WP Migrator options", "Joomla2WP", 1, "Joomla2WP", "joomla2wp_menu");

  return;    
}

function joomla2wp_deinstall()
{
  global $wpdb;

  unregister_setting( 'joomla2wp', 'j2wp_plugin_options');

  return;
}


function joomla2wp_install()
{
    global $wpdb;

    register_j2wp_options();

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

    return;
}

?>