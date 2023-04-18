<?php

function neve_child_load_css() {
	wp_enqueue_style( 'neve-child-style', get_stylesheet_uri(), array( 'neve-style' ), wp_get_theme()->get( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'neve_child_load_css' );


