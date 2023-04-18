( 
	function( wp ) {
	/**
	 * Registers a new block provided a unique name and an object defining its behavior.
	 * @see https://github.com/WordPress/gutenberg/tree/master/blocks#api
	 */
	var registerBlockType = wp.blocks.registerBlockType;
	/**
	 * Returns a new element of given type. Element is an abstraction layer atop React.
	 * @see https://github.com/WordPress/gutenberg/tree/master/element#element
	 */
	var el = wp.element.createElement;
	/**
	 * Retrieves the translation of text.
	 * @see https://github.com/WordPress/gutenberg/tree/master/i18n#api
	 */
	var __ = wp.i18n.__;
	
	var get_theme_group_label = function(theme_group_key) {
		if ( typeof(easy_testimonials_admin.theme_group_labels[theme_group_key]) !== 'undefined' ) {
			return easy_testimonials_admin.theme_group_labels[theme_group_key];
		}
		return 'Themes';
	};	

	var build_post_options = function(posts) {
		var opts = [
			{
				label: 'Select a Testimonial',
				value: ''
			}
		];

		// build list of options from goals
		for( var i in posts ) {
			post = posts[i];
			opts.push( 
			{
				label: post.title.rendered,
				value: post.id
			});
		}
		return opts;
	};	

	var get_theme_options = function() {
		var theme_opts = [];
		for( theme_group in easy_testimonials_admin.themes ) {
			//theme_group_label = get_theme_group_label(theme_group);
			for ( theme_name in easy_testimonials_admin.themes[theme_group] ) {
				theme_opts.push({
					label: easy_testimonials_admin.themes[theme_group][theme_name],
					value: theme_name,
				});				
			}
		}
		return theme_opts;
	};
	
	var extract_label_from_options = function (opts, val) {
		var label = '';
		for (j in opts) {
			if ( opts[j].value == val ) {
				label = opts[j].label;
				break;
			}										
		}
		return label;
	};
	
	var checkbox_control = function (label, checked, onChangeFn) {
		// add checkboxes for which fields to display
		var controlOptions = {
			checked: checked,
			label: label,
			value: '1',
			onChange: onChangeFn,
		};	
		return el(  wp.components.CheckboxControl, controlOptions );
	};

	var iconGroup = [];
	iconGroup.push(	el(
			'path',
			{ d: "M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"}
		)
	);
	iconGroup.push(	el(
			'path',
			{ d: "M0 0h24v24H0z", fill: 'none' }
		)
	);
	
	var iconEl = el(
		'svg', 
		{ width: 24, height: 24 },
		iconGroup
	);	
	
	/**
	 * Every block starts by registering a new block type definition.
	 * @see https://wordpress.org/gutenberg/handbook/block-api/
	 */
	registerBlockType( 'easy-testimonials/single-testimonial', {
		/**
		 * This is the display title for your block, which can be translated with `i18n` functions.
		 * The block inserter will show this name.
		 */
		title: __( 'Single Testimonial' ),

		/**
		 * Blocks are grouped into categories to help users browse and discover them.
		 * The categories provided by core are `common`, `embed`, `formatting`, `layout` and `widgets`.
		 */
		category: 'easy-testimonials',

		/**
		 * Optional block extended support features.
		 */
		supports: {
			// Removes support for an HTML mode.
			html: false,
		},

		/**
		 * The edit function describes the structure of your block in the context of the editor.
		 * This represents what the editor will render when the block is used.
		 * @see https://wordpress.org/gutenberg/handbook/block-edit-save/#edit
		 *
		 * @param {Object} [props] Properties passed from the editor.
		 * @return {Element}       Element to render.
		 */
		edit: wp.data.withSelect( function( select ) {
					return {
						posts: select( 'core' ).getEntityRecords( 'postType', 'testimonial' )
					};
				} ) ( function( props ) {
							var retval = [];
							var inspector_controls = [],
								id = props.attributes.id || '',
								testimonial_title = props.attributes.testimonial_title || '',
								theme = props.attributes.theme || 'light_grey-classic_style',
								width = props.attributes.width || '',
								show_title = typeof(props.attributes.show_title) != 'undefined' ? props.attributes.show_title : false,
								use_excerpt = typeof(props.attributes.use_excerpt) != 'undefined' ? props.attributes.use_excerpt : false,
								reveal_full_content = typeof(props.attributes.reveal_full_content) != 'undefined' ? props.attributes.reveal_full_content : false,
								show_thumbs = typeof(props.attributes.show_thumbs) != 'undefined' ? props.attributes.show_thumbs : true,
								show_position = typeof(props.attributes.show_position) != 'undefined' ? props.attributes.show_position : true,
								show_date = typeof(props.attributes.show_date) != 'undefined' ? props.attributes.show_date : true,
								show_other = typeof(props.attributes.show_other) != 'undefined' ? props.attributes.show_other : true,
								hide_view_more = typeof(props.attributes.hide_view_more) != 'undefined' ? props.attributes.hide_view_more : true,
								output_schema_markup = typeof(props.attributes.output_schema_markup) != 'undefined' ? props.attributes.output_schema_markup : true,
								show_rating = typeof(props.attributes.show_rating) != 'undefined' ? props.attributes.show_rating : 'stars',
								focus = props.isSelected;
								
							props.setAttributes({
								theme: theme
							});								
								
						if ( !! focus || ! id.length ) {
							
							retval.push( el('h5', { className: 'block-heading' }, __('Easy Testimonials - Single Testimonial') ) );
							
							// add <select> to choose the testimonial
							var opts = build_post_options(props.posts);
							var controlOptions = {
								label: __('Select a Testimonial:'),
								value: id,
								onChange: function( newVal ) {
									testimonial_title = extract_label_from_options(opts, newVal);
									props.setAttributes({
										id: newVal,
										testimonial_title: testimonial_title
									});
								},
								options: opts,
							};
						
							retval.push(
									el(  wp.components.SelectControl, controlOptions )
							);
							
							var theme_fields = [];
							
							// add <select> to choose the Theme
							// note: Gutenburg's select control does not currently support optgroups
							var controlOptions = {
								label: __('Select a Theme:'),
								value: theme,
								onChange: function( newVal ) {
									props.setAttributes({
										theme: newVal
									});
								},
								options: get_theme_options(),
							};
						
							theme_fields.push(
								el(  wp.components.SelectControl, controlOptions )
							);

							if ( !easy_testimonials_admin.is_pro ) {
								theme_fields.push(
									el(  
										'a',
										{ 
											className: 'gp-upgrade-link',
											href: 'http://goldplugins.com/our-plugins/easy-testimonials-details/upgrade-to-easy-testimonials-pro/?utm_source=gutenburg_inspector&utm_campaign=pro_themes',
											target: '_blank',
										},
										__('Unlock All 100+ Pro Themes!') )
								);
							}
							
							inspector_controls.push(							
								el (
									wp.components.PanelBody,
									{
										title: __('Theme'),
										className: 'gp-panel-body',
										initialOpen: true,
									},
									theme_fields
								)
							);
							
							// add checkboxes for which fields to display
							var display_fields = [];							
							display_fields.push( 
								checkbox_control( __('Show Testimonial Title'), show_title, function( newVal ) {
									props.setAttributes({
										show_title: newVal,
									});
								})
							);
							
							display_fields.push( 
								checkbox_control( __('Use Testimonial Excerpt'), use_excerpt, function( newVal ) {
									props.setAttributes({
										use_excerpt: newVal,
									});
								})
							);

							display_fields.push( 
								checkbox_control( __('Click To Reveal Full Text'), reveal_full_content, function( newVal ) {
									props.setAttributes({
										reveal_full_content: newVal,
									});
								})
							);

							display_fields.push( 
								checkbox_control( __('Show Featured Image'), show_thumbs, function( newVal, ev ) {
									props.setAttributes({
										show_thumbs: newVal,
									});
								})
							);

							display_fields.push( 
								checkbox_control( __('Show Author\'s Title/Positon'), show_position, function( newVal ) {
									props.setAttributes({
										show_position: newVal,
									});
								})
							);
							display_fields.push( 
								checkbox_control( __('Show Testimonial Date'), show_date, function( newVal ) {
									props.setAttributes({
										show_date: newVal,
									});
								})
							);

							display_fields.push( 
								checkbox_control( __('Show Location/Product Reviewed'), show_other, function( newVal ) {
									props.setAttributes({
										show_other: newVal,
									});
								})
							);

							display_fields.push( 
								checkbox_control( __('Hide View More Testimonials Link'), hide_view_more, function( newVal ) {
									props.setAttributes({
										hide_view_more: newVal,
									});
								})
							);
							
							display_fields.push( 
								checkbox_control( __('Output Review Markup'), output_schema_markup, function( newVal ) {
									props.setAttributes({
										output_schema_markup: newVal,
									});
								})
							);
							
							inspector_controls.push( 
								el (
									wp.components.PanelBody,
									{
										title: __('Display Options'),
										className: 'gp-panel-body',
										initialOpen: false,
									},
									el('div', { className: 'janus_editor_field_group' }, display_fields)
								)
							);
							
							// add Show Rating options
							var show_rating_fields = [];
							var show_rating_opts = [
								{
									label: __('Before Testimonial'),
									value: 'before'
								},
								{
									label: __('After Testimonial'),
									value: 'after'
								},
								{
									label: __('As Stars'),
									value: 'stars'
								},
								{
									label: __('Do Not Show'),
									value: ''
								},								
							];
							var controlOptions = {
								label: __('How should ratings be displayed:'),
								onChange: function( newVal ) {
									props.setAttributes({
										show_rating: newVal
									});
								},
								options: show_rating_opts,
								selected: show_rating,
							};
							
							show_rating_fields.push(
									el(  wp.components.RadioControl, controlOptions )
							);
							
							inspector_controls.push( 
								el (
									wp.components.PanelBody,
									{
										title: __('Show Ratings'),
										className: 'gp-panel-body',
										initialOpen: false,
									},
									el('div', { className: 'janus_editor_field_group janus_editor_field_group_no_heading' }, show_rating_fields)
								)
							);


							// add text input for width
							var controlOptions = {
								label: __('Width:'),
								value: width,
								onChange: function( newVal ) {
									props.setAttributes({
										width: newVal
									});
								},
							};
						
							
							inspector_controls.push(							
								el (
									wp.components.PanelBody,
									{
										title: __('Width'),
										className: 'gp-panel-body',
										initialOpen: false,
									},
									el(  wp.components.TextControl, controlOptions )
								)
							);

							retval.push(
								el( wp.editor.InspectorControls, {}, inspector_controls ) 
							);

						}

						else {
							var inner_fields = [];
							inner_fields.push( el('h3', { className: 'block-heading' }, 'Easy Testimonials - Single Testimonial') );							
							inner_fields.push( el('blockquote', {}, testimonial_title) );
							retval.push( el('div', {'className': 'easy-testimonials-editor-not-selected'}, inner_fields ) );
						}
						
				return el( 'div', { className: 'easy-testimonials-single-testimonial-editor'}, retval );
			} ),

		/**
		 * The save function defines the way in which the different attributes should be combined
		 * into the final markup, which is then serialized by Gutenberg into `post_content`.
		 * @see https://wordpress.org/gutenberg/handbook/block-edit-save/#save
		 *
		 * @return {Element}       Element to render.
		 */
		save: function() {
			return null;
		},
		attributes: {
			id: {
				type: 'string',
			},
			testimonial_title: {
				type: 'string',
			},
			theme: {
				type: 'string',
			},
			width: {
				type: 'string',
			},
			show_title: {
				type: 'boolean',
			},
			use_excerpt: {
				type: 'boolean',
			},
			reveal_full_content: {
				type: 'boolean',
			},
			show_thumbs: {
				type: 'boolean',
			},
			show_position: {
				type: 'boolean',
			},
			show_date: {
				type: 'boolean',
			},
			show_other: {
				type: 'boolean',
			},
			hide_view_more: {
				type: 'boolean',
			},
			output_schema_markup: {
				type: 'boolean',
			},
			show_rating: {
				type: 'string',
			},
		},
		icon: iconEl,
	} );
} )(
	window.wp
);
