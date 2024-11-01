<?php
/**
 Plugin Name: Show Twitter Followers
 Plugin URI: https://zoxion.com/show-twitter-followers
 Version: 1.0.4
 Description: Provides a widget to display profile photos of your Twitter followers in a sidebar
 Author: classicon
 Author URI: https://zoxion.com
 */
 
 class Show_Twitter_Followers extends WP_Widget {
	function Show_Twitter_Followers() {
		$widget_ops = array('classname' => 'widget_show_twitter_followers', 'description' => 'Displays your twitter followers\' photos in a sidebar box' );
		$this->WP_Widget('show_twitter_followers','Show Twitter Followers', $widget_ops);
	}
	
    function array_randsort($array,$preserve_keys=false){
    	if(!is_array($array)):
    		exit('Supplied argument is not a valid array.');
    	else:
    		$i = NULL;
    		$array_length = count($array); 
    		$randomize_array_keys = array_rand($array,$array_length);
    		if($preserve_keys===true) {
    			foreach($randomize_array_keys as $k=>$v){
    				$randsort[$randomize_array_keys[$k]] = $array[$randomize_array_keys[$k]];
    			}
    		} else {
    			for($i=0; $i < $array_length; $i++){
    				$randsort[$i] = $array[$randomize_array_keys[$i]];
    			}
    		}
    		return $randsort;
    	endif;
    }




function widget($args, $instance) {
 
    global $wpdb, $wp_json;
    
  $table_name =$wpdb->prefix . "showtwitterfol";
		extract($args, EXTR_SKIP);
		$stf_username = ($instance['stf_username'] != '') ? $instance['stf_username'] : 'codebyfreeman';
		$stf_number   = ($instance['stf_number'] != '')   ? $instance['stf_number']   : '8';
		$stf_border   = ($instance['stf_border'] != '')   ? $instance['stf_border']   : '94a3c4';
		$stf_head_link  = ($instance['stf_head_link'] != '')   ? $instance['stf_head_link']  : '3b5998';
		$stf_head_bg  = ($instance['stf_head_bg'] != '')   ? $instance['stf_head_bg']  : 'eceff5';
		$stf_body_bg  = ($instance['stf_body_bg'] != '')  ? $instance['stf_body_bg']  : 'fff';
		$stf_follow_image  = ($instance['stf_follow_image'] != '')  ? $instance['stf_follow_image']  : $this->get_plugin_url().'/follow-me-black.png';
                $stf_width=($instance['stf_width'] != '')  ? $instance['stf_width']  : '250';
                $stf_height=($instance['stf_height'] != '')  ? $instance['stf_height']  : '400';

            //get cached data
          $data=  $wpdb->get_row("SELECT * FROM $table_name WHERE id='".$stf_username."'",ARRAY_A );
        // as array
          $data=  json_decode($data['user_json'],true);
        

          $fol=$data['fans'];
            
         // randomize array;
     shuffle($fol);
        
$stf_mwidth=$stf_width.'px';
$stf_mheight=$stf_height.'px';

        echo "
        <style>
        .stf {overflow:hidden; width: $stf_mwidth; height:$stf_height; border:1px solid #$stf_border; margin:auto; margin-bottom:10px; font-family:\"lucida grande\",tahoma,verdana; }
        .stfhead {background:#$stf_head_bg; padding:10px 10px 8px 10px}
        .stfhead span { font-size:10px;}
        .stfstatus {font-size:11px; padding-bottom:5px;}
        .stfheadlink { font-size:14px; font-weight:bold; color:#$stf_head_link; text-decoration:none;}
        .stfbody {border-top:1px solid #ddd; padding:10px 10px 4px 10px; background:#$stf_body_bg}
        .stfbody a { color:#808080;  text-decoration:none;}
        .stffan {float:left; width:55px; font-size:9px;padding-bottom:8px;}
        </style>
        ";

		//echo $before_widget;
		echo '<div class="stf">';
		echo '<div class="stfhead">';
		echo '<img src="'.$data['profile_image_url'].'" style="float:left; width:50; height:50; padding-right:10px" />';
		echo '<div><a class="stfheadlink" href="https://twitter.com/'.$data['screen_name'].'" target="_blank"><b>'.$data['name'] . '</b> <span>on Twitter</span></a></div>';
		echo '<div style="padding-top:4px;"><a href="https://twitter.com/'.$data['screen_name'].'" target="_blank"><img src="'.$stf_follow_image.'" border="0" /></a></div>';
		echo '</div>';
		echo '<div class="stfbody">';
		echo '<div class="stfstatus">'.$data['name'].' has '.$data['followers_count'].' followers</div>';
       
        //loop toshow fans
                for($i=0; $i<$stf_number; $i++)
        {
      
            echo '<div class="stffan"><a href="https://twitter.com/'.$fol[$i]['screen_name'].'" target="_blank"><img src="'.$fol[$i]['profile_image_url'].'" width="50" height="50" /><div style="text-align:center">'. substr($fol[$i]['screen_name'], 0, 8) . '</a></div></div>';
        }
		echo '<div style="clear:both"></div>';
		echo '</div>';
		echo '</div>';
	
}

function update($new_instance, $old_instance){
    
            //database & json
            global $wpdb;
            //table name
             $table_name =$wpdb->prefix . "showtwitterfol";
		$instance = $old_instance;

		$instance['stf_username']  = strip_tags($new_instance['stf_username']);
		$instance['stf_number']    = strip_tags($new_instance['stf_number']);
		$instance['stf_border']    = strip_tags($new_instance['stf_border']);
		$instance['stf_head_link'] = strip_tags($new_instance['stf_head_link']);
		$instance['stf_head_bg']   = strip_tags($new_instance['stf_head_bg']);
                $instance['stf_body_bg']   = strip_tags($new_instance['stf_body_bg']);
                $instance['stf_follow_image']   = strip_tags($new_instance['stf_follow_image']);
                $instance['stf_width']   = strip_tags($new_instance['stf_width']);
                $instance['stf_height']   = strip_tags($new_instance['stf_height']);
//initialisation

                if($instance['stf_username']!=""){


        //url to check for rate limit status
         $status_url= 'https://twitter.com/account/rate_limit_status.json';

      
        //ensure the status allows for querying
         if($this->get_remaining_server_hits($status_url)>50){
           
        //url to get user detail
        $url = 'https://api.twitter.com/1/users/show.json?screen_name='.$instance['stf_username'];

        //user detail as array
        $user = $this->json_to_array($url)        ;

        $followers_url = 'https://api.twitter.com/1/followers/ids.json?screen_name='.$instance['stf_username'];
       $stf_username=$instance['stf_username'];

       //all followers id
       $fans = $this->json_to_array($followers_url);


        $show_twitter_followers = get_option('show_twitter_followers');
        $show_twitter_followers['name'] = (string)$user['name'];
        $show_twitter_followers['profile_image_url']    = (string)$user['profile_image_url'];
        $show_twitter_followers['screen_name']          = (string)$user['screen_name'];
        $show_twitter_followers['followers_count']          = (string)$user['followers_count'];
$max_num=30;
if($max_num >$show_twitter_followers['followers_count'] ){$max_num=$show_twitter_followers['followers_count'] ;}
        for($i=0; $i<$max_num; $i++){ $url="https://api.twitter.com/1/users/show.json?user_id=".$fans['ids'][$i];

               //store in array
               $fandata[$i]=  $this->json_to_array($url);
               
            $show_twitter_followers['fans'][$i]['screen_name'] = (string)$fandata[$i]['screen_name'];
            $show_twitter_followers['fans'][$i]['profile_image_url'] = (string)$fandata[$i]['profile_image_url'];
        }
       

        delete_option('show_twitter_followers');
        update_option('show_twitter_followers', $show_twitter_followers);

        $sql="INSERT INTO $table_name (id,user_json, created_at) VALUES ('" .$wpdb->escape($stf_username) . "','" .$wpdb->escape(json_encode($show_twitter_followers))."',NOW()) ON DUPLICATE KEY UPDATE user_json='".$wpdb->escape(json_encode($show_twitter_followers))."',created_at=NOW()" ;
   
        $wpdb->query($sql);
                }
                }
                                        
		return $instance;
	}
 
function form($instance) {
   
		$instance = wp_parse_args( (array) $instance, array( 'stf_username' => '', 'stf_number' => '', 'stf_border' => '', 'stf_head_link' => '', 'stf_head_bg' => '', 'stf_body_bg' => '', 'stf_follow_image' => '' ) );
		$stf_username = strip_tags($instance['stf_username']);
		$stf_number = strip_tags($instance['stf_number']);
		$stf_border = strip_tags($instance['stf_border']);
		$stf_head_link = strip_tags($instance['stf_head_link']);
		$stf_head_bg = strip_tags($instance['stf_head_bg']);
		$stf_body_bg = strip_tags($instance['stf_body_bg']);
		$stf_follow_image = strip_tags($instance['stf_follow_image']);
                $stf_width   = strip_tags($instance['stf_width']);
                $stf_height   = strip_tags($instance['stf_height']);

?>
		<p><label for="<?php echo $this->get_field_id('stf_username'); ?>">Twitter Username: <input class="widefat" id="<?php echo $this->get_field_id('stf_username'); ?>" name="<?php echo $this->get_field_name('stf_username'); ?>" type="text" value="<?php echo esc_attr($stf_username); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('stf_number'); ?>">No of Followers to Show: <input class="widefat" id="<?php echo $this->get_field_id('stf_number'); ?>" name="<?php echo $this->get_field_name('stf_number'); ?>" type="text" value="<?php echo esc_attr($stf_number); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('stf_border'); ?>">Border Color (e.g. 94a3c4): <input class="widefat" id="<?php echo $this->get_field_id('stf_border'); ?>" name="<?php echo $this->get_field_name('stf_border'); ?>" type="text" value="<?php echo esc_attr($stf_border); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('stf_head_link'); ?>">Heading Color (e.g. 3b5998): <input class="widefat" id="<?php echo $this->get_field_id('stf_head_link'); ?>" name="<?php echo $this->get_field_name('stf_head_link'); ?>" type="text" value="<?php echo esc_attr($stf_head_link); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('stf_head_bg'); ?>">Head BG Color (e.g. eceff5): <input class="widefat" id="<?php echo $this->get_field_id('stf_head_bg'); ?>" name="<?php echo $this->get_field_name('stf_head_bg'); ?>" type="text" value="<?php echo esc_attr($stf_head_bg); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('stf_body_bg'); ?>">Body BG Color (e.g. ffffff): <input class="widefat" id="<?php echo $this->get_field_id('stf_body_bg'); ?>" name="<?php echo $this->get_field_name('stf_body_bg'); ?>" type="text" value="<?php echo esc_attr($stf_body_bg); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('stf_width'); ?>">Width (e.g. 250): <input class="widefat" id="<?php echo $this->get_field_id('stf_width'); ?>" name="<?php echo $this->get_field_name('stf_width'); ?>" type="text" value="<?php echo esc_attr($stf_width); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('stf_height'); ?>">Height (e.g. 400): <input class="widefat" id="<?php echo $this->get_field_id('stf_height'); ?>" name="<?php echo $this->get_field_name('stf_height'); ?>" type="text" value="<?php echo esc_attr($stf_height); ?>" /></label></p>
                <p><label for="<?php echo $this->get_field_id('stf_follow_image'); ?>">Follow Me Button (url of an image): <input class="widefat" id="<?php echo $this->get_field_id('stf_follow_image'); ?>" name="<?php echo $this->get_field_name('stf_follow_image'); ?>" type="text" value="<?php echo esc_attr($stf_follow_image); ?>" /></label></p>
                <?php
	}

        //update database

function cron_update_followers(){
    $url='https://twitter.com/account/rate_limit_status.json';

global $wpdb;
$options=get_option('show_twitter_followers');
$table_name =$wpdb->prefix . "showtwitterfol";
$stf_username=$options['screen_name'];
        //url to check for rate limit status


        //check if
        //twitter uses ip address for rate limits thus on shared servers there is risk of rate abuse
        if($this->get_remaining_server_hits($url)>50){
        //url to get user detail
        $url = 'https://api.twitter.com/1/users/show.json?screen_name='.$stf_username;

 
        //user detail as array
        $user =  $this->json_to_array($url)        ;

        //url to get user followers
        $url="https://api.twitter.com/1/followers/ids.json?screen_name=".$stf_username;

        //all fans array
        $fans=$this->json_to_array($url);

        //username
        $show_twitter_followers['name'] = (string)$user['name'];
        //profile image
        $show_twitter_followers['profile_image_url']    = (string)$user['profile_image_url'];
        //screen name
        $show_twitter_followers['screen_name']          = (string)$user['screen_name'];
        //followers count
        $show_twitter_followers['followers_count']          = (string)$user['followers_count'];
        //maximum number of followers to query from the API
                $max_num =30;
         
         if($show_twitter_followers['followers_count'] <$max_num){$max_num=$show_twitter_followers['followers_count'] ;}

        //loop to get followers details
        for($i=0; $i<$max_num; $i++)
        {$url="https://api.twitter.com/1/users/show.json?user_id=".$fans['ids'][$i];

               //store in array
              $fan_info=$this->json_to_array($url);

              $all_fans_info[$i]=$fan_info;
              //add to my array for storage
            $show_twitter_followers['fans'][$i]['screen_name'] = (string)$fan_info['screen_name'];
            $show_twitter_followers['fans'][$i]['profile_image_url'] = (string)$fan_info['profile_image_url'];


        } //randomize the fans

       

	//save in database for caching, update database if user exists
        $sql="INSERT INTO $table_name (id,user_json,created_at) VALUES ('" .
						$wpdb->escape($stf_username) . "','" .$wpdb->escape(json_encode($show_twitter_followers))."',NOW()) ON DUPLICATE KEY UPDATE user_json='".$wpdb->escape(json_encode($show_twitter_followers))."',created_at=NOW()" ;


        $wpdb->query($sql);

        }

        }


 function get_remaining_server_hits($url){
    $array=$this->json_to_array($url);

    return $array['remaining_hits'];

}

 function json_to_array($url){
        $json=  $this->get_json($url);
				if(function_exists("json_decode")){

					$array= json_decode($json,true);
                                      
                                }
                                return $array;
        }

 function get_json($url){

    if (function_exists("curl_init")){
$json=  $this->curl_get_content($url);

                                }
 # plan B is to use file_get_contents
  elseif (function_exists('file_get_contents')) {
   
    $json = @file_get_contents($url);
  }
  # fallback is to use fopen
  else {
    if ($fh = fopen($url, 'rb')) {
      clearstatcache();
      if ($fsize = @filesize($url)) {
        $json = fread($fh, $fsize);
      }
      else {
          while (!feof($fh)) {
            $json .= fread($fh, 8192);
          }
      }
      fclose($fh);
    }
  }return $json;
}


 function curl_get_content($url){
    $ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, "Firefox (WindowsXP) - Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_HEADER, 0);
    ob_start();
    curl_exec ($ch);
    curl_close ($ch);
    $return = ob_get_contents();
    ob_end_clean();

    return $return;
}
function get_plugin_url() {
    	if ( !function_exists('plugins_url') )
    		return get_option('siteurl') . '/wp-content/plugins/' . plugin_basename(dirname(__FILE__));

    	return plugins_url(plugin_basename(dirname(__FILE__)));
    }

}//end class


    


function stf_db_install() {
	global $wpdb;

	$table_name = $wpdb->prefix . "showtwitterfol";
	$tb_o = get_option("show_twitter_followers");

	$tb_db_version = "5";
	$installed_ver = $tb_o["db_version"];

	// if table is not already there - create it
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE " . $table_name . " (
			  id VARCHAR(100) NOT NULL PRIMARY KEY,
                          user_json TEXT NOT NULL,
			  created_at TIMESTAMP
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$tb_o['db_version'] = $tb_db_version;
		update_option('show_twitter_followers',$tb_o);
	}
	// if table is there but has old structure
	elseif ($installed_ver != $tb_db_version) {

		$sql = "CREATE TABLE " . $table_name . " (
			  id VARCHAR(200) NOT NULL PRIMARY KEY,
			  user_json TEXT NOT NULL,
			  created_at TIMESTAMP
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
                $tb_o['db_version'] = $tb_db_version;
		update_option('show_twitter_followers',$tb_o);
	}
}

function stf_db_cache_clear($where_sql = ''){
	global $wpdb;
	$table_name = $wpdb->prefix . "showtwitterfol";

	// delete tweets that are older than predefined period
	$wpdb->query("DELETE FROM $table_name $where_sql");
}


function register_show_twitter_followers(){
    register_widget('Show_Twitter_Followers');
    
}

add_action('wp','stf_clear_cron');



//set up cron
function stf_cron_activation() { 
	if ( !wp_next_scheduled( 'stf_update_followers' ) ) {
           
		wp_schedule_event(time(), 'hourly', 'stf_update_followers');
               
	}
}
//

add_action('wp', 'stf_cron_activation');
add_action('stf_update_followers', "init_xclass_and_cron");
function init_xclass_and_cron() {
    $myxclass = new  Show_Twitter_Followers();
    $url='https://twitter.com/account/rate_limit_status.json';
  
    $myxclass->cron_update_followers();
}
//clean_up cron if inactive
function stf_clear_cron(){
if ( !is_active_widget(false,false,'show_twitter_followers') && wp_next_scheduled( 'stf_update_followers' ) ) {
    wp_clear_scheduled_hook('stf_update_followers');
}

}


register_activation_hook(__FILE__, 'stf_db_install');
//add_action('wp', array('Show_Twitter_Followers','cron_update_followers'));
register_deactivation_hook(__FILE__, 'stf_db_cache_clear');
add_action('init', 'register_show_twitter_followers', 1);
        
        $xclass = new  Show_Twitter_Followers();
        global $xclass;
?>