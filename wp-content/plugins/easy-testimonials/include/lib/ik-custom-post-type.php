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
*/
class ikTestimonialsCustomPostType
{
	var $customFields = false;
	var $customPostTypeName = 'custompost';
	var $customPostTypeSingular = 'customPost';
	var $customPostTypePlural = 'customPosts';
	var $prefix = '_ikcf_';

	function __construct($postType, $customFields = false, $removeDefaultCustomFields = false, $custom_args = array(), $custom_labels = array())
	{
		$this->setupCustomPostType($postType, $custom_args, $custom_labels);
		
		if ($customFields)
		{
			$this->setupCustomFields($customFields);
		}
		
		// remove the standard custom fields box if desired
		if ($removeDefaultCustomFields)
		{
			add_action( 'do_meta_boxes', array( $this, 'removeDefaultCustomFields' ), 10, 3 );
		}
	}
	
	function setupCustomPostType($postType, $custom_args = array(), $custom_labels = array())
	{
		$singular = ucwords($postType['name']);
		$plural = isset($postType['plural']) ? ucwords($postType['plural']) : $singular . 's';
		$exclude_from_search = isset($postType['exclude_from_search']) ? $postType['exclude_from_search'] : false;
		$default_supports = array('title','editor','author','thumbnail','excerpt','comments','custom-fields');		
		$supports = isset( $postType['supports'] )
					? $postType['supports']
					: $default_supports;
					
		
		$this->customPostTypeName = $postType['slug'];
		$this->customPostTypeSingular = $singular;
		$this->customPostTypePlural = $plural;

		if ($this->customPostTypeName != 'post' && $this->customPostTypeName != 'page')
		{		
			$labels = array
			(
				'name' => _x($plural, 'post type general name'),
				'singular_name' => _x($singular, 'post type singular name'),
				'add_new' => _x('Add New ' . $singular, strtolower($singular)),
				'add_new_item' => __('Add New ' . $singular),
				'edit_item' => __('Edit ' . $singular),
				'new_item' => __('New ' . $singular),
				'view_item' => __('View ' . $singular),
				'search_items' => __('Search ' . $plural),
				'not_found' =>  __('No ' . strtolower($plural) . ' found'),
				'not_found_in_trash' => __('No ' . strtolower($plural) . ' found in Trash'), 
				'parent_item_colon' => ''
			);
			$labels = array_merge($labels, $custom_labels);
			
			$args = array(
				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => true,
				'exclude_from_search' => $exclude_from_search,
				'show_ui' => true, 
				'query_var' => true,
				'rewrite' => array( 
					'slug' => $postType['slug'],
					'with_front' => empty($postType['slug'])
				),
				'capability_type' => 'post',
				'hierarchical' => false,
				'supports' => $supports,
				'menu_icon' => '',
				'show_in_rest' => true,
				//'show_in_menu' => 'easy-testimonials-settings',
				'show_in_menu' => true,
			); 
			$args = array_merge($args, $custom_args);
			
			$this->customPostTypeArgs = $args;	
			if ( did_action('init') >= 1 ) {
				$this->registerPostTypes();
			} else {
				add_action('init', array($this, 'registerPostTypes'));
			}
		
			//hook functions to change "Post Updated", etc, to relevant CPT naming
			add_filter( 'post_updated_messages', array( &$this, 'add_update_messages' ) );
			add_filter( 'bulk_post_updated_messages', array( &$this, 'add_bulk_update_messages' ), 10, 2 );
		}
	}

	function registerPostTypes()
	{
	  register_post_type($this->customPostTypeName,$this->customPostTypeArgs);
	}
	
	function setupCustomFields($fields)
	{
		$this->customFields = array();
		foreach ($fields as $f)
		{
			$this->customFields[] = array
			(
				"name"			=> $f['name'],
				"title"			=> $f['title'],
				"description"	=> isset($f['description']) ? $f['description'] : '',
				"type"			=> isset($f['type']) ? $f['type'] : "text",
				"scope"			=>	array( $this->customPostTypeName ),
				"capability"	=> "edit_posts",
				"data"			=> isset($f['data']) ? $f['data'] : false,
				"placeholder"	=> isset($f['placeholder']) ? $f['placeholder'] : ''
			);
		}
		// register hooks
		add_action( 'admin_menu', array( $this, 'createCustomFields' ) );
		add_action( 'save_post', array( $this, 'saveCustomFields' ), 1, 2 );
	}
	
	/**
	* Remove the default Custom Fields meta box
	*/
	function removeDefaultCustomFields( $type, $context, $post ) 
	{
		foreach ( array( 'normal', 'advanced', 'side' ) as $context ) 
		{
			//remove_meta_box( 'postcustom', 'post', $context );
			//remove_meta_box( 'postcustom', 'page', $context );
			remove_meta_box( 'postcustom', $this->customPostTypeName, $context );//RWG
		}
	}
		
	/**
	* Create the new Custom Fields meta box
	*/
	function createCustomFields() 
	{
		if ( function_exists( 'add_meta_box' ) ) 
		{
			//add_meta_box( 'my-custom-fields', 'Custom Fields', array( $this, 'displayCustomFields' ), 'page', 'normal', 'high' );
			//add_meta_box( 'my-custom-fields', 'Custom Fields', array( $this, 'displayCustomFields' ), 'post', 'normal', 'high' );
			add_meta_box( 'my-custom-fields'.md5(serialize($this->customFields)), $this->customPostTypeSingular . ' Information', array( $this, 'displayCustomFields' ), $this->customPostTypeName, 'normal', 'high' );//RWG
		}
	}

	/**
	* Display the new Custom Fields meta box
	*/
	function displayCustomFields() {
		global $post;
		?>
		<div class="form-wrap">
			<?php
			wp_nonce_field( 'my-custom-fields', 'my-custom-fields_wpnonce', false, true );
			foreach ( $this->customFields as $customField ) {
				// Check scope
				$scope = $customField[ 'scope' ];
				$output = false;
				foreach ( $scope as $scopeItem ) {
					switch ( $scopeItem ) {
						case "post": {
							// Output on any post screen
							if ( basename( $_SERVER['SCRIPT_FILENAME'] )=="post-new.php" || $post->post_type=="post" )
								$output = true;
							break;
						}
						case "page": {
							// Output on any page screen
							if ( basename( $_SERVER['SCRIPT_FILENAME'] )=="page-new.php" || $post->post_type=="page" )
								$output = true;
							break;
						}
						default:{//RWG
							if ($post->post_type==$scopeItem )
								$output = true;
							break;
						}
					}
					if ( $output ) break;
				}
				// Check capability
				if ( !current_user_can( $customField['capability'], $post->ID ) ) {
					$output = false;
				}
				
				// Output if allowed
				if ( $output ) { 
					$placeholder_attr = !empty($customField['placeholder'])
										? sprintf( 'placeholder="%s"', htmlspecialchars($customField['placeholder']) )
										: '';				
				?>
					<div class="form-field form-required">
						<?php
						switch ( $customField[ 'type' ] ) {
							case "checkbox": {
								// Checkbox
								echo '<label for="' . esc_attr($this->prefix) . esc_attr($customField[ 'name' ]) .'" style="display:inline;"><b>' . esc_html($customField[ 'title' ]) . '</b></label>&nbsp;&nbsp;';
								echo '<input type="checkbox" name="' . esc_attr($this->prefix) . esc_attr($customField['name'] ). '" id="' . esc_attr($this->prefix) . esc_attr($customField['name']) . '" value="yes"';
								if ( get_post_meta( $post->ID, $this->prefix . $customField['name'], true ) == "yes" )
									echo ' checked="checked"';
								echo '" style="width: auto;" />';
								break;
							}
							case "textarea": {
								// Text area
								echo '<label for="' . esc_attr($this->prefix) . esc_attr($customField[ 'name' ]) .'"><b>' . esc_html($customField[ 'title' ]) . '</b></label>';
								echo '<textarea name="' . esc_attr($this->prefix) . esc_attr($customField[ 'name' ]) . '" id="' . esc_attr($this->prefix) . esc_attr($customField[ 'name' ]) . '" ' . esc_attr($placeholder_attr) . ' columns="30" rows="3">' . esc_html( get_post_meta( $post->ID, $this->prefix . $customField[ 'name' ], true ) ) . '</textarea>';
								break;
							}
							case "number": {
								$min = !empty($customField[ 'min' ]) ? sprintf( 'min="%s"',$customField['min'] ) : 'min="1"';
								$max = !empty($customField[ 'max' ]) ? sprintf( 'max="%s"',$customField['max'] ) : 'max="5"';
								
								
								// HTML5 Number
								echo '<label for="' . esc_attr($this->prefix) . esc_attr($customField[ 'name' ]) .'"><b>' . esc_html($customField[ 'title' ]) . '</b></label>';
								echo '<input type="number" '.esc_attr($min).' '.esc_attr($max).' name="' . esc_attr($this->prefix) . esc_attr($customField[ 'name' ]) . '" id="' . esc_attr($this->prefix) . esc_attr($customField[ 'name' ]) . '" value="' . esc_html( get_post_meta( $post->ID, $this->prefix . $customField[ 'name' ], true ) ) . '" />';
								break;
							}
							case "select": {
								// Drop Down
								echo '<label for="' . esc_attr($this->prefix) . esc_attr($customField[ 'name' ]) .'"><b>' . esc_html($customField[ 'title' ]) . '</b></label>';
								echo '<select name="' . esc_attr($this->prefix) . esc_attr($customField[ 'name' ]) . '" id="' . esc_attr($this->prefix) . esc_attr($customField[ 'name' ]) . '" columns="30" rows="3">';
								foreach($customField['data'] as $label => $value){
									$selected = "";
									if($value == htmlspecialchars( get_post_meta( $post->ID, $this->prefix . $customField[ 'name' ], true ) )){
										$selected = 'selected="SELECTED"';
									}
									echo '<option value="'.esc_attr($value).'" '.esc_attr($selected).'>'.esc_html($label).'</option>';
								}
								echo '</select>';
								break;
							}
							default: {
								// Plain text field
								echo '<label for="' . esc_attr($this->prefix) . esc_attr($customField[ 'name' ]) .'"><b>' . esc_html($customField[ 'title' ]) . '</b></label>';
								echo '<input type="text" name="' . esc_attr($this->prefix) . esc_attr($customField[ 'name' ]) . '" id="' . esc_attr($this->prefix) . esc_attr($customField[ 'name' ]) . '" value="' . esc_html( get_post_meta( $post->ID, $this->prefix . $customField[ 'name' ], true ) ) . '" ' . esc_attr($placeholder_attr) . '/>';
								break;
							}
						}
						?>
						<?php if ( $customField[ 'description' ] ) echo '<p>' . esc_html($customField[ 'description' ]) . '</p>'; ?>
					</div>
				<?php
				}
			} ?>
		</div>
		<?php
	}

	/**
	* Save the new Custom Fields values
	*/
	function saveCustomFields( $post_id, $post ) {
		//idea from here: http://wordpress.stackexchange.com/questions/37967/custom-field-being-erased-after-autosave
		//don't udpate custom fields on quickedit screen so that they aren't erased.
		if ((defined('DOING_AJAX') && DOING_AJAX) || isset($_REQUEST['bulk_edit'])) {
			return;
		}
	
		// Quit now if no/invalid nonce presented (e.g., when WP first creates the record to display the New Post screen)
		if ( empty($_POST[ 'my-custom-fields_wpnonce' ]) 
			 || ! wp_verify_nonce( $_POST[ 'my-custom-fields_wpnonce' ], 'my-custom-fields' ) ) {
			return;
		}
		
		if ( !current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		//if ( $post->post_type != 'page' && $post->post_type != 'post')//RWG
		//	return;
		foreach ( $this->customFields as $customField ) {
			if ( current_user_can( $customField['capability'], $post_id ) ) {
				
				$cf_name = $this->prefix . $customField['name'];
				$new_value = isset( $_POST[$cf_name] )
							 ? sanitize_textarea_field($_POST[$cf_name])
							 : '';		
				if ( strlen( trim($new_value) ) > 0 ) {
					update_post_meta( $post_id, $cf_name, $new_value );
				} else {
					delete_post_meta( $post_id, $cf_name );
				}
			}
		}
	}
		
	/**
	 * Add customized update messages for the custom post type. This way WP
	 * will say e.g., "Custom Post Type updated. View custom post type."
	 * instead of "Post updated. View post".
	 *
	 * See https://codex.wordpress.org/Function_Reference/register_post_type
	 *
	 * @param array $messages Existing post update messages.
	 *
	 * @return array Updated list with our messages added
	 */
	function add_update_messages( $messages )
	{
		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );
		$textdomain = $this->customPostTypeName; // TODO: pass this in as an option

		$messages[ $this->customPostTypeName ] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => $this->customPostTypeSingular . __( ' updated.', $textdomain ),
			2  => __( 'Custom field updated.', $textdomain ),
			3  => __( 'Custom field deleted.', $textdomain ),
			4  => $this->customPostTypeSingular . __( ' updated.', $textdomain ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( $this->customPostTypeSingular . __( ' restored to revision from %s', $textdomain ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => $this->customPostTypeSingular . __( ' published.', $textdomain ),
			7  => $this->customPostTypeSingular . __( ' saved.', $textdomain ),
			8  => $this->customPostTypeSingular . __( ' submitted.', $textdomain ),
			9  => sprintf(
				$this->customPostTypeSingular . __( ' scheduled for: <strong>%1$s</strong>.', $textdomain ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i', $textdomain ), strtotime( $post->post_date ) )
			),
			10 => $this->customPostTypeSingular . __( ' draft updated.', $textdomain )
		);

		// Append "View Custom Post Type" links to the end of some messages
		// if we are currently viewing viewing an obkect of this Post Type
		if ( $post_type_object->publicly_queryable && ($this->customPostTypeName === $post_type) ) {
			$permalink = get_permalink( $post->ID );

			$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View ', $textdomain ) . strtolower($this->customPostTypeSingular) );
			$messages[ $post_type ][1] .= $view_link;
			$messages[ $post_type ][6] .= $view_link;
			$messages[ $post_type ][9] .= $view_link;

			$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
			$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview ', $textdomain ) . strtolower($this->customPostTypeSingular) );
			$messages[ $post_type ][8]  .= $preview_link;
			$messages[ $post_type ][10] .= $preview_link;
		}

		return $messages;
	}
	
	/**
	 * Add customized update messages for bulk actions applied to the custom
	 * post type (e.g., post(s) moved to Trash). This way WP will say e.g.,
	 * "1 Custom Post Type moved to the trash.", instead of "1 post moved to
	 * the trash".
	 *
	 * See https://codex.wordpress.org/Plugin_API/Filter_Reference/bulk_post_updated_messages
	 *
	 * @param array $bulk_messages Existing bulk update messages.
	 * @param array $bulk_counts The number of posts with each new status
	 *							 ('trashed', 'updated', etc)
	 *
	 * @return array Updated list with our bulk messages added
	 */
	function add_bulk_update_messages( $bulk_messages, $bulk_counts )
	{
		$singular = strtolower($this->customPostTypeSingular);
		$plural = strtolower($this->customPostTypePlural);
		$bulk_messages[ $this->customPostTypeName ] = array(
			'updated'   => _n( '%s ' . $singular . ' updated.', '%s ' . $plural . ' updated.', $bulk_counts['updated'] ),
			'locked'    => _n( '%s ' . $singular . ' not updated, somebody is editing it.', '%s ' . $plural . ' not updated, somebody is editing them.', $bulk_counts['locked'] ),
			'deleted'   => _n( '%s ' . $singular . ' permanently deleted.', '%s ' . $plural . ' permanently deleted.', $bulk_counts['deleted'] ),
			'trashed'   => _n( '%s ' . $singular . ' moved to the Trash.', '%s ' . $plural . ' moved to the Trash.', $bulk_counts['trashed'] ),
			'untrashed' => _n( '%s ' . $singular . ' restored from the Trash.', '%s ' . $plural . ' restored from the Trash.', $bulk_counts['untrashed'] ),
		);	
		return $bulk_messages;
	}
}