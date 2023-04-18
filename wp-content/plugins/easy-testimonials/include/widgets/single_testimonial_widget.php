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
along with Easy Testimonials.  If not, see <http://www.gnu.org/licenses/>.

Shout out to http://www.makeuseof.com/tag/how-to-create-wordpress-widgets/ for the help
*/

class singleTestimonialWidget extends WP_Widget
{	
	var $config;
	var $defaults;

	function __construct(){
		//load config
		$this->config = new easyTestimonialsConfig();
		
		//load defaults
		$this->defaults = array(
			'testimonial_id' => '',
			'title' => '',
			'show_title' => 0,
			'category' => '',
			'use_excerpt' => 0,
			'show_rating' => 'stars',
			'reveal_full_content' => false,
			'show_date' => true,
			'width' => false,
			'show_testimonial_image' => get_option('testimonials_image', true),
			'order' => 'ASC',
			'order_by' => 'date',
			'show_other' => true,
			'theme' => get_option('testimonials_style', 'light_grey-classic_style'),
			'hide_view_more' => 0,
			'output_schema_markup' => get_option('easy_t_output_schema_markup', true)
		);
		
		$widget_ops = array('classname' => 'singleTestimonialWidget', 'description' => 'Displays a Single Testimonial.' );
		parent::__construct('singleTestimonialWidget', 'Easy Testimonials Single Testimonial', $widget_ops);	
	}
		
	function singleTestimonialWidget()
	{
		$this->__construct();
	}

	function form($instance){
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		$testimonial_id = $instance['testimonial_id'];
		$title = $instance['title'];
		$show_title = $instance['show_title'];
		$show_rating = $instance['show_rating'];
		$use_excerpt = $instance['use_excerpt'];
		$reveal_full_content = $instance['reveal_full_content'];
		$category = $instance['category'];
		$show_date = $instance['show_date'];
		$width = $instance['width'];
		$show_testimonial_image = $instance['show_testimonial_image'];
		$order = $instance['order'];
		$order_by = $instance['order_by'];
		$show_other = $instance['show_other'];
		$theme = $instance['theme'];
		$testimonial_categories = get_terms( 'easy-testimonial-category', 'orderby=title&hide_empty=0' );
		$hide_view_more = $instance['hide_view_more'];
		$output_schema_markup = $instance['output_schema_markup'];		
		$ip = $this->config->is_pro;
		
		//load themes
		$themes = $this->config->load_theme_array();
		?>
		<div class="gp_widget_form_wrapper">
			<p class="hide_in_popup">
				<label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>">Widget Title:</label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr($title); ?>" data-shortcode-hidden="1" />
			</p>			
		
			<?php 
				$testimonials = get_posts('post_type=testimonial&posts_per_page=-1&nopaging=true');
				
			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id('testimonial_id') ); ?>">Testimonial to Display</label>
				<select id="<?php echo esc_attr( $this->get_field_id('testimonial_id') ); ?>" name="<?php echo esc_attr( $this->get_field_name('testimonial_id') ); ?>" data-shortcode-key="id">
				<?php if($testimonials): ?>
					<?php foreach ( $testimonials as $testimonial  ) : ?>
					<option value="<?php echo esc_attr( $testimonial->ID ); ?>"  <?php if($testimonial_id == $testimonial->ID): ?> selected="SELECTED" <?php endif; ?>><?php echo esc_html($testimonial->post_title); ?></option>
					<?php endforeach; ?>
				<?php endif;?>
				</select>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id('theme') ); ?>">Theme:</label><br/>
				<select name="<?php echo esc_attr( $this->get_field_name('theme') ); ?>" id="<?php echo esc_attr( $this->get_field_id('theme') ); ?>">	
					<?php foreach($themes as $group_key => $theme_group): ?>
					<?php $group_label = $this->get_theme_group_label($theme_group); ?>									
						<optgroup  label="<?php echo esc_attr($group_label);?>">
							<?php foreach($theme_group as $key => $theme_name): ?>
								<option value="<?php echo esc_attr($key); ?>" <?php if($theme == $key): echo 'selected="SELECTED"'; endif; ?>><?php echo esc_html($theme_name); ?></option>
							<?php endforeach; ?>
						</optgroup>
					<?php endforeach; ?>
				</select>
				<?php if (!$ip): ?>
				<br />
				<em><a target="_blank" href="http://goldplugins.com/our-plugins/easy-testimonials-details/upgrade-to-easy-testimonials-pro/?utm_source=wp_widgets&utm_campaign=widget_themes">Upgrade To Unlock All 100+ Pro Themes!</a></em>
				<?php endif; ?>
			</p>
			
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id('width') ); ?>">Width: </label><br />
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('width') ); ?>" name="<?php echo esc_attr( $this->get_field_name('width') ); ?>" type="text" value="<?php echo esc_attr($width); ?>" /></label>
				<br/>
				<em>(e.g. 100px or 25%)</em>
			</p>
		
			<fieldset class="radio_text_input">
				<legend>Fields To Display:</legend> &nbsp;
				<div class="bikeshed_radio">
					<p>
						<input name="<?php echo esc_attr( $this->get_field_name('show_title') ); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('show_title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_title') ); ?>" type="checkbox" value="1" <?php if($show_title){ ?>checked="CHECKED"<?php } ?> data-shortcode-value-if-unchecked="0" />
						<label for="<?php echo esc_attr( $this->get_field_id('show_title') ); ?>">Show Testimonial Title</label>
					</p>
					
					<p>
						<input name="<?php echo esc_attr( $this->get_field_name('use_excerpt') ); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('use_excerpt') ); ?>" name="<?php echo esc_attr( $this->get_field_name('use_excerpt') ); ?>" type="checkbox" value="1" <?php if($use_excerpt){ ?>checked="CHECKED"<?php } ?> data-shortcode-value-if-unchecked="0" />
						<label for="<?php echo esc_attr( $this->get_field_id('use_excerpt') ); ?>">Use Testimonial Excerpt</label>
					</p>	
					
					<p>
						<input name="<?php echo esc_attr( $this->get_field_name('reveal_full_content') ); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('reveal_full_content') ); ?>" name="<?php echo esc_attr( $this->get_field_name('reveal_full_content') ); ?>" type="checkbox" value="1" <?php if($reveal_full_content){ ?>checked="CHECKED"<?php } ?> data-shortcode-value-if-unchecked="0" />
						<label for="<?php echo esc_attr( $this->get_field_id('reveal_full_content') ); ?>">Click To Reveal Full Text</label>
					</p>	
					
					<p>
						<input name="<?php echo esc_attr( $this->get_field_name('show_testimonial_image') ); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('show_testimonial_image') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_testimonial_image') ); ?>" type="checkbox" value="1" <?php if($show_testimonial_image){ ?>checked="CHECKED"<?php } ?> data-shortcode-key="show_thumbs" data-shortcode-value-if-unchecked="0"/>
						<label for="<?php echo esc_attr( $this->get_field_id('show_testimonial_image') ); ?>">Show Featured Image</label>
					</p>
					
					<p>
						<input name="<?php echo esc_attr( $this->get_field_name('show_date') ); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('show_date') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_date') ); ?>" type="checkbox" value="1" <?php if($show_date){ ?>checked="CHECKED"<?php } ?> data-shortcode-value-if-unchecked="0"/>
						<label for="<?php echo esc_attr( $this->get_field_id('show_date') ); ?>">Show Testimonial Date</label>
					</p>
					
					<p>
						<input name="<?php echo esc_attr( $this->get_field_name('show_other') ); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('show_other') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_other') ); ?>" type="checkbox" value="1" <?php if($show_other){ ?>checked="CHECKED"<?php } ?> data-shortcode-value-if-unchecked="0" />
						<label for="<?php echo esc_attr( $this->get_field_id('show_other') ); ?>">Show "Location Reviewed / Product Reviewed / Item Reviewed" Field</label>
					</p>
					
					<p>
						<input name="<?php echo esc_attr( $this->get_field_name('hide_view_more') ); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('hide_view_more') ); ?>" name="<?php echo esc_attr( $this->get_field_name('hide_view_more') ); ?>" type="checkbox" value="1" <?php if($hide_view_more){ ?>checked=""<?php } ?> data-shortcode-value-if-unchecked="0" />
						<label for="<?php echo esc_attr( $this->get_field_id('hide_view_more') ); ?>">Hide View More Testimonials Link</label>
					</p>
					
					<p>
						<input type="hidden" value="0" name="<?php echo esc_attr( $this->get_field_name('output_schema_markup') ); ?>" />
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('output_schema_markup') ); ?>" name="<?php echo esc_attr( $this->get_field_name('output_schema_markup') ); ?>" type="checkbox" value="1" <?php if($output_schema_markup){ ?>checked=""<?php } ?> data-shortcode-value-if-unchecked="0" />
						<label for="<?php echo esc_attr( $this->get_field_id('output_schema_markup') ); ?>">Output Review Markup</label>
					</p>
					<p class="description">Output the JSON-LD schema.org compliant review markup with each Testimonial.</p>
				</div>
			</fieldset>

			<fieldset class="radio_text_input">
					<legend>Show Rating:</legend> &nbsp;
						<div class="bikeshed bikeshed_radio">
							<div class="radio_wrapper">
								<p class="radio_option"><label><input class="tog" name="<?php echo esc_html( $this->get_field_name('show_rating') ); ?>" type="radio" value="before" <?php if($show_rating=='before'){ ?>checked="checked"<?php } ?> > Before Testimonial</label></p>
								<p class="radio_option"><label><input class="tog" name="<?php echo esc_html( $this->get_field_name('show_rating') ); ?>" type="radio" value="after" <?php if($show_rating=='after'){ ?>checked="checked"<?php } ?> > After Testimonial</label></p>
								<p class="radio_option"><label><input class="tog" name="<?php echo esc_html( $this->get_field_name('show_rating') ); ?>" type="radio" value="stars" <?php if($show_rating=='stars'){ ?>checked="checked"<?php } ?> > As Stars</label></p>
								<p class="radio_option"><label><input class="tog" name="<?php echo esc_html( $this->get_field_name('show_rating') ); ?>" type="radio" value="" <?php if($show_rating==''){ ?>checked="checked"<?php } ?> > Do Not Show</label></p>
							</div>
						</div>						
						<br />
						<span style="padding-left:0px" class="description">Whether to show Ratings, and How.  If you are using a custom theme, make sure you follow the recommended settings here.</span>
					</p>
			</fieldset>
		</div>
		<?php
	}
	
	function update($new_instance, $old_instance){
		$new_instance = wp_parse_args( (array) $new_instance, $this->defaults );
		
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['testimonial_id'] = $new_instance['testimonial_id'];
		$instance['show_title'] = $new_instance['show_title'];
		$instance['show_rating'] = $new_instance['show_rating'];
		$instance['use_excerpt'] = $new_instance['use_excerpt'];
		$instance['show_date'] = $new_instance['show_date'];	
		$instance['width'] = $new_instance['width'];
		$instance['show_testimonial_image'] = $new_instance['show_testimonial_image'];
		$instance['show_other'] = $new_instance['show_other'];
		$instance['theme'] = $new_instance['theme'];
		$instance['hide_view_more'] = $new_instance['hide_view_more'];
		$instance['output_schema_markup'] = $new_instance['output_schema_markup'];
		$instance['reveal_full_content'] = $new_instance['reveal_full_content'];
				
		return $instance;
	}

	function widget($args, $instance){
		global $easy_t_in_widget;
		global $easy_testimonials;
		
		$easy_t_in_widget = true;
		
		extract($args, EXTR_SKIP);

		echo wp_kses_post($before_widget);
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		$show_title = empty($instance['show_title']) ? 0 : $instance['show_title'];
		$show_rating = empty($instance['show_rating']) ? false : $instance['show_rating'];
		$use_excerpt = empty($instance['use_excerpt']) ? 0 : $instance['use_excerpt'];
		$reveal_full_content = empty($instance['reveal_full_content']) ? 0 : $instance['reveal_full_content'];
		$show_date = empty($instance['show_date']) ? false : $instance['show_date'];
		$width = empty($instance['width']) ? false : $instance['width'];
		$show_testimonial_image = empty($instance['show_testimonial_image']) ? 0 : $instance['show_testimonial_image'];
		$show_other = empty($instance['show_other']) ? 0 : $instance['show_other'];
		$testimonials_link = empty($instance['testimonials_link']) ? get_option('testimonials_link') : $instance['testimonials_link'];
		$testimonial_id = $instance['testimonial_id'];
		$theme = $instance['theme'];
		$hide_view_more = empty($instance['hide_view_more']) ? 0 : $instance['hide_view_more'];
		$output_schema_markup = empty($instance['output_schema_markup']) ? 0 : $instance['output_schema_markup'];

		if (!empty($title)){
			echo wp_kses_post($before_title . $title . $after_title);
		}
		
		// ensure width has a unit
		if( is_numeric($width) && strpos($width, 'px') === FALSE && strpos($width, 'em') === FALSE && strpos($width, '%') === FALSE && strlen($width) > 0 ) {
			$width .= 'px';
		}
		
		$args = array(
			'testimonials_link' => $testimonials_link, 
			'show_title' => $show_title,
			'use_excerpt' => $use_excerpt,
			'reveal_full_content' => $reveal_full_content,
			'show_rating' => $show_rating,
			'show_date' => $show_date,
			'width' => $width,
			'show_thumbs' => $show_testimonial_image,
			'show_other' => $show_other,
			'theme' => $theme,
			'id' => $testimonial_id,
			'hide_view_more' => $hide_view_more,
			'output_schema_markup' => $output_schema_markup
		);
		
		$easy_testimonials->outputSingleTestimonial( $args );

		echo wp_kses_post($after_widget);
		
		$easy_t_in_widget = false;
	} 
	
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
}
?>