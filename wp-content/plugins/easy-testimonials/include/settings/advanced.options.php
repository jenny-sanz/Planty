<?php
class easyTestimonialAdvancedOptions extends easyTestimonialOptions{
	var $tabs;
	var $config;
	
	function __construct($config){		
		
		//call register settings function
		add_action( 'admin_init', array($this, 'register_settings'));	
		
		//assign config
		$this->config = $config;
		
		//handle any changes in hello t status
		//also handle any "import now" commands
		$this->process_hello_testimonials_options();		
	}
	
	function register_settings(){		
		//register our settings	
		
		/* Hello T */
		register_setting( 'easy-testimonials-advanced-settings-group', 'easy_t_hello_t_json_url' );		
		register_setting( 'easy-testimonials-advanced-settings-group', 'easy_t_hello_t_enable_cron' );	
		register_setting( 'easy-testimonials-advanced-settings-group', 'easy_t_cache_buster', array($this, 'easy_t_bust_options_cache') );
		register_setting( 'easy-testimonials-private-settings-group', 'easy_t_hello_t_last_time' );			
		
		/* Shortcodes */
		register_setting( 'easy-testimonials-advanced-settings-group', 'ezt_testimonials_shortcode' );
		register_setting( 'easy-testimonials-advanced-settings-group', 'ezt_single_testimonial_shortcode' );
		register_setting( 'easy-testimonials-advanced-settings-group', 'ezt_submit_testimonial_shortcode' );
		register_setting( 'easy-testimonials-advanced-settings-group', 'ezt_cycle_testimonial_shortcode' );
		register_setting( 'easy-testimonials-advanced-settings-group', 'ezt_random_testimonial_shortcode' );
		register_setting( 'easy-testimonials-advanced-settings-group', 'ezt_testimonials_count_shortcode' );
		register_setting( 'easy-testimonials-advanced-settings-group', 'ezt_testimonials_grid_shortcode' );
		
		/* Compatibility Options */
		register_setting( 'easy-testimonials-advanced-settings-group', 'easy_t_disable_cycle2' );
		register_setting( 'easy-testimonials-advanced-settings-group', 'easy_t_use_cycle_fix' );
		register_setting( 'easy-testimonials-advanced-settings-group', 'easy_t_apply_content_filter' );
		register_setting( 'easy-testimonials-advanced-settings-group', 'easy_t_avada_filter_override' );
	}
	
	function render_settings_page()
	{
		//instantiate tabs object for output basic settings page tabs
		$tabs = new GP_Sajak( array(
			'header_label' => 'Advanced Settings',
			'settings_field_key' => 'easy-testimonials-advanced-settings-group', // can be an array			
		) );		
		
		$this->settings_page_top();
		$this->setup_basic_tabs($tabs);
		$this->settings_page_bottom();
	}
			
	function output_demo_testimonial_options()
	{		
		
		if( !empty($this->deleted_testimonials) ){
			echo "<p><strong>All demo testimonials have been deleted!</strong></p>";
		}
		
		if( !empty($this->created_testimonials) ){
			echo "<p><strong>Demo testimonials have been created!</strong></p>";
		}		?>							
			<h3>Demo Testimonials</h3>	
			
			<div id="ezt_ajax_demo_message"></div>
			
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label>Delete Demo Testimonials</label></th>
					<td>
						<p class="submit">
							<a id="ezt_btn_delete_demo_testimonials" href="#" xhref="?page=easy-testimonials-advanced-settings&delete-demo-testimonials=true#tab-demo_content_options" class="button-primary" title="<?php esc_attr_e('Delete Demo Testimonials', 'easy-testimonials') ?>"><?php esc_html_e('Delete Demo Testimonials', 'easy-testimonials') ?></a>
						</p>
						<p class="description">If clicked, we will delete any demo testimonials added by Easy Testimonials.</p>
					</td>
				</tr>
			</table>
			
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label>Create Demo Testimonials</label></th>
					<td>
						<p class="submit">
							<a id="ezt_btn_create_demo_testimonials" href="#" xhref="?page=easy-testimonials-advanced-settings&create-demo-testimonials=true#tab-demo_content_options" class="button-primary" title="<?php esc_attr_e('Create Demo Testimonials', 'easy-testimonials') ?>"><?php esc_html_e('Create Demo Testimonials', 'easy-testimonials') ?></a>
						</p>
						<p class="description">If clicked, we will create the demo Testimonials to your website.</p>
					</td>
				</tr>
			</table>
		<?php 

	}	
	
	function output_hello_testimonials_options(){		
		?>							
			<h3>Hello Testimonials</h3>	
			<p><strong>Want to learn more about Hello Testimonials? <a href="http://hellotestimonials.com/p/welcome-easy-testimonials-users/">Click Here!</a></strong></p>
			
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="easy_t_hello_t_json_url">Hello Testimonials JSON Feed URL</label></th>
					<td><textarea name="easy_t_hello_t_json_url" id="easy_t_hello_t_json_url" rows=1 ><?php echo esc_attr(get_option('easy_t_hello_t_json_url')); ?></textarea>
					<p class="description">This is the JSON URL you copied from the Custom Integrations page inside Hello Testimonials.</p>
					</td>
				</tr>
			</table>
			
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="easy_t_hello_t_enable_cron">Enable Hello Testimonials Integration</label></th>
					<td><input type="checkbox" name="easy_t_hello_t_enable_cron" id="easy_t_hello_t_enable_cron" value="1" <?php if(get_option('easy_t_hello_t_enable_cron', 0)){ ?> checked="CHECKED" <?php } ?>/>
					<p class="description">If checked, new Testimonials will be loaded from your Hello Testimonials account and automatically added to your Testimonials list.</p>
					</td>
				</tr>
			</table>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="easy_t_hello_t_enable_cron">Import From Hello Testimonials Now</label></th>
					<td>
						<p class="submit">
							<a href="?page=easy-testimonials-advanced-settings&run-cron-now=true" class="button-primary" title="<?php esc_attr_e('Import Now', 'easy-testimonials') ?>"><?php esc_html_e('Import Now', 'easy-testimonials') ?></a>
						</p>
						<p class="description">If clicked, we will process any new testimonials available in Hello Testimonials now.</p>
					</td>
				</tr>
			</table>
		<?php 
	}	
	
	function output_compatibility_options(){
		?>
		<h3 id="compatibility_options">Compatibility Options</h3>
		<p class="description">Use these fields to troubleshoot suspected compatibility issues with your Theme or other Plugins.</p>
		<table class="form-table">
			<tr valign="top">
				<td><input type="checkbox" name="easy_t_disable_cycle2" id="easy_t_disable_cycle2" value="1" <?php if(get_option('easy_t_disable_cycle2', false)){ ?> checked="CHECKED" <?php } ?>/>
				<label for="easy_t_disable_cycle2">Disable Cycle2 Output</label>
				<p class="description">If checked, we won't include the Cycle2 JavaScript file.  If you suspect you are having JavaScript compatibility issues with our plugin, please try checking this box.</p>
				</td>
			</tr>
		</table>
		
		<table class="form-table">
			<tr valign="top">
				<td><input type="checkbox" name="easy_t_use_cycle_fix" id="easy_t_use_cycle_fix" value="1" <?php if(get_option('easy_t_use_cycle_fix', false)){ ?> checked="CHECKED" <?php } ?>/>
				<label for="easy_t_use_cycle_fix">Use Cycle Fix</label>				
				<p class="description">If checked, we will try and trigger Cycle2 a different way.  If you suspect you are having JavaScript compatibility issues with our plugin, please try checking this box.  NOTE: If you have Disable Cycle2 Output checked, this box will have no effect.</p>
				</td>
			</tr>
		</table>
		
		<table class="form-table">
			<tr valign="top">
				<td><input type="checkbox" name="easy_t_apply_content_filter" id="easy_t_apply_content_filter" value="1" <?php if(get_option('easy_t_apply_content_filter', true)){ ?> checked="CHECKED" <?php } ?>/>
				<label for="easy_t_apply_content_filter">Apply The Content Filter</label>
				<p class="description">If checked, we will apply the content filter to Testimonial content.  Use this if you are experiencing problems with other plugins applying their shortcodes, etc, to your Testimonial content.</p>
				</td>
			</tr>
		</table>
		
		<?php
			/* Avada Check */
			$my_theme = wp_get_theme();
			$additional_message = "";
			$additional_classes = "";
			if( strpos( $my_theme->get('Name'), "Avada" ) === 0 ) {
				// looks like we are using Avada! 
				// make sure we have avada compatibility enabled. If not, show a warning!
				if(!get_option('easy_t_avada_filter_override', false)){
					$additional_classes = "has_avada";
					$additional_message = "We have detected that you are using the Avada theme.  Please enable this option to ensure compatibility.";
				}
			}
		?>
		
		<table class="form-table <?php echo esc_attr($additional_classes); ?>">
			<tr valign="top">
				<td><input type="checkbox" name="easy_t_avada_filter_override" id="easy_t_avada_filter_override" value="1" <?php if(get_option('easy_t_avada_filter_override', false)){ ?> checked="CHECKED" <?php } ?>/>
				<?php if(strlen($additional_message)>0){ echo esc_html("<p class='error'><strong>$additional_message</strong></p>");}?>
				<label for="easy_t_avada_filter_override">Override Avada Blog Post Content Filter on Testimonials</label>
				<p class="description">If checked, we will attempt to prevent the Avada blog layouts from overriding our Testimonial themes.  If you are having issues getting your themes to display when viewing Testimonial Categories in the Avada theme, try toggling this option.</p>
				</td>
			</tr>
		</table>
		<?php
	}
	
	function output_shortcode_options(){
		?>
			<h3>Shortcode Options</h3>
			<p class="description">Use these fields to control our registered shortcodes. This can be helpful when Easy Testimonials and another plugin (or your theme) are using the same shortcodes.</p>
			<p class="description"><strong>Tip:</strong> Try changing these fields if our shortcodes are not displaying anything at all.</p>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="ezt_random_testimonial_shortcode">Random Testimonial Shortcode</label></th>
					<td><input type="text" name="ezt_random_testimonial_shortcode" id="ezt_random_testimonial_shortcode" value="<?php echo esc_attr(get_option('ezt_random_testimonial_shortcode', 'random_testimonial')); ?>" />
					<p class="description">Displays one or more testimonials, chosen at random on each page load. Default: random_testimonial</p>
					</td>
				</tr>
			</table>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="ezt_single_testimonial_shortcode">Single Testimonial Shortcode</label></th>
					<td><input type="text" name="ezt_single_testimonial_shortcode" id="ezt_single_testimonial_shortcode" value="<?php echo esc_attr(get_option('ezt_single_testimonial_shortcode', 'single_testimonial')); ?>" />
					<p class="description">Displays a single testimonial, chosen by you. Default: single_testimonial</p>
					</td>
				</tr>
			</table>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="ezt_testimonials_shortcode">Testimonials List Shortcode</label></th>
					<td><input type="text" name="ezt_testimonials_shortcode" id="ezt_testimonials_shortcode" value="<?php echo esc_attr(get_option('ezt_testimonials_shortcode', 'testimonials')); ?>" />
					<p class="description">Displays a list of testimonials. Default: testimonials</p>
					</td>
				</tr>
			</table>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="ezt_cycle_testimonial_shortcode">Testimonials Cycle Shortcode</label></th>
					<td><input type="text" name="ezt_cycle_testimonial_shortcode" id="ezt_cycle_testimonial_shortcode" value="<?php echo esc_attr(get_option('ezt_cycle_testimonial_shortcode', 'testimonials_cycle')); ?>" />
					<p class="description">Displays a rotating slideshow of testimonials. Default: testimonial_cycle</p>
					</td>
				</tr>
			</table>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="ezt_submit_testimonial_shortcode">Testimonial Submission Form Shortcode</label></th>
					<td><input type="text" name="ezt_submit_testimonial_shortcode" id="ezt_submit_testimonial_shortcode" value="<?php echo esc_attr(get_option('ezt_submit_testimonial_shortcode', 'submit_testimonial')); ?>" />
					<p class="description">Displays the Submit Your Testimonial Form, which collects new Testimonials from your visitors. Default: submit_testimonial</p>
					</td>
				</tr>
			</table>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="ezt_testimonials_count_shortcode">Testimonials Count Shortcode</label></th>
					<td><input type="text" name="ezt_testimonials_count_shortcode" id="ezt_testimonials_count_shortcode" value="<?php echo esc_attr(get_option('ezt_testimonials_count_shortcode', 'testimonials_count')); ?>" />
					<p class="description">Displays the numeric count of testimonials, based on the attributes you specify. Default: testimonials_count</p>
					</td>
				</tr>
			</table>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="ezt_testimonials_grid_shortcode">Testimonials Grid Shortcode</label></th>
					<td><input type="text" name="ezt_testimonials_grid_shortcode" id="ezt_testimonials_grid_shortcode" value="<?php echo esc_attr(get_option('ezt_testimonials_grid_shortcode', 'testimonials_grid')); ?>" />
					<p class="description">Displays a responsive grid of testimonials, up to 10 columns wide. Default: testimonials_grid</p>
					</td>
				</tr>
			</table>
		<?php
	}	
	
	function setup_basic_tabs($tabs){	
		$this->tabs = $tabs;
		
		//load additional label string based upon pro status
		$pro_string = $this->config->is_pro ? "" : " (Pro)";
	
		$this->tabs->add_tab(
			'hello_testimonials_options', // section id, used in url fragment
			'Hello Testimonials', // section label
			array($this, 'output_hello_testimonials_options'), // display callback
			array(
				'class' => 'extra_li_class', // extra classes to add sidebar menu <li> and tab wrapper <div>
				'icon' => 'exchange' // icons here: http://fontawesome.io/icons/
			)
		);
		
		$this->tabs->add_tab(
			'demo_content_options', // section id, used in url fragment
			'Demo Content', // section label
			array($this, 'output_demo_testimonial_options'), // display callback
			array(
				'class' => 'extra_li_class', // extra classes to add sidebar menu <li> and tab wrapper <div>
				'icon' => 'gear' // icons here: http://fontawesome.io/icons/
			)
		);
		
		$this->tabs->add_tab(
			'shortcode_options', // section id, used in url fragment
			'Shortcode Options', // section label
			array($this, 'output_shortcode_options'), // display callback
			array(
				'class' => 'extra_li_class', // extra classes to add sidebar menu <li> and tab wrapper <div>
				'icon' => 'code' // icons here: http://fontawesome.io/icons/
			)
		);
		$this->tabs->add_tab(
			'compatibility_options', // section id, used in url fragment
			'Compatibility Options', // section label
			array($this, 'output_compatibility_options'), // display callback
			array(
				'class' => 'extra_li_class', // extra classes to add sidebar menu <li> and tab wrapper <div>
				'icon' => 'check-square-o' // icons here: http://fontawesome.io/icons/
			)
		);		
		
		$this->tabs->display();
	}
	
	function process_hello_testimonials_options(){
		//schedule cron if enabled
		if(get_option('easy_t_hello_t_enable_cron', 0)){
			//and if the cron job hasn't already been scheduled
			if(!wp_get_schedule('hello_t_subscription')){
				//schedule the cron job
				$this->hello_t_cron_activate();
			}
			
			//if the run cron now button has been clicked
			if (isset($_GET['run-cron-now']) && $_GET['run-cron-now'] == 'true'){
				//go ahead and add the testimonials, too
				add_action('admin_init', array($this, 'add_hello_t_testimonials') );
			}
		} else {
			//else if the cron job option has been unchecked
			//clear the scheduled job
			$this->hello_t_cron_deactivate();
			
			//if the run cron now button has been clicked
			if (isset($_GET['run-cron-now']) && $_GET['run-cron-now'] == 'true'){				
				$this->messages[] = 'Hello Testimonials Integration is disabled!  Please enable to Import Testimonials.';
			}
		}
	}
	
	//open up the json
	//determine which testimonials are new, or assume we have loaded only new testimonials
	//parse object and insert new testimonials
	function add_hello_t_testimonials(){	
		$the_time = time();
		
		$json_url = get_option('easy_t_hello_t_json_url', '');
		if ( empty($json_url) ) {
			return;
		}
		
		$url = $json_url . "?last=" . get_option('easy_t_hello_t_last_time', 0);		
		$response = wp_remote_get( $url, array('sslverify' => false ));
				
		if ( is_wp_error($response) ) {
			// invalid URL, show error message
			$this->messages[] = '<strong>Error:</strong> the Hello Testimonials JSON URL you entered could not be reached. Please check the URL in your Hello Testimonials settings, or try again in a few minutes.';
			return;
		}		
		
		if( !empty($response) && !empty($response['body']) ) {
			$response = json_decode($response['body']);
			
			if(isset($response->testimonials)){
				$testimonial_author_id = get_option('easy_t_testimonial_author', 1);
				
				foreach($response->testimonials as $testimonial){							
					//look for a testimonial with the same HTID
					//if not found, insert this one
					$args = array(
						'post_type' => 'testimonial',
						'meta_query' => array(
							array(
								'key' => '_ikcf_htid',
								'value' => $testimonial->id,
							)
						)
					 );
					$postslist = get_posts( $args );
					
					//if this is empty, a match wasn't found and therefore we are safe to insert
					if(empty($postslist)){				
						//insert the testimonials
						
						//defaults
						$the_name = isset( $testimonial->name ) ? $testimonial->name : '';
						$the_rating = isset( $testimonial->rating ) ? $testimonial->rating : 5;
						$the_position = isset( $testimonial->position ) ? $testimonial->position : '';
						$the_item_reviewed = isset( $testimonial->item_reviewed ) ? $testimonial->item_reviewed : '';
						$the_email = isset( $testimonial->email ) ? $testimonial->email : '';
						
						$tags = array();
					   
						$post = array(
							'post_title'    => $testimonial->name,
							'post_content'  => $testimonial->body,
							'post_category' => array(1),  // custom taxonomies too, needs to be an array
							'tags_input'    => $tags,
							'post_status'   => 'publish',
							'post_type'     => 'testimonial',
							'post_date'		=> $testimonial->publish_time,
							'post_author' 	=> $testimonial_author_id
						);
					
						$new_id = wp_insert_post($post);
					   
						update_post_meta( $new_id,	'_ikcf_client',		$the_name );
						update_post_meta( $new_id,	'_ikcf_rating',		$the_rating );
						update_post_meta( $new_id,	'_ikcf_htid',		$testimonial->id );
						update_post_meta( $new_id,	'_ikcf_position',	$the_position );
						update_post_meta( $new_id,	'_ikcf_other',		$the_item_reviewed );
						update_post_meta( $new_id,	'_ikcf_email',		$the_email );
					   
						$inserted = true;
						
						//update the last inserted id
						update_option( 'easy_t_hello_t_last_time', $the_time );
					}
				}
			}
		}
		
		//all done, so say something letting them know.
		$this->messages[] = 'Success!  Your Testimonials have been imported!';
	}

	function hello_t_nag_ignore() {
		global $current_user;
		$user_id = $current_user->ID;
		/* If user clicks to ignore the notice, add that to their user meta */
		if ( isset($_GET['hello_t_nag_ignore']) && '0' == $_GET['hello_t_nag_ignore'] ) {
			 add_user_meta($user_id, 'hello_t_nag_ignore', 'true', true);
		}
	}

	//activate the cron job
	function hello_t_cron_activate(){
		wp_schedule_event( time(), 'hourly', 'hello_t_subscription');
	}

	//deactivate the cron job when the plugin is deactivated
	function hello_t_cron_deactivate(){
		wp_clear_scheduled_hook('hello_t_subscription');
	}
}