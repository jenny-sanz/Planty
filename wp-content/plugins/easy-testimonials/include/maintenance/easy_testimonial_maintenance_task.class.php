<?php

class Easy_Testimonials_Maintenance_Task
{
	var $key;
	
	function __construct($key = '')
	{
		if ( !empty($key) ) {
			$this->key = $key;
		}
		add_action('easy_testimonials_maintenance_tasks', array($this, 'run'));
	}
	
	function run($version = '')
	{
	
	}
	
	function task_has_been_run()
	{
		$tasks = get_site_option( 'easy_testimonials_maintenance_tasks_run', '' );
		if ( empty($tasks) ) {
			return false;
		}
		return in_array($this->key, $tasks);
	}

	function mark_task_run()
	{
		$tasks = get_site_option( 'easy_testimonials_maintenance_tasks_run', '' );
		if ( empty($tasks) ) {
			$tasks = array();
		}

		if  ( !in_array($this->key, $tasks) ) {
			$tasks[] = $this->key;
		}
		
		update_option( 'easy_testimonials_maintenance_tasks_run', $tasks );
	}
}