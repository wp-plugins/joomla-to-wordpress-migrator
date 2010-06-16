<?php
global  $j2wp_mysql_vars;

function joomla2wp_print_option_page()
{
  global $wpdb;
  global  $j2wp_mysql_srv,
          $j2wp_mysql_usr,
          $j2wp_mysql_pswd,
          $j2wp_mysql_change_vars,
          $j2wp_joomla_db_name,
          $j2wp_joomla_tb_prefix,
          $j2wp_joomla_web_url,
          $j2wp_wp_db_name,
          $j2wp_wp_tb_prefix,
          $j2wp_wp_web_url;
  global $j2wp_mysql_vars;

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

  // get the options
  $cat_sel = get_option("j2wp_cat_sel");
  if ( $cat_sel == 'on' )
  {
    $cat_sel_checkbox = ' checked="checked" ';
  }
  else
  {
    $cat_sel_checkbox = ' ';
  }

  $mysql_change_vars = get_option("j2wp_mysql_change_vars");
  if ( $mysql_change_vars == 'on' )
  {
    $mysql_change_vars_checkbox = ' checked="checked" ';
  }
  else
  {
    $mysql_change_vars_checkbox = ' ';
  }


  echo '  <div class="wrap">' . "\n" .
  '    <h2>Joomla To WordPress Migrator</h2>' . "\n" .
  '    <form id="j2wp_plugin_options_form" action="" method="post">' . "\n" .
  '      <br /><hr />' . "\n" .
  '      ' . __( 'This Plugin migrates all content from Joomla 1.5 to Wordpress 2.9', 'joomla2wp') . "\n" .
  '      <br /><hr />' . "\n" .
  '      <p class="submit">' . "\n" .
  '        <input type="submit" name="j2wp_options_update" value="Update Options &raquo;" />' . "\n" .
  '        <br />' . "\n" .
  '      </p>' . "\n";


  echo '      <br />' . "\n" .
  '      <fieldset>' . "\n" .
  '        <h3>Joomla and WP - Database Parameters</h3>' . "\n" .
  '        <div id="plugin_option_set">' . "\n" .
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

/*
  echo  '      <fieldset>' . "\n" .
  '        <h3>MySQL System Variables - Settings</h3>' . "\n" .
  '        <div id="plugin_option_set">' . "\n" .
  '          <p>' . __( 'If you webhoster allows, you can decide here if you want change the MySQL Server variables settings.', 'joomla2wp' ) . '</p>' . "\n" .
  '          <table>' . "\n" .
  '          <tr>' . "\n" .
  '            <td>' . "\n" .
  '              Change System Variables:' . "\n" .
  '            </td>' . "\n" .
  '            <td>' . "\n" .
  '	       <input type="checkbox" name="new_j2wp_mysql_change_vars" value="open" ' . $mysql_change_vars_checkbox . '/>' . "\n" .
  '            </td>' . "\n" .
  '          </tr>' . "\n" .
  '          <tr>' . "\n" .
  '            <td>' . "\n" .
  '            </td>' . "\n" .
  '          </tr>' . "\n";


  $j2wp_mysql_vars = $_SESSION['j2wp_mysql_vars'];
  if ( $mysql_change_vars == 'on' )
  {
    $temp_count = count($j2wp_mysql_vars);
    if ( $temp_count == 0 )
    {
      _e( 'Authorization Error - Your MySQL Server settings do not allow to change any variable.', 'joomla2wp');
    }
    for ( $i = 0; $i < count($j2wp_mysql_vars); $i++ )
    {
      echo '          <tr>' . "\n" .
         '            <td>' . "\n" .
         '              ' . $j2wp_mysql_vars[$i]['Variable_name'] . ': ' . 
         '            </td>' . "\n" .
         '            <td>' . "\n" .
         '            </td>' . "\n" .
         '            <td>' . "\n" .
         '              <input type="text" size="10" name="new_j2wp_mysql_var_' . $i . '" value="' . $j2wp_mysql_vars[$i]["Value"] . '" />' .
         '            </td>' . "\n" .
         '          </tr>' . "\n";
    }
  }

echo '          <tr>' . "\n" .
  '            <td>' . "\n" .
  '            </td>' . "\n" .
  '          </tr>' . "\n" .
  '          </table>' . "\n" .
  '        </div>' . "\n" .
  '      </fieldset>' . "\n";
*/
echo '      <br />' . "\n" .
  '      <fieldset>' . "\n" .
  '        <h3>Category Selection</h3>' . "\n" .
  '        <div id="plugin_option_set">' . "\n" .
  '          <p>' . __( 'Here you can decide if you want migrate <b>all categories</b> or if you want <b>select</b> them <b>separately', 'joomla2wp') . '</b>.</p>' . "\n" .
  '          <table>' . "\n" .
  '          <tr>' . "\n" .
  '            <td>' . "\n" .
  '              Migrate all Categories:' . "\n" .
  '            </td>' . "\n" .
  '            <td>' . "\n" .
  '	       <input type="checkbox" name="new_j2wp_cat_sel" value="open" ' . $cat_sel_checkbox . '/>' . "\n" .
  '            </td>' . "\n" .
  '          </tr>' . "\n" .
  '          </table>' . "\n" .
  '        </div>' . "\n" .
  '      </fieldset>' . "\n" .
  '      <br />' . "\n" .
  '      <p class="submit">' . "\n" .
  '        <input type="submit" name="j2wp_options_update" value="Update Options &raquo;" />' . "\n" .
  '        <br />' . "\n" .           
  '      </p>' . "\n";

echo '<br /><hr /><br />' . "\n";
echo '<h3>Data Migration</h3>' . "\n";
echo '<br />' . "\n";
  _e('To start the migration of Joomla posts to Wordpress - press the button below!', 'joomla2wp'); 
  echo "\n";
echo '<br />' . "\n";
echo '<div id="j2wp_migrator_btn">' . "\n";
echo '<p class="submit">';
echo '<input type="submit" name="do_mig_btn" value="Start Migration - Data from Joomla to WP" />';
echo '</p>' . "\n";
echo '</div><br /><hr /><br />' . "\n";
/*
echo '<p class="submit">';
echo '<input type="submit" name="get_cat_btn" value="Get Cats" />';
echo '</p>';
*/
echo '<h3>URLs in Posts Migration</h3>' . "\n";
echo '<br />' . "\n";
  _e('To change the URLs in the content from Joomla posts to WP posts - press the button below!', 'joomla2wp');
  echo "\n";
echo '<br />' . "\n";
echo '<p class="submit">';
echo '<input type="submit" name="change_urls_btn" value="Change Urls" />';
echo '</p>';
echo '</form>';
echo '</div>   <!--- DIV wrap END  --->' . "\n";

  return;
}


function joomla2wp_print_cat_sel_page()
{
  //  get all cats from joomla
  $joomla_cats = j2wp_get_joomla_cats();

  // print panel with cats
  echo '<div class="wrap">' . "\n";
  echo '<h3>' . __( 'Select the categories you want migrate to WP !' , 'joomla2wp' ) . '</h3>' . "\n";
  echo '<br />' . "\n";
  echo '<form id="j2wp_cat_sel_form" name="joomla_cat_sel_list" enctype="application/x-www-form-urlencoded" method="post">' . "\n";
  echo '  <p>' . "\n";
  $rows = count($joomla_cats);
  $height = $rows * 10;
//  echo '    <select name="joomla_cat_box[]" style="height:' . $height . 'px" multiple="multiple" size="' . $rows . '">' . "\n";
  echo '    <select id="cat_select_id" class="cat_select" name="joomla_cat_box[]" multiple="multiple" size="' . $rows . '">' . "\n";
  $index = 0;
  foreach ( $joomla_cats as $jcat )
  {
    echo '      <option value="' . $index . '" >' . $jcat['title'] . '</option>' . "\n";
    $index++;
  }
  echo '    </select>' . "\n";
  echo '  </p>' . "\n";
  echo '  <p class="submit">' . "\n";
  echo '    <input type="submit" name="j2wp_cats_abort_btn" value="' . __( 'Abort', 'joomla2wp') . '" />' . "\n";
  echo '    <input id="j2wp_cat_sel_cont_btn" type="submit" name="j2wp_cats_continue_btn" value="' . __( 'Continue', 'joomla2wp') . '" />' . "\n";
  echo '    <br />' . "\n";
  echo '  </p>' . "\n";
  echo '</form>' . "\n";
  echo '</div>   <!--- DIV wrap END  --->' . "\n";
  
  return;
}



?>