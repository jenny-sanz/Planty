<?php
class easyTestimonialBasicOptions extends easyTestimonialOptions
{
	var $tabs;
	var $config;
	
	function __construct($config)
	{
		//call register settings function
		add_action( 'admin_init', array($this, 'register_settings'));	
		
		//assign config
		$this->config = $config;
			
		//if the flush cache now button has been clicked
		if (isset($_GET['flush-cache-now']) && $_GET['flush-cache-now'] == 'true'){
			//go ahead and flush the cache
			add_action('admin_init', array($this, 'easy_t_clear_cache') );
		}
	}
	
	function register_settings()
	{
		//register our settings
		
		/* Basic options */
		register_setting( 'easy-testimonials-settings-group', 'easy_t_custom_css' );
		register_setting( 'easy-testimonials-settings-group', 'easy_t_custom_tablet_css' );
		register_setting( 'easy-testimonials-settings-group', 'easy_t_custom_phone_css' );
		register_setting( 'easy-testimonials-settings-group', 'easy_t_cache_buster', array($this, 'easy_t_bust_options_cache') );
		register_setting( 'easy-testimonials-settings-group', 'easy_t_show_in_search' );
		register_setting( 'easy-testimonials-settings-group', 'easy_t_allow_tags' );
		
		/* Item Reviewed */
		register_setting( 'easy-testimonials-settings-group', 'easy_t_use_global_item_reviewed' );
		register_setting( 'easy-testimonials-settings-group', 'easy_t_global_item_reviewed' );		
		
		/* Cache */
		register_setting( 'easy-testimonials-settings-group', 'easy_t_cache_time' );
		register_setting( 'easy-testimonials-settings-group', 'easy_t_cache_enabled' );
		
		/* Review Markup */		
		register_setting( 'easy-testimonials-settings-group', 'easy_t_output_schema_markup' );
	}
	
	function render_settings_page()
	{
		//instantiate tabs object for output basic settings page tabs
		$tabs = new GP_Sajak( array(
			'header_label' => 'Basic Settings',
			'settings_field_key' => 'easy-testimonials-settings-group', // can be an array			
		) );		
		
		$this->settings_page_top();
		$this->setup_basic_tabs($tabs);
		$this->settings_page_bottom();
	}
		
	function setup_basic_tabs($tabs)
	{
		$this->tabs = $tabs;
	
		$this->tabs->add_tab(
			'basic_options', // section id, used in url fragment
			'Basic Options', // section label
			array($this, 'output_basic_options'), // display callback
			array(
				'class' => 'extra_li_class', // extra classes to add sidebar menu <li> and tab wrapper <div>
				'icon' => 'gear' // icons here: http://fontawesome.io/icons/
			)
		);
		$this->tabs->add_tab(
			'itemreviewed_options', // section id, used in url fragment
			'Item Reviewed Options', // section label
			array($this, 'output_itemreviewed_options'), // display callback
			array(
				'class' => 'extra_li_class', // extra classes to add sidebar menu <li> and tab wrapper <div>
				'icon' => 'tag' // icons here: http://fontawesome.io/icons/
			)
		);
		$this->tabs->add_tab(
			'cache_options', // section id, used in url fragment
			'Cache Options', // section label
			array($this, 'output_cache_options'), // display callback
			array(
				'class' => 'extra_li_class', // extra classes to add sidebar menu <li> and tab wrapper <div>
				'icon' => 'rocket' // icons here: http://fontawesome.io/icons/
			)
		);
		$this->tabs->display();
	}
	
	function output_basic_options()
	{
		?>
		<h3>Basic Options</h3>
			
		<p>Use the below options to control whether Testimonials are shown in public lists such as Search, whether HTML tags are allowed in Testimonials, and Custom CSS.</p>		
		
		<table class="form-table">
			<tr valign="top">
				<td><input type="checkbox" name="easy_t_show_in_search" id="easy_t_show_in_search" value="1" <?php if(get_option('easy_t_show_in_search', true)){ ?> checked="CHECKED" <?php } ?>/>
				<label for="easy_t_show_in_search">Show in Search</label>
				<p class="description">If checked, we will Show your Testimonials in the public site search in WordPress.</p>
				</td>
			</tr>
		</table>
		
		<table class="form-table">
			<tr valign="top">
				<td>
					<label>Allow Tags in HTML: <?php echo defined('EASY_TESTIMONIALS_ALLOW_HTML') ? esc_html__('Yes', 'easy-testimonials') : esc_html__('No', 'easy-testimonials');  ?></label>
					<?php if ( defined('EASY_TESTIMONIALS_ALLOW_HTML') ): ?>
					<p class="danger warning description" style="color: red; font-weight: bold;"><?php esc_attr_e('Danger: Allowing HTML in your output has security implications and is not recommended.', 'easy-testimonials'); ?></p>
					<?php else: ?>
					<p><?php esc_html_e('Including HTML in Testimonials is not allowed for security reasons. It can be enabled by defining the constant EASY_TESTIMONIALS_ALLOW_HTML (highly unrecommended).', 'easy-testimonials'); ?></p>
					<?php endif; ?>
				</td>
			</tr>
		</table>
		
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="easy_t_custom_css">Custom CSS (All Screens)</a></th>
				<td><textarea name="easy_t_custom_css" id="easy_t_custom_css" rows="8" cols="80"><?php echo esc_attr(get_option('easy_t_custom_css', '')); ?></textarea>
				<p class="description">Input any Custom CSS you want to use here.  <br/>For a list of available classes, click <a href="https://goldplugins.com/documentation/easy-testimonials-documentation/html-css-information-for-easy-testimonials/" target="_blank">here</a>.</p></td>
			</tr>
		</table>
		
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="easy_t_custom_tablet_css">Custom CSS (Tablet, 768px and below)</a></th>
				<td><textarea name="easy_t_custom_tablet_css" id="easy_t_custom_tablet_css" rows="8" cols="80"><?php echo esc_attr(get_option('easy_t_custom_tablet_css', '')); ?></textarea>
				<p class="description">Input any Custom CSS you want to use here, for screens that are 768px wide or narrower.  <br/>For a list of available classes, click <a href="https://goldplugins.com/documentation/easy-testimonials-documentation/html-css-information-for-easy-testimonials/" target="_blank">here</a>.</p></td>
			</tr>
		</table>
		
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="easy_t_custom_phone_css">Custom CSS (Phone, 320px and below)</a></th>
				<td><textarea name="easy_t_custom_phone_css" id="easy_t_custom_phone_css" rows="8" cols="80"><?php echo esc_attr(get_option('easy_t_custom_phone_css', '')); ?></textarea>
				<p class="description">Input any Custom CSS you want to use here, for screens that are 320px wide or narrower.  <br/>For a list of available classes, click <a href="https://goldplugins.com/documentation/easy-testimonials-documentation/html-css-information-for-easy-testimonials/" target="_blank">here</a>.</p></td>
			</tr>
		</table>
		<?php
	}
	
	function output_itemreviewed_options()
	{
		?>
		<h3>Item Reviewed Options</h3>		
		<table class="form-table">
			<tr valign="top">				
				<td><input type="checkbox" name="easy_t_output_schema_markup" id="easy_t_output_schema_markup" value="1" <?php if(get_option('
		easy_t_output_schema_markup', true)){ ?> checked="CHECKED" <?php } ?>/>
				<label for="easy_t_output_schema_markup">Output Review Markup</label>
				<p class="description">If checked, Schema.org review markup will be output using <a href="http://json-ld.org" target="_blank">JSON-LD</a>. This will allow search engines like Google and Bing crawl your data, improving your website's SEO.</p>
				</td>
			</tr>
		</table>
		
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="easy_t_global_item_reviewed">Global Item Reviewed</label></th>
				<td><input type="text" name="easy_t_global_item_reviewed" id="easy_t_global_item_reviewed" value="<?php echo esc_attr(get_option('easy_t_global_item_reviewed', '')); ?>" />
				<p class="description">If nothing is set on the individual Testimonial, this will be used as the itemReviewed value for the Testimonial.  This is so people, and Search Engines, know what your Testimonials are all about!</p>
				</td>
			</tr>
			<tr valign="top">
				<td><input type="checkbox" name="easy_t_use_global_item_reviewed" id="easy_t_use_global_item_reviewed" value="1" <?php if(get_option('easy_t_use_global_item_reviewed', false)){ ?> checked="CHECKED" <?php } ?>/>
				<label for="easy_t_use_global_item_reviewed">Use Global Item Reviewed</label>
				<p class="description">If checked, and an individual Testimonial does not have a value for the Item being Reviewed, we will use the Global Item Reviewed setting instead.</p>
				</td>
			</tr>
		</table>
		<?php
	}
	
	function output_cache_options()
	{
		?>
		<h3>Cache Options</h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="easy_t_cache_time">Cache Time</label></th>
				<td><input type="text" name="easy_t_cache_time" id="easy_t_cache_time" value="<?php echo esc_attr(get_option('easy_t_cache_time', 900)); ?>" />
				<p class="description">The time, in seconds, to keep items in the cache. The default value is 15 minutes (900 seconds.)</p>
				</td>
			</tr>
			<tr valign="top">
				<td><input type="checkbox" name="easy_t_cache_enabled" id="easy_t_cache_enabled" value="1" <?php if(get_option('easy_t_cache_enabled', true)){ ?> checked="CHECKED" <?php } ?>/>
				<label for="easy_t_cache_enabled">Use Caching</label>
				<p class="description">To disable caching, uncheck this option.  This is good for in development websites.</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="easy_t_cache_enabled">Flush Cache Now</label></th>
				<td>			
					<p class="submit">
						<a href="?page=easy-testimonials-settings&flush-cache-now=true" class="button-primary" title="<?php esc_attr_e('Click to Flush Cache Now', 'easy-testimonials') ?>"><?php esc_attr_e('Click to Flush Cache Now', 'easy-testimonials') ?></a>
					</p>
				</td>
			</tr>
		</table>	
		<?php
	}
}
