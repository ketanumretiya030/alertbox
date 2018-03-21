<?php
/*
Plugin Name: Alert Box
Text Domain: alert-on-post-and-pages-selected
Domain Path: /languages
Plugin URI: http://wordpresshtml.com/
Description: Frontend Alert selected post or page from having a option to  select page or post and add custom alert view in front when selected page or post load.
Version: 1
Author: Ketan umretiya 
Author URI: https://profiles.wordpress.org/ketanumretiya030
*/  
// add sub menu in admin settings
function alb_options_page()
{
    add_submenu_page( 'options-general.php', 'Alert Box','Alert Box', 'manage_options', 'alert_option', 'alt_option_page_html' );
}
add_action('admin_menu', 'alb_options_page');

//admin page code
function alt_option_page_html()
{
?>
    <h2>Select alert Post type </h2>
    <form name="alerform" id="alertboxform" method="post" action="options-general.php?page=alert_option">
		<?php
          $core_function = new AlertPosttypeSelect();
          $core_function->alt_list_posttype();
         ?>
        <h3>Alert Content </h3><textarea name="alert_text" id="alert_text"    class="large-text code"><?php echo get_option('alert_text');?></textarea>
        <p class="submit"><input name="submit" id="submit" class="button button-primary" value="Save Changes" type="submit"></p>
    </form>
 <?php
}

//hook for call on plugin activation time to set default meta value for custom post type
register_activation_hook( __FILE__, 'alt_plugin_activate' );
function alt_plugin_activate()
{
   $alt_types = get_post_types();
   $alt_post_types = get_post_types($args,'name','or');
   $posttypes_array = array();
   $notinarray = array ('attachment','revision','nav_menu_item','custom_css','customize_changeset');
   foreach ($alt_post_types  as $post_type_alt ) 
	{
		if(!in_array( $post_type_alt->name,$notinarray))
		{
		   if(!get_option($post_type_alt->name.'_metaalert'))
		   {
				update_option($post_type_alt->name.'_metaalert', 'empty');
		   }
		}		
	}
}

//ADD Script into admin side
function alt_admin_js()  //enque script
  {   
		wp_enqueue_script( 'admin_alert_js', plugin_dir_url( __FILE__ ) . 'js/admin_js.js' );
  }
add_action('admin_enqueue_scripts', 'alt_admin_js');

//hook to add code in wp head and chek if post is selected or not
add_action('wp_head', 'alt_checkposttype_alert_front');
function alt_checkposttype_alert_front()
  {
	$post_type_alt = get_post_type( get_the_id() );
	$meta_value = get_option($post_type_alt.'_metaalert');
	if(count($meta_value)==1)
	{
		//echo '<pre>';
		//var_dump($meta_value);
	  //$meta_value[]='';	
	}
	if(!empty($meta_value))
	{
		//echo count($meta_value);
		if($meta_value != 'empty')
		{
			
			if(in_array(get_the_id(),$meta_value))
			{ 						
				 wp_register_script( 'alert_messages_js', plugin_dir_url( __FILE__ ) . 'js/alt_myscript.js' );

					// Localize the script with new data
					$translation_array = array(
						'alt_val_msg' => __( get_option('alert_text'), 'alertbox' ),
						'a_value' => 'first'
					);
					wp_localize_script( 'alert_messages_js', 'alt_object_js', $translation_array );
					
					// Enqueued script with localized data.
					wp_enqueue_script( 'alert_messages_js' );
				
			}
		}
	}
  }
 
//core clas for chek post type and display list and save data
class AlertPosttypeSelect
 {
  function alt_list_posttype()
	{
	   $alt_types = get_post_types();
	   $alt_post_types = get_post_types($args,'name','or');

		$posttypes_array = array();
		$notinarray = array ('attachment','revision','nav_menu_item','custom_css','customize_changeset','wpcf7_contact_form','acf-field-group','acf-field','mc4wp-form');
		foreach ($alt_post_types  as $post_type_alt ) 
		{
			
			if(isset($_POST[$post_type_alt->name]))
			  {
				  if($_POST[$post_type_alt->name] == 'on')
				  {
				   $this->alt_save_alert_data($post_type_alt->name);
				  } 
				  
			  }else
			  {
				  if(isset($_POST['submit']))
				  {
				  if($_POST[$post_type_alt->name] != 'on')
				  {
					  $this->alt_remove_all_field($post_type_alt->name);
				  }
				  }
			  }			
		   if(!in_array($post_type_alt->name,$notinarray))
		   {
			  ?>
			 <div id="<?php echo $post_type_alt->name; ?>">
			   <input  <?php if(get_option($post_type_alt->name.'_metaalert') != 'empty'  || $_POST[$post_type_alt->name]=='on'){ ?> checked="checked" <?php } ?>  type="checkbox" class="chekalert"  id="<?php echo $post_type_alt->name; ?>" name="<?php echo $post_type_alt->name; ?>" /><?php echo $post_type_alt->label; ?>
			    <div <?php if(get_option($post_type_alt->name.'_metaalert') == 'empty'){?> style="display:none;"  <?php }?> class="<?php echo $post_type_alt->name; ?>">
				 <?php
				   $this->alt_get_postlist_alert($post_type_alt->name);
				 ?>
				 </div>
			   </div>
			 <?php
		   }
		}
	} 
   function alt_get_postlist_alert($posttype)
	{
	    $args = array('posts_per_page' => -1,'post_type' => $posttype,'post_status' => array('publish', 'future', 'private'));
		query_posts($args);
		//if( $_POST[$posttype]=='on'  || get_option($posttype.'_metaalert')!='empty')
		  {
		    echo '<select   multiple="multiple"  name="'.$posttype.'_option[]">';
		     while ( have_posts() ) : the_post();
				if(in_array(get_the_id(),get_option($posttype.'_metaalert')))
				{
				  echo '<option selected value="'.get_the_id().'">'.get_the_title().'</option>';
				}else
				{
				  echo '<option value="'.get_the_id().'">'.get_the_title().'</option>';	
				}
		     endwhile;
		   echo ' </select> ';
		  }	
	}  
   function alt_save_alert_data($posttype_alt)
	{
	        $alt_updatemsg = sanitize_text_field($_POST['alert_text']); 
			$alt_datavalue =  $_POST[$posttype_alt.'_option'];
			
			  $postp_altid = sanitize_option($posttype_alt.'_metaalert',$alt_datavalue); 
			
		//	print_r($postp_altid );
			
			update_option($posttype_alt.'_metaalert',$postp_altid);
			update_option('alert_text', $alt_updatemsg);
	}	
   function alt_remove_all_field($posttype_alt)
	{
	        $alt_updatemsg = sanitize_text_field($_POST['alert_text']); 
		    update_option($posttype_alt.'_metaalert', 'empty');
			update_option('alert_text', $alt_updatemsg);
		 
	} 
	
	 	
 }
 