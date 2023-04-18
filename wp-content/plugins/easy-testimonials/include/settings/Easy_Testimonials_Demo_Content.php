<?php

	class Easy_Testimonials_Demo_Content
	{
		public function get_page($pagename)
		{
			$filename = plugin_dir_path( dirname( __FILE__ ) ) . 'settings/demo_content/' . $pagename . '.html';
			if ( file_exists($filename) ) {
				$content = file_get_contents($filename);
				return sprintf('<div class="admin_demo_content_wrapper">%s</div>', $content);
			}
			return '';
		}
		
		function check_icon()
		{
			echo '<div class="easy_testimonials_checkbox"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z"/></svg></div>';
		}
		
		function get_upgrade_url($campaign = 'freeplugin', $medium = 'general')
		{
			global $wp_version;
			//$file_data = get_file_data('/some/real/path/to/your/plugin', array('Version'), 'plugin');
			$base_url = 'https://goldplugins.com/special-offers/upgrade-to-easy-testimonials-pro-2/?';
			
			$params = array(
				'utm_source' => 'WordPress',
				'utm_campaign' => $campaign,
				'utm_medium' => $medium,
				'wp_version' => $wp_version,
				'plugin_version' => $this->get_plugin_version(),
			);
			// TODO: add days since install (buckets?)
			
			return $base_url . http_build_query($params);		
		}
		
		function get_plugin_version()
		{
			$cached_val = wp_cache_get( 'easy_testimonials_free_version' );
			if ( !empty($cached_val) ) {
				return $cached_val;
			}
			
			$all = get_plugins();
			if ( empty($all['easy-testimonials/easy-testimonials.php']) ) {
				return '';
			}

			if ( empty($all['easy-testimonials/easy-testimonials.php']['Version']) ) {
				return '';
			}
			
			$version = $all['easy-testimonials/easy-testimonials.php']['Version'];
			wp_cache_set( 'easy_testimonials_free_version', $version );
			return $version;
		}		

		public function get_allowed_post_tags()
		{
			$tags = wp_kses_allowed_html('post');
			$tags['style'] = true;
			$tags['select'] = true;
			$tags['input'] = true;
			$tags['label'] = true;
			$tags['button'] = true;

			$svg_args = array(
				'svg'   => array(
					'class'           => true,
					'aria-hidden'     => true,
					'aria-labelledby' => true,
					'role'            => true,
					'xmlns'           => true,
					'width'           => true,
					'height'          => true,
					'viewbox'         => true // <= Must be lower case!
				),
				'g'     => array( 'fill' => true ),
				'title' => array( 'title' => true ),
				'path'  => array( 
					'd'               => true, 
					'fill'            => true  
				)
			);
			$tags = array_merge($tags, $svg_args);
			return $tags;
		}
		
		public function testimonial_forms_page()
		{
			echo wp_kses( $this->get_page('testimonial_forms'), $this->get_allowed_post_tags()  );
			echo wp_kses( $this->collect_testimonials_demo_modal(), $this->get_allowed_post_tags()  );
		}
		
		public function import_export_page()
		{
			echo wp_kses( $this->get_page('import_export'), $this->get_allowed_post_tags() );
			echo wp_kses( $this->import_export_demo_modal(), $this->get_allowed_post_tags() );
		}
		
		public function text_styles_page()
		{
 			echo wp_kses( $this->get_page('text_styles'), $this->get_allowed_post_tags() );
			echo wp_kses( $this->text_styles_demo_modal(), $this->get_allowed_post_tags() );
		}
		
		public function submission_form_settings_page()
		{
			echo wp_kses( $this->get_page('text_styles'), $this->get_allowed_post_tags() );
			echo wp_kses( $this->collect_testimonials_demo_modal(), $this->get_allowed_post_tags() );
		}
		
		function collect_testimonials_demo_modal()
		{
			ob_start();
			?>
			<div class="easy_testimonials_demo_modal">
				<div class="easy_testimonials_demo_modal_top">
					<h2>Collect New Testimonials Automatically</h2>
					<p class="subhead"><strong>The free version of Easy Testimonials does not support Testimonial Collection Forms.</strong></p>
					<p>Once you upgrade to Easy Testimonials Pro, you will be able to use Testimonial&nbsp;Collection&nbsp;Forms to collect new testimonials automatically.</p>		
					<ul class="easy_testimonials_feature_list easy_testimonials_feature_list_left">
						<li><?php $this->check_icon(); ?> Automatically Collect New Testimonials</li>
						<li><?php $this->check_icon(); ?> Email Notications for New Testimonials </li>
						<li><?php $this->check_icon(); ?> Approve New Testimonials Before Display</li>
						<li><?php $this->check_icon(); ?> Spam-Prevention (CAPTCHA)</li>
					</ul>
					<ul class="easy_testimonials_feature_list easy_testimonials_feature_list_right">
						<li><?php $this->check_icon(); ?> Create Unlimited Forms</li>
						<li><?php $this->check_icon(); ?> Pre-made Form Templates</li>
						<li><?php $this->check_icon(); ?> Drag-and-Drop Form Builder</li>
						<li><?php $this->check_icon(); ?> Add Forms To Any Page or Post</li>
					</ul>
					<div style="clear:both"></div>
				</div>
				<div class="easy_testimonials_demo_modal_bottom">
					<a class="easy_testimonials_btn" href="<?php echo esc_url($this->get_upgrade_url('placeholders', 'testimonial_forms')); ?>" target="_blank">Upgrade To Easy Testimonials Pro Now</a>
					<p class="easy_testimonials_after_button_text"><em>and start collecting Testimonials!</em></p>
				</div>
			</div>
			<?php
			$output = ob_get_contents();
			ob_end_clean();
			return $output;	
		}			
	
		function text_styles_demo_modal()
		{
			ob_start();
			?>
			<div class="easy_testimonials_demo_modal">
				<div class="easy_testimonials_demo_modal_top">
					<h2>Customize Every Aspect of Your Testimonials</h2>
					<p class="subhead"><strong>The free version of Easy Testimonials does not support Typography Settings.</strong></p>
					<p style="max-width: 510px">Once you upgrade to Easy Testimonials Pro, you will be able to choose the font, color, size, and style of each element of your Testimonials.</p>
					<p class="easy_testimonials_feature_list_heading">Easy Testimonials Pro's Features Include:</p>					
					<ul class="easy_testimonials_feature_list easy_testimonials_feature_list_left">
						<li><?php $this->check_icon(); ?> Typography Settings</li>
						<li><?php $this->check_icon(); ?> 100+ New Themes</li>
						<li><?php $this->check_icon(); ?> Import & Export Your Testimonials</li>
						<li><?php $this->check_icon(); ?> New Testimonial Notifications</li>
					</ul>
					<ul class="easy_testimonials_feature_list easy_testimonials_feature_list_right">
						<li><?php $this->check_icon(); ?> Testimonial Collection Forms</li>
						<li><?php $this->check_icon(); ?> Drag-and-Drop Form Builder</li>
						<li><?php $this->check_icon(); ?> Pre-made Form Templates</li>
						<li><?php $this->check_icon(); ?> More Slideshow Options</li>
					</ul>
					<div style="clear:both"></div>
				</div>
				<div class="easy_testimonials_demo_modal_bottom">
					<a class="easy_testimonials_btn" href="<?php echo esc_url( $this->get_upgrade_url('placeholders', 'text_styles') ); ?>" target="_blank">Upgrade To Easy Testimonials Pro Now</a>
					<p class="easy_testimonials_after_button_text"><em>and start customizing your Testimonials!</em></p>
				</div>
			</div>
			<?php
			$output = ob_get_contents();
			ob_end_clean();
			return $output;	
		}
		
		function import_export_demo_modal()
		{
			ob_start();
			?>
			<div class="easy_testimonials_demo_modal">
				<div class="easy_testimonials_demo_modal_top">
					<h2>Reclaim Your Time - Import&nbsp;&amp;&nbsp;Export Your Testimonials</h2>
					<p class="subhead"><strong>The free version of Easy Testimonials does not support Import&nbsp;&amp;&nbsp;Export.</strong></p>
					<p>Once you upgrade to Easy Testimonials Pro, you will be able to Import&nbsp;&amp;&nbsp;Export your Testimonials from any file type using our wizard.</p>
					<p class="easy_testimonials_feature_list_heading">Easy Testimonials Pro's Features Include:</p>					
					<ul class="easy_testimonials_feature_list easy_testimonials_feature_list_left">
						<li><?php $this->check_icon(); ?> Import Your Testimonials From Any File</li>
						<li><?php $this->check_icon(); ?> Export Testimonials to CSV files</li>
						<li><?php $this->check_icon(); ?> 100+ Professionally Themes</li>
						<li><?php $this->check_icon(); ?> Typography Settings</li>
					</ul>
					<ul class="easy_testimonials_feature_list easy_testimonials_feature_list_right">
						<li><?php $this->check_icon(); ?> Testimonial Collection Forms</li>
						<li><?php $this->check_icon(); ?> Drag-and-Drop Form Builder</li>
						<li><?php $this->check_icon(); ?> Pre-made Form Templates</li>
						<li><?php $this->check_icon(); ?> New Testimonial Notifications</li>
					</ul>
					<div style="clear:both"></div>
				</div>
				<div class="easy_testimonials_demo_modal_bottom">
					<a class="easy_testimonials_btn" href="<?php echo esc_url( $this->get_upgrade_url('placeholders', 'import_export') ); ?>" target="_blank">Upgrade To Easy Testimonials Pro Now</a>
					<p class="easy_testimonials_after_button_text"><em>and import your Testimonials in minutes!</em></p>
				</div>
			</div>
			<?php
			$output = ob_get_contents();
			ob_end_clean();
			return $output;	
		}	
	}
	
	


