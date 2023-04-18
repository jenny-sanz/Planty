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
		<div class="header-area-5">
			<div class="title-area-5">
				<?php if ($show_title): ?>
				<div class="easy_testimonial_title"><?php echo esc_html( $this->easy_t_clean_html($display_testimonial['title']) ); ?></div>
				<?php else: ?>
				<div class="easy_testimonial_title"></div>
				<?php endif; // output placeholder to maintain styling ?>
				<?php if ($show_the_other): ?>
				<div class="testimonial-other"><?php echo esc_html( $this->easy_t_clean_html($display_testimonial['other']) ); ?></div>
				<?php endif; ?>
			</div>
			<div class="rate-area-5">
				<div class="right-arrow"></div>
				<?php if($show_the_date): ?>
				<div class="date-5"><?php echo esc_html( $this->easy_t_clean_html($display_testimonial['date']) ); ?></div>
				<?php endif; ?>
				<div class="easy_testimonial_star_wrapper right-icon">
				<?php			
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
			</div>
		</div>
		<div class="main-content-5">
			<div class="<?php echo esc_attr($body_class); ?>"><?php echo wp_kses_post( wpautop($display_testimonial['content']) ); ?></div>
		</div>

		<div class="footer-area-5">
			<div class="footer-arrow"></div>
			<?php if ($show_thumbs) {
				?><div style="display: inline-block" class="easy_testimonial_image_wrapper"><?php
				echo wp_kses_post( $display_testimonial['image'] );
				?></div><?php
			} ?>
			<div class="footer-text">
				<?php if($show_the_client): ?>
				<div class="testimonial-client"><?php echo esc_html( $this->easy_t_clean_html($display_testimonial['client']) ); ?></div>
				<?php endif; ?>
				<?php if($show_the_position): ?><div class="testimonial-position"><?php echo esc_html( $this->easy_t_clean_html($display_testimonial['position']) ); ?></div><?php endif; ?>
			</div>
		</div>
	</div>
</div>