<?php
class easyTestimonialThemeOptions extends easyTestimonialOptions{
	var $tabs;
	var $config;	
	
	function __construct($config){			
		//call register settings function
		add_action( 'admin_init', array($this, 'register_settings'));	
		add_action( 'wp_ajax_easy_testimonials_render_preview_html', array($this, 'render_testimonial_preview'));
		
		//assign config
		$this->config = $config;		
	}
	
	function register_settings(){		
		//register our settings				
	
		/* Theme selection */
		register_setting( 'easy-testimonials-style-settings-group', 'testimonials_style' );
	}
	
	function render_settings_page()
	{
		//instantiate tabs object for output basic settings page tabs
		$tabs = new GP_Sajak( array(
			'header_label' => 'Theme Settings',
			'settings_field_key' => 'easy-testimonials-style-settings-group', // can be an array			
		) );		
		
		$this->settings_page_top();
		$this->setup_basic_tabs($tabs);
		$this->settings_page_bottom();
	}
	
	function output_theme_options(){			
		$themes = $this->config->load_theme_array();
		
		//load currently selected theme
		$current_theme = get_option('testimonials_style');
		?>
		
		<h3>Style &amp; Theme Options</h3>
		<p class="description">Select which style you want to use.  If 'No Style' is selected, only your Theme's CSS, and any Custom CSS you've added, will be used.</p>
				
		<table class="form-table easy_t_options">
			<tr>
				<td>
					<fieldset>
						<legend>Select Your Theme</legend>
						<select name="testimonials_style" id="testimonials_style">	
							<?php foreach($themes as $group_key => $theme_group): ?>
							<?php $group_label = $this->get_theme_group_label($theme_group); ?>									
								<optgroup  label="<?php echo esc_attr($group_label);?>">
									<?php foreach($theme_group as $key => $theme_name): ?>
										<option value="<?php echo esc_attr($key) ?>" <?php if($current_theme == $key): echo 'selected="SELECTED"'; endif; ?>><?php echo wp_kses_post($theme_name); ?></option>
									<?php endforeach; ?>
								</optgroup>
							<?php endforeach; ?>
						</select>
					</fieldset>
					
					<h4>Preview Selected Theme</h4>
					<p class="description">Please note: your Theme's CSS may impact the appearance.</p>
					<p><strong>Current Saved Theme Selection:</strong>  <?php echo esc_html(ucwords(str_replace('-', ' - ', str_replace('_',' ', str_replace('-style', '', $current_theme))))); ?></p>
					<div id="easy_t_preview" class="easy_t_preview">
						<p class="easy_testimonials_not_registered" style="display: none; margin-bottom: 20px;"><a href="https://goldplugins.com/our-plugins/easy-testimonials-details/upgrade-to-easy-testimonials-pro/?utm_source=themes_preview"><?php esc_html_e('This Theme Requires Pro! Upgrade to Easy Testimonials Pro now', 'easy-testimonials');?></a> <?php esc_html_e('to unlock all 75+ themes!', 'easy-testimonials');?> </p>
						<div id="easy_t_preview_inner" class="easy_t_preview_inner">
							<p>Loading..</p>
						</div>
					</div>
				</td>
			</tr>
		</table>
		<?php
	}
	
	function setup_basic_tabs($tabs){	
		$this->tabs = $tabs;
	
		$this->tabs->add_tab(
			'theme_options', // section id, used in url fragment
			'Theme Options', // section label
			array($this, 'output_theme_options'), // display callback
			array(
				'class' => 'extra_li_class', // extra classes to add sidebar menu <li> and tab wrapper <div>
				'icon' => 'paint-brush' // icons here: http://fontawesome.io/icons/
			)
		);
		
		$this->tabs->display();
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
	
	function render_testimonial_preview()
	{
		$testimonial_image_size = get_option('easy_t_image_size');
		
		$new_theme = filter_input(INPUT_POST, 'theme', FILTER_SANITIZE_STRING);
		if ( empty($new_theme) ) {
			wp_die( '' );
		}
		
		$gpt = new GP_Testimonial();
		$img_url = plugins_url('../../include/assets/img/mystery-person-v4.png', __FILE__);
		if ( 
			strpos($new_theme, 'shout_out') !== false 
			|| strpos($new_theme, 'highlights') !== false 
		) {
			$img_url = plugins_url('../../include/assets/img/mystery-person-v5.png', __FILE__);			
		}
		
		$stag = new GP_SmartTextAvatarGenerator();
		$img_classes = 'attachment-'.$testimonial_image_size.' wp-post-image easy_testimonial_fallback';
		$client_name = $this->generate_client_name();
		$image_tag = $this->config->smart_text_avatar_generator->get_image_tag($client_name['full_name'], 150, 150, $img_classes);
		
		$view_vars = (object) array(
			'display_testimonial' => array(
			'date' => date('M d, Y'),
			'content' => '<p>I looked at several testimonial plugins, and Easy Testimonials was by far the best, most user friendly and customizable plugin I found (and a reasonable price).</p>',
				'id' => 8,
				'rating' => '<p class="easy_t_ratings" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating"><meta itemprop="worstRating" content = "1"/><span itemprop="ratingValue" >5</span>/<span itemprop="bestRating">5</span> Stars.</p>',
				'num_stars' => '5',
				//'image' => sprintf('<img class="attachment-easy_testimonial_thumb wp-post-image easy_testimonial_mystery_person" alt="default image" src="%s" />', $img_url),
				'image' => $image_tag,
				'client' => $client_name['full_name'],
				'position' => $client_name['last_name'] . ' Design',
				'other' => 'Easy Testimonials Pro',			
				'title' => 'Support is second to none',
			),
			'attribute_classes' => ' show_thumbs hide_title show_position show_date stars_rating show_other ',
			'output_theme' => 'style-' . $new_theme,
			'width_value' => '',
			'width_style' => '',
			'show_view_more' => false,
			'testimonial_metadata' =>
			array (
				'show_the_client' => true,
				'show_the_position' => true,
				'show_the_other' => true,
				'show_the_date' => true,
				'show_the_rating' => true,
			),
			'atts' => array (
				'testimonials_link' => false,
				'show_title' => 1,
				'count' => -1,
				'body_class' => 'testimonial_body',
				'author_class' => 'testimonial_author',
				'id' => '8',
				'use_excerpt' => false,
				'reveal_full_content' => false,
				'category' => '',
				'show_thumbs' => true,
				'short_version' => false,
				'orderby' => 'date',
				'order' => 'DESC',
				'show_rating' => 'stars',
				'paginate' => false,
				'testimonials_per_page' => 10,
				'theme' => 'ash-kudos_style',
				'show_position' => true,
				'show_date' => true,
				'show_other' => true,
				'width' => false,
				'hide_view_more' => 0,
				'meta_data_position' => 'below',
				'output_schema_markup' => '1',
				'word_limit' => false,
			),
			'output_schema_markup' => false,
			'show_thumbs' => true,
			'show_title' => 1,
			
		);
		
		$output = $gpt->render_single_testimonial($view_vars);
		$this->safely_display_testimonial($output);
		wp_die();
	}

	function safely_display_testimonial($output)
	{	
		$output = $this->strip_html_comments($output);
		$tags = wp_kses_allowed_html('post');
		$tags['script'] = true; // have to allow <script> tags for JSON-LD markup
		$tags['div']['style'] = true; // allow div's to have style attributes
		add_filter( 'safe_style_css', array($this, 'allow_display_in_style'));
		echo wp_kses($output, $tags);
		remove_filter( 'safe_style_css', array($this, 'allow_display_in_style'));
	}
	
	// Remove unwanted HTML comments
	function strip_html_comments($content)
	{
		return preg_replace('/<!--(.|\s)*?-->/', '', $content);		
	}
	
	function generate_client_name()
	{
		$fake_data = new GP_Fake_Data_Generator();
		return $fake_data->full_name_array();
	}
}