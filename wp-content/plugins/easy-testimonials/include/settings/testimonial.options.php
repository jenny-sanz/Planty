<?php
/*
This file is part of Easy Testimonials.

Easy Testimonials is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Easy Testimonials is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with The Easy Testimonials.  If not, see <http://www.gnu.org/licenses/>.
*/
require_once('basic.options.php');
require_once('display.options.php');
require_once('theme.options.php');
require_once('advanced.options.php');
require_once('shortcode-generator.options.php');
require_once('Easy_Testimonials_Demo_Content.php');

class easyTestimonialOptions
{	
	var $config;
	var $basic_settings_page;
	var $display_settings_page;
	var $theme_settings_page;
	var $shortcode_generator_page;
	var $advanced_settings_page;
	var $messages = array();
	
	function __construct($config)
	{
		//load config
		$this->config = $config;
		
		//instantiate Sajak so we get our JS and CSS enqueued
		new GP_Sajak();
		
		//may be running in non WP mode (for example from a notification)
		if(function_exists('add_action')){
			//setup our classes
			$this->basic_settings_page = new easyTestimonialBasicOptions($this->config);
			$this->display_settings_page = new easyTestimonialDisplayOptions($this->config);
			$this->theme_settings_page = new easyTestimonialThemeOptions($this->config);
			$this->advanced_settings_page = new easyTestimonialAdvancedOptions($this->config);
			$this->shortcode_generator_page = new easyTestimonialShortcodeGeneratorOptions($this->config);
			
			//add a menu item
			add_action( 'admin_menu', array($this, 'add_admin_menu_items') );	
			add_action( 'admin_menu', array($this, 'add_upgrade_to_pro_link'), 20 ); // add late, to end of list
			
			//call register settings function
			add_action( 'admin_init', array($this, 'register_settings'));
			
			add_action( 'admin_init', array($this, 'maybe_ask_for_review'), 1);	
			add_action( 'wp_ajax_easy_testimonials_dismiss_review_alert', array($this, 'dismiss_review_alert') );
			add_action( 'wp_ajax_easy_testimonials_dismiss_admin_message', array($this, 'dismiss_admin_message') );

			add_action( 'admin_init', array($this, 'add_extra_classes_to_admin_menu') );
			
			add_action( 'easy_t_admin_render_settings_tabs', array($this, 'easy_t_admin_tabs'), 10, 1 );
		}
		
		//flush cache if settings have been saved
		if( isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true' ){
			$this->easy_t_clear_cache();
		}

		$this->demo_content = new Easy_Testimonials_Demo_Content();
	}
	
	function get_oldest_testimonial_date()
	{
		$oldest_post = get_posts( array(
			'post_type' => 'testimonial',
			'order' => 'ASC',
			'orderby' => 'date',
			'posts_per_page' => 1,
		) );
		
		return isset($oldest_post[0])
			   ? $oldest_post[0]->post_date
			   : '';
	}
	
	function get_testimonials_count()
	{
		$posts = get_posts('post_type=testimonial&posts_per_page=-1');
		return count($posts); 
	}
	
	function is_our_settings_page()
	{
		return (
			is_admin()
			&& !empty($_SERVER['REQUEST_URI'])
			&& strpos($_SERVER['REQUEST_URI'], 'aloha') === false
			&& strpos($_SERVER['REQUEST_URI'], 'upgrade') === false
			&& strpos($_SERVER['REQUEST_URI'], 'easy-testimonials') !== false
		);
	}
	
	function review_notice_dismissed()
	{
		$user_id = get_current_user_id();
		if ( empty($user_id) ) {
			return false;
		}
		$meta_val = get_user_meta($user_id, 'dismiss_review_alert', true);
		return ( !empty($meta_val) );		
	}
	
	function upgrade_message_dismissed()
	{
		$user_id = get_current_user_id();
		if ( empty($user_id) ) {
			return false;
		}
		$upgrade_message_dismissed = get_user_meta($user_id, 'dismiss_notice_upgrade_message', true);
		return ( !empty($upgrade_message_dismissed) );
	}
	
	/*
	 * AJAX function to permanently dismiss the review alert
	 */
	function dismiss_review_alert()
	{
		$user_id = get_current_user_id();
		if ( !empty($user_id) ) {
			update_user_meta($user_id, 'dismiss_review_alert', '1');
			echo "OK";
		} else {				
			echo "Login required.";
		}
		wp_die();
	}	
	
	/*
	 * AJAX function to permanently dismiss an alert by ID
	 */
	function dismiss_admin_message()
	{
		$user_id = get_current_user_id();
		$alert_id = isset($_POST['alert_id'])
					? sanitize_text_field($_POST['alert_id'])
					: '';
					
		if ( !empty($user_id) && !empty($alert_id) ) {
			
			switch ( $alert_id ) {
				case 'notice-upgrade-message':
					update_user_meta( $user_id, 'dismiss_notice_upgrade_message', date('U') );
				break;
				
				default: 
				break;
			}
		}
		wp_die("OK");
	}	
	
	function maybe_ask_for_review( $hook )
	{
		// quit now if Pro
		if ( $this->config->is_pro ) {
			return;
		}

		// if viewing one of our settings pages,
		// and is an administrator,
		// and has > 10 testimonials,
		// and the user has been using the plugin for at least a month, 
		// ask for a review in the WP directory on our settings page
		$oldest_post_date = $this->get_oldest_testimonial_date();
		if ( current_user_can('administrator')
			 && !empty($oldest_post_date)
			 && ( strtotime($oldest_post_date) < strtotime('1 month ago') ) 
			 && !$this->upgrade_message_dismissed()
		) {
			 add_action( 'admin_notices', array($this, 'display_upgrade_message') );
		}
		else if ( $this->is_our_settings_page()
			 && current_user_can('administrator')
			 && $this->get_testimonials_count() > 10
			 && !empty($oldest_post_date)
			 && ( $oldest_post_date < strtotime('1 month ago') )
			 && !$this->review_notice_dismissed()
		) {
			 add_action( 'admin_notices', array($this, 'display_review_ask') );			
		}
	}
	
	function display_review_ask()
	{		
		$message = sprintf( '<h3 style="margin: 10px auto; font-size: 18px;">%s</h3>', __('Do you have a moment to help us?') );
		$message .= sprintf( '<p>%s <strong>%s</strong></p>', __('We hope you\'re enjoying'), 'Easy Testimonials!' );
		$message .= sprintf( '<p>%s</p>', __('If you like the plugin and you have a free moment, would you mind leaving us a review?') );
		$message .= sprintf( '<p>%s</p>', __('We\'re a small but dedicated team, and good reviews are the best way we have of reaching new users.') );
		$message .= '<p>{{review_url}}</p>';
		$message .=  sprintf('<p>%s</p>', __('Thanks so much for your time!') );
		$message .= sprintf('<p><em>%s</em></p>', __('If you don\'t want to see this notice again, just click the close button in the top right corner.') );
		
		// replace {{review_url}} with URL in message
		$review_url = "https://wordpress.org/support/plugin/easy-testimonials/reviews/";
		$review_button = sprintf( '<a href="%s" class="button">Click Here To Leave A Review</a>', esc_url($review_url) );
		$message = str_replace('{{review_url}}', $review_button, $message);
		
		?>
		<div class="notice notice-success is-dismissible" data-dismissible="notice-review-ask" id="easy_testimonials_review_request_alert">
			<?php echo wp_kses_post($message); ?>
		</div>
		<?php
	}
	
	function display_upgrade_message()
	{
		?>
		<style>
			#easy_t_upgrade_message_box h3::before { content: "\01F389"; margin-right: 10px }
			#easy_t_upgrade_message_box h3 { color: #3b8dd4; font-size: 1.3em; line-height: 2em; margin: 6px 0 6px; }
			#easy_t_upgrade_message_box p { margin-bottom: 12px; max-width: 1020px; }
		</style>
		<script type="text/javascript">
		var ezt_setup_upgrade_message = function () {
			var box = jQuery('#easy_testimonials_upgrade_message_alert');
			if ( box.length == 0 ) {
				return;
			}
			box.on('click', '.notice-dismiss', function () {
				jQuery.ajax({
					type:"POST",
					url: ajaxurl,
					data: { 
						action: "easy_testimonials_dismiss_admin_message",
						alert_id: "notice-upgrade-message",
					},
					success: function (data) {}
				});
			});
		};	
		jQuery(function () {
			ezt_setup_upgrade_message();
		});
		</script>
		<?php
		$message = sprintf( '<h3 style="">%s</h3>', __('Thanks for using Easy&nbsp;Testimonials!') );
		$message .= sprintf( '<p style="font-size: 16px; color:green;">%s </p>', __('Now that you\'ve been using Easy&nbsp;Testimonials for some time, we want to thank you by offering you a 25% discount on Easy&nbsp;Testimonials&nbsp;Pro!') );
		$message .= sprintf( '<p>%s <strong>%s</strong> %s</p>', __('When you upgrade to '), 'Easy&nbsp;Testimonials&nbsp;Pro', __(' you will instantly gain access to new features such as the Testimonial Form builder,  typography settings, Import&nbsp;&amp;&nbsp;Export, and more. Visit our website using the link below to learn more and to claim your discount.') );
		$upgrade_url = 'https://goldplugins.com/special-offers/upgrade-to-easy-testimonials-pro/?discount=onemonthbday&utm_campaign=plugin_one_month&utm_source=' . urlencode( site_url() );
		$message .= sprintf( '<p><a target=_"blank" href="%s" class="button button-primary">%s</a></p>', esc_url($upgrade_url), __('Save 25% on Your Upgrade Now!') );
		$message .= sprintf( '<p><em>%s</em> </p>', __('This is a one-time offer. If you don\'t wish to see it again simply click the close button.') );;		
		?>
		<div class="notice notice-success is-dismissible" data-dismissible="notice-upgrade-message" id="easy_testimonials_upgrade_message_alert" style="margin: 10px 0px 10px 3px">
			<div id="easy_t_upgrade_message_box">
			<?php echo wp_kses_post($message); ?>
			</div>
		</div>
		<?php
	}
	
	function add_admin_menu_items()
	{
		global $submenu;
		$title = "Easy Testimonials Settings";
		$page_title = "Easy Testimonials Settings";
		//$top_level_slug = "easy-testimonials-settings";
		$top_level_slug = 'edit.php?post_type=testimonial';
		
		//create new top-level menu
		//add_menu_page( $page_title, $title, 'administrator', $top_level_slug , array($this->basic_settings_page, 'render_settings_page') );		
		$submenu_pages = array();
		
		//basic options page
		$submenu_pages[] = array(
				'top_level_slug' => $top_level_slug,
				'page_title' => 'Basic Settings',
				'menu_title' => 'Basic Settings',
				'role' => 'administrator',
				'slug' => 'easy-testimonials-settings',
				'callback' => array($this->basic_settings_page, 'render_settings_page'),
				'hide_in_menu' => false
		);

		//display options page
		$submenu_pages[] = array(
			'top_level_slug' => $top_level_slug,
			'page_title' => 'Display Settings',
			'menu_title' => 'Display Settings',
			'role' => 'administrator',
			'slug' => 'easy-testimonials-display-settings',
			'callback' => array($this->display_settings_page, 'render_settings_page'),
			'hide_in_menu' => true
		);
		
		// Typography settings placeholder
		if ( !$this->config->is_pro ) {
			$submenu_pages[] = array(
				'top_level_slug' => $top_level_slug,
				'page_title' => 'Typography Settings',
				'menu_title' => 'Typography Settings',
				'role' => 'administrator',
				'slug' => 'easy-testimonials-typography-settings',
				'callback' => array($this->demo_content, 'text_styles_page'),
				'hide_in_menu' => false
			);
		}		
		
		//theme options page
		$submenu_pages[] = array(
				'top_level_slug' => $top_level_slug,
				'page_title' => 'Theme Settings',
				'menu_title' => 'Theme Settings',
				'role' => 'administrator',
				'slug' => 'easy-testimonials-style-settings',
				'callback' => array($this->theme_settings_page, 'render_settings_page'),
				'hide_in_menu' => true
		);
		
		//advanced options page
		$submenu_pages[] = array(
				'top_level_slug' => $top_level_slug,
				'page_title' => 'Advanced Settings',
				'menu_title' => 'Advanced Settings',
				'role' => 'administrator',
				'slug' => 'easy-testimonials-advanced-settings',
				'callback' => array($this->advanced_settings_page, 'render_settings_page'),
				'hide_in_menu' => false
		);
		
		if ( !$this->config->is_pro ) {
			
			// Import/Export placeholder
			$submenu_pages[] = array(
				'top_level_slug' => $top_level_slug,
				'page_title' => 'Import & Export',
				'menu_title' => 'Import & Export',
				'role' => 'administrator',
				'slug' => 'easy-testimonials-import-export-settings',
				'callback' => array($this->demo_content, 'import_export_page'),
				'hide_in_menu' => false
			);
			
			// Submission Form settings placeholder
			$submenu_pages[] = array(
				'top_level_slug' => $top_level_slug,
				'page_title' => 'Collection Form Settings',
				'menu_title' => 'Collection Form Settings',
				'role' => 'administrator',
				'slug' => 'easy-testimonials-submission-settings',
				'callback' => array($this->demo_content, 'submission_form_settings_page'),
				'hide_in_menu' => false
			);
		}		
		
		//shortcode generator page
		$submenu_pages[] = array(
				'top_level_slug' => $top_level_slug,
				'page_title' => 'Shortcode Generator',
				'menu_title' => 'Shortcode Generator',
				'role' => 'administrator',
				'slug' => 'easy-testimonials-shortcode-generator',
				'callback' => array($this->shortcode_generator_page, 'render_settings_page'),
				'hide_in_menu' => false
		);
		
		//help and instructions page
		$submenu_pages[] = array(
				'top_level_slug' => $top_level_slug,
				'page_title' => 'Help & Instructions',
				'menu_title' => 'Help & Instructions',
				'role' => 'administrator',
				'slug' => 'easy-testimonials-help',//'https://goldplugins.com/documentation/easy-testimonials-documentation/?utm_src=admin_menu_item',
				'callback' => array($this, 'render_help_page'),//null,
				'hide_in_menu' => false
		);

		$submenu_pages = apply_filters("easy_t_admin_submenu_pages", $submenu_pages);
		
		// add placeholders
		if ( !$this->config->is_pro ) {
			
			// testimonial forms
			array_unshift( $submenu_pages, array(
				'top_level_slug' => $top_level_slug,
				'page_title' => 'Testimonial Forms',
				'menu_title' => 'Testimonial Forms',
				'role' => 'administrator',
				'slug' => 'easy-testimonials-testmionial-forms',
				'callback' => array($this->demo_content, 'testimonial_forms_page'),
				'hide_in_menu' => false
			) );

		}
		
		//add submenu items
		foreach ($submenu_pages as $submenu_page) {
			add_submenu_page( 
				$submenu_page['top_level_slug'],
				$submenu_page['page_title'],
				$submenu_page['menu_title'],
				$submenu_page['role'],
				$submenu_page['slug'],
				$submenu_page['callback'] );
		}

		$this->rename_admin_menu('Testimonials', 'Easy Testimonials', 'edit.php?post_type=testimonial');

		$new_submenu_label = __('All', 'easy-testimonials') . ' Testimonials';
		$this->rename_admin_submenu('Testimonials', $new_submenu_label);
	}
	
	function add_upgrade_to_pro_link()
	{
		$top_level_slug = 'edit.php?post_type=testimonial';
		if ( !$this->config->is_pro ) {
			add_submenu_page(
				$top_level_slug,
				__('Upgrade To Pro'),
				__('Upgrade To Pro'),
				'administrator',
				'easy-testimonials-upgrade-to-pro',
				array($this, 'render_upgrade_page')
			);
		}
	}
	
	function add_extra_classes_to_admin_menu() 
	{
		global $menu;
		
		//sometimes $menu is not an array (perhaps this is running in non-admin mode due to another plugin)
		if( !is_array($menu) ){
			return;
		}
		
		foreach( $menu as $key => $value ) {
			$extra_classes = 'easy_testimonials_admin_menu';
			$extra_classes .= $this->config->is_pro
						    ? ' easy_testimonials_pro_admin_menu'
							: ' easy_testimonials_free_admin_menu';
			if( 'Easy Testimonials' == $value[0] ) {
				$menu[$key][4] .= ' ' . $extra_classes;
			}
		}
	}			
	
	function add_upgrade_submenu($submenu_pages, $top_level_slug)
	{
		$new_menu = array(
			'top_level_slug' => $top_level_slug,
			'page_title' => __('Upgrade To Pro'),
			'menu_title' => __('Upgrade To Pro'),
			'role' => 'administrator',
			'slug' => 'easy-testimonials-upgrade-to-pro',
			'callback' => array($this, 'render_upgrade_page'),//null,
			'hide_in_menu' => false
		);
		$submenu_pages[] = $new_menu;
		return $submenu_pages;
	}
	
	//output the help page
	function render_help_page(){		
		//instantiate tabs object for output basic settings page tabs
		$tabs = new GP_Sajak( array(
			'header_label' => 'Help &amp; Instructions',
			'settings_field_key' => 'easy-testimonials-help-settings-group', // can be an array	
			'show_save_button' => false, // hide save buttons for all panels   		
		) );
		
		$this->settings_page_top(false);
	
		$tabs->add_tab(
			'help', // section id, used in url fragment
			'Help Center', // section label
			array($this, 'output_help_page'), // display callback
			array(
				'class' => 'extra_li_class', // extra classes to add sidebar menu <li> and tab wrapper <div>
				'icon' => 'life-buoy' // icons here: http://fontawesome.io/icons/
			)
		);
		
		$tabs = apply_filters('easy_testimonials_admin_help_tabs', $tabs);		
		$tabs->display();
		
		$this->settings_page_bottom();
	}
	
	//output the upgrade page
	function render_upgrade_page(){		
		?>		
		<div class="easy_testimonials_admin_wrap">
			<div class="gp_upgrade">
				<h1 class="gp_upgrade_header">Upgrade To Easy Testimonials Pro</h1>
				<div class="gp_upgrade_body">
				
					<div class="header_wrapper">
						<div class="video_wrapper">
							<iframe src="https://www.youtube.com/embed/JPSGgPA3TDo" allowfullscreen="" width="480" height="270" frameborder="0"></iframe>
						</div>
						<div class="customer_testimonial">
								<div class="stars">
									<span class="dashicons dashicons-star-filled"></span>
									<span class="dashicons dashicons-star-filled"></span>
									<span class="dashicons dashicons-star-filled"></span>
									<span class="dashicons dashicons-star-filled"></span>
									<span class="dashicons dashicons-star-filled"></span>
								</div>
								<p class="customer_testimonial_title"><strong>Easy Testimonials was by far the best</strong></p>
								“I looked at several testimonial plugins, and Easy Testimonials was by far the best, most user friendly and customizable plugin I found (and a reasonable price).”
								<p class="author">— Greg Campisi</p>
						</div>
					</div>
					<div style="clear:both;"></div>
					<div class="upgrade_left_col">
						<div class="upgrade_left_col_inner">
							<h3>Easy Testimonials Pro Adds Powerful New Features, Including:</h3>
							<ul>
								<li>Submit Your Testimonial forms, to automatically collect new Testimonials</li>
								<li>The Testimonial Form Builder, to create unlimited, customized Testimonial&nbsp;Forms</li>
								<li>Over 100 professionally designed themes</li>
								<li>Options to select fonts, font sizes, and colors for all Testimonial elements</li>
								<li>Use Smart Text Avatars for your testimonials without a photo</li>
								<li>Choose from 8 transitions for your testimonial slideshows</li>
								<li>Import &amp; Export your testimonials</li>
								<li>Full support for WordPress multisite</li>
								<li>A full year of technical support</li>
								<li>A full year of free upgrades</li>
							</ul>

							<p class="all_features_link">And many more! <a href="https://goldplugins.com/downloads/easy-testimonials-pro/?utm_source=upgrade_page_plugin&amp;utm_campaign=see_all_features">Click here for a full list of features included in Easy Testimonials Pro</a>.</p>
							<p class="upgrade_button"><a href="https://goldplugins.com/special-offers/upgrade-to-easy-testimonials-pro/?utm_source=easy_t_free_plugin&utm_campaign=upgrade_page_button">Upgrade Now</a></p>
						</div>
					</div>
					<div class="bottom_cols">
						<div class="how_to_upgrade">
							<h4>How To Upgrade:</h4>
							<ol>
								<li><a href="https://goldplugins.com/special-offers/upgrade-to-easy-testimonials-pro/?utm_source=easy_t_free_plugin&utm_campaign=how_to_upgrade_steps">Purchase an API Key from GoldPlugins.com</a></li>
								<li>Install and Activate the Easy Testimonials Pro plugin.</li>
								<li>Under the Easy Testimonials &raquo; License Options menu, enter your API key and click Activate.</li>
							</ol>
							<p class="upgrade_more">That's all! Upgrading happens instantly, and won't affect your data.</p>
						</div>
						<div class="questions">
							<h4>Have Questions?</h4>
							<p class="questions_text">We can help. <a href="https://goldplugins.com/contact/">Click here to Contact Us</a>.</p>
							<p class="all_plans_include_support">All plans include a full year of technical support.</p>
						</div>
					</div>
				</div>
				
				<div id="signup_wrapper" class="upgrade_sidebar">
					<div id="mc_embed_signup">
						<div class="save_now">
							<h3>Save 10% Now!</h3>
							<p class="pitch">Subscribe to our newsletter now, and we’ll send you a coupon for 10% off your upgrade to the Pro version.</p>
						</div>
						<form action="https://goldplugins.com/atm/atm.php?u=403e206455845b3b4bd0c08dc&amp;id=a70177def0" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate="">
							<div class="fields_wrapper">
								<label for="mce-NAME">Your Name (optional)</label>
								<input value="golden" name="NAME" class="name" id="mce-NAME" placeholder="Your Name" type="text">
								<label for="mce-EMAIL">Your Email</label>
								<input value="services@illuminatikarate.com" name="EMAIL" class="email" id="mce-EMAIL" placeholder="email address" required="" type="email">
								<!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
								<div style="position: absolute; left: -5000px;"><input name="b_403e206455845b3b4bd0c08dc_6ad78db648" tabindex="-1" value="" type="text"></div>
							</div>
							<div class="clear"><input value="Send My Coupon" name="subscribe" id="mc-embedded-subscribe" class="whiteButton" type="submit"></div>
							<p class="secure"><img src="<?php echo esc_url(plugins_url('/easy-testimonials/include/assets/img/lock.png')); ?>" alt="Lock" width="16px" height="16px">We respect your privacy.</p>							
							
							<input id="mc-upgrade-plugin-name" value="Easy Testimonials Pro" type="hidden">
							<input id="mc-upgrade-link-per" value="https://goldplugins.com/purchase/easy-testimonials-pro/single?promo=success10" type="hidden">
							<input id="mc-upgrade-link-biz" value="https://goldplugins.com/purchase/easy-testimonials-pro/business?promo=success10" type="hidden">
							<input id="mc-upgrade-link-dev" value="https://goldplugins.com/purchase/easy-testimonials-pro/developer?promo=success10" type="hidden">
							<input id="gold_plugins_already_subscribed" name="gold_plugins_already_subscribed" value="0" type="hidden">
						</form>					
					</div>
					
					<div class="sidebar_hello_t hello_t_banner">
						<div class=""><h3><strong>Need more Testimonials?</strong></h3>
							<p>Then try <strong>Hello Testimonials</strong>, our plug-and-play system which delivers a constant stream of customer testimonials and timely feedback to business owners.</p>
							<p>Easy Testimonials users receive a free 14-day trial!</p>
							<p><a class="whiteButton" href="http://hellotestimonials.com/p/welcome-easy-testimonials-users/" title="Click Here To Learn More">Click Here To Learn More</a></p>
							<br>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script type="text/javascript">
		jQuery(function () {
			if (typeof(et_gold_plugins_init_coupon_box) == 'function') {
				et_gold_plugins_init_coupon_box();
			}
		});
		</script>
		<?php
	}
	
	function output_contact_page(){
		if($this->config->is_pro){		
			//load all plugins on site
			$all_plugins = get_plugins();
			//load current theme object
			$the_theme = wp_get_theme();
			//load current easy t options
			$the_options = $this->load_all_options();
			//load wordpress area
			global $wp_version;
			
			$site_data = array(
				'plugins'	=> $all_plugins,
				'theme'		=> $the_theme,
				'wordpress'	=> $wp_version,
				'options'	=> $the_options
			);
			
			$current_user = wp_get_current_user();
			?>
			<h3>Contact Support</h3>
			<p>Would you like personalized support? Use the form below to submit a request!</p>
			<p>If you aren't able to find a helpful answer in our Help Center, go ahead and send us a support request!</p>
			<p>Please be as detailed as possible, including links to example pages with the issue present and what steps you've taken so far.  If relevant, include any shortcodes or functions you are using.</p>
			<p>Thanks!</p>
			<div class="gp_support_form_wrapper">
				<div class="gp_ajax_contact_form_message"></div>
				
				<div data-gp-ajax-form="1" data-ajax-submit="1" class="gp-ajax-form" method="post" action="https://goldplugins.com/tickets/galahad/catch.php">
					<div style="display: none;">
						<textarea name="your-details" class="gp_galahad_site_details">
							<?php
								echo esc_html(json_encode($site_data));
							?>
						</textarea>
						
					</div>
					<div class="form_field">
						<label>Your Name (required)</label>
						<input type="text" aria-invalid="false" aria-required="true" size="40" value="<?php echo (!empty($current_user->display_name) ?  esc_attr($current_user->display_name) : ''); ?>" name="your_name">
					</div>
					<div class="form_field">
						<label>Your Email (required)</label>
						<input type="email" aria-invalid="false" aria-required="true" size="40" value="<?php echo (!empty($current_user->user_email) ?  esc_attr($current_user->user_email) : ''); ?>" name="your_email"></span>
					</div>
					<div class="form_field">
						<label>URL where problem can be seen:</label>
						<input type="text" aria-invalid="false" aria-required="false" size="40" value="" name="example_url">
					</div>
					<div class="form_field">
						<label>Your Message</label>
						<textarea aria-invalid="false" rows="10" cols="40" name="your_message"></textarea>
					</div>
					<div class="form_field">
						<input type="hidden" name="include_wp_info" value="0" />
						<label for="include_wp_info">
							<input type="checkbox" id="include_wp_info" name="include_wp_info" value="1" />Include information about my WordPress environment (server information, installed plugins, theme, and current version)
						</label>
					</div>					
					<p><em>Sending this data will allow the Gold Plugins can you help much more quickly. We strongly encourage you to include it.</em></p>
					<input type="hidden" name="registered_email" value="<?php echo esc_attr(get_option('easy_t_registered_name')); ?>" />
					<input type="hidden" name="site_url" value="<?php echo esc_attr(site_url()); ?>" />
					<input type="hidden" name="challenge" value="<?php echo esc_attr(substr(md5(sha1('bananaphone' . get_option('easy_t_registered_key') )), 0, 10) ); ?>" />
					<div class="submit_wrapper">
						<input type="submit" class="button submit" value="Send">			
					</div>
				</div>
			</div>
			<?php
		} else {
			?>
			<h3>Contact Support</h3>
			<p>Would you like personalized support? Upgrade to Pro today to receive hands on support and access to all of our Pro features!</p>
			<p><a class="button upgrade" href="https://goldplugins.com/special-offers/upgrade-to-easy-testimonials-pro/?utm_source=easy_testimonials_freep&utm_campaign=galahad_support_tab&utm_content=learn_more_button_1">Click Here To Learn More</a></p>			
			<?php
		}
	}
	
	function output_help_page(){
		?>
		<h3>Help Center</h3>
		<div class="help_box">
			<h4>Have a Question?  Check out our FAQs!</h4>
			<p>Our FAQs contain answers to our most frequently asked questions.  This is a great place to start!</p>
			<p><a class="easy_t_support_button" target="_blank" href="https://goldplugins.com/documentation/easy-testimonials-documentation/faqs/?utm_source=help_page">Click Here To Read FAQs</a></p>
		</div>
		<div class="help_box">
			<h4>Looking for Instructions? Check out our Documentation!</h4>
			<p>For a good start to finish explanation of how to add Testimonials and then display them on your site, check out our Documentation!</p>
			<p><a class="easy_t_support_button" target="_blank" href="https://goldplugins.com/documentation/easy-testimonials-documentation/?utm_source=help_page">Click Here To Read Our Docs</a></p>
		</div>
		<?php		
	}
	
	//loads all options
	//builds array of options matching our prefix
	//returns our array
	private function load_all_options(){
		$my_options = array();
		$all_options = wp_load_alloptions();
		
		$patterns = array(
			'testimonials_link',
			'testimonials_image',
			'meta_data_position',
			'ezt_(.*)',
			'testimonials_style',
			'easy_t_(.*)',
		);
		
		foreach ( $all_options as $name => $value ) {
			if ( $this->preg_match_array( $name, $patterns ) ) {
				$my_options[ $name ] = $value;
			}
		}
		
		return $my_options;
	}
	
	function preg_match_array( $candidate, $patterns )
	{
		foreach ($patterns as $pattern) {
			$p = sprintf('#%s#i', $pattern);
			if ( preg_match($p, $candidate, $matches) == 1 ) {
				return true;
			}
		}
		return false;
	}
	
	//function to produce tabs on admin screen
	function easy_t_admin_tabs($current = 'homepage' ) {
	
		$tabs = array( 	'easy-testimonials-settings' => __('Basic', 'easy-testimonials'), 
						'easy-testimonials-display-settings' => __('Display', 'easy-testimonials'),
						'easy-testimonials-style-settings' => __('Themes', 'easy-testimonials'),
						'easy-testimonials-advanced-settings' => __('Advanced', 'easy-testimonials')						
					);
		
		if ( !$this->config->is_pro ) {
			$tabs['easy-testimonials-upgrade-to-pro'] = __('Upgrade', 'easy-testimonials');
		}		
		
		//allow additional tabs to be insterted
		$tabs = apply_filters('easy_t_admin_tabs', $tabs);
		
		echo '<h2 style="display:none"></h2>';
		echo '<div id="icon-themes" class="icon32"><br></div>';
		echo '<h2 class="nav-tab-wrapper">';
			foreach( $tabs as $tab => $name ){
				$class = ( $tab == $current ) ? ' nav-tab-active' : '';
				printf('<a class="nav-tab%s" href="edit.php?post_type=testimonial&page=%s">%s</a>', esc_attr($class), esc_url($tab), esc_html($name));
			}
		echo '</h2>';
	}
	
	function register_settings(){						
	}	
	
	/* Utility Functions */
	
	//output top of settings page
	function settings_page_top($show_tabs = true){
		$title = "Easy Testimonials Settings";		
		if( isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true' ){
			$this->messages[] = "Easy Testimonials settings updated.";
		}
		
		global $pagenow;
	?>
	<div class="wrap easy_testimonials_admin_wrap">
	<?php
		if( !empty($this->messages) ){
			foreach($this->messages as $message){
				echo '<div id="messages" class="gp_updated fade">';
				echo '<p>' . wp_kses_post($message) . '</p>';
				echo '</div>';
			}
			
			$this->messages = array();
		}
	?>
        <div id="icon-options-general" class="icon32"></div>
		<?php
		
		if($show_tabs){
			$this->get_and_output_current_tab($pagenow);
		}
	}
	
	//builds the bottom of the settings page
	//includes the signup form, if not pro
	function settings_page_bottom(){
		?>
		</div>
		<?php
	}
	
	function get_and_output_current_tab($pagenow){
		$tab = ! empty($_GET['page'])
			   ? sanitize_key($_GET['page'])
			   : '';
		$this->easy_t_admin_tabs($tab); 
		return $tab;
	}
	
	function easy_t_excerpt_text($val){
		//if nothing set, default to Continue Reading
		if(strlen($val)<1){
			return "Continue Reading";
		} else {
			return $val;
		}
	}
	
	function easy_t_excerpt_length($val){
		//if nothing set, default to 55
		if(strlen($val)<1){
			return 55;
		} else {
			return intval($val);
		}
	}
	
	function typography_input($name, $label, $description)
	{
		global $EasyT_BikeShed;
		$options = array();
		$options['name'] = $name;
		$options['label'] = $label;
		$options['description'] = $description;
		$options['google_fonts'] = true;
		$options['default_color'] = '';
		$options['values'] = $this->get_typography_values($name);		
		$options['disabled'] = !$this->config->is_pro; // typography inputs are Pro only
		$EasyT_BikeShed->typography( $options );
	}
	
	//from http://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
	function easy_t_output_image_options(){
		global $_wp_additional_image_sizes;
		$sizes = array();
		foreach( get_intermediate_image_sizes() as $s ){
			$sizes[ $s ] = array( 0, 0 );
			if( in_array( $s, array( 'thumbnail', 'medium', 'large' ) ) ){
				$sizes[ $s ][0] = get_option( $s . '_size_w' );
				$sizes[ $s ][1] = get_option( $s . '_size_h' );
			}else{
				if( isset( $_wp_additional_image_sizes ) && isset( $_wp_additional_image_sizes[ $s ] ) )
					$sizes[ $s ] = array( $_wp_additional_image_sizes[ $s ]['width'], $_wp_additional_image_sizes[ $s ]['height'], );
			}
		}

		$current_size = get_option('easy_t_image_size');
		
		foreach( $sizes as $size => $atts ){
			$selected = ( 0 === strcmp($current_size, $size) )
						? 'selected="selected"'
						: $size . '';
						
			// create the option label
			// should be: [Size Name] - [WidthxHeight]
			$size_name = str_replace( array('-', '_'), array(' ', ' '), $size );
			$size_name = ucwords($size_name);
			$size_dimensions = implode( 'x', $atts );
			$label = $size_name . ' - ' . $size_dimensions;
			
			// output the <option> tag
			printf( '<option value="%s" %s>%s</option>', esc_attr($size), esc_html($selected), esc_html($label) );
		}
	}
	
	function get_typography_values($pattern, $default_value = '')
	{
		$keys = array();
		$values = array();
		$keys[] = 'font_size';
		$keys[] = 'font_family';
		$keys[] = 'font_style';
		$keys[] = 'font_color';
		foreach($keys as $key) {			
			$option_key = str_replace('*', $key, $pattern);
			$values[$key] = get_option($option_key, $default_value);
		}
		return $values;
	}
	
	function easy_t_bust_options_cache()
	{
		delete_transient('_easy_t_webfont_str');
		delete_transient('_easy_t_testimonial_style');
		
		//this should flush our frontend cache.
		add_action('admin_init', array($this, 'easy_t_clear_cache') );
	}	
	
	//some functions for theme output
	function get_theme_group_label($theme_group)
	{
		reset($theme_group);
		$first_key = key($theme_group);
		$group_label = $theme_group[$first_key];
		if ( ($dash_pos = strpos($group_label, ' -')) !== FALSE && ($avatar_pos = strpos($group_label, 'Avatar')) === FALSE ) {
			$group_label = substr($group_label, 0, $dash_pos);
		}
		return $group_label;
	}
	
	//load all easy_t transients
	//fix the cache keys for delete_transient function
	//loop through cached items and delete them
	function easy_t_clear_cache(){
		//initialize counter
		$counter = 0;
	
		global $wpdb;
		$sql = "SELECT `option_name` AS `name`, `option_value` AS `value`
				FROM  $wpdb->options
				WHERE `option_name` LIKE '%transient_easy_t%'
				ORDER BY `option_name`";

		$results = $wpdb->get_results( $sql );
		$transients = array();
		
		//loop through found transients and try to delete them
		foreach ( $results as $result )
		{
			//remove _transient_ from the transient key name
			$cache_key = str_replace("_transient_", "", $result->name);
			
			//delete the transient
			$success = delete_transient($cache_key);
			
			//keep track of how many we've deleted
			$counter ++;
			
		}
		
		//let them know what you did!
		if(empty($results)){
			$this->messages[] = "No cached items to flush.";
		} else {
			//pluralize if deleting more than one item
			$string = "item";
			
			if( $counter > 1 ){
				$string = "items";
			}
			$this->messages[] = "Successfully flushed {$counter} {$string} from the cache.";
		}
	}
	
	function array_put_to_position(&$array, $object, $position, $name = null)
	{
			$count = 0;
			$return = array();
			foreach ($array as $k => $v)
			{  
					// insert new object
					if ($count == $position)
					{  
							if (!$name) $name = $count;
							$return[$name] = $object;
							$inserted = true;
					}  
					// insert old object
					$return[$k] = $v;
					$count++;
			}  
			if (!$name) $name = $count;
			if (!$inserted) $return[$name];
			$array = $return;
			return $array;
	}
	
	function array_put_to_position_numeric(&$array, $object, $position)
	{
			$count = 0;
			$return = array();
			foreach ($array as $k => $v)
			{  
					// insert new object
					if ($count == $position)
					{  
						$return[] = $object;
						$inserted = true;
						$count++;
					}  
					// insert old object
					$return[] = $v;
					$count++;
			}  
			$array = $return;
			return $array;
	}
	
	function rename_admin_menu($old_label, $new_label, $match_slug = '')
	{
		global $menu;
		end ($menu);
		while (prev($menu)){
			$value = $menu[key($menu)][0];
			if( strcmp($value, $old_label ) == 0 ) {

				// enforce match on slug (if one was specified) to keep from 
				// messing with other plugin's using the same menu label
				$slug = $menu[key($menu)][2];
				if ( !empty($match_slug) && 
					 0 != strcmp($match_slug, $slug) ) {
					continue;
				}
				
				// NOTE: $menu[key($menu)] is an array. str_replace will replace 
				// inside every value in the array and then return a new array
				$menu[key($menu)] = str_replace($old_label, $new_label, $menu[key($menu)]);
				break;
			}
		}
	}

	function rename_admin_submenu($old_label, $new_label)
	{
		global $submenu;
		$top_level_slug = 'edit.php?post_type=testimonial';
		$found_index = $this->find_submenu_pos_by_label($old_label);
		if ( $found_index >= 0 ) {
			$submenu[$top_level_slug][$found_index][0] = $new_label;
			$submenu[$top_level_slug][$found_index][3] = $new_label;
		}	
	}

	function find_submenu_pos_by_label($label)
	{
		global $submenu;		
		$top_level_slug = 'edit.php?post_type=testimonial';
		$found_index = -1;
		foreach( $submenu[$top_level_slug] as $index => $sm ) {
			if ( $label == $sm[0] ) {
				return $index;
			}
		}
		return -1;
	}
	
} // end class