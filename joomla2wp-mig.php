<?php

global  $wpdb;
global  $CON,
        $user_id;
global  $j2wp_mysql_srv,
        $j2wp_mysql_usr,
        $j2wp_mysql_pswd,
        $j2wp_joomla_db_name,
        $j2wp_joomla_tb_prefix,
        $j2wp_joomla_web_url,
        $j2wp_wp_db_name,
        $j2wp_wp_tb_prefix,
        $j2wp_wp_web_url;
global  $j2wp_mysql_vars;


//  functions i8mported
function throwERROR($msg) 
{
    echo '<br />' . $msg . '<br />' . "\n";
    return;
}

function j2wp_prepare_mig( $func )
{
  ob_end_flush();
  ob_start();
  flush();
  ob_flush();


  if ( is_array($func) )
  {
    $sel_values = $func;
    $func = 2;
  }

  switch ( $func )
  {
    case 1:
      j2wp_print_output_page();

      //  get all cats from joomla
      $joomla_cats = j2wp_get_joomla_cats();

      echo '<br /> Found ' . count($joomla_cats) . ' Categories...<br /><br />' . "\n";
      flush();

      j2wp_do_mig( $joomla_cats );
  
      break;
    case 2:
      j2wp_print_output_page();
      ob_end_flush();
      ob_start();

      //  get all cats from joomla
      $joomla_cats = j2wp_get_joomla_cats();

      $joomla_temp_cats = array();
      foreach ( $sel_values as $val )
      {
        $joomla_temp_cats[] = array(
                              'id'    => $joomla_cats[$val]['id'],
                              'title' => $joomla_cats[$val]['title']
                              );
      }

      j2wp_do_mig( $joomla_temp_cats );

      break;
  }

  return;
}



function j2wp_do_mig( $joomla_cats )
{
  global  $wpdb,
          $CON,
          $user_id;

  global  $j2wp_mysql_srv,
          $j2wp_mysql_usr,
          $j2wp_mysql_pswd,
          $j2wp_joomla_db_name,
          $j2wp_joomla_tb_prefix,
          $j2wp_joomla_web_url,
          $j2wp_wp_db_name,
          $j2wp_wp_tb_prefix,
          $j2wp_wp_web_url;

  // setting timelimit
  if ( function_exists('set_time_limit') )
  {
    ignore_user_abort(1);
    set_time_limit(25);
  }
  else
    _e( '<br />Warning: can not execute set_time_limit() script may abort...<br />', 'joomla2wp');

  if ( !$CON )
    $CON = j2wp_do_mysql_connect();

  //  check if user adminwp exists
  $user_name = 'adminwp';
  $user_id = username_exists( $user_name );
  if ( !$user_id ) 
  {
    $random_password = wp_generate_password( 12, false );
    $user_id = wp_create_user( $user_name, $random_password, $user_email );
  } 
  
  //  create categories in wp and fill category field
  $mig_cat_array = j2wp_create_cat_wp( $joomla_cats );

  //  j2wp_joomla_wp_posts_by_cat( $mig_cat_array[10], 10, $user_id );

  $index = 0;  
  foreach ( $joomla_cats as $jcat )
  {
    // for each category in joomla process all posts
    j2wp_joomla_wp_posts_by_cat( $mig_cat_array[$index], $index, $user_id );
    $index++;
  }

  echo '<div id="message" class="updated fade">';
  echo '<strong>Migration done </strong>.</div>';

  ob_end_flush();

  return;
}


function j2wp_create_cat_wp( $joomla_cats )
{
  global  $wpdb,
          $CON;
  
/*              
  $wp_cats[0]  = 236;          
  $wp_cats[1]  = 239;          
  $wp_cats[2]  = 237;          
  $wp_cats[3]  = 233;          
  $wp_cats[4]  = 241;          
  $wp_cats[5]  = 238;          
  $wp_cats[6]  = 240;          
  $wp_cats[7]  = 234;          
  $wp_cats[8]  = 232;          
  $wp_cats[9]  = 235;          
  $wp_cats[10] = 2715;          
*/
          
  $j2wp_wp_tb_prefix = get_option('j2wp_wp_tb_prefix');
  j2wp_do_wp_connect();
  
  foreach ( $joomla_cats as $jcat )
  {
    $mig_cat_array[] = array(
                        'joomla_id'     => $jcat['id'],
                        'joomla_title'  => $jcat['title'],
                        'wp_id'         => wp_create_category( $jcat['title'] ),
//                        'wp_id'         => $wp_cats[$index],
                        'wp_title'      => $jcat['title']    
                          );
  }

  return $mig_cat_array;
}

function j2wp_get_joomla_cats()
{
  global  $wpdb,
          $CON;
          

  if ( !$CON )
    $CON = j2wp_do_mysql_connect();

  $j2wp_joomla_tb_prefix = get_option('j2wp_joomla_tb_prefix');
  j2wp_do_joomla_connect();
  
  $query = "SELECT id, title FROM " . $j2wp_joomla_tb_prefix . "categories WHERE section NOT LIKE('com_%') ORDER BY id ";
  $result = mysql_query($query, $CON);
  if ( !$result )
    echo mysql_error();
  
  while($row = mysql_fetch_array($result)) 
  {
    $joomla_cats[] = array(
                          'id' => $row['id'],
                          'title' => $row['title']
                          );
  }
  mysql_free_result($result);

  return $joomla_cats;
}


function j2wp_get_post_count( $mig_cat_array )
{
  global  $wpdb,
          $CON;

  $j2wp_joomla_tb_prefix = get_option('j2wp_joomla_tb_prefix');
  j2wp_do_joomla_connect();
  set_time_limit(25);

  $query = "SELECT COUNT(*) FROM `" . $j2wp_joomla_tb_prefix . "content` WHERE catid = '" . $mig_cat_array['joomla_id'] . "' ORDER BY `created` ";
  $result = mysql_query($query, $CON);
  if ( !$result )
    echo mysql_error();
  while($R = mysql_fetch_array($result)) 
  {
    $j2wp_post_count = $R[0];
  }
  mysql_free_result($result);

  return $j2wp_post_count;
}



function  j2wp_joomla_wp_posts_by_cat( $mig_cat_array, $cat_index, $user_id )
{
  global  $wpdb,
          $user_id,
          $CON;
          
  $wp_cat_id = $mig_cat_array['wp_id'];
          
  echo '<br />' . __('Processing Category: <b>', 'joomla2wp') . $mig_cat_array['joomla_title'] . '</b>   ===>';

  // first get count of posts in category
  $j2wp_post_count = j2wp_get_post_count( $mig_cat_array );

  _e( ' found ', 'joomla2wp');
  echo $j2wp_post_count . ' posts.... <br />';
  flush();
  ob_flush();
  sleep(1);

  // if there are too many posts - split to parts
  $working_rounds = 1;
  if ( $j2wp_post_count > 400 )
  {
    $working_rounds = ceil($j2wp_post_count / 200);
    //  $working_rounds = 1;
    $working_steps  = ' 200';
    $working_pos = 0;
  }
  else
  {
    $working_steps  = ' 400';
    $working_pos = 0;
  }
  
  // process all posts in steps
  for ( $i = 0; $i < $working_rounds; $i++)
  {
    set_time_limit(25);
    $result_array = j2wp_process_posts_by_step( $mig_cat_array, $working_steps, $working_pos, $user_id);

    $working_pos = $working_pos + $working_steps;

    $sql_query = $result_array[0];
    $wp_posts  = $result_array[1];
    $post_tags = $result_array[2];

    j2wp_insert_posts_to_wp( $sql_query, $wp_posts, $post_tags, $wp_cat_id );
  }
  
  return;
}

function j2wp_insert_posts_to_wp( $sql_query, $wp_posts, $post_tags, $wp_cat_id )
{
  global  $wpdb,
          $user_id,
          $CON;
          

  set_time_limit(25);
  $j2wp_wp_tb_prefix = get_option('j2wp_wp_tb_prefix');
  j2wp_do_wp_connect();

  $count = 0;
  //  foreach ($wp_posts as $j2wp_post) 
  foreach ($sql_query as $query) 
  {
    if ( (($count % 50) == 0) )
      echo '.';
    flush();
    ob_flush();
    set_time_limit(25);
    $query_rc = mysql_query($query,$CON);
    if ( mysql_error() )
      echo mysql_error();

    //  $id = wp_insert_post( $j2wp_post );

    set_time_limit(25);
    $id = mysql_insert_id($CON);
    if($id) 
    {
      set_time_limit(25);
      wp_set_post_categories( $id, array($wp_cat_id) );

      //  add tags to post
      $tags = $post_tags[$count];
      set_time_limit(25);
      wp_set_post_tags( $id, $tags, false );
      ++$count;
    }
  }

  echo '<br />';
  _e( 'Inserted ', 'joomla2wp');
  echo $count . ' Posts<br /><br />';

  flush();
  ob_flush();

  return;
}



function j2wp_process_posts_by_step( $mig_cat_array, $working_steps, $working_pos, $user_id)
{
  global  $wpdb,
          $user_id,
          $CON;
          
  $wp_cat_id = $mig_cat_array['wp_id'];

  set_time_limit(25);
  $j2wp_joomla_tb_prefix = get_option('j2wp_joomla_tb_prefix');
  j2wp_do_joomla_connect();
  $query = "SELECT * FROM `" . $j2wp_joomla_tb_prefix . "content` WHERE catid = '" . $mig_cat_array['joomla_id'] . "' ORDER BY `created` LIMIT " . $working_pos . ", " . $working_steps . " ";
  set_time_limit(25);
  $result = mysql_query($query, $CON);
  if ( !$result )
    echo mysql_error();
  
  $sql_query = array();
  $post_tags = array();
  $STORAGE   = array();
  $wp_posts  = array();

  $j2wp_wp_tb_prefix = get_option('j2wp_wp_tb_prefix');

  while($R = mysql_fetch_array($result)) 
  {
    set_time_limit(25);
    // Title is unique so check that it will be used only once
    if ( $R['alias'] )
    {
      $tmp = $R['alias'];
      if ( $STORAGE[$tmp] == true )
        $R['alias'] = $R['alias'] . "-II";
      $tmp = $R['alias'];
      $STORAGE[$tmp] = true;
    }
    else
    {
      $R['alias'] = sanitize_title($R['title']); 
    }

    $array = array(
        "post_author" => $user_id,
        "post_parent" => intval($wp_cat_id),
        "post_date"=>$R['created'],
        "post_date_gmt"=>$R['created'],
        "post_modified"=>$R['modified'],
        "post_modified_gmt"=>$R['modified'],
        "post_title"=>$R['title'],
        "post_status"=>"publish",
        "comment_status"=>"open",
        "ping_status"=>"open",
        "post_name"=>$R['alias'],
        "post_type"=>"post"
      );
    if($R['fulltext'] AND $R['introtext'])
      $array["post_content"] = $R["introtext"] . '<br /><!--more--><br />' . $R["fulltext"]; 
    elseif($R['introtext'] AND !$R['fulltext'])
      $array["post_content"] = $R['introtext'];
    
    // Content Filter
    $array["post_content"] = str_replace('<hr id="system-readmore" />',"<!--more-->",$array["post_content"]);
    $array["post_content"] = str_replace('src="images/','src="/images/',$array["post_content"]);

    $insert_sql = "INSERT INTO " . $j2wp_wp_tb_prefix . "posts" . " set ";
    $inserted = 0;
    foreach ($array as $k => $v) 
    {
      if($k AND $v) 
      {
        if($inserted > 0) 
          $insert_sql .= ",";
        $insert_sql .= " ".$k." = '".mysql_escape_string(str_replace("`","",$v))."'";
        ++$inserted;
      }
    }    
    $sql_query[] = $insert_sql;

    $wp_posts[] = array(
        'post_author' => $user_id,
        'post_category' => array($wp_cat_id),
        'post_content' => $array['post_content'], 
        'post_date' => $R['created'],
        'post_date_gmt' => $R['created'],
        'post_modified' => $R['modified'],
        'post_modified_gmt' => $R['modified'],
        'post_title' => $R['title'],
        'post_status' => 'publish',
        'comment_status' => 'open',
        'ping_status' => 'open',
        'post_name' => $R['alias'],
        'tags_input' => $R['metakey'], 
        'post_type' => 'post'
      );

    $post_tags[] = $R['metakey'];
    set_time_limit(25);
  }

  echo '<br /> ' . __( 'Processing ', 'joomla2wp') . count($wp_posts) . ' Posts...' . "\n";
  flush();
  ob_flush();

  $result_array[0] = $sql_query;
  $result_array[1] = $wp_posts;
  $result_array[2] = $post_tags;

  mysql_free_result($result);

  return $result_array;
}


function joomla2wp_change_urls()
{
  global  $wpdb,
          $CON;


  j2wp_print_output_page();

  $wp_posts = array();

// check
// <a href="mortgagecenter/39-news/11548-focus-on-the-6500-tax-credit.html"> $6,500 tax credit.</a>


  if ( !$CON )
    $CON = j2wp_do_mysql_connect();
  
  set_time_limit(0);

  $j2wp_wp_tb_prefix = get_option('j2wp_wp_tb_prefix');
  j2wp_do_wp_connect();

  //  get all posts with links to joomla categories  ==>  href="/......"
  $loc_str  = '\'href=' . '"' . '/\'';
  $loc_str2 = '\'href=' . '"' . '/?p=\'';
  $query = 'SELECT * FROM ' . $j2wp_wp_tb_prefix . 'posts WHERE ( (LOCATE(' . $loc_str . ', post_content)) AND ' .
            'NOT (LOCATE(' . $loc_str2 . ', post_content)) ) ';
  $post_list = mysql_query($query, $CON);
  if ( !$post_list )
    echo mysql_error();

  while( $row = mysql_fetch_array($post_list) ) 
  {
    $wp_posts[] = array(
        'ID' => $row['ID'],
        'post_author' => $row['post_author'],
        'post_content' => $row['post_content'], 
        'post_date' => $row['post_date'],
        'post_date_gmt' => $row['post_date_gmt'],
        'post_modified' => $row['post_modified'],
        'post_modified_gmt' => $row['post_modified_gmt'],
        'post_title' => $row['post_title'],
        'post_name' => $row['post_name']
        );  
    if ( mysql_error() )
      echo mysql_error();
  }
  
  //  check each post for liks
  echo '<br />' . __( 'The following links must be changed manually:', 'joomla2wp') . ' <br /><br />' . "\n";

  foreach ( $wp_posts as $j2wp_post )
  {
    set_time_limit(0);
    //  clear variable 
    $lnk_pos = 0;
    $post_changed = 0;
    //  get pos from href string and check if there are more
    while ( $lnk_pos = strpos( $j2wp_post['post_content'], 'href="/', $lnk_pos) )
    {
      //  with each link check if image
      if ( (!strpos( $j2wp_post['post_content'], 'href="/image', $lnk_pos)) AND
           (!strpos( $j2wp_post['post_content'], 'href="/"', $lnk_pos)) )
      {
        $j2wp_post = j2wp_change_single_url( $j2wp_post, $lnk_pos );
        //  do changes to post
        $post_changed = 1;
      }
      
      //  go to position after http:// to check if there is another link in the content
      $lnk_pos = $lnk_pos + 8;
    }
    
    if ( $post_changed )
    {
      j2wp_do_wp_connect();

      // wp_update_post not working properly
      // wp_update_post( $j2wp_post );
      $query = 'UPDATE  ' . $j2wp_wp_tb_prefix . 'posts SET post_content = "' . mysql_real_escape_string( $j2wp_post['post_content'] ) . '" WHERE ID = ' . $j2wp_post['ID'] . ' ';
      $update_rc = mysql_query($query, $CON);
      if ( !$update_rc )
        echo mysql_error();
    }
  }
  
  return;
}


function j2wp_change_single_url( $j2wp_post, $lnk_pos )
{
  global  $CON;
  
  $j2wp_wp_tb_prefix      = get_option('j2wp_wp_tb_prefix');
  $j2wp_joomla_tb_prefix  = get_option('j2wp_joomla_tb_prefix');

  $post_lnk_end       = strpos( $j2wp_post['post_content'], '"', $lnk_pos + 8);
  $post_lnk_string    = substr( $j2wp_post['post_content'], $lnk_pos, $post_lnk_end - $lnk_pos + 1 );
  $pos_lnk_last_slash = strrpos( $post_lnk_string, '/');

  //  check if itemid is there
  $pos = 0;
  $itemid = '';
  $last_string = substr( $post_lnk_string, $pos_lnk_last_slash + 1 );

  while ( is_numeric( $last_string[$pos] ) )
  {
    $itemid = $itemid . $last_string[$pos]; 
    $pos = $pos + 1;
  } 

  //  itemid is there - look it joomla for title and creation,modified date    
  if ( $itemid )
  {
    $itemid_numeric = intval( $itemid );
         
    // get title and creation date/time in Joomla TB
    $title = '';
    $date_created = '';
    j2wp_do_joomla_connect();
    $query = 'SELECT title, created FROM ' . $j2wp_joomla_tb_prefix . 'content WHERE id = ' . $itemid_numeric;
    $result = mysql_query($query, $CON);
    if ( !$result )
      echo mysql_error();
          
    while ( $joomla_row = mysql_fetch_array($result) ) 
    {
      $j2wp_title = $joomla_row['title']; 
      $j2wp_date_created = $joomla_row['created'];
    }
          
    // get post_id from WP for same title and creation date/time
    j2wp_do_wp_connect();
    $query =  'SELECT ID FROM ' . $j2wp_wp_tb_prefix . 'posts WHERE post_title = "' . $j2wp_title . 
              '" AND post_date = "' . $j2wp_date_created . '"';
    $result = mysql_query($query, $CON);
    if ( !$result )
      echo mysql_error();
          
    while ( $row = mysql_fetch_array($result) ) 
    {
      $url_post_id = $row['ID'];
    }
                                
    // $permalink = get_permalink( $url_post_id );
    //  update URL String with new content
    $j2wp_post['post_content'] =  substr( $j2wp_post['post_content'], 0, $lnk_pos) . 'href="/?p=' . $url_post_id . '" ' .
                                  substr( $j2wp_post['post_content'], $post_lnk_end + 1);
  }
  else
  {
    //  it is a category or .html or attachment file
    $link_string        = substr( $post_lnk_string, 7, strlen( $post_lnk_string ) - 8);
    $pos_lnk_last_slash = strrpos( $link_string, '/'); 
    
    //  check if is a category page
    if (  !strpos($link_string, '.') )
    {
      // determine the slug 
      if ( !$pos_lnk_last_slash )
      {
        $cat_slug = $link_string;
      }
      else
      {
        $cat_slug = substr( $link_string, strrpos( $link_string, '/') + 1);
      }
      j2wp_do_joomla_connect();
      // Get the ID of a given category from Joomla
      $query =  'SELECT id, title, alias FROM ' . $j2wp_joomla_tb_prefix . 'categories WHERE alias = "' . $cat_slug . '" ';
      $result = mysql_query($query, $CON);
      if ( !$result )
        echo mysql_error();
          
      while( $row = mysql_fetch_array($result) ) 
      {
        $joomla_cat_id = $row['id'];
      }
      // $category_id = get_cat_ID( $row['title'] );
      // Get the URL of this category
      // $category_link = get_category_link( $category_id );
      $j2wp_post['post_content'] =  substr( $j2wp_post['post_content'], 0, $lnk_pos) . 'href="' . $category_link . '" ' . 
                                    substr( $j2wp_post['post_content'], $post_lnk_end + 1);
    }
    else
    {
      //  check if there is a '.' inside the $last_string and not .html - then it is an attachment
      //  strrpos($last_string, '.')
      echo 'Post ID: ' . $j2wp_post['ID'] . ' link: ' . $post_lnk_string . '<br />'; 
    }
  } 

  return $j2wp_post;
}


function j2wp_check_mysql_variables()
{
  global $j2wp_mysql_vars;

  $CON = j2wp_do_mysql_connect();

  $query = "SHOW VARIABLES LIKE '%_timeout';";
  $result = mysql_query($query, $CON);
  if ( !$result )
    echo mysql_error();
  else
  {
    while ( $row = mysql_fetch_array($result) )
    { 
      $j2wp_mysql_vars_temp[] = array(
              'Variable_name' => $row['Variable_name'],
              'Value'    => $row['Value']
              );
    }
  }
  
  // check for each Variable if SET is possible
  foreach ( $j2wp_mysql_vars_temp as $mysql_var )
  {
    switch( $mysql_var['Variable_name'] )
    {
      case 'connect_timeout':
        $str = 'SET GLOBAL ';
        $r = j2wp_try_mysql_var_set( $str, $mysql_var['Variable_name'], $mysql_var['Value'] );
        if ( $r )
        {
          $j2wp_mysql_vars[] = array(
              'Variable_name' => $mysql_var['Variable_name'],
              'Value'    => $mysql_var['Value'],
              'str'      => $str
              );
        }
        break;
      case 'delayed_insert_timeout':
        $str = 'SET GLOBAL ';
        $r = j2wp_try_mysql_var_set( $str, $mysql_var['Variable_name'], $mysql_var['Value'] );
        if ( $r )
        {
          $j2wp_mysql_vars[] = array(
              'Variable_name' => $mysql_var['Variable_name'],
              'Value'    => $mysql_var['Value'],
              'str'      => $str
              );
        }
        break;
      case 'interactive_timeout':
        $str = 'SET SESSION ';
        $r = j2wp_try_mysql_var_set( $str, $mysql_var['Variable_name'], $mysql_var['Value'] );
        if ( $r )
        {
          $j2wp_mysql_vars[] = array(
              'Variable_name' => $mysql_var['Variable_name'],
              'Value'    => $mysql_var['Value'],
              'str'      => $str
              );
        }
        break;
      case 'net_read_timeout':
        $str = 'SET SESSION ';
        $r = j2wp_try_mysql_var_set( $str, $mysql_var['Variable_name'], $mysql_var['Value'] );
        if ( $r )
        {
          $j2wp_mysql_vars[] = array(
              'Variable_name' => $mysql_var['Variable_name'],
              'Value'    => $mysql_var['Value'],
              'str'      => $str
              );
        }
        break;
      case 'net_write_timeout':
        $str = 'SET SESSION ';
        $r = j2wp_try_mysql_var_set( $str, $mysql_var['Variable_name'], $mysql_var['Value'] );
        if ( $r )
        {
          $j2wp_mysql_vars[] = array(
              'Variable_name' => $mysql_var['Variable_name'],
              'Value'    => $mysql_var['Value'],
              'str'      => $str
              );
        }
        break;
      case 'slave_net_timeout':
        $str = 'SET GLOBAL ';
        $r = j2wp_try_mysql_var_set( $str, $mysql_var['Variable_name'], $mysql_var['Value'] );
        if ( $r )
        {
          $j2wp_mysql_vars[] = array(
              'Variable_name' => $mysql_var['Variable_name'],
              'Value'    => $mysql_var['Value'],
              'str'      => $str
              );
        }
        break;
      case 'table_lock_wait_timeout':
        $str = 'SET GLOBAL ';
        $r = j2wp_try_mysql_var_set( $str, $mysql_var['Variable_name'], $mysql_var['Value'] );
        if ( $r )
        {
          $j2wp_mysql_vars[] = array(
              'Variable_name' => $mysql_var['Variable_name'],
              'Value'    => $mysql_var['Value'],
              'str'      => $str
              );
        }
        break;
      case 'wait_timeout':
        $str = 'SET GLOBAL ';
        $r = j2wp_try_mysql_var_set( $str, $mysql_var['Variable_name'], $mysql_var['Value'] );
        if ( $r )
        {
          $j2wp_mysql_vars[] = array(
              'Variable_name' => $mysql_var['Variable_name'],
              'Value'    => $mysql_var['Value'],
              'str'      => $str
              );
        }
        break;
      default:
        break;
    }
  }

  $_SESSION['j2wp_mysql_vars'] = $j2wp_mysql_vars;

  return $j2wp_mysql_vars;
}


function j2wp_try_mysql_var_set( $str, $Variable_name, $Value )
{
  global $CON;

  if ( !$CON )
    $CON = j2wp_do_mysql_connect();
  
  $query = $str . $Variable_name . '=' . $Value . ';';
  $result = mysql_query($query, $CON);
  if ( !$result )
  {
    // echo mysql_error() . '<br />';
    return NULL;
  }
  else
    return 1;
}



function j2wp_set_mysql_variables()
{
  global $j2wp_mysql_vars;

  $error = 0;
  set_time_limit(0);
  if ( !$CON )
    $CON = j2wp_do_mysql_connect();

  $j2wp_mysql_vars = $_SESSION['j2wp_mysql_vars'];

  foreach ( $j2wp_mysql_vars as $var_temp )
  {
    $query = $var_temp['str'] . $var_temp['Variable_name'] . '=' . $var_temp['Value'] . ';';
    $result = mysql_query($query, $CON);
    if ( !$result )
    {
      echo mysql_error();
      $error = 1;
    }
  }

  if ( $error )
  {
    _e( '<br />Warning: can not SET MySQL time variables ... script may abort or stop working...<br />', 'joomla2wp');
  }

  return;
}

function j2wp_do_mysql_connect()
{
  static  $CON = NULL;
  global  $j2wp_mysql_srv,
          $j2wp_mysql_usr,
          $j2wp_mysql_pswd;
  
  $j2wp_mysql_srv       = get_option("j2wp_mysql_srv");
  $j2wp_mysql_usr       = get_option("j2wp_mysql_usr");
  $j2wp_mysql_pswd      = get_option("j2wp_mysql_pswd");
     
  // Testing SQL Settings
  $CON = mysql_connect($j2wp_mysql_srv, $j2wp_mysql_usr, $j2wp_mysql_pswd, 0) or die(throwERROR("Cant get MySQL Connection.".mysql_errno()." - ".mysql_error()));

  return $CON;  
}

function j2wp_do_wp_connect()
{
  global $CON;
  global $j2wp_wp_db_name;
  
  $j2wp_wp_db_name  =	get_option('j2wp_wp_db_name');
  
  // Database connection to WP DB
  mysql_select_db($j2wp_wp_db_name,$CON) or die(throwERROR("Cant select MySQL Database.".mysql_errno()." - ".mysql_error()));

  return;
}

function j2wp_do_joomla_connect()
{
  global $CON;
  global $j2wp_joomla_db_name;
 
  $j2wp_joomla_db_name  =	get_option('j2wp_joomla_db_name');

  // And action, getting existing posts and write them in WP Table:
  mysql_select_db($j2wp_joomla_db_name,$CON) or die(throwERROR("Cant select MySQL Database.".mysql_errno()." - ".mysql_error()));

  return;
}

?>