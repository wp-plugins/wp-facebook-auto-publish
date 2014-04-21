<?php 
/*
 * Plugin Name: WP Facebook Auto Publish
 * Plugin URI: http://www.vivacityinfotech.com/
 * Description: A Simple wordpress plugin to automatically post your wordpress posts and pages on Facebook along with their featured image.
 * Version: 1.0
 * Author: Vivacity Infotech Pvt. Ltd.
 * Author URI: http://www.vivacityinfotech.com/
 * License: GPL2
*/
/*
Copyright 2014  Vivacity InfoTech Pvt. Ltd.  (email : vivacityinfotech.jaipur@gmail.com)
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
include('lib/facebook.php');
define('WP_POST_REVISIONS', false);
function wfap_get_access_token()
{
	if(isset($_REQUEST['code']))
	{	
		$code=$_REQUEST['code'];
		$page_url = get_site_url();
	$auth_url='https://graph.facebook.com/oauth/access_token?client_id=512993528731249&client_secret=d9ab9699d107a5c15971bb14e1c684f6&redirect_uri='.urlencode($page_url).'&code='.$code;	
		$accesscode = file_get_contents($auth_url);
		$allval = array(); $token = array();
		$allval =  explode("=",$accesscode );
		$token = explode("&", $allval[1]);
		//echo $accesscode; exit;
		update_option('wfap_user_tkn',$token[0]);
		$location = admin_url( 'admin.php?page=wp-facebook-auto-publish/wp-fap-gui.php'); 
		wp_redirect( $location);
		exit;
	}
}
add_action( 'wp_ajax_fbsettings', 'wfap_save_settings' );
add_action( 'init', 'wfap_get_access_token' );
add_action( 'wp_ajax_authenticate', 'wfap_authenticate' );
add_action('admin_menu', 'wp_fap_on_admin');
add_action( 'publish_post', 'wfap_post_on_facebook' ); 
add_action( 'save_post', 'wfap_post_on_facebook' ); 

function wp_fap_on_admin() {
    add_menu_page( 'WP Fb Auto Publish', 'WP2FB Auto Post', 'manage_options', 'wp-facebook-auto-publish/wp-fap-gui.php', '', plugins_url( 'wp-facebook-auto-publish/images/icon.png' ),99);
}

function wfap_save_settings(){
		$wfab_app_id = $_REQUEST['app_id'];
		$wfab_sec_id = $_REQUEST['sec_id'];
		$wfab_page_id = $_REQUEST['page_id'];
		$wfab_user_tkn = $_REQUEST['user_tkn'];
		$wfab_default_img = $_REQUEST['default_img'];
		$wfab_default_img_link = $_REQUEST['default_img_link'];
		if(!empty($_REQUEST['post_list']))
			$wfab_post_type = implode(",",$_REQUEST['post_list']);
		else
			$wfab_post_type = 0;
			
		if(empty($wfab_app_id)) $wfab_app_id=0;
		if(empty($wfab_sec_id)) $wfab_sec_id=0;
		if(empty($wfab_page_id)) $wfab_page_id='me';
		//echo $wfab_app_id."   ---  ".$wfab_sec_id."  ---   ".$wfab_page_id."  ---  ".$wfab_post_type;
		update_option('wfap_app_id',$wfab_app_id);
		update_option('wfap_sec_id',$wfab_sec_id);
		update_option('wfap_page_id',$wfab_page_id);
		//update_option('wfap_user_tkn',$wfab_user_tkn);
		update_option('wfap_post_type',$wfab_post_type);
		update_option('wfap_default_img',$wfab_default_img);
		update_option('wfab_default_img_link',$wfab_default_img_link);

		echo " Settings Saved Successfully";
		die();
}

function wfap_authenticate(){

		Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
		Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYHOST] = 2;

		$id =  $_REQUEST['app_id'];
		$sec = $_REQUEST['sec_id'];

		// 512993528731249
		// d9ab9699d107a5c15971bb14e1c684f6
		$fb_config = array(
				'appId'  => $id,
				'secret' => $sec,
				'cookie' => true
		);
		$fb = new Facebook($fb_config);
		$access_token = $fb->getAccessToken();
		$user = $fb->getUser();
		//$adminurl = admin_url( 'admin.php?page=wp-facebook-auto-publish/wp-fap-gui.php'); 
		$adminurl =  get_site_url();
		if($user == 0)
		{
		   $params = array( 'scope' => 'publish_actions, email, read_stream, user_interests, user_likes, user_location, user_status, friends_likes',
							'redirect_uri'=>$adminurl);
		   
			echo $fbook_login_url = $fb->getLoginUrl($params);
			$access_token = $fb->getAccessToken();
			$fb->setAccessToken($access_token);
			///echo $id." - ".$sec;
			die();
		}
		else
		{
			update_option('wfap_app_id',$id);
			update_option('wfap_sec_id',$sec);
			echo "done";

		}
}

function wfap_post_on_facebook($post_id){
		$hook =  current_filter();
		$post_status = get_post_status( $post_id );
		if( $post_status == 'inherit' || $post_status == 'publish')
		{	//echo "checkpoint 1 <br/>";
			Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
			Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYHOST] = 2;
			
			$id = get_option('wfap_app_id');
			$sec = get_option('wfap_sec_id');
			$page = get_option('wfap_page_id');
			$post_types = get_option('wfap_post_type');
			$user_tkn = get_option('wfap_user_tkn');
			$default_img = get_option('wfap_default_img');
			$default_img_link = get_option('wfab_default_img_link');
			if(empty($page))
				$page ='me';
			// check if the authentication is done and the user token is set.
			if(!empty($user_tkn))
			{	//echo "checkpoint 2 <br/>";
				// check if the published post is of the category which is set by user
				$check = wfap_check_post_type( $post_id, $post_types, $hook );
					if( $check )
					{	
						//echo "going to fb  <br/>"; 
						// 512993528731249
						// d9ab9699d107a5c15971bb14e1c684f6
						$fb_config = array(
								'appId'  => $id,
								'secret' => $sec,
								'cookie' => true
						);
						$fb = new Facebook($fb_config);
						$access_token = $fb->getAccessToken();
						$user = $fb->getUser();
				
						$access_token = $fb->getAccessToken();
						$fb->setAccessToken($access_token);
							$title = get_the_title($post_id);
							$post = get_post( $post_id );
							$decription = $post->post_content;
							$decription = trim($decription);
							$caption = '';
							
							$params = array(
									"access_token"=> $user_tkn,
									"message" => $title,
									"name" => $title,
									"caption" => $caption,
									"description" => $decription
							);

							$img_url = get_post_thumbnail_id( $post_id );
						if($default_img == 1)
						{
							if(!empty( $img_url ))
							{
								$picture = wp_get_attachment_image_src($img_url, 'single-post-thumbnail');
								$img_url = $picture[0];
							}
							else
							{
								if(!empty($default_img_link))
									$img_url = $default_img_link;
								else
									$img_url = site_url().'/wp-content/plugins/wp-facebook-auto-publish/images/default.png';
							}
							$params["picture"] = $img_url;
						}
						else
						{
							if(!empty( $img_url ))
							{
								$picture = wp_get_attachment_image_src($img_url, 'single-post-thumbnail');
								$img_url = $picture[0];
								$params["picture"] = $img_url;
							}
							else
							{
								$params["message"] = $decription;
							}
						}
				
						$ret_obj = $fb->api('/'.$page.'/feed', 'POST', $params);
						//echo "gone FB <br/> "; 
					}
			}
		}
//exit;
}

function wfap_check_post_type( $post_id, $post_types, $hook ){

		$exist = false;
		$post_type_list = array();
		$post_type_list  = explode(",",$post_types);
		if( isset( $post_id ) && !empty( $post_id ))
		{	
			$post_type = $_POST['post_type'];
			if( in_array( $post_type ,$post_type_list ) )	
			{
				// for default page and post of wordpress
				if( $post_type == 'post' && $hook== 'publish_post' )
				{	$exist = true;		}
				else if( $post_type =='page' )
				{	$exist = true;		}
				// for custom post and their taxonomies
				else if( $post_type != 'post' &&  $post_type != 'page' && $hook == 'save_post')
				{
					$exist = true;
				}
			}
		}
		return $exist;
}
?>