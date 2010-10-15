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
          $j2wp_joomla_images_path,
          $j2wp_joomla_images_folder,
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

  $j2wp_cms_types = array(
        '0'  => 'Joomla',
        '1'  => 'Mambo'
        );

  // get the options
  $j2wp_cms_type     = get_option('j2wp_cms_type');
  $cat_sel           = get_option("j2wp_cat_sel");
  $mysql_change_vars = get_option("j2wp_mysql_change_vars");

  if ( $cat_sel == 'on' )
  {
    $cat_sel_checkbox = ' checked="checked" ';
  }
  else
  {
    $cat_sel_checkbox = ' ';
  }

  if ( $mysql_change_vars == 'on' )
  {
    $mysql_change_vars_checkbox = ' checked="checked" ';
  }
  else
  {
    $mysql_change_vars_checkbox = ' ';
  }


  echo '  <div class="wrap">' . "\n" .
  '    <h2>' . $j2wp_cms_types[$j2wp_cms_type] . ' To WordPress Migrator</h2>' . "\n" .
  '    <form id="j2wp_plugin_options_form" action="" method="post">' . "\n" .
  '      <br /><hr />' . "\n" .
  '      ' . __( 'This Plugin migrates all content from Joomla/Mambo to Wordpress', 'joomla2wp') . "\n" .
  '      <br /><hr />' . "\n" .
  '      <p class="submit">' . "\n" .
  '        <input type="submit" name="j2wp_options_update" value="Update Options &raquo;" />' . "\n" .
  '        <br />' . "\n" .
  '      </p>' . "\n";

  echo 
  '      <div id="plugin_option_set">' . "\n" .
  '      <p>' . "\n" .
  '        <b>' . __('Before you start the migration, please copy all your images from ' . $j2wp_cms_types[$j2wp_cms_type] . ' to the Plugin Images Directory!!!', 'joomla2wp') . '</b><br />' . "\n" .
  '        ' . __('This is needed so that wordpress can determine the correct mime type of the images.', 'joomla2wp') . "\n" .
  '      </p>' . "\n" .
  '      </div><br />' . "\n";

  echo '      <br />' . "\n" .
  '      <fieldset>' . "\n" .
  '        <h3>CMS Selection</h3>' . "\n" .
  '        <div id="plugin_option_set">' . "\n" .
  '          <table>' . "\n" .
  '          <tr><th class="j2wp_option_left_part"><label for="">' . __('Type of CMS:', 'joomla2wp') . '</label></th>' . "\n" .
  '              <td>&nbsp;&nbsp;</td>' . "\n" .
  '              <td><ul><li>' . "\n";

  foreach( $j2wp_cms_types as $key => $value)
  {
    if ( $j2wp_cms_type == $key )
      $checked = ' checked="checked" ';
    else
      $checked = ' ';
    echo '      <input type="radio" class="j2wp-radio" name="new_j2wp_cms_type" id="j2wp_cms_type_' . $key . '" value="' . $key . '"' . $checked . ' />' . "\n";
    echo '      <label for="j2wp_cms_type_' . $key . '">' . $value . '</label>' . "\n";
  }

  echo
  '            </li></ul></td>' . "\n" .
  '          </tr>' . "\n" .
  '          </table>' . "\n" .
  '        </div>' . "\n" .
  '      </fieldset>' . "\n" .
  '      <br />' . "\n";

  echo '      <br />' . "\n" .
  '      <fieldset>' . "\n" .
  '        <h3>' . $j2wp_cms_types[$j2wp_cms_type] . ' and WP - Database Parameters</h3>' . "\n" .
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
  '          <p><b>' . $j2wp_cms_types[$j2wp_cms_type] . ' DB Params</b></p>' . "\n" .
  '          <table>' . "\n" .
  '          <tr>' . "\n" .
  '            <td>' . "\n" .
  '              ' . $j2wp_cms_types[$j2wp_cms_type] . ' Database Name:' . "\n" .
  '            </td>' . "\n" .
  '            <td>' . "\n" .
  '              <input type="text" size="10" name="new_j2wp_joomla_db_name" value="' . get_option("j2wp_joomla_db_name" ) . '" />' . "\n" .
  '            </td>' . "\n" .
  '          </tr>' . "\n" .
  '          <tr>' . "\n" .
  '            <td>' . "\n" .
  '              ' . $j2wp_cms_types[$j2wp_cms_type] . ' TB Prefix:' . "\n" .
  '            </td>' . "\n" .
  '            <td>' . "\n" .
  '              <input type="text" size="10" name="new_j2wp_joomla_tb_prefix" value="' . get_option("j2wp_joomla_tb_prefix" ) . '" />    (<i>normally jos_</i>)' . "\n" .
  '            </td>' . "\n" .
  '          </tr>' . "\n" .
  '          <tr>' . "\n" .
  '            <td>' . "\n" .
  '              ' . $j2wp_cms_types[$j2wp_cms_type] . ' Images Path:' . "\n" .
  '            </td>' . "\n" .
  '            <td>' . "\n" .
  '              <input type="text" size="25" name="new_j2wp_joomla_images_path" value="' . get_option("j2wp_joomla_images_path" ) . '" />' . "\n" .
  '            </td>' . "\n" .
  '          </tr>' . "\n" .
  '          <tr>' . "\n" .
  '            <td>' . "\n" .
  '              ' . $j2wp_cms_types[$j2wp_cms_type] . ' Images folder:' . "\n" .
  '            </td>' . "\n" .
  '            <td>' . "\n" .
  '              <input type="text" size="25" name="new_j2wp_joomla_images_folder" value="' . get_option("j2wp_joomla_images_folder" ) . '" />' . "\n" .
  '            </td>' . "\n" .
  '          </tr>' . "\n" .
  '          <tr>' . "\n" .
  '            <td>' . "\n" .
  '              ' . $j2wp_cms_types[$j2wp_cms_type] . ' Website URL:' . "\n" .
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
  '              WP Images folder:' . "\n" .
  '            </td>' . "\n" .
  '            <td>' . "\n" .
  '              <input type="text" size="40" name="new_j2wp_wp_images_folder" value="' . get_option("j2wp_wp_images_folder" ) . '" />' . "\n" .
  '            </td>' . "\n" .
  '          </tr>' . "\n" .
  '          <tr>' . "\n" .
  '            <td>' . "\n" .
  '            </td>' . "\n" .
  '            <td>' . "\n" .
  '              No forward slash on the end of the folder !!!' . "\n" .
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
  _e('To start the migration of ' . $j2wp_cms_types[$j2wp_cms_type] . ' posts to Wordpress - press the button below!', 'joomla2wp'); 
  echo "\n";
echo '<br />' . "\n";
echo '<div id="j2wp_migrator_btn">' . "\n";
echo '<p class="submit">';
echo '<input type="submit" name="do_mig_btn" value="Start Migration to WP" />';
echo '</p>' . "\n";
echo '</div><br /><hr /><br />' . "\n";
/*
echo '<p class="submit">';
echo '<input type="submit" name="get_cat_btn" value="Get Cats" />';
echo '</p>';
*/
echo '<h3>URLs in Posts Migration</h3>' . "\n";
echo '<br />' . "\n";
  _e('To change the URLs in the content from ' . $j2wp_cms_types[$j2wp_cms_type] . ' posts to WP posts - press the button below!', 'joomla2wp');
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