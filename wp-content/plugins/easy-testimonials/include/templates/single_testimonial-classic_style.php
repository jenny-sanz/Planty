<?php
		//strip 'style-' from the front of the output class so our css selectors work
		$output_theme = str_replace("style-","",$output_theme);
?>
<div class="easy_testimonial">
	<div class="<?php echo esc_attr($output_theme); ?>">
		<?php
			//output json-ld review markup, if option is set
			if($output_schema_markup){
				$this->output_jsonld_markup($display_testimonial);
			}
		?>
		<div class="main-wrapper">
			<div class="header-area-3">
				<div class="title-area-3">
					<?php if ($show_title): ?>
					<div class="easy_testimonial_title"><?php echo wp_kses_post( $this->easy_t_clean_html($display_testimonial['title']) ); ?></div>
					<?php endif; ?>
					<?php if ($show_the_other): ?>
					<div class="testimonial-other"><?php echo wp_kses_post( $this->easy_t_clean_html($display_testimonial['other']) );?></div>
					<?php endif; ?>
				</div>
				<div class="rate-area-3">
					<?php if($show_the_date): ?>
					<div class="date-3"><?php echo wp_kses_post( $this->easy_t_clean_html($display_testimonial['date']) ); ?></div>
					<?php endif; ?>
					<?php if($show_the_rating): ?>
					<div class="easy_testimonial_star_wrapper right-icon-3"><?php			
						$max_stars = 5;
						$remaining_stars = 5;
						//output dark stars for the filled in ones
						for($i = 0; $i < $display_testimonial['num_stars']; $i ++){
							echo '<i class="ion-star"></i>';
							
							// add an nbsp to each star except the last
							if( $i < $max_stars - 1 ){
								echo '&nbsp;';
							}
							
							$remaining_stars--; //one less star available
						}
						//fill out the remaining empty stars
						for($i = 0; $i < $remaining_stars; $i++){
							echo '<span class="ccicon"><i class="ion-star"></i></span>';
							
							// add an nbsp to each star except the last
							if( $i < $remaining_stars - 1 ){
								echo '&nbsp;';
							}
						}
					?>
					</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="main-content-3">
				<div class="<?php echo esc_attr($body_class); ?>"><?php echo wp_kses( wpautop($display_testimonial['content']), 'post' ); ?></div>
			</div>
		</div>

		<div class="footer-area-3">
			<?php if ($show_thumbs) {
				?><div style="display: inline-block" class="easy_testimonial_image_wrapper"><?php
				echo wp_kses_post($display_testimonial['image']);
				?></div><?php
			} ?>
			<div class="footer-text-3">
				<?php if($show_the_client): ?>
				<div class="testimonial-client"><?php echo wp_kses_post( $this->easy_t_clean_html($display_testimonial['client']) ); ?></div>
				<?php endif; ?>
				<?php if($show_the_position): ?><div class="testimonial-position"><?php echo wp_kses_post( $this->easy_t_clean_html($display_testimonial['position']) ); ?></div><?php endif; ?>
			</div>
		</div>
	</div>
</div>