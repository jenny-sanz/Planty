<?php
if ( !class_exists('GP_Sajak') ):

	class GP_Sajak
	{
		var $_tabs = array();
		
		function __construct( $options = array() )
		{
			$this->set_options($options);
			add_action( 'admin_enqueue_scripts', array($this, 'setup_scripts') );
		}
		
		function set_options($options)
		{
			$defaults = array(
				'header_label' => 'Settings',
				'settings_field_key' => '',
				'extra_buttons_header' => array(),
				'extra_buttons_footer' => array(),
				'show_save_button' => true
			);
			$this->options = array_merge($defaults, $options);
		}
		
		function setup_scripts()
		{
			wp_register_script(
				'gp_sajak',
				plugins_url('assets/js/gp_sajak.js', __FILE__),
				array( 'jquery', 'jquery-ui-tabs' ),
				false,
				true
			);
			wp_enqueue_script('gp_sajak');
			
			$css_url = plugins_url( 'assets/css/gp_sajak.css' , __FILE__ );
			wp_register_style('gp-sajak', $css_url);
			wp_enqueue_style('gp-sajak');
			wp_style_add_data( 'gp-sajak', 'rtl', 'replace' );

			$css_url = plugins_url( 'assets/font-awesome/css/font-awesome.min.css' , __FILE__ );
			wp_register_style('gp-sajak-font-awesome', $css_url);
			wp_enqueue_style('gp-sajak-font-awesome');
		}
		
		function add_tab($id, $label, $callback = false, $options = array())
		{
			$this->_tabs[] = compact('id', 'label', 'options', 'callback');
		}

		function display()
		{
			$this->header();
			$this->sidebar();
			echo '<div class="gp_sajak_main">';
			foreach($this->_tabs as $tab)			
			{
				$tab_content = '';
				if ( is_callable($tab['callback']) ) 
				{
					ob_start();
					call_user_func($tab['callback']);
					$tab_content = ob_get_contents();
					ob_end_clean();							   					
				}
				$add_class = !empty($tab['options']) && !empty($tab['options']['class'])
							 ? $tab['options']['class']
							 : '';

				$show_save_button = !empty($tab['options']) && isset($tab['options']['show_save_button'])
									? $tab['options']['show_save_button']
									: true;
									
				$show_save_attr = !$show_save_button 
								  ? 'data-show-save-button="0"'
								  : '';
				printf( '<div id="tab-%s" class="gp_sajak_tab %s" %s><div class="gp_sajak_tab_body">%s</div></div>',
					    esc_attr($tab['id']), esc_attr($add_class), esc_attr($show_save_attr), wp_kses($tab_content, $this->tab_content_allowed_tags()) );
			}
			echo '</div>';
			echo esc_html( $this->footer() );
		}

		function header()
		{
			// WordPress attaches admin notices below the first <h2> it finds.
			// Our <h2> is inside our layout, so we need to give it a blank <h2>
			// that it can target instead. Else the flash messages will end up
			// inside the Sajak layout!			
			echo '<h2 style="display:none"></h2>';
			
			echo '<form method="post" action="options.php" enctype="multipart/form-data">';

			// This prints out all hidden setting fields
			if ( !empty($this->options['settings_field_key']) ) {
				$field_keys = is_array($this->options['settings_field_key'])
							  ? $this->options['settings_field_key']
							  : array( $this->options['settings_field_key'] );
				foreach ($field_keys as $field_key)
				{
					settings_fields( $field_key );
				}
			}

			echo '<div class="gp_sajak">';// closed in footer()
			echo '<div class="gp_sajak_header">';

			echo '<div class="gp_sajak_buttons">';

			if ( !empty($this->options['extra_buttons_header']) ) {
				$this->output_extra_buttons( $this->options['extra_buttons_header'] );
			}
			
			if ( $this->options['show_save_button'] ) {
				echo '<div class="gp_sajak_save_button">';
				submit_button();
				echo '</div>';
			}

			echo '</div>'; // end gp_sajak_buttons

			if ( !empty($this->options['header_label']) ) {
				printf( '<h2>%s</h2>', esc_html($this->options['header_label']) );
			}
			
			echo '</div>';
			echo '<div class="gp_sajak_body">';
		}

		function sidebar()
		{
			echo '<div class="gp_sajak_sidebar">';
				echo '<ul class="gp_sajak_menu">';
				foreach($this->_tabs as $tab)			
				{
					$add_class = !empty($tab['options']) && !empty($tab['options']['class'])
								 ? $tab['options']['class']
								 : '';			

					// add font-awesome icon class if one is specified
					$icon = '';
					if ( !empty($tab['options']) && !empty($tab['options']['icon']) ) {
						$icon = sprintf( '<span class="fa fa-%s"></span>', esc_attr($tab['options']['icon']) );
					}
					
					printf( '<li id="gp_sajak_menu_label-%s" class="gp_sajak_menu_label %s"><a href="#tab-%s">%s<span class="label_text">%s</span></a></li>',
							esc_attr($tab['id']), esc_attr($add_class), esc_attr($tab['id']), wp_kses_post($icon), wp_kses_post($tab['label']) );
				}
				echo '</ul>';
			echo '</div>';
		}

		function footer()
		{
			echo '</div><!-- end .gp_sajak_body -->'; // opened in header()
			echo '<div class="gp_sajak_footer">';

			echo '<div class="gp_sajak_buttons">';

			if ( !empty($this->options['extra_buttons_footer']) ) {
				$this->output_extra_buttons( $this->options['extra_buttons_footer'] );
			}
			
			if ( $this->options['show_save_button'] ) {
				echo '<div class="gp_sajak_save_button">';
				submit_button();
				echo '</div>';
			}

			echo '</div>'; // end gp_sajak_buttons

			echo '</div>';
			echo '</div><!-- end .gp_sajak -->'; // opened in header()

			echo '</form>';
		}
		
		function output_extra_buttons($buttons)
		{
			$button_defaults = array(
				'class' => '',
				'label' => '',
				'url' => '',
				'target' => '_blank'
			);

			foreach($buttons as $btn)
			{
				$btn = array_merge($button_defaults, $btn);
				echo '<div class="gp_sajak_button">';
				printf('<a class="%s button" href="%s" target="%s">%s</a>',
					esc_attr($btn['class']),
					esc_attr($btn['url']),
					esc_attr($btn['target']),
					esc_html($btn['label'])
				);
				echo '</div>';
			}
			
		}

		/*
		 * Expanded list of HTML tags allowed in tab content areas.
		 * For use for sanitization in conjunction with wp_kses()
		 *
		 * @return array List of allowed tags.
		 */
		function tab_content_allowed_tags()
		{
			$global_atts = array(
				'aria-*'              => true,
				'accesskey'           => true,
				'autocapitalize'      => true,
				'autocomplete'        => true,
				'class'               => true,
				'data-*'              => true,
				'hidden'              => true,
				'id'                  => true,
				'inputmode'           => true,
				'itemid'              => true,
				'intemprop'           => true,
				'itemref'             => true,
				'itemscope'           => true,
				'itemtype'            => true,
				'lang'                => true,
				'spellcheck'          => true,
				'style'               => true,
				'tabindex'            => true,
				'title'               => true,
				'translate'           => true,					
			);
			$my_allowed = array(
				'style' => array_merge(
					$global_atts,
					array(
						'type'  => true,
						'media' => true,
						'nonce' => true,
						'title' => true,
					)
				),
				'form'     => array_merge(
					$global_atts,
					array(
						'accept'         => true, // Deprecated.
						'accept-charset' => true,
						'action'         => true,
						'enctype'        => true,
						'method'         => true,
						'name'           => true,
						'novalidate'     => true,
						'target'         => true,
					)
				),
				'input'    => array_merge(
					$global_atts,
					array(
						'accept'         => true,
						'alt'            => true,
						'autocomplete'   => true,
						'autofocus'      => true,
						'capture'        => true,
						'checked'        => true,
						'dirname'        => true,
						'disabled'       => true,
						'form'           => true,
						'formaction'     => true,
						'formenctype'    => true,
						'formmethod'     => true,
						'formnovalidate' => true,
						'formtarget'     => true,
						'height'         => true,
						'list'           => true,
						'max'            => true,
						'maxlength'      => true,
						'min'            => true,
						'minlength'      => true,
						'multiple'       => true,
						'name'           => true,
						'pattern'        => true,
						'placeholder'    => true,
						'readonly'       => true,
						'required'       => true,
						'size'           => true,
						'src'            => true,
						'step'           => true,
						'type'           => true,
						'value'          => true,
						'width'          => true,
					)
				),
				'label'    => array_merge(
					$global_atts,
					array(
						'for'  => true,
						'form' => true, // Deprecated.
					)
				),
				'optgroup' => array_merge(
					$global_atts,
					array(
						'disabled' => true,
						'label' => true,
					)
				),
				'option'   => array_merge(
					$global_atts,
					array(
						'disabled' => true,
						'label'    => true,
						'selected' => true,
						'value'    => true,
					)
				),
				'select'   => array_merge(
					$global_atts,
					array(
						'autofocus' => true,
						'disabled'  => true,
						'form'      => true,
						'multiple'  => true,
						'name'      => true,
						'required'  => true,
						'size'      => true,
					)
				),
				'textarea' => array_merge(
					$global_atts,
					array(
						'autofocus'   => true,
						'cols'        => true,
						'disabled'    => true,
						'form'        => true,
						'maxlength'   => true,
						'minlength'   => true,
						'name'        => true,
						'placeholder' => true,
						'readonly'    => true,
						'required'    => true,
						'rows'        => true,
						'spellcheck'  => true,
						'wrap'        => true,
					)
				),
			);
			$post_allowed = wp_kses_allowed_html( 'post' );
			return array_merge($post_allowed, $my_allowed);
		}
	}
	
endif;//class_exists