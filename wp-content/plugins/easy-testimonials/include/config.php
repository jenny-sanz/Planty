<?php
class easyTestimonialsConfig{	
	var $cycle_transitions;
	var $dir_path;
	var $url_path;
	var $is_pro;
	var $do_export;
	var $cache_time;
	var $cache_enabled;
	var $typography_cache_key;
	var $content_filter_has_run = false;

	function __construct(){		
		$this->cycle_transitions = $this->load_cycle_transitions();
		$this->dir_path = plugin_dir_path( __FILE__ );
		$this->url_path = plugin_dir_url( __FILE__ );
		$this->is_pro = isValidKey();
		$this->do_export = ( isset($_POST['_easy_t_do_export']) && $_POST['_easy_t_do_export'] == '_easy_t_do_export' ) ? true : false;
		$this->cache_time = get_option('easy_t_cache_time', 900); //default to 15 minutes
		$this->cache_enabled = get_option('easy_t_cache_enabled', true); //default to true
		$this->smart_text_avatar_generator = new GP_SmartTextAvatarGenerator();
	}

	function load_theme_array()
	{
		$themes = get_transient('easy_testimonials_theme_listx');
		if ( empty($themes) ) {
			// array of free themes that are available
			$theme_array = array(
				'accolades_style' => array(
					'red-accolades_style' => 'Accolades Style - Red',
					'blue-accolades_style' => 'Accolades Style - Blue',
					'black-accolades_style' => 'Accolades Style - Black',
					'grey-accolades_style' => 'Accolades Style - Grey',
					'green-accolades_style' => 'Accolades Style - Green',
				),
				'merit_style' => array(
					'green-merit_style' => 'Merit Style - Green',
					'red-merit_style' => 'Merit Style - Red',
					'orange-merit_style' => 'Merit Style - Orange',
					'purple-merit_style' => 'Merit Style - Purple',
					'grey-merit_style' => 'Merit Style - Grey',
				),
				'classic_style' => array(
					'light_grey-classic_style' => 'Classic Style - Light Grey',
					'red-classic_style' => 'Classic Style - Red',
					'gold-classic_style' => 'Classic Style - Gold',
					'blue-classic_style' => 'Classic Style - Blue',
					'dark_grey-classic_style' => 'Classic Style - Dark Grey',
				),
				'compliments_style' => array(
					'dark_grey-compliments_style' => 'Compliments Style - Dark Grey',
					'blue-compliments_style' => 'Compliments Style - Blue',
					'green-compliments_style' => 'Compliments Style - Green',
					'light_grey-compliments_style' => 'Compliments Style - Light Grey',
					'red-compliments_style' => 'Compliments Style - Red',
				),
				'ribbon_style' => array(
					'green-ribbon_style' => 'Ribbon Style - Green',
					'blue-ribbon_style' => 'Ribbon Style - Blue',
					'teal-ribbon_style' => 'Ribbon Style - Teal',
					'gold-ribbon_style' => 'Ribbon Style - Gold',
					'grey-ribbon_style' => 'Ribbon Style - Grey',
				),
				'standard_themes' => array(
					'default_style' => 'Standard Themes - Default Style',
					'dark_style' => 'Standard Themes - Dark Style',
					'light_style' => 'Standard Themes - Light Style',
					'clean_style' => 'Standard Themes - Clean Style',
					'no_style' => 'Standard Themes - No Style'
				)
			);
			$themes = apply_filters('easy_testimonials_theme_array', $theme_array);
			set_transient('easy_testimonials_theme_list', $themes, 3600); // cache for one hour
		}
		return $themes;
	}
	
	function load_cycle_transitions(){
		$cycle_transitions = array(
			'scrollHorz' => 
				array(
					'label' => 	'Horizontal Scroll',
					'pro'	=>	false
				),
			'scrollVert' => 
				array(
					'label' => 	'Vertical Scroll',
					'pro'	=>	true
				),
			'fade' => 
				array(
					'label' => 	'Fade',
					'pro'	=>	false
				),
			'fadeout' => 
				array(
					'label' => 	'Fade Out',
					'pro'	=>	true
				),
			'carousel' => 
				array(
					'label' => 	'Carousel',
					'pro'	=>	true
				),
			'flipHorz' => 
				array(
					'label' => 	'Horizontal Flip',
					'pro'	=>	true
				),
			'flipVert' => 
				array(
					'label' => 	'Vertical Flip',
					'pro'	=>	true
				),
			'tileSlide' => 
				array(
					'label' => 	'Tile Slide',
					'pro'	=>	true
				)
		);	

		return apply_filters('easy_testimonials_transitions_array', $cycle_transitions);
	}
	
	function set_content_flag($new_value)
	{
		$this->content_filter_has_run = $new_value;
	}
	
	function content_filter_has_run()
	{
		return $this->content_filter_has_run;
	}
}	