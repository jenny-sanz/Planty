<?php

class Easy_Testimonials_Maintenance_Fix_Featured_Images extends Easy_Testimonials_Maintenance_Task
{
	var $key = 'fix_testimonial_featured_images_02022017';
	
	function run($version = '')
	{
		// remove WP_Errors that might have been saved as featured images
		if ( !$this->task_has_been_run() ) {
			$this->fix_testimonial_featured_images();
			$this->mark_task_run();
		}
	}

	function fix_testimonial_featured_images()
	{
		$args = array(
			'post_type' => 'testimonial',
			'posts_per_page' => -1,
			'nopaging' => true
		);
		$posts = get_posts( $args );
		
		foreach ($posts as $post) {		
			// If the post has a bad featured image, remove the meta
			if ( is_wp_error(get_post_thumbnail_id($post->ID) ) ) {
				delete_post_meta($post->ID, '_thumbnail_id');
			}
		}
	}
} // end class

$my_Easy_Testimonials_Maintenance_Fix_Featured_Images = new Easy_Testimonials_Maintenance_Fix_Featured_Images();