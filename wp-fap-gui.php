<?php
 $url = plugin_dir_url(__FILE__);
 $post_types = get_post_types();

$id = get_option('wfap_app_id');
$sec = get_option('wfap_sec_id');
$page = get_option('wfap_page_id');
//$user_tkn = get_option('wfap_user_tkn');
$default_img = get_option('wfap_default_img');
if($default_img == 0)
	echo '<style type="text/css">#default_img_link {display:none;}</style>';
$default_img_link = get_option('wfab_default_img_link');
$post_list = array();
$post = get_option('wfap_post_type');
$post_list  = explode(",", $post );
?>
<script type="text/javascript">
	var url = '"<?php echo $url; ?>';
	var admin_url = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>
<link href="<?php echo $url; ?>css/fbstyle.css" type="text/css" rel="stylesheet" />
<script src="<?php echo $url; ?>js/fbscript.js" type="text/javascript"></script>
<div id="wrapper">
<span id="status_msg" class="">Setting saved</span>
	<div class="container">
		<form action="" method="post">
	        <div class="fb_form">
	           <h2>WP Facebook Auto Publish</h2>
                <table>
                    <tr>
						<td>Facebook App ID<span class="create_app">To create facebook app click <a href="https://developers.facebook.com/apps/">here</a></span></td>                    
                        <td><input type="text" name="fb_app_id" id="fb_app_id" value="<?php echo $id; ?>" /> </td>
                    </tr>
                    <tr>
						<td>Facebook App Secret</td>                    
                        <td><input type="text" name="fb_app_sec_id" id="fb_app_sec_id" value="<?php echo $sec; ?>" /> 
                        <input type="button" id="wfap_authenticate" value=" Authenticate"  class="button-primary" /><span id="auth_gif"><img src="<?php echo $url;?>images/loading_gif.gif" /> Authenticating... </span> 
                        </td>
                    </tr>
					<tr>
						<td>Page or Profile</td>                    
                        <td><input type="text" name="fb_page_id" id="fb_page_id" value="<?php echo $page; ?>" /> 
                        	<span class="note">Note: You can use "me" or leave blank for your own profile ID</span>
                        </td>
                    </tr>
                    <tr>
						<td>Post Type</td>                    
                        <td>
                        	<div class="list_custom">
                            <?php foreach($post_types as $pt){ 

								if($pt != "attachment" && $pt != "revision" && $pt != "nav_menu_item" ) {?>                            	
                                <span><input type="checkbox" name="post_ty[]" value="<?php echo $pt; ?>" <?php if (in_array($pt, $post_list)) echo ' checked="checked"'; ?> /><?php echo $pt; ?></span>
                            <?php } } ?>
                            </div>
                        </td>
                    </tr>
				 <tr>
						<td>Show Default Image If no Image available</td>                    
                        <td>
	                        <div>
                                <span><input type="checkbox" name="default_img" id="default_img" value="1"  <?php if ($default_img == 1) echo ' checked="checked"'; ?> /></span>
                                <span><input type="text" name="default_img_link" id="default_img_link" value="<?php if (empty($default_img_link)) echo 'Add the link of the default image'; else echo $default_img_link;?>" onblur="add_label('Add the link of the default image')"  /></span>
                           </div>
                        </td>
                    </tr>
               </table>
                <input type="submit" value="Save Changes" id="save_btn" class="button-primary" /><span id="load_gif"><img src="<?php echo $url;?>images/loading_gif.gif" /> Saving Settings ... </span>
            </div>
        </form>
    </div>
    
    <div class="sidebar">
    
			<h3 class="hndle"><span>Important Note</span></h3>
			<div class="inside">
				<p>According to the new security policy of the Facebook, the App needs to be submitted and verified by Facebook team.</p>
				<p>Please follow the bellow instructions to submit an app.</p>
				<ol>
					<li>A logo must be provided to the App. In order to add a logo, go your Facebook <strong>App->App Details->Icons</strong>.</li>
					<li>Go to your Facebook <strong>App->Status & Review->Start a Submission</strong>. Give the required "<strong>LOGIN PERMISSIONS</strong>" to the app to be used and accessed by your domain and by our plugin.
						<p>Your app must have below permissions to use auto post feature</p>
						<ul>
							<li>manage_pages</li>
							<li>publish_actions</li>
						</ul>
						<p>You also need to explain the use of the above permissions on Facebook.</p>
					</li>
					<li>You must provide a long description of your app. Go to you Facebook <strong>App->App Details->Long Description</strong>. This description must explain a valid reason or purpose to use this app for your domain.
					<br/><br/> Later all the above settings will be reviewed by the Facebook team with in next 7 business days.</li>
				</ol>
							
			</div>
			
    </div>
</div>
