<?php
/*
  Plugin Name: WP Widget As Link & Widget Background & CSS
  Description: Add link to Wordpress widget. Support Widget Background and user widget CSS Classes!
	Version: 1.5.5
  Author: Alex Egorov
	Author URI: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SLHFMF373Z9GG&source=url
	Plugin URI: https://wordpress.org/plugins/wp-widget-as-link/
  GitHub Plugin URI:
  License: GPLv2 or later (license.txt)
  Text Domain: wal
  Domain Path: /languages
*/
define("WAL_PATH", dirname(__FILE__));

if (!defined('WAL_URL'))
	define('WAL_URL', untrailingslashit(plugins_url('', __FILE__)));

define('WAL_IMAGE', WAL_URL."/images/placeholder.png");


function wal_admin_theme_style() {
    wp_enqueue_style('wal-admin-theme', WAL_URL.'/includes/wp-admin.css');
}

function yummi_wl_plugin_action_links($links, $file) {
    static $this_plugin;
    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    if ($file == $this_plugin) { // check to make sure we are on the correct plugin
			$settings_link = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SLHFMF373Z9GG&source=url" target="_blank">❤ ' . __('Donate', 'wal') . '</a>';
        array_unshift($links, $settings_link); // add the link to the list
    }
    return $links;
}
add_filter('plugin_action_links', 'yummi_wl_plugin_action_links', 10, 2);

class WAL {

  public function __construct() {
    load_plugin_textdomain( 'wpi-widget-as-link', false, basename( dirname( __FILE__ ) ) . '/languages' );

    add_action( 'in_widget_form', array( $this, 'add_link_field_to_widget_form' ), 1, 3 );

    add_action('admin_enqueue_scripts', 'wal_admin_theme_style');
    add_action('login_enqueue_scripts', 'wal_admin_theme_style');

    add_filter( 'widget_form_callback', array( $this, 'register_widget_link_field'), 10, 2 );
    add_filter( 'widget_update_callback', array( $this, 'widget_update_extend'), 10, 2 );
    add_filter( 'dynamic_sidebar_params', array( $this, 'add_link_to_widget'), 99, 2 );

		//add_action('wp_enqueue_scripts','yhead',100);

  	if( is_admin() ) {
  		add_action( 'admin_menu', array(&$this, 'admin_menu') );
			//add_filter( 'dynamic_sidebar_params', array( $this, 'wal_widget_styling'), 20 );
  	}
  }

	function yhead(){
		// wp_dequeue_style('yummi-hint-css');
		// wp_deregister_style('yummi-hint-css');
		// wp_enqueue_style( 'widget-hint', plugins_url('/css/hint.css', __FILE__) );
	}

	function wal_widget_styling( array $params ) {
		global $wal_options;
		//print_r($wal_color);

		/*
		Array ( [0] => Array (
			[name] => Главная страница
			[id] => sidebar-home
			[description] =>
			[class] =>
			[before_widget] =>
			[after_widget] =>
			[before_title] => %BEG_OF_TITLE%
			[after_title] => %END_OF_TITLE%
			[widget_id] => execphp-3
			[widget_name] => PHP-код
		)
		[1] => Array ( [number] => 3 ) )
		*/

    $widget =& $params[0];
		//$hide = $wal_options['hide'] ? 'dashicons-hidden' : 'dashicons-visibility';
		$widget['before_widget'] = $widget['before_widget'].$wal_options['id'].'<span class="wal dashicons dashicons-visibility"></span>';
		//$widget['before_widget'] = //dashicons dashicons-hidden
		$widget['widget_name'] = $widget['widget_name'] .' | id:'. $widget['widget_id'];

		echo $wal_options ? '<style>[id$="'.$wal_options['id'].'"] .widget-top{background:'.$wal_options['hex'].';}</style>' : null;
		//print_r($params);

    return $params;
	}

  public function admin_menu() {
    /*add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );*/
    if( empty( $GLOBALS['admin_page_hooks']['yummi']) )
      $main_page = add_menu_page( 'yummi', 'Yummi '.__('Plugins'), 'manage_options', 'yummi', array($this, 'yummi_plugins_wl'), WAL_URL.'/includes/img/dashicons-yummi.png' );
  }

	function yummi_plugins_wl() {
		if(!is_admin() || !current_user_can("manage_options"))
		  die( 'yummi-oops' );
		if(!function_exists('yummi_plugins'))
		  include_once( WAL_PATH . '/includes/yummi-plugins.php' );
	  }

  /**
   * Add Link field to widget form
   *
   * @since 1.0
   * @uses add_action() 'in_widget_form'
   */
  public function add_link_field_to_widget_form( $widget, $args, $instance ) {
    if (get_bloginfo('version') >= 3.5){
  		wp_enqueue_media();
			wp_enqueue_script('wp-color-picker');
			wp_enqueue_style('wp-color-picker');
  	} else {
  		wp_enqueue_style('thickbox');
  		wp_enqueue_script('thickbox');
  	}
		// global $wal_options;
		// $wal_options['id'] = $widget->id;
		// $wal_options['hide'] = $instance['widget_hide'];
		// $wal_options['hex'] = $instance['wal_color'];
		//prr( $widget );
		//prr($instance);
		?>
    <fieldset class="title-link-options">
      <div class="wal_div wal_div_link">
        <div class="wal_fleft wal_half">
					<input type="checkbox" id="<?php echo $widget->get_field_id('widget_hide_title'); ?>" name="<?php echo $widget->get_field_name('widget_hide_title'); ?>"<?php checked( $instance['widget_hide_title'] ); ?> />
					<label for="<?php echo $widget->get_field_id('widget_hide_title'); ?>"><?php _e('Hide Title', 'wal'); ?></label>
        </div>
        <div class="wal_fright wal_half">
					<input type="checkbox" class="checkbox" id="<?php echo $widget->get_field_id('widget_link_target_blank'); ?>" name="<?php echo $widget->get_field_name('widget_link_target_blank'); ?>"<?php checked( $instance['widget_link_target_blank'] ); ?> />
					<label for="<?php echo $widget->get_field_id('widget_link_target_blank'); ?>"><?php _e( 'Open link in new window/tab', 'wal' ); ?></label>
        </div>
				<!--<label for="<.?php echo $widget->get_field_id('widget_link'); ?>"><.?php _e('Link: <small class="description">(Example: http://google.com)</small>', 'wal'); ?></label>-->
        <input type="text" name="<?php echo $widget->get_field_name('widget_link'); ?>" id="<?php echo $widget->get_field_id('widget_link'); ?>" class="wal_widget_link" value="<?php echo $instance['widget_link']; ?>" placeholder="<?php _e( 'Link URL', 'wal' ); ?>" />
        <span class="dashicons dashicons-admin-links wal_fleft"></span>
      </div>

      <div class="wal_div wal_div_bg">
        <img class="taxonomy-image-<?php echo $widget->id ?> wlis" src="<?php echo $instance['widget_link_bg']; ?>"/><br/>

        <input type="text" name="<?php echo $widget->get_field_name('widget_link_bg'); ?>" id="<?php echo $widget->get_field_id('widget_link_bg'); ?>" class="widget_link_bg" value="<?php echo $instance['widget_link_bg']; ?>" placeholder="<?php _e('Background image URL','wal'); ?>" /><span class="dashicons dashicons-admin-media wal_fleft"></span>
        <!-- <input type="text" name="taxonomy_image" id="taxonomy_image" value="'.$image_url.'" /><br /> -->
        <button class="wal_remove_image_btn<?php echo $widget->option_name.$widget->number ?> button wal_fright"><?php _e('Remove', 'wal'); ?></button>
        <button class="wal_upload_image_btn<?php echo $widget->option_name.$widget->number ?> button wal_fright" style=""><span class="dashicons dashicons-admin-media" style="color:#82878c"></span> &ensp;<?php _e('Upload/Add image', 'wal'); ?></button>

        <script type="text/javascript">
				var a=0,i=0;
				 jQuery(document).on('widget-added', function(event, widget){
					 if(a == 0){
						 var widget_id = jQuery(widget).attr('id');
						 jQuery('[id$="'+widget_id+'"]').prepend("<i class='clone-me wal-clone-action wal dashicons dashicons-admin-page' title='<?php _e('Clone', 'wal')?>'></i><i class='wal dashicons dashicons-visibility' title='<?php _e('Show/Hide', 'wal') ?>'></i>");

						 jQuery('[id$="'+widget_id+'"] .wal.dashicons:not(.clone-me)').one('click',function(){
 							if( jQuery(this).is('.dashicons-visibility') ) {
 								// console.log('hidden');
 								jQuery(this).removeClass('dashicons-visibility').addClass('dashicons-hidden');
 								jQuery('input#'+widget_id+'').prop( "checked", true );
 						  } else {
 								// console.log('visibility');
 								jQuery(this).removeClass('dashicons-hidden').addClass('dashicons-visibility');
 								jQuery('input#'+widget_id+'').prop( "checked", false );
 						  }
 							jQuery('[id$="'+widget_id+'"] input[type=submit]').click();
 							// console.log('--- click: '+i+' | id: '+widget_id+' ---');
 						});
					 }
					 a++;
					 // console.log('-');
				 });
					jQuery(document).on('widget-updated', function(event, widget){
						var widget_id = jQuery(widget).attr('id');
					  jQuery('input.wal_color').wpColorPicker();
					});
          jQuery(document).ready(function($) {
            if( document.getElementById("<?php echo $widget->get_field_id('widget_link_bg'); ?>").value == '' )
              document.getElementById('bg-<?php echo $widget->id; ?>').style.display = 'none';
            else
              document.getElementById('bg-<?php echo $widget->id; ?>').style.display = 'block';

						//$('[id$="<?=$widget->id?>"] .widget-title-action').prepend('<i class="dashicons dashicons-visibility"></i>');

						<?php
							//echo $instance["widget_hide"] == 1 ? 'alert("'.$widget->id.'");' : '';
							$hide = $instance["widget_hide"] == 1 ? "<i class='wal dashicons dashicons-hidden' title=\'".__('Show/Hide','wal')."\'></i>" : "<i class='wal dashicons dashicons-visibility' title='".__('Show/Hide','wal')."'></i>";
							echo '
								if($(\'.widgets-sortables [id$="'.$widget->id.'"]:not(.wal_center) .wal.dashicons\').length == 0)
									$(\'.widgets-sortables [id$="'.$widget->id.'"]:not(.wal_center)\').prepend("<i class=\'clone-me wal-clone-action wal dashicons dashicons-admin-page\' title=\''.__('Clone','wal').'\'></i>'.$hide.'");
							';
						?>
						//console.log('<.?='id:'.$widget->id.' widget_hide:'.$instance["widget_hide"]?>');

						<?php if( !empty($instance['wal_color']) ) echo '$(\'[id$="'.$widget->id.'"] .widget-top\').css("border-left","4px solid '.$instance['wal_color'].'");' ?>

						$('[id$="<?php echo $widget->id; ?>"] .wal.dashicons:not(.clone-me)').one('click',function(){ i++;
							if( $(this).is('.dashicons-visibility') ) {
								// console.log('hidden');
								$(this).removeClass('dashicons-visibility').addClass('dashicons-hidden');
								$('input#<?php echo $widget->get_field_id('widget_hide') ?>').prop( "checked", true );
						  } else {
								// console.log('visibility');
								$(this).removeClass('dashicons-hidden').addClass('dashicons-visibility');
								$('input#<?php echo $widget->get_field_id('widget_hide') ?>').prop( "checked", false );
						  }
							$('[id$="<?php echo $widget->id ?>"] input[type=submit]').click();
							//alert('<.?php echo $widget->id; ?>');
							// console.log('--- click: '+i+' | id: <?php echo $widget->id; ?> ---');
						});

						// http://wp-kama.ru/id_4621/vyibora-tsveta-iris-color-picker-v-wordpress.html
						var walOptions = {
							defaultColor: false,
							change: function(event, ui){
								var element = event.target;
				        var color = ui.color.toString();
								setTimeout(function(){
									jQuery( element ).trigger('change'); // enable widget "Save" button
								},1);
							},
							clear: function(){
								var element = jQuery(event.target).siblings('.wp-color-picker')[0];
				        var color = '';
								jQuery( event.target ).trigger('change'); // enable widget "Save" button
							},
							hide: true,
							palettes: true
						};
						$('input#<?php echo $widget->get_field_id('wal_color'); ?>').wpColorPicker(walOptions);

            $("#<?php echo $widget->get_field_id('widget_link_bg'); ?>").keyup(function(event) {
              var bg = $(this).parent().parent().children('#bg-<?php echo $widget->id ?>')[0];
							var img = $(this).parent().children();
							img.attr("src", $(this).val());
							if(bg){
	              if( $(this).val() === '' )
	                bg.style.display = 'none';
	              else
	                bg.style.display = 'block';
								}
            });

            var wordpress_ver = "<?php echo get_bloginfo("version"); ?>", upload_btn;
						var frame;
            $(".wal_upload_image_btn<?php echo $widget->option_name.$widget->number; ?>").on('click',function(event) {
							 event.preventDefault();
              upload_btn = $(this);
              if (wordpress_ver >= "3.5") {
                if (frame) {
                  frame.open();
                  return;
                }
                frame = wp.media({
						      title: '<?php _e('Select or Upload Media for chosen widget', 'wal'); ?>',
						      // button: {text: 'Use this media'},
						      multiple: false  // Set to true to allow multiple files to be selected
						    });
                frame.on('select', function() {
									var bg = upload_btn.parent().children('#bg-<?php echo $widget->id ?>')[0];
									var img = upload_btn.parent().children();
									var input = upload_btn.parent().children().next();
                  // Grab the selected attachment.
                  var attachment = frame.state().get("selection").first().toJSON();
									// Send the attachment URL to our custom image input field.
                  //frame.close();
                  if (img.hasClass("wlis")) {
                    img.attr("src", attachment.url);
                    input.val(attachment.url);
										input.change();
										if(bg){
	                    if( input.value !== '' && bg.style.display == 'block' )
	                      bg.style.display = 'none';
	                    else
	                      bg.style.display = 'block';
											}
											$('#bg-<?php echo $widget->id ?>').css('display','block');
                  }else{
                    $("#<?php echo $widget->get_field_id('widget_link_bg'); ?>").val(attachment.url);
									}
                });
                frame.open();
              } else {
                tb_show("", "media-upload.php?type=image&amp;TB_iframe=true");
                return false;
              }
            });

            $(".wal_remove_image_btn<?php echo $widget->option_name.$widget->number; ?>").click(function() {
              $(".taxonomy-image-<?php echo $widget->id ?>").attr("src", ""); //<.?php echo WAL_IMAGE ?>
              $("#<?php echo $widget->get_field_id('widget_link_bg'); ?>").val("");
							$("#<?php echo $widget->get_field_id('widget_link_bg'); ?>").change();
              $('#bg-<?php echo $widget->id ?>').css('display','none');
              //$(this).parent().siblings(".title").children("img").attr("src","<?php echo WAL_IMAGE ?>");
              //$(".inline-edit-col :input[name='<.?php echo $widget->get_field_name('widget_link_bg'); ?>']").val("");
              return false;
            });

            if( wordpress_ver < "3.5" ) {
              window.send_to_editor = function(html) {
                imgurl = $("img",html).attr("src");
                var bg = upload_btn.parent().children('#bg-<?php echo $widget->id ?>')[0];
                var img = upload_btn.parent().children();
                var input = upload_btn.parent().children().next();

                if( img.hasClass("wlis") ) {
                  img.attr("src", imgurl);
                  input.val(imgurl);
									input.change();
									if(bg){
	                  if( input.value !== '' && bg.style.display == 'block' )
	                    bg.style.display = 'none';
	                  else
	                    bg.style.display = 'block';
										}
                }else{
                  $("#<?php echo $widget->get_field_id('widget_link_bg'); ?>").val(imgurl);
								}
                tb_remove();
              }
            }

            <?php /*$(".editinline").click(function() {
                var tax_id = $(this).parents("p").attr("id").substr(4);
                var thumb = $("#tag-"+tax_id+" .thumb img").attr("src");

              if (thumb != "<.?php echo WAL_IMAGE ?>") {
                $(".inline-edit-col :input[name='<.?php echo $widget->get_field_name('widget_link_bg'); ?>']").val(thumb);
              } else {
                $(".inline-edit-col :input[name='<.?php echo $widget->get_field_name('widget_link_bg'); ?>']").val("");
              }

              $(".inline-edit-col .title img").attr("src",thumb);
            });*/ ?>
          });
        </script>
      </div>

      <div class="wal_div wal_div_css wal_cboth">
        <input type="text" name="<?php echo $widget->get_field_name('widget_link_css'); ?>" id="<?php echo $widget->get_field_id('widget_link_css'); ?>" class="wal_widget_link_css" value="<?php echo $instance['widget_link_css']; ?>" placeholder="<?php _e('CSS classes separated by space', 'wal'); ?>" /><b class="wal_fleft">CSS</b>
      </div>

      <div id="bg-<?php echo $widget->id; ?>" class="wal_center wal_cboth">
        <small class="description"><?php _e('If not filled Title and Content', 'wal') ?></small><br/>
        <input type="number" name="<?php echo $widget->get_field_name('widget_link_w'); ?>" id="<?php echo $widget->get_field_id('widget_link_w'); ?>" class="wal_half wal_fleft" value="<?php echo $instance['widget_link_w']; ?>" placeholder="Widget width in px" />
        <input type="number" name="<?php echo $widget->get_field_name('widget_link_h'); ?>" id="<?php echo $widget->get_field_id('widget_link_h'); ?>" class="wal_half wal_fright" value="<?php echo $instance['widget_link_h']; ?>" placeholder="Widget height in px"/>
      </div><br/>

			<div class="wal_div wal_div_css wal_cboth" style="position:relative">
				<style>.wp-picker-holder{position:absolute;z-index:9999}.wp-picker-container.wp-picker-active{position:relative}</style>
				<span style="position:absolute;top:14px;right:0;">-> <?php _e('Auxiliary color mark', 'wal'); ?></span>
        <input type="text" name="<?php echo $widget->get_field_name('wal_color'); ?>" id="<?php echo $widget->get_field_id('wal_color'); ?>" class="wal_color" value="<?php echo $instance['wal_color']; ?>" />
      </div>

			<input type="checkbox" style="display:none" id="<?php echo $widget->get_field_id('widget_hide'); ?>" name="<?php echo $widget->get_field_name('widget_hide'); ?>"<?php checked( $instance['widget_hide'] ); ?> />
      <?php /*<p>
        <input type="checkbox" class="checkbox" id="<?php //echo $widget->get_field_id('widget_link_wrap'); ?>" name="<?php //echo $widget->get_field_name('widget_link_wrap'); ?>"<?php //checked( $instance['widget_link_wrap'] ); ?> />
        <label for="<?php //echo $widget->get_field_id('widget_link_wrap'); ?>"><?php //_e( 'Make the entire title bar clickable', 'wpi-widget-as-link' ); ?></label>
      </p> */?>
    </fieldset>
  <?php
  }

  /**
   * Register the additional widget field
   *
   * @since 1.0
   * @uses add_filter() 'widget_form_callback'
   */
  public function register_widget_link_field ( $instance, $widget ) {
    if ( !isset($instance['widget_link']) )								$instance['widget_link'] = null;
    if ( !isset($instance['widget_link_bg']) )						$instance['widget_link_bg'] = null;
    if ( !isset($instance['widget_link_w']) )							$instance['widget_link_w'] = null;
    if ( !isset($instance['widget_link_h']) )							$instance['widget_link_h'] = null;
    if ( !isset($instance['widget_link_target_blank']) )	$instance['widget_link_target_blank'] = null;
    if ( !isset($instance['widget_link_wrap']) )					$instance['widget_link_wrap'] = null;
    if ( !isset($instance['widget_link_css']) )						$instance['widget_link_css'] = null;
		if ( !isset($instance['widget_hide_title']) )					$instance['widget_hide_title'] = null;
		if ( !isset($instance['widget_hide']) )								$instance['widget_hide'] = null;
		if ( !isset($instance['wal_color']) )									$instance['wal_color'] = null;
    return $instance;
  }

  /**
   * Add the additional field to widget update callback
   *
   * @since 1.0
   * @uses add_filter() 'widget_update_callback'
   */
  public function widget_update_extend ( $instance, $new_instance ) {
    $instance['widget_link']							= esc_url( $new_instance['widget_link'] );
    $instance['widget_link_bg']						= esc_url( $new_instance['widget_link_bg'] );
    $instance['widget_link_w']						= $new_instance['widget_link_w'];
    $instance['widget_link_h']						= $new_instance['widget_link_h'];
    $instance['widget_link_target_blank'] = !empty($new_instance['widget_link_target_blank']) ? 1 : 0;
    $instance['widget_link_wrap']					= !empty($new_instance['widget_link_wrap']) ? 1 : 0;
    $instance['widget_link_css']					= $new_instance['widget_link_css'];
		$instance['widget_hide_title']				= !empty($new_instance['widget_hide_title']) ? 1 : 0;
		$instance['widget_hide']							= !empty($new_instance['widget_hide']) ? 1 : 0;
		$instance['wal_color']								= !empty($new_instance['wal_color']) ? $new_instance['wal_color'] : 'transparent';
    return $instance;
  }

  /**
   * Add link to widget on output
   *
   * Title link should be set by editors
   * in widget settings in Appearance->Widgets
   *
   * @since 1.o
   * @uses add_filter() 'dynamic_sidebar_params'
   */
  public function add_link_to_widget( $params ) {
    if (is_admin())
      return $params;

    global $wp_registered_widgets;
    $id = $params[0]['widget_id'];

		//prr($wp_registered_widgets[$id]['callback']);

    if (isset($wp_registered_widgets[$id]['callback'][0]) && is_object($wp_registered_widgets[$id]['callback'][0])) {
      // Get settings for all widgets of this type
      $settings = $wp_registered_widgets[$id]['callback'][0]->get_settings();

      // Get settings for this instance of the widget
      $instance = $settings[substr( $id, strrpos( $id, '-' ) + 1 )];

			if( isset($instance['widget_hide']) && $instance['widget_hide'] == 1 ) /* hide widget if $instance['widget_hide'] checked */
				$wp_registered_widgets[$id]['callback'][1] = '';

      // Allow overriding the title link programmatically via filters
      $bg		= isset($instance['widget_link_bg']) ? $instance['widget_link_bg'] : null;
      $link = isset($instance['widget_link']) ? $instance['widget_link'] : null;
      $link = apply_filters('widget_widget_link', $link, $instance);
      $css	= !empty($instance['widget_link_css']) ? $instance['widget_link_css'] : null;

			//apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Pages' ) : $instance['title'], $instance, $this->id_base );

			if( isset($instance['widget_hide_title']) && $instance['widget_hide_title'] == 1) {
				$params[0]['before_title'] = '<span style="display:none">';
				$params[0]['after_title'] = '</span>';
			}

      if( $link && $bg ){

        $bg = !empty($instance['widget_link_bg']) ? 'display: block; background: url('.$instance['widget_link_bg'].') repeat left top; -webkit-background-size: cover; -moz-background-size: cover; background-size: cover; ' : null;
        $w = !empty($instance['widget_link_w']) ? 'display: block; width:'.$instance['widget_link_w'].'px; ' : null;
        $h = !empty($instance['widget_link_h']) ? 'height:'.$instance['widget_link_h'].'px;' : null;

        $target = $instance['widget_link_target_blank'] ? ' target="_blank"' : '';

        $params[0]['before_widget'] = $params[0]['before_widget'] . '<a href="' . $link . '"' . $target . ' class="widget-as-link '.$css.'" style="'.$bg.$w.$h.'">';
        $params[0]['after_widget']  = '</a>' . $params[0]['after_widget'];

      }elseif ( $link && !$bg ) {

        $target = $instance['widget_link_target_blank'] ? ' target="_blank"' : '';

          $params[0]['before_widget'] = $params[0]['before_widget'] . '<a href="' . $link . '"' . $target . ' class="widget-as-link '.$css.'">';
          $params[0]['after_widget']  = '</a>' . $params[0]['after_widget'];

      }elseif( !$link && $bg ){

        $bg = !empty($instance['widget_link_bg']) ? 'background: url('.$instance['widget_link_bg'].') repeat left top; -webkit-background-size: cover; -moz-background-size: cover; background-size: cover; ' : null;
        $w = !empty($instance['widget_link_w']) ? 'display: block; width:'.$instance['widget_link_w'].'px; ' : null;
        $h = !empty($instance['widget_link_h']) ? 'height:'.$instance['widget_link_h'].'px;' : null;

        $params[0]['before_widget'] = $params[0]['before_widget'] . '<div style="'.$bg.$w.$h.'" class="widget-as-link '.$css.'">';
        $params[0]['after_widget']  = '</div>' . $params[0]['after_widget'];

      }else{
        $params[0]['before_widget'] = $params[0]['before_widget'] . '<div class="widget-as-link '.$css.'">';
        $params[0]['after_widget']  = '</div>' . $params[0]['after_widget'];
      }
    }

    return $params;
  }
}

new WAL();

class WAL_Clone_Widgets {
	function __construct() {
		add_filter( 'admin_head', array( $this, 'clone_script'  )  );
	}

	function clone_script() {
		global $pagenow;

		if( $pagenow != 'widgets.php' )
			return;
?>
<script>
(function($) {
	if(!window.wal) window.wal = {};

	wal.CloneWidget = {
		init: function() {
			$('body').on('click', '.clone-me', wal.CloneWidget.Clone); // .widget-control-actions .clone-me
			wal.CloneWidget.Bind();
		},

		Bind: function() {
			$('#widgets-right').off('DOMSubtreeModified', wal.CloneWidget.Bind);
			$('.widget-control-actions:not(.wal-cloneable)').each(function() {
				var $widget = $(this);

				var $clone = $( '<a>' );
				var clone = $clone.get()[0];
				$clone.addClass( 'clone-me wal-clone-action' )
							.attr( 'title', '<?php _e('Clone this Widget', 'wal') ?>' )
							.attr( 'href', '#' )
							.html( '<?php _e('Clone', 'wal') ?>' );


				$widget.addClass('wal-cloneable');
				$clone.insertAfter( $widget.find( '.alignleft .widget-control-remove') );

				//Separator |
				clone.insertAdjacentHTML( 'beforebegin', ' | ' );
			});

			$('#widgets-right').on('DOMSubtreeModified', wal.CloneWidget.Bind);
		},

		Clone: function(ev) {
			var $original = $(this).parents('.widget');
			var $widget = $original.clone();

			// Find this widget's ID base. Find its number, duplicate.
			var idbase = $widget.find('input[name="id_base"]').val();
			var number = $widget.find('input[name="widget_number"]').val();
			var mnumber = $widget.find('input[name="multi_number"]').val();
			var highest = 0;

			$('input.widget-id[value|="' + idbase + '"]').each(function() {
				var match = this.value.match(/-(\d+)$/);
				if(match && parseInt(match[1]) > highest)
					highest = parseInt(match[1]);
			});

			var newnum = highest + 1;

			$widget.find('.widget-content').find('input,select,textarea').each(function() {
				if($(this).attr('name'))
					$(this).attr('name', $(this).attr('name').replace(number, newnum));
			});

			// assign a unique id to this widget:
			var highest = 0;
			$('.widget').each(function() {
				var match = this.id.match(/^widget-(\d+)/);

				if(match && parseInt(match[1]) > highest)
					highest = parseInt(match[1]);
			});
			var newid = highest + 1;

			// Figure out the value of add_new from the source widget:
			var add = $('#widget-list .id_base[value="' + idbase + '"]').siblings('.add_new').val();
			$widget[0].id = 'widget-' + newid + '_' + idbase + '-' + newnum;
			$widget.find('input.widget-id').val(idbase+'-'+newnum);
			$widget.find('input.widget_number').val(newnum);
			$widget.hide();
			$original.after($widget);
			$widget.fadeIn();

			// Not exactly sure what multi_number is used for.
			$widget.find('.multi_number').val(newnum);

			wpWidgets.save($widget, 0, 0, 1);

			ev.stopPropagation();
			ev.preventDefault();
		}
	}

	$(wal.CloneWidget.init);
})(jQuery);

</script>
<?php
	}
}
new WAL_Clone_Widgets();
