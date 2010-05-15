<?php

function joomla2wp_print_option_page()
{
  global $wpdb;
  
  global  $j2wp_mysql_srv,
          $j2wp_mysql_usr,
          $j2wp_mysql_pswd,
          $j2wp_joomla_db_name,
          $j2wp_joomla_tb_prefix,
          $j2wp_joomla_web_url,
          $j2wp_wp_db_name,
          $j2wp_wp_tb_prefix,
          $j2wp_wp_web_url;

/*
  echo '  <div class="wrap">' . "\n";
  echo '  <form action="options.php"  method="post">' . "\n";
  settings_fields('joomla2wp');
  do_settings_sections('j2wp_mysql_page'); 
  do_settings_sections('j2wp_joomla_page'); 
  do_settings_sections('j2wp_wp_page'); 
  echo '    <input name="Submit" type="submit" value="' . esc_attr_e('Update Options') . '" />' . "\n";
  echo '  </form>' . "\n";
  echo '  </div>' . "\n";
*/

  $outline =
  '  <div class="wrap">' . "\n" .
  '    <h2>Joomla To WordPress Migrator</h2>' . "\n" .
  '    <form action="" method="post">' . "\n" .
  '      <br /><hr />' . "\n" .
  '      This Plugin migrates all content from Joomla 1.5 to Wordpress 2.9' . "\n" .
  '      <br /><hr />' . "\n" .
  '      <p class="submit">' . "\n" .
  '        <input type="submit" name="j2wp_options_update" value="Update Options &raquo;" />' . "\n" .
  '        <br />' . "\n" .
  '      </p>' . "\n";

  _e( $outline, 'joomla2wp');

$outline =
'      <br />' . "\n" .
'      <fieldset>' . "\n" .
'        <h3>Joomla and WP - Database Parameters</h3>' . "\n" .
'        <div id="theme_option_set">' . "\n" .
'          <p><b>DB Connection Parameters</b></p>' . "\n" .
'          <table>' . "\n" .
'          <tr>' . "\n" .
'            <td>' . "\n" .
'              MySQL Server:' . "\n" .
'            </td>' . "\n" .
'            <td>' . "\n" .
'              <input type="text" size="15" name="new_j2wp_mysql_srv" value="' . get_option("j2wp_mysql_srv" ) . '" />  (normally <i>localhost</i>)' . "\n" .
'            </td>' . "\n" .
'          </tr>' . "\n" .
'          <tr>' . "\n" .
'            <td>' . "\n" .
'              MySQL Server User:' . "\n" .
'            </td>' . "\n" .
'            <td>' . "\n" .
'              <input type="text" size="10" name="new_j2wp_mysql_usr" value="' . get_option("j2wp_mysql_usr" ) . '" />' . "\n" .
'            </td>' . "\n" .
'          </tr>' . "\n" .
'          <tr>' . "\n" .
'            <td>' . "\n" .
'              MySQL Server Password:' . "\n" .
'            </td>' . "\n" .
'            <td>' . "\n" .
'              <input type="password" size="10" name="new_j2wp_mysql_pswd" value="' . get_option("j2wp_mysql_pswd" ) . '" />' . "\n" .
'            </td>' . "\n" .
'          </tr>' . "\n" .
'          </table>' . "\n" .
'          <p><b>Joomla DB Params</b></p>' . "\n" .
'          <table>' . "\n" .
'          <tr>' . "\n" .
'            <td>' . "\n" .
'              Joomla Database Name:' . "\n" .
'            </td>' . "\n" .
'            <td>' . "\n" .
'              <input type="text" size="10" name="new_j2wp_joomla_db_name" value="' . get_option("j2wp_joomla_db_name" ) . '" />' . "\n" .
'            </td>' . "\n" .
'          </tr>' . "\n" .
'          <tr>' . "\n" .
'            <td>' . "\n" .
'              Joomla TB Prefix:' . "\n" .
'            </td>' . "\n" .
'            <td>' . "\n" .
'              <input type="text" size="10" name="new_j2wp_joomla_tb_prefix" value="' . get_option("j2wp_joomla_tb_prefix" ) . '" />    (<i>normally jos_</i>)' . "\n" .
'            </td>' . "\n" .
'          </tr>' . "\n" .
'          <tr>' . "\n" .
'            <td>' . "\n" .
'              Joomla Website URL:' . "\n" .
'            </td>' . "\n" .
'            <td>' . "\n" .
'              <span class="small">http://</span><input type="text" size="25" name="new_j2wp_joomla_web_url" value="' . get_option("j2wp_joomla_web_url" ) . '" />' . "\n" .
'            </td>' . "\n" .
'          </tr>' . "\n" .
'          </table>' . "\n" .
'          <p><b>WP DB Params</b></p>' . "\n" .
'          <table>' . "\n" .
'          <tr>' . "\n" .
'            <td>' . "\n" .
'              WP Database Name:' . "\n" .
'            </td>' . "\n" .
'            <td>' . "\n" .
'              <input type="text" size="10" name="new_j2wp_wp_db_name" value="' . get_option("j2wp_wp_db_name" ) . '" />' . "\n" .
'            </td>' . "\n" .
'          </tr>' . "\n" .
'          <tr>' . "\n" .
'            <td>' . "\n" .
'              WP TB Prefix:' . "\n" .
'            </td>' . "\n" .
'            <td>' . "\n" .
'              <input type="text" size="10" name="new_j2wp_wp_tb_prefix" value="' . get_option("j2wp_wp_tb_prefix" ) . '" />    (<i>normally wp_</i>)' . "\n" .
'            </td>' . "\n" .
'          </tr>' . "\n" .
'          <tr>' . "\n" .
'            <td>' . "\n" .
'              WP Website URL:' . "\n" .
'            </td>' . "\n" .
'            <td>' . "\n" .
'              <span class="small">http://</span><input type="text" size="25" name="new_j2wp_wp_web_url" value="' . get_option("j2wp_wp_web_url" ) . '" />' . "\n" .
'            </td>' . "\n" .
'          </tr>' . "\n" .
'          </table>' . "\n" .
'        </div>' . "\n" .
'      </fieldset>' . "\n" .
'      <br />' . "\n";
_e( $outline, 'joomla2wp');

$outline =
'      <p class="submit">' . "\n" .
'        <input type="submit" name="j2wp_options_update" value="Update Options &raquo;" />' . "\n" .
'        <br />' . "\n" .           
'      </p>' . "\n";
_e( $outline, 'joomla2wp');


echo '<br /><hr /><br />' . "\n";
echo '<h3>Data Migration</h3>' . "\n";
echo '<br />' . "\n";
echo 'To start the migration of Joomla posts to Wordpress - press the button below!' . "\n";
echo '<br />' . "\n";
echo '<div id="j2wp_migrator_btn">' . "\n";
echo '<p class="submit">';
echo '<input type="submit" name="do_mig_btn" value="Migrate Data from Joomla to WP" />';
echo '</p>' . "\n";
echo '</div><br /><hr /><br />' . "\n";
/*
echo '<p class="submit">';
echo '<input type="submit" name="get_cat_btn" value="Get Cats" />';
echo '</p>';
*/
echo '<h3>URLs in Posts Migration</h3>' . "\n";
echo '<br />' . "\n";
echo 'To change the URLs in the content from Joomla posts to WP posts - press the button below!' . "\n";
echo '<br />' . "\n";
echo '<p class="submit">';
echo '<input type="submit" name="change_urls_btn" value="Change Urls" />';
echo '</p>';
echo '</form>';
echo '</div>   <!--- DIV wrap END  --->' . "\n";


}





?>