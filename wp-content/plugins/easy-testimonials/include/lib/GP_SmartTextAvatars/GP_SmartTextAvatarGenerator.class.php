<?php
class GP_SmartTextAvatarGenerator
{
	function __construct()
	{
	
	}

	function get_image_tag($text = '', $width = 150, $height = 150, $css_classes = '')
	{
		// exit if GD is not enabled
		if ( !function_exists('imagecreate') ) {
			return '';
		}
		
		// Create a new image and fill it with a background color chosen by the text
		$im = imagecreate( $width, $height );
		$white = imagecolorallocate($im, 255, 255, 255); // text color is always white, for now
		$bg_color = $this->get_random_background_color($im, $text); // choose color based on text (same every time for same input)
		imagefill($im, 0, 0, $bg_color); // fill image with selected color
		
		$initials = $this->get_initials_from_string($text);
		
		$this->add_centered_text($im, $white, $initials);
		$image_tag = $this->create_image_tag_from_gd_image($im, 'png', $css_classes);
		return $image_tag;

	}

	function add_centered_text($im, $text_color, $text = '')
	{
		$font = plugin_dir_path( __FILE__ ) . "fonts/open_sans/OpenSans-Semibold.ttf";
		$font_size = 32;
		$angle = 0;

		// Get image Width and Height
		$image_width = imagesx($im);  
		$image_height = imagesy($im);

		// Get Bounding Box Size
		$text_box = imagettfbbox($font_size, $angle, $font, $text);

		// Get your Text Width and Height
		$text_width = $text_box[2]-$text_box[0];
		$text_height = $text_box[7]-$text_box[1];

		// Calculate coordinates of the text
		$x = ($image_width/2) - ($text_width/2);
		$y = ($image_height/2) - ($text_height/2);
		
		// offset for J. seriously.
		if ( substr($text, 0, 1) == 'J' ) {
			$y -= 4;
			$x += 4;
		}

		// Add the text
		imagettftext($im, $font_size, 0, $x, $y, $text_color, $font, $text);
	}

	// Create an HTML Img Tag with Base64 Image Data
	function create_image_tag_from_gd_image( $gdImg, $format='jpg', $css_classes = '' )
	{

		// Validate Format
		if( in_array( $format, array ('jpg', 'jpeg', 'png', 'gif') ) ) {

			ob_start();
			if( $format == 'jpg' || $format == 'jpeg' ) {
				imagejpeg( $gdImg );
			} elseif( $format == 'png' ) {
				imagepng( $gdImg );
			} elseif( $format == 'gif' ) {
				imagegif( $gdImg );
		   }
			$data = ob_get_contents();
			ob_end_clean();
	 
			// Check for gd errors / buffer errors
			if( !empty($data) ) {
				$data = base64_encode( $data );

				// Check for base64 errors
				if ( $data !== false ) {
					// Success
					return sprintf("<img class='%s' src='data:image/$format;base64,$data'>", $css_classes);
				}
			}
		}

		// Failure
		return '<img>';
	}

	function get_random_background_color($im, $text_input = '')
	{
		if ( !empty($text_input) ) {
			$index = 0;
			$parts = explode(' ', $text_input);
			foreach($parts as $part) {
				$index += ord($part);
			}			
		} else {
			$index = rand(0, 100);
		}
		
		$bgcolor = array(
			array(244,67,54),
			array(233,30,99),
			array(156,39,176),
			array(103,58,183),
			array(63,81,181),
			array(33,150,243),
			array(0,150,136),
			array(76,175,80),
			array(255,152,0),
			array(255,87,34),
			array(96,125,139)
		);
		 
		$selected = $index % ( count($bgcolor) - 1 );
			 
		// Create the selected color and return it
		$backgroundColor = imagecolorallocate($im, (integer)$bgcolor[$selected][0], (integer)$bgcolor[$selected][1], (integer)$bgcolor[$selected][2]);
		return $backgroundColor;
	}

	function get_initials_from_string($text, $max_initials = 4)
	{
		// normalize text
		$text = trim($text);
		if ( empty($text) ) {
			return '';
		}

		// gather the first letter of each word in the name
		$parts = explode(' ', $text);

		// if too many words to display, just go with first initial
		if ( count($parts) > $max_initials ) {
			$initials = substr($text, 0, 1);
		} else {
			$initials = '';
			foreach ( $parts as $p ) {
				$initials .= substr($p, 0, 1);
			}
		}

		// normalize, fitler, and return initials
		$initials = strtoupper($initials);
		return apply_filters('easy_testimonials_fallback_image_text', $initials, $text, $max_initials);
	}
}