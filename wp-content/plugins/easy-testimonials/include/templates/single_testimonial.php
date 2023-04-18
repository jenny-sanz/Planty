<?php
//single testimonial default template
 ?>
		<div class="<?php echo esc_attr($output_theme); ?> <?php echo esc_attr($attribute_classes); ?> easy_t_single_testimonial" <?php echo esc_html($width_style); ?>>
			<?php
				//output json-ld review markup, if option is set
				if($output_schema_markup){
					$this->output_jsonld_markup($display_testimonial);
				}
			?>
			<blockquote class="easy_testimonial">
				<?php if ($show_thumbs) {
					?><div class="easy_testimonial_image_wrapper"><?php
					echo wp_kses($display_testimonial['image'], 'post');
					?></div><?php
				} ?>		
				<?php if ($show_title) {
					echo '<p class="easy_testimonial_title">' . esc_html($this->easy_t_clean_html($display_testimonial['title'])) . '</p>';
				} ?>	
				<?php if($meta_data_position == "above") { ?>				
				<p class="<?php echo esc_attr($author_class); ?>">
					<?php //if any of the items have data and are set to be displayed, construct the html ?>
					<?php if($show_the_client || $show_the_position || $show_the_other || $show_the_date || $show_rating == "stars" ): ?>
					<cite>
						<?php if($show_the_client): ?>
							<span class="testimonial-client"><?php echo esc_html($this->easy_t_clean_html($display_testimonial['client']));?></span>
						<?php endif; ?>
						<?php if($show_the_position): ?>
							<span class="testimonial-position"><?php echo esc_html($this->easy_t_clean_html($display_testimonial['position']));?></span>
						<?php endif; ?>
						<?php if($show_the_other): ?>
							<span class="testimonial-other"><?php echo esc_html($this->easy_t_clean_html($display_testimonial['other']));?></span>
						<?php endif; ?>
						<?php if($show_the_date): ?>
							<span class="date"><?php echo esc_html($this->easy_t_clean_html($display_testimonial['date']));?></span>
						<?php endif; ?>
						<?php if($show_the_rating): ?>
							<?php if(strlen($display_testimonial['num_stars'])>0): ?>
							<span class="stars">
							<?php			
								$x = 5; //total available stars
								//output dark stars for the filled in ones
								for($i = 0; $i < $display_testimonial['num_stars']; $i ++){
									echo '<span class="dashicons dashicons-star-filled"></span>';
									$x--; //one less star available
								}
								//fill out the remaining empty stars
								for($i = 0; $i < $x; $i++){
									echo '<span class="dashicons dashicons-star-filled empty"></span>';
								}
							?>			
							</span>	
							<?php endif; ?>
						<?php endif; ?>
					</cite>
					<?php endif; ?>					
				</p>	
				<?php } ?>
				<div class="<?php echo esc_attr($body_class); ?>">
					<?php echo wp_kses($display_testimonial['content'], 'post'); ?>
					<?php if ( !empty($display_testimonial['full_content']) ) : ?>
						<div class="reveal_full_content" style="display:none"><?php echo wp_kses($display_testimonial['full_content'], 'post'); ?></div>
						<?php if( !get_option('easy_t_link_excerpt_to_full', false) ): ?>
						<div class="reveal_link">
							<p><a href="#"><?php esc_html_e( apply_filters('easy_testimonials_reveal_full_text_label', __('Read More', 'easy-testimonials'), $display_testimonial ) ); ?></a></p>
						</div>
						<?php endif; ?>
					<?php endif; ?>
				</div>	
				<?php if($meta_data_position == "below") { ?>				
				<p class="<?php echo esc_attr($author_class); ?>">
					<?php //if any of the items have data and are set to be displayed, construct the html ?>
					<?php if($show_the_client || $show_the_position || $show_the_other || $show_the_date || $show_rating == "stars" ): ?>
					<cite>
						<?php if($show_the_client): ?>
							<span class="testimonial-client"><?php echo esc_html($this->easy_t_clean_html($display_testimonial['client']));?></span>
						<?php endif; ?>
						<?php if($show_the_position): ?>
							<span class="testimonial-position"><?php echo esc_html($this->easy_t_clean_html($display_testimonial['position']));?></span>
						<?php endif; ?>
						<?php if($show_the_other): ?>
							<span class="testimonial-other"><?php echo esc_html($this->easy_t_clean_html($display_testimonial['other']));?></span>
						<?php endif; ?>
						<?php if($show_the_date): ?>
							<span class="date"><?php echo esc_html($this->easy_t_clean_html($display_testimonial['date']));?></span>
						<?php endif; ?>
						<?php if($show_the_rating): ?>
							<?php if(strlen($display_testimonial['num_stars'])>0): ?>
							<span class="stars">
							<?php			
								$x = 5; //total available stars
								//output dark stars for the filled in ones
								for($i = 0; $i < $display_testimonial['num_stars']; $i ++){
									echo '<span class="dashicons dashicons-star-filled"></span>';
									$x--; //one less star available
								}
								//fill out the remaining empty stars
								for($i = 0; $i < $x; $i++){
									echo '<span class="dashicons dashicons-star-filled empty"></span>';
								}
							?>			
							</span>	
							<?php endif; ?>
						<?php endif; ?>
					</cite>
					<?php endif; ?>					
				</p>	
				<?php if($show_view_more):?><a href="<?php echo esc_url($testimonials_link); ?>" class="easy_testimonials_read_more_link"><?php echo esc_html(get_option('easy_t_view_more_link_text', 'Read More Testimonials')); ?></a><?php endif; ?>
				<?php } ?>
				<div class="easy_t_clear"></div>
			</blockquote>
		</div>