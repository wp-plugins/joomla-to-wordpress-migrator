<?php

global  $wpdb;
global  $CON,
        $user_id;
global  $j2wp_mysql_srv,
        $j2wp_mysql_usr,
        $j2wp_mysql_pswd,
        $j2wp_error_flag,
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
  global  $j2wp_error_flag;

  ob_end_flush();
  ob_start();
  flush();
  ob_flush();


  if ( is_array($func) )
  {
    $sel_values = $func;
    $func = 2;
  }

  // check if Plugin Options are set
  if ( !((strlen(get_option( 'j2wp_mysql_srv' )) != 0) AND
       (strlen(get_option( 'j2wp_mysql_usr' )) != 0) AND
       (strlen(get_option( 'j2wp_mysql_pswd' )) != 0) AND
       (strlen(get_option( 'j2wp_joomla_db_name' )) != 0) AND
       (strlen(get_option( 'j2wp_joomla_tb_prefix' )) != 0) AND
       (strlen(get_option( 'j2wp_wp_db_name' )) != 0) AND
       (strlen(get_option( 'j2wp_wp_tb_prefix' )) != 0)) )
  {
    $j2wp_error_flag = -70000;
  }
  else
  {
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
    $j2wp_error_flag = 0;
  }

  return $j2wp_error_flag;
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


  $mtime = microtime(); 
  $mtime_start = explode(' ',$mtime); 

  // setting timelimit
  if ( function_exists('set_time_limit') )
  {
    ignore_user_abort(1);
    set_time_limit(0);
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


  $mtime = microtime(); 
  $mtime_end = explode(' ',$mtime); 
  $totaltime[0] = ($mtime_end[0] - $mtime_start[0]); // microseconds like 0.xxxxxxx
  $totaltime[1] = ($mtime_end[1] - $mtime_start[1]); 

  echo '<br />' . "\n";
  echo 'script execution time: ' . $totaltime[1] . ' seconds <br /><br />'; 

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
    set_time_limit(0);
    $result_array = j2wp_process_posts_by_step( $mig_cat_array, $working_steps, $working_pos, $user_id);
    sleep(1);
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
          

  set_time_limit(0);
  $j2wp_wp_tb_prefix = get_option('j2wp_wp_tb_prefix');
  j2wp_do_wp_connect();

  $count = 0;
  //  foreach ($wp_posts as $j2wp_post) 
  foreach ($sql_query as $query) 
  {
    if ( (($count % 50) == 0) )
      echo '.';

    set_time_limit(0);

    //  set timeout values 
    $query_cmd  = "SET net_read_timeout = 18000;";
    $query_rc = mysql_query($query_cmd, $CON);
    if ( mysql_error() )
      echo mysql_error();
    $query_cmd  = "SET net_write_timeout = 18000;";
    $query_rc = mysql_query($query_cmd, $CON);
    if ( mysql_error() )
      echo mysql_error();

    set_time_limit(0);
    $query_rc = mysql_query($query,$CON);
    if ( mysql_error() )
      echo mysql_error();
    //  wait for proccessing the sql
    usleep(10000);


    //  $id = wp_insert_post( $j2wp_post );

    set_time_limit(0);
    $id = mysql_insert_id($CON);
    if($id) 
    {
      wp_set_post_categories( $id, array($wp_cat_id) );
      usleep(10);

      //  add tags to post
      $tags = $post_tags[$count];
      wp_set_post_tags( $id, $tags, false );
      usleep(10);
      $count++;
    }
  }

  //  flush tables
  // $query = 'FLUSH TABLES ' . $j2wp_wp_tb_prefix . 'posts;';
  // $query_rc = mysql_query($query,$CON);

  echo '<br />';
  _e( 'Inserted ', 'joomla2wp');
  echo $count . ' Posts<br /><br />';

/*
  if ( mysql_error() )
  {
    echo 'Could not perform FLUSH TABLES statement!!!  -  MySQL Error: ';
    echo mysql_error();
    echo '<br />';
  }
*/

  //  enable table indixes
  $query = 'ALTER TABLE ' . $j2wp_wp_tb_prefix . 'posts ENABLE KEYS;';
  $query_rc = mysql_query($query,$CON);
  if ( mysql_error() )
  {
    echo 'Could not perform ALTER TABLE statement!!!  -  MySQL Error: ';
    echo mysql_error();
    echo '<br />';
  }


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
  $j2wp_wp_tb_prefix = get_option('j2wp_wp_tb_prefix');

  j2wp_do_wp_connect();
  //  enable table indixes
  $query = 'ALTER TABLE ' . $j2wp_wp_tb_prefix . 'posts DISABLE KEYS;';
  $query_rc = mysql_query($query,$CON);
  if ( mysql_error() )
  {
    echo 'Could not perform ALTER TABLE statement!!!  -  MySQL Error: ';
    echo mysql_error();
    echo '<br />';
  }

  set_time_limit(0);
  $j2wp_joomla_tb_prefix = get_option('j2wp_joomla_tb_prefix');
  j2wp_do_joomla_connect();

  unset($result);
  set_time_limit(0);
  $query  = "SET net_read_timeout = 18000;";
  $query_rc = mysql_query($query, $CON);
  if ( mysql_error() )
    echo mysql_error();
  $query  = "SET net_write_timeout = 18000;";
  $query_rc = mysql_query($query, $CON);
  if ( mysql_error() )
    echo mysql_error();

  $query  = "SELECT * FROM `" . $j2wp_joomla_tb_prefix . "content` WHERE catid = '" . $mig_cat_array['joomla_id'] . "' ORDER BY `created` LIMIT " . $working_pos . ", " . $working_steps . " ";
  $result = mysql_query($query, $CON);
  if ( !$result )
    echo mysql_error();

  unset($result_array);  
  $sql_query = array();
  $post_tags = array();
  $STORAGE   = array();
  $wp_posts  = array();

  while($R = mysql_fetch_array($result)) 
  {
    if ( mysql_error() )
      echo mysql_error();
    set_time_limit(0);
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
    set_time_limit(0);
  }

  echo '<br /> ' . __( 'Processing ', 'joomla2wp') . count($wp_posts) . ' Posts...' . "\n";
  flush();
  ob_flush();
  set_time_limit(0);

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
  global  $j2wp_error_flag;

  j2wp_print_output_page();

  $wp_posts = array();

  if ( !$CON )
    $CON = j2wp_do_mysql_connect();
  
  // setting timelimit
  if ( function_exists('set_time_limit') )
  {
    ignore_user_abort(1);
    set_time_limit(0);
  }
  else
    _e( '<br />Warning: can not execute set_time_limit() script may abort...<br />', 'joomla2wp');

  $j2wp_wp_tb_prefix = get_option('j2wp_wp_tb_prefix');
  j2wp_do_wp_connect();

  //  get all posts with links to joomla categories  ==>  href="/.........."
  //                                             or  ==>  href="xxxx......."
  //                                        but not  ==>  href="http://...."   externe links - not changed
  //                                        and not  ==>  href="/?p=......."   already point to wordpress posts
  $loc_str  = '\'href="\'';
  $loc_str2 = 'href="/?p=';
  $loc_str3 = 'href="http://';
  $loc_str4 = 'href="https://';
  $loc_str5 = 'href="mailto:';
  $loc_str6 = 'href="image';
  $loc_str7 = 'href="/image';
  $loc_str8 = 'href="/"';
  $loc_str9 = 'href=""';

  $query = 'SELECT * FROM ' . $j2wp_wp_tb_prefix . 'posts WHERE ( LOCATE(' . $loc_str . ', post_content) ) ';
  $post_list = mysql_query($query, $CON);
  if ( mysql_error() )
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
  
  echo '<br />' . __( 'The following links must be changed manually:', 'joomla2wp') . ' <br /><br />' . "\n";

  //  check each post for links
  foreach ( $wp_posts as $j2wp_post )
  {
    set_time_limit(0);
    //  clear variables 
    $lnk_pos = 0;
    $post_changed = 0;
    //  get pos from href string and check if there are more
    while ( $lnk_pos = strpos( $j2wp_post['post_content'], 'href="', $lnk_pos) )
    {
      //  check if this link is ok or not
      $j2wp_length = strpos( $j2wp_post['post_content'], '"', $lnk_pos + 7) - $lnk_pos + 1;
      $j2wp_temp_link = substr( $j2wp_post['post_content'], $lnk_pos, $j2wp_length);
      if ( (strpos( $j2wp_temp_link, $loc_str2) === false ) AND
           (strpos( $j2wp_temp_link, $loc_str3) === false ) AND
           (strpos( $j2wp_temp_link, $loc_str4) === false ) AND
           (strpos( $j2wp_temp_link, $loc_str5) === false ) AND
           (strpos( $j2wp_temp_link, $loc_str6) === false ) AND
           (strpos( $j2wp_temp_link, $loc_str7) === false ) AND
           (strpos( $j2wp_temp_link, $loc_str8) === false ) AND
           (strpos( $j2wp_temp_link, $loc_str9) === false )
         )
      {
        $j2wp_post = j2wp_change_single_url( $j2wp_post, $lnk_pos );
        //  do changes to post
        $post_changed = 1;
      }
      //  go to position after href=" to check if there is another link in the content
      $lnk_pos = $lnk_pos + 7;
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
  
  $j2wp_error_flag = 0;

  echo '<div id="message" class="updated fade"><strong>URLs changed ! </strong>.</div>';

  flush();
  ob_flush();
  ob_end_flush();

  return $j2wp_error_flag;
}


function j2wp_change_single_url( $j2wp_post, $lnk_pos )
{
  global  $CON,
          $wpdb;
  
  $j2wp_wp_tb_prefix      = get_option('j2wp_wp_tb_prefix');
  $j2wp_joomla_tb_prefix  = get_option('j2wp_joomla_tb_prefix');

  $permalink = false;
  $j2wp_url_processed = false;

  //  $lnk_pos         ---> pos at href=" string in post_content
  //  $post_lnk_end    ---> pos at last " in link string of post_content
  //  $post_lnk_string ---> contains the whole link string inkl. " at the end
  $post_lnk_end       = strpos( $j2wp_post['post_content'], '"', $lnk_pos + 7);
  $post_lnk_string    = substr( $j2wp_post['post_content'], $lnk_pos, $post_lnk_end - $lnk_pos + 1 );
  if ( !(strrpos( $post_lnk_string, '/') === false) )
    $pos_lnk_last_slash = strrpos( $post_lnk_string, '/');

  //  urls with structure: href="index.php?option=com_content&view=article&id=9257:2009-fha-loan-limits&catid=52:fha&Itemid=97"
  if ( !(strpos( $post_lnk_string, 'view=article') === false) )
  {      
    $pos_article_id = strpos( $post_lnk_string, 'article&amp;id=') + 15;
    $article_id = j2wp_extract_number( substr( $post_lnk_string, $pos_article_id ) );   

    $url_post_id = j2wp_get_post_url_for_id( $article_id );

    $permalink = get_permalink( $url_post_id );
    $j2wp_url_processed = true;
  }

  //  urls with structure: index.php?option=com_content&view=category&id=49:credit-optimization&layout=blog&Itemid=78
  if ( (!(strpos( $post_lnk_string, 'view=category') === false)) AND ($j2wp_url_processed === false) )
  {
    $pos_cat_id = strpos( $post_lnk_string, 'view=category') + 16;
    $url_post_id = j2wp_get_post_url_for_cat_id( $article_id );

    $permalink = get_permalink( $url_post_id );
    $j2wp_url_processed = true;
  }

  //  urls with structure: index.php?option=com_content&view=section&id=9&layout=blog&Itemid=64
  if ( strpos( $post_lnk_string, 'view=section') AND ($j2wp_url_processed === false) )
  {
    echo 'Post ID: ' . $j2wp_post['ID'] . ' link: ' . $post_lnk_string . '<br />'; 
    $j2wp_url_processed = true;
  }

  if ( ($j2wp_url_processed === false) )
  {
    //  urls with structure: /82345-fha-loan-limits
    //                   or  href="mortgagecenter/39-news/11548-focus-on-the-6500-tax-credit.html"
    $itemid = j2wp_extract_number( substr( $post_lnk_string, $pos_lnk_last_slash + 1 ) );   
    //  itemid is there - look it joomla for title and creation,modified date    
    if ( $itemid )
    {
      $url_post_id = j2wp_get_post_url_for_id( $itemid );

      $permalink = get_permalink( $url_post_id );
      $j2wp_url_processed = true;
    }
  
    if ( ($j2wp_url_processed === false) )
    {
      //  it is a category or .html or attachment file
      $link_string        = substr( $post_lnk_string, 7, strlen( $post_lnk_string ) - 8);
      $pos_lnk_last_slash = strrpos( $link_string, '/'); 
    
      //  urls with structure: /glossary
      //  check if is a category page
      if (  !strpos($link_string, '.') )
      {
        $joomla_cat_title = NULL;
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
          $joomla_cat_title = $row['title'];
        }
        if ( $joomla_cat_title )
        {
          j2wp_do_wp_connect();
          if ( $category_id = get_cat_ID( $joomla_cat_title ) )
          {
            // Get the URL of this category
            $permalink = get_category_link( $category_id );
          }
          else
          {
            // it must be an entry in the jos_content  -  should not happen this else tree
            echo 'LOOOOK -->  Post ID: ' . $j2wp_post['ID'] . ' link: ' . $post_lnk_string . '<br />'; 
          }
        }
      }
      else
      {
        //  there is a '.' inside the $last_string - check if .html - or an attachment .pdf .jpg .mpeg etc.
        //  strrpos($last_string, '.')
        echo 'Post ID: ' . $j2wp_post['ID'] . ' link: ' . $post_lnk_string . '<br />'; 
      }
    } 
  }

  //  update URL String with new content
  if ( $permalink )
  {
    $j2wp_post['post_content'] =  substr( $j2wp_post['post_content'], 0, $lnk_pos) . 'href="' . $permalink . '" ' .
                                  substr( $j2wp_post['post_content'], $post_lnk_end + 1);
  }

  return $j2wp_post;
}


function j2wp_get_post_url_for_cat_id( $cat_id )
{
  global  $CON,
          $wpdb;

  $j2wp_wp_tb_prefix      = get_option('j2wp_wp_tb_prefix');
  $j2wp_joomla_tb_prefix  = get_option('j2wp_joomla_tb_prefix');

  j2wp_do_joomla_connect();
  // Get Cat title of a given category id from Joomla
  $query =  'SELECT id, title, alias FROM ' . $j2wp_joomla_tb_prefix . 'categories WHERE id = "' . $cat_id . '" ';
  $result = mysql_query($query, $CON);
  if ( !$result )
    echo mysql_error();
          
  while( $row = mysql_fetch_array($result) ) 
  {
    $joomla_cat_title = $row['title'];
  }
  if ( $joomla_cat_title )
  {
    j2wp_do_wp_connect();
    if ( $category_id = get_cat_ID( $joomla_cat_title ) )
    {
      // Get the URL of this category
      $permalink = get_category_link( $category_id );
    }
  }

  return $url_post_id;
}


function j2wp_extract_number( $last_string )
{
  $pos = 0;
  $itemid = '';
  while ( is_numeric( $last_string[$pos] ) )
  {
    $itemid = $itemid . $last_string[$pos]; 
    $pos++;
  } 

  return $itemid;
}

function j2wp_get_post_url_for_id( $itemid )
{
  global  $CON;
  
  $j2wp_wp_tb_prefix      = get_option('j2wp_wp_tb_prefix');
  $j2wp_joomla_tb_prefix  = get_option('j2wp_joomla_tb_prefix');

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
  $query =  'SELECT ID FROM ' . $j2wp_wp_tb_prefix . 'posts WHERE post_title = "' . mysql_real_escape_string($j2wp_title) . '" AND post_date = "' . $j2wp_date_created . '"';
  $result = mysql_query($query, $CON);
  if ( !$result )
    echo mysql_error();
          
  while ( $row = mysql_fetch_array($result) ) 
  {
    $url_post_id = $row['ID'];
  }

  return $url_post_id;
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