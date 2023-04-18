<?php
class easyTestimonialShortcodeGeneratorOptions extends easyTestimonialOptions{
	var $tabs;
	var $config;
	
	function __construct($config){	
		//assign config
		$this->config = $config;
	}
	
	function render_settings_page()
	{
		//instantiate tabs object for output basic settings page tabs
		$tabs = new GP_Sajak( array(
			'header_label' => 'Shortcode Generator',
			'settings_field_key' => 'easy-testimonials-shortcode_generator-settings-group', // can be an array		
			'show_save_button' => false, // hide save buttons for all panels   	
		) );		
		
		$this->settings_page_top(false);
		$this->setup_basic_tabs($tabs);
		$this->settings_page_bottom();
	}
	
	function setup_basic_tabs($tabs){	
		$this->tabs = $tabs;
	
		$this->tabs->add_tab(
			'shortcode_generator_page', // section id, used in url fragment
			'Shortcode Generator', // section label
			array($this, 'output_shortcode_generator'), // display callback
			array(
				'class' => 'extra_li_class', // extra classes to add sidebar menu <li> and tab wrapper <div>
				'icon' => 'code' // icons here: http://fontawesome.io/icons/
			)
		);
		$this->tabs->display();
	}
	
	function output_shortcode_generator(){
		?>		
		<p>Using the buttons below, select your desired method and options for displaying Testimonials.</p>
		<p>Instructions:</p>
		<ol>
			<li>Click the Testimonials button, below,</li>
			<li>Pick from the available display methods listed, such as Grid of Testimonials,</li>
			<li>Set the options for your desired method of display,</li>
			<li>Click "Insert Now" to generate the shortcode.</li>
			<li>The generated shortcode will appear in the textarea below - simply copy and paste this into the Page or Post where you would like Testimonials to appear!</li>
		</ol>
		
		<div id="easy-t-shortcode-generator">
		
		<?php 
			$content = "";//initial content displayed in the editor_id
			$editor_id = "easy_t_shortcode_generator";//HTML id attribute for the textarea NOTE hyphens will break it
			$settings = array(
				'tinymce' => false
			);
			wp_editor($content, $editor_id, $settings); 
		?>
		</div>
		<?php
	}
}