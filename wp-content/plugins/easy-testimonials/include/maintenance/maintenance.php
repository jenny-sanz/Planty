<?php
require_once('easy_testimonial_maintenance_task.class.php');

global $easy_testimonials_maintenance_version;
$easy_testimonials_maintenance_version = '02022017';

class Easy_Testimonials_Maintenance
{
	function __construct()
	{
		if( is_admin() ){
			add_action( 'plugins_loaded', array($this, 'maintenance_check') );
		}
	}

	function maintenance_check()
	{
		global $easy_testimonials_maintenance_version;
		if ( get_site_option( 'easy_testimonials_maintenance_version' ) != $easy_testimonials_maintenance_version ) {
			//only require and instantiate this if we are actually performing maintenance
			require_once('tasks/fix_testimonial_featured_images.php');
			
			do_action('easy_testimonials_maintenance_tasks', $easy_testimonials_maintenance_version);

			/*
			 * All tasks complete
			 */
			update_option( 'easy_testimonials_maintenance_version', $easy_testimonials_maintenance_version );			
		}
	}	
}

$easy_t_maintenance = new Easy_Testimonials_Maintenance();