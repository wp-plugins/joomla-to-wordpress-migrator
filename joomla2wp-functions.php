<?php
/*
Plugin Name: Joomla/Mambo to WP Migrator
Plugin URI: http://www.it-gnoth.de/wordpress/wordpress-plugins/
Description: migrates all posts from Joomla/Mambo tables to WP tables
Version: 1.3.3
Author: Christian Gnoth
Author URI: http://www.it-gnoth.de
License: GPL2
*/

/*  Copyright 2010  Christian Gnoth  (email : support@it-gnoth.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


ini_set('max_execution_time', 9000); 
ini_set('mysql.connect_timeout', 9000);
//  ini_set('display_errors', 1); 
//  error_reporting(E_ALL);


////////////////////////////////////////////////////////////////////////////////////////
//  define varaibles to have them in global scope including files and functions
////////////////////////////////////////////////////////////////////////////////////////
$j2wp_mysql_vars = array();
$j2wp_error_flag = 0;
$ERROR_MSG = array();


ob_implicit_flush(1);

include_once( dirname(__FILE__) . '/joomla2wp-config.php');
include_once( dirname(__FILE__) . '/joomla2wp-output.php');
include_once( dirname(__FILE__) . '/joomla2wp-mig.php');
include_once( dirname(__FILE__) . '/joomla2wp-admin.php');

define('JTWPURL', WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__),"",plugin_basename(__FILE__)) );
define('JTWPDIR', WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__),"",plugin_basename(__FILE__)) );


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


$phpver = phpversion();
if ($phpver < '4.1.0') {
	$_GET = $HTTP_GET_VARS;
	$_POST = $HTTP_POST_VARS;
	$_SERVER = $HTTP_SERVER_VARS;
}
if ($phpver >= '4.0.4pl1' && strstr($_SERVER["HTTP_USER_AGENT"],'compatible')) {
	if (extension_loaded('zlib')) {
		ob_end_clean();
		ob_start('ob_gzhandler');
	}
} else if ($phpver > '4.0') {
	if (strstr($HTTP_SERVER_VARS['HTTP_ACCEPT_ENCODING'], 'gzip')) {
		if (extension_loaded('zlib')) {
			$do_gzip_compress = TRUE;
			ob_start();
			ob_implicit_flush(0);
			//header('Content-Encoding: gzip');
		}
	}
}

$phpver = explode(".", $phpver);
$phpver = "$phpver[0]$phpver[1]";
if ($phpver >= 41) {
	$PHP_SELF = $_SERVER['PHP_SELF'];
}

if (!ini_get("register_globals")) {
	import_request_variables('GPC');
}



////////////////////////////////////////////////////////////////////////////////
// load plugin wp-admin css
////////////////////////////////////////////////////////////////////////////////
function j2wp_load_css()
{
  echo 	"\n\n";
  echo 	'<!-- Joomla/Mambo to Wordpress Converter - Plugin Option CSS -->' . "\n";
  echo 	'<link rel="stylesheet" type="text/css" media="all" href="' . JTWPURL . 'css/plugin-option.css" />';
  echo 	'<!-- Joomla/Mambo to Wordpress Converter - Plugin Directory Variable -->' . "\n";
  echo  '<script type="text/javascript">/* <![CDATA[ */' . "\n";
  echo  '   var plugin_dir_url = "' . plugin_dir_url( __FILE__ ) . '";' . "\n";
  echo  ' /* ]]> */</script>' . "\n";
  echo 	"\n\n";
	
  return;
}

////////////////////////////////////////////////////////////////////////////////
// register plugin options
////////////////////////////////////////////////////////////////////////////////
function register_j2wp_options()
{
  //  wp_enqueue_script( 'json-form' );
  //  wp_enqueue_script( 'get_output', plugin_dir_url( __FILE__ ) . 'js/ajax/get_output.js', array( 'jquery', 'json2' ), "1.0.30", true );


  //  add options
  add_option( 'j2wp_mysql_change_vars', 'off' );
  add_option( 'j2wp_cms_type', '0' );
  add_option( 'j2wp_cat_sel', 'on' );
  add_option( 'j2wp_mysql_srv', 'localhost' );
  add_option( 'j2wp_mysql_usr', '' );
  add_option( 'j2wp_mysql_pswd', '' );
  add_option( 'j2wp_joomla_db_name', '' );
  add_option( 'j2wp_joomla_tb_prefix', 'jos_' );
  add_option( 'j2wp_joomla_images_path', '' );
  add_option( 'j2wp_joomla_images_folder', '/images/stories' );
  add_option( 'j2wp_joomla_web_url', '' );
  add_option( 'j2wp_wp_db_name', '' );
  add_option( 'j2wp_wp_tb_prefix', 'wp_' );
  add_option( 'j2wp_wp_images_folder', '/wp-content/themes/twentyten/images' );
  add_option( 'j2wp_wp_web_url', '' );
//  register_setting( 'joomla2wp', 'j2wp_mysql_usr', 'localhost' );
//  register_setting( 'joomla2wp', 'j2wp_mysql_srv', 'localhost' );

  return;  
}

////////////////////////////////////////////////////////////////////////////////
// plugin options page
////////////////////////////////////////////////////////////////////////////////
function joomla2wp_plugin_create_option_page()
{
  global $wpdb;
  global $joomla_cats,
         $j2wp_error_flag;
  static $sel_values = 0;

  if ( isset( $_POST['j2wp_options_update'] ) )
  {
     //  call update function
     update_j2wp_options();
  }

  joomla2wp_print_plugin_option_page();

  switch( $j2wp_error_flag )
  {
    case 0:
      break;
    case -70000:
      echo '<div id="message" class="error">';
      echo '<strong>Please fill up all MySQL Parameters !!!</strong>.</div>';
      j2wp_print_error_msg();
      break;
  }

  return;
}


////////////////////////////////////////////////////////////////////////////////
// plugin options page
////////////////////////////////////////////////////////////////////////////////
function joomla2wp_plugin_create_migration_page()
{
  global $wpdb;
  global $joomla_cats,
         $j2wp_error_flag;
  static $sel_values = 0;


  if ( isset( $_POST['j2wp_cat_sel_update'] ) )
  {
    //  check if category selection option checkbox is set 
    if (!isset( $_POST['new_j2wp_cat_sel'] ))
    {
      $_POST['new_j2wp_cat_sel'] = 'off';
      $cat_sel = 'off';
    }
    else
    {
      $_POST['new_j2wp_cat_sel'] = 'on';
      $cat_sel = 'on';
    }

     //  call update function
     update_option( 'j2wp_cat_sel', $cat_sel );
  }

  if ( isset( $_POST['change_urls_btn'] ) )
  {
    $j2wp_error_flag = joomla2wp_change_urls();
  }
	
  if ( isset( $_POST['do_mig_btn'] ) )
  {
    // check if categories should be selected
    $j2wp_cat_sel = get_option('j2wp_cat_sel');
    if ( $j2wp_cat_sel == 'off' )
    {
      $_POST['print_cats_sel_page'] = true;
    }
    else
    {
      $j2wp_error_flag = j2wp_prepare_mig( 1 );
    }
  }

  if ( isset( $_POST['j2wp_cats_abort_btn'] ) )
  {
    $_POST['print_cats_sel_page'] = false;
    echo '<div id="message" class="updated fade">';
    echo '<strong>Migration stopped </strong>.</div>';
  }

  if ( isset( $_POST['j2wp_cats_continue_btn'] ) )
  {
    // get the selected cats
    $sel_values = $_POST['joomla_cat_box'];

    if ( $sel_values )
    {
      $j2wp_error_flag = j2wp_prepare_mig( $sel_values );
    }
    else
    {
      echo '<div id="message" class="error">';
      echo '<strong>No category selected !!!</strong>.</div>';
      $_POST['print_cats_sel_page'] = true;
    }
  }

  if ( $_POST['print_cats_sel_page'] )
  {
    // show all cats from joomla on panel and get selection
    joomla2wp_print_cat_sel_page();
  }

  if ( !(isset( $_POST['do_mig_btn'] )) AND !(isset( $_POST['j2wp_cats_continue_btn'] )) )
  {
    $_POST['print_cats_sel_page'] = false;
    joomla2wp_print_plugin_migration_page();
  }

  return;
}

         
////////////////////////////////////////////////////////////////////////////////
// plugin options
////////////////////////////////////////////////////////////////////////////////
function joomla2wp_get_options()
{
  global  $j2wp_mysql_srv,
          $j2wp_mysql_usr,
          $j2wp_mysql_pswd,
          $j2wp_mysql_change_vars,
          $j2wp_joomla_db_name,
          $j2wp_joomla_tb_prefix,
          $j2wp_joomla_images_path,
          $j2wp_joomla_images_folder,
          $j2wp_joomla_web_url,
          $j2wp_wp_db_name,
          $j2wp_wp_tb_prefix,
          $j2wp_wp_web_url;

  $j2wp_mysql_change_vars = get_option("j2wp_mysql_change_vars");
  $j2wp_cat_sel         = get_option('j2wp_cat_sel');
  $j2wp_cms_type        = get_option("j2wp_cms_type");
  $j2wp_mysql_srv       = get_option("j2wp_mysql_srv");
  $j2wp_mysql_usr       = get_option("j2wp_mysql_usr");
  $j2wp_mysql_pswd      = get_option("j2wp_mysql_pswd");
  $j2wp_joomla_db_name  =	get_option('j2wp_joomla_db_name');
  $j2wp_joomla_tb_prefix  =	get_option('j2wp_joomla_tb_prefix');
  $j2wp_joomla_images_path  =	get_option('j2wp_joomla_images_path');
  $j2wp_joomla_images_folder  =	get_option('j2wp_joomla_images_folder');
  $j2wp_joomla_web_url  =	get_option('j2wp_joomla_web_url');
  $j2wp_wp_db_name      =	get_option('j2wp_wp_db_name');
  $j2wp_wp_tb_prefix    =	get_option('j2wp_wp_tb_prefix');
  $j2wp_wp_images_folder =	get_option('j2wp_wp_images_folder');
  $j2wp_wp_web_url      =	get_option('j2wp_wp_web_url');

  return;
}



////////////////////////////////////////////////////////////////////////////////
function update_j2wp_options()
{
  global $j2wp_mysql_vars;

  //  check if Change Mysql Server Variables option checkbox is set 
  if (!isset( $_POST['new_j2wp_mysql_change_vars'] ))
  {
    $_POST['new_j2wp_mysql_change_vars'] = 'off';
    $j2wp_mysql_change_vars = 'off';
  }
  else
  {
    $_POST['new_j2wp_mysql_change_vars'] = 'on';
    $j2wp_mysql_change_vars = 'on';
    // set values
    $j2wp_mysql_vars = $_SESSION['j2wp_mysql_vars'];
    for ( $i = 0; $i < count($j2wp_mysql_vars); $i++ )
    {
      $temp_str = 'new_j2wp_mysql_var_' . $i;
      $j2wp_mysql_vars[$i]['Value'] = $_POST[$temp_str];
    }
    // save new values in session variable
    $_SESSION['j2wp_mysql_vars'] = $j2wp_mysql_vars;
    j2wp_set_mysql_variables();
  }

  //  write Mysql Server if changed
  if  ( $_POST['new_j2wp_mysql_srv'] != get_option( 'j2wp_mysql_srv' ) )
  {
    $j2wp_mysql_srv = $_POST['new_j2wp_mysql_srv'];
  }
  //  write Mysql User if changed
  if  ( $_POST['new_j2wp_mysql_usr'] != get_option( 'j2wp_mysql_usr' ) )
  {
    $j2wp_mysql_usr = $_POST['new_j2wp_mysql_usr'];
  }
  //  write Mysql User Password if changed
  if  ( $_POST['new_j2wp_mysql_pswd'] != get_option( 'j2wp_mysql_pswd' ) )
  {
    $j2wp_mysql_pswd = $_POST['new_j2wp_mysql_pswd'];
  }

  update_option( 'j2wp_mysql_change_vars', $_POST['new_j2wp_mysql_change_vars'] );
  update_option( 'j2wp_cms_type', $_POST['new_j2wp_cms_type'] );
  update_option( 'j2wp_mysql_srv', $_POST['new_j2wp_mysql_srv'] );
  update_option( 'j2wp_mysql_usr', $_POST['new_j2wp_mysql_usr'] );
  update_option( 'j2wp_mysql_pswd', $_POST['new_j2wp_mysql_pswd'] );
  update_option( 'j2wp_joomla_db_name', $_POST['new_j2wp_joomla_db_name'] );
  update_option( 'j2wp_joomla_tb_prefix', $_POST['new_j2wp_joomla_tb_prefix'] );
  update_option( 'j2wp_joomla_images_path', $_POST['new_j2wp_joomla_images_path'] );
  update_option( 'j2wp_joomla_images_folder', $_POST['new_j2wp_joomla_images_folder'] );
  update_option( 'j2wp_joomla_web_url', $_POST['new_j2wp_joomla_web_url'] );
  update_option( 'j2wp_wp_db_name', $_POST['new_j2wp_wp_db_name'] );
  update_option( 'j2wp_wp_tb_prefix', $_POST['new_j2wp_wp_tb_prefix'] );
  update_option( 'j2wp_wp_images_folder', $_POST['new_j2wp_wp_images_folder'] );
  update_option( 'j2wp_wp_web_url', $_POST['new_j2wp_wp_web_url'] );

  echo '<div id="message" class="updated fade">';
  echo '<strong>Options updated !</strong></div>' . "\n";
	
	return;
}


////////////////////////////////////////////////////////////////////////////////
function joomla2wp_admin_actions()
{
  add_menu_page( 'Joompla2WP Plugin Options','Joomla2WP', 'manage_options', 'joomla2wp-option-page', 'joomla2wp_plugin_create_option_page');
  add_submenu_page( 'joomla2wp-option-page', 'Joomla To Wordpress Migrator - Settings', 'Settings',  'manage_options', 'joomla2wp-option-page','joomla2wp_plugin_create_option_page');
  add_submenu_page( 'joomla2wp-option-page', 'Joomla To Wordpress Migrator - Migration','Migration', 'manage_options', 'joomla2wp-migration-page','joomla2wp_plugin_create_migration_page');

  return;    
}

////////////////////////////////////////////////////////////////////////////////
function joomla2wp_deinstall()
{
  global $wpdb;

  unregister_setting( 'joomla2wp', 'j2wp_plugin_options');

  return;
}


////////////////////////////////////////////////////////////////////////////////
function joomla2wp_install()
{
  global $wpdb;

  register_j2wp_options();

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);

  return;
}

////////////////////////////////////////////////////////////////////////////////
// print error messages
////////////////////////////////////////////////////////////////////////////////
function j2wp_print_error_msg()
{
  global $j2wp_error_flag,
         $ERROR_MSG;

  echo '<br /><br /><br />';
  echo '<div style="font-size:20px;text-align:center;margin:0 auto;"><b>' . $ERROR_MSG[$j2wp_error_flag] . '</b></div>';
  $j2wp_error_flag = 0;

  return;
}


?>