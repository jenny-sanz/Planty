<?php

class Easy_Testimonials_Custom_Columns
{
	
	function __construct()
	{
		// setup custom columns on View All Testimonials page
		if ( is_admin() ) {

			add_filter( 'manage_testimonial_posts_columns' , array($this, 'update_testimonials_list_columns') );
			add_action( 'manage_testimonial_posts_custom_column' , array($this, 'output_testimonials_column_content'), 10, 2 );
			add_filter( 'manage_edit-testimonial_sortable_columns', array($this, 'make_custom_columns_sortable') );
			add_action( 'pre_get_posts', array($this, 'enable_orderby_for_custom_columns') );
			add_filter( 'default_hidden_columns', array($this, 'set_default_hidden_columns'), 10, 2 );
			
			//add_action( 'admin_head-edit.php', array($this, 'change_title_in_list') );
			add_action( 'load-edit.php', array($this, 'force_excerpt_mode') );
		}	
	}


	/* 
	 * Register our custom columns with WordPress
	 *
	 * Called by WP's manage_testimonial_posts_columns hook
	 *
	 * @param array $columns The current list of columns
	 * @return array The updated list of columns, with our custom columns added
	 */
	function update_testimonials_list_columns($columns)
	{
		// now add the rest of the columns
		$new_cols = array(
			//'testimonial' => __('Testimonial'),
			'rating' => __('Rating'),
			'client' => __('Client Name'),
			'position' => __('Title or Position'),
			'other' => __('Location or Product Reviewed'),
			'single_shortcode' => __('Shortcode'),
		);
		// insert our new cols between the 2nd (Title) and 3rd (Categories) cols
		$columns = array_slice($columns, 0, 2, true) +
				   $new_cols +
				   array_slice($columns, 2, count($columns)-2, true);
		
		return $columns;
	}
	
	/* 
	 * Output the content for our custom columns
	 *
	 * Called by WP's output_testimonials_column_content hook
	 *
	 * @param string $column The key of the column to output
	 * @param int The ID of the testimonial (post) in the database
	 */
	function output_testimonials_column_content($column, $testimonial_id)
	{
		switch ( $column ) {
			case 'excerpt' :
			case 'testimonial' :			
				$output = $this->get_excerpt( $testimonial_id );
				echo esc_html($output);
				break;

			case 'rating' :
				$rating = get_post_meta( $testimonial_id , '_ikcf_rating', true ); 
				$output = !empty($rating)
						  ? $rating . ' / 5'
						  : '';
				echo esc_html($output);
				break;
			
			// simple cases (meta key matches column key, just echo the value)
			case 'client' :
			case 'position' :
			case 'other' :
				$meta_key = sprintf( '_ikcf_%s', $column );
				$output = get_post_meta( $testimonial_id , $meta_key , true );
				echo esc_html($output);
				break;
				
			
			case 'single_shortcode' :
				printf( '<input type=\"text\" value="[single_testimonial id=\'%s\']" style="max-width:100%%" />',
						esc_html($testimonial_id) );
				break;

			default:
				echo esc_html($column);
				break;
		}
		
	
		
    }

	/* 
	 * Tell WordPress that some of our custom columns are sortable
	 *
	 * Called by WP's manage_edit-testimonial_sortable_columns hook
	 *
	 * @param array $columns The list of currently sortable columns
	 * @return array The list of sortable columns with our columns added
	 */
	function make_custom_columns_sortable( $columns )
	{
		$columns['client'] = 'testimonials_client';
		$columns['position'] = 'testimonials_position';
		$columns['rating'] = 'testimonials_rating';
		$columns['other'] = 'testimonials_other';
		return $columns;
	}
	
	/* 
	 * Teaches WordPress how to convert our custom keys into an orderby clause
	 *
	 * Called by WP's pre_get_posts hook
	 *
	 * @param WP_Query $query 
	 */
	function enable_orderby_for_custom_columns( $query )
	{
		if( ! is_admin() ) {
			return;
		}
		
		$orderby = $query->get( 'orderby');	
		
		// make sure the column name begins with our prefix, 'testimonials_'
		// Note: make sure its a string first, to prevent conflicts with other plugins
		if ( !is_string($orderby) || strpos($orderby, 'testimonials_') !== 0 ) {
			return;
		}
		
		// strip off the prefix and continue
		$orderby = substr( $orderby, 13);
		if ( $this->is_testimonials_custom_column($orderby) ) {
			$meta_key = sprintf('_ikcf_%s', $orderby);
			$query->set('meta_key', $meta_key);
			$query->set('orderby','meta_value'); // sort alphabetically
		}
	}
	
	/* 
	 * Tells whether the given key is one of our custom column keys
	 * 
	 * @param string $key the key name to check
	 * 
	 * @return bool, true if its one of our columns, false if not
	 */
	function is_testimonials_custom_column($key)
	{
		$testimonial_cols = array( 
			'testimonial',
			'excerpt',
			'rating',
			'position',
			'other',
			'single_shortcode'
		);
		return in_array($key, $testimonial_cols);
	}
	
	/* 
	 * Hides some of the custom columns on Testimonials by default
	 * Once the user interacts with the screen options, their preferences will 
	 * be automatically saved by WP, and these defaults will be overriden.
	 *
	 * Called by WP's default_hidden_columns hook
	 *
	 * @param array $hidden List of currently hidden columns
	 * @param object $screen WordPress screen object for the current screen
	 */
	function set_default_hidden_columns( $hidden, $screen )
	{
		// We only want to modify the edit.php?post_type=testimonial screen, so
		// if this is another screen quit now
		if ( empty($screen->id) || $screen->id !== 'edit-testimonial' ) {
			return $hidden;
		}
		
		// init empty array if needed
		if ( !is_array($hidden) || empty($hidden) ) {
			$hidden = array();
		}
		
		// add our hidden-by-default columns to the list
		$hidden[] = 'client';
		return $hidden;		
	}
	
	function change_title_in_list()
	{
		add_filter(
			'the_title',
			array($this, 'construct_new_title'),
			100,
			2
		);
		//remove_filter( 'the_title', 'esc_html' );
	}
	
	function construct_new_title( $title, $id )
	{
		$excerpt = $this->get_excerpt( $id );
		return $title;
		return $title . "</a><br>{$excerpt}<a>";
	}

	function get_excerpt($testimonial_id)
	{
		$excerpt = apply_filters( 'the_excerpt', get_post_field('post_excerpt', $testimonial_id) );
		if ( empty($excerpt) ) {
			$post = get_post($testimonial_id);
			$excerpt = wp_trim_words($post->post_content, 20);
			$excerpt = apply_filters( 'the_excerpt', $excerpt );
		}
		return $excerpt;
	}
	
	function force_excerpt_mode()
	{
		if ( !empty($_GET['post_type'])
			 && 'testimonial' == $_GET['post_type']
			 && !isset($_REQUEST['mode'])
		) {
			$_REQUEST['mode'] = 'excerpt';
		}
	}
	
} // end class