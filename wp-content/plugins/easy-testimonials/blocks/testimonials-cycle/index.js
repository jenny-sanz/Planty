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

	var build_category_options = function(categories) {
		var opts = [
			{
				label: 'All Categories',
				value: ''
			}
		];

		// build list of options from goals
		for( var i in categories ) {
			cat = categories[i];
			opts.push( 
			{
				label: cat.name,
				value: cat.slug
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
	
	var update_paginate_panel = function () {
		setTimeout( function () {
			var field_groups =  jQuery('.janus_editor_field_group');
			field_groups.each(function () {
				field_group = jQuery(this);
				var val = field_group.find(':checked').val();
				if ( 'max' == val ) {
					field_group.find('.field_per_page').show();
					field_group.find('.field_count').hide();
				}
				else if ( 'paginate' == val ) {
					field_group.find('.field_per_page').hide();
					field_group.find('.field_count').show();
				}
				else {
					field_group.find('.field_per_page').hide();
					field_group.find('.field_count').hide();
				}			
				
				return true;
			});
		}, 100 );
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
	registerBlockType( 'easy-testimonials/testimonials-cycle', {
		/**
		 * This is the display title for your block, which can be translated with `i18n` functions.
		 * The block inserter will show this name.
		 */
		title: __( 'Testimonials Cycle' ),

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
						categories: select( 'core' ).getEntityRecords( 'taxonomy', 'easy-testimonial-category', {
							order: 'asc',
							orderby: 'id'
						})
					};
				} ) ( function( props ) {
							var retval = [];
							var inspector_controls = [],
								id = props.attributes.id || '',
								testimonials_count = props.attributes.testimonials_count || '',
								testimonials_per_slide = props.attributes.testimonials_per_slide || '1',
								transition = props.attributes.transition || 'fade',
								timer = props.attributes.timer || '5000',
								pause_on_hover = typeof(props.attributes.pause_on_hover) != 'undefined' ? props.attributes.pause_on_hover : true,
								auto_height = typeof(props.attributes.auto_height) != 'undefined' ? props.attributes.auto_height : true,
								show_pager_icons = typeof(props.attributes.show_pager_icons) != 'undefined' ? props.attributes.show_pager_icons : true,
								prev_next = typeof(props.attributes.prev_next) != 'undefined' ? props.attributes.prev_next : true,
								display_pagers_above = typeof(props.attributes.display_pagers_above) != 'undefined' ? props.attributes.display_pagers_above : false,
								paused = typeof(props.attributes.paused) != 'undefined' ? props.attributes.paused : false,								
								category = props.attributes.category || '',
								paginate = props.attributes.paginate || 'all',
								count = props.attributes.count || '',
								testimonials_per_page = props.attributes.testimonials_per_page || '',
								order = props.attributes.order || '',
								orderby = props.attributes.orderby || '',
								testimonial_title = props.attributes.testimonial_title || '',
								theme = props.attributes.theme || 'light_grey-classic_style',
								width = props.attributes.width || '',
								show_title = typeof(props.attributes.show_title) != 'undefined' ? props.attributes.show_title : false,
								use_excerpt = typeof(props.attributes.use_excerpt) != 'undefined' ? props.attributes.use_excerpt : false,
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
							
						var slideshow_fields = [];
						var one_thru_ten = [];
						for( var i = 1; i <= 10; i++ ) {
							one_thru_ten.push({
								label: i,
								value: i,
							});
						}
						
						var transition_opts = [];
						for( var tran_key in easy_testimonials_admin.transitions ) {
							var tr = easy_testimonials_admin.transitions[tran_key];
							if ( !easy_testimonials_admin.is_pro && tr.pro ) {
								continue;
							}
							transition_opts.push({
								label: tr.label,
								value: tran_key,
							});
						}
						
						var seconds_opts = [];
						for( var i = 1; i <= 20; i++ ) {
							seconds_opts.push({
								label: i + __(' seconds'),
								value: (i * 1000),
							});
						}
						
						// add <select> to choose the Number of columns
						var controlOptions = {
							label: __('Testimonials Per Slide:'),
							value: testimonials_per_slide,
							onChange: function( newVal ) {
								props.setAttributes({
									testimonials_per_slide: newVal
								});
							},
							options: one_thru_ten,
						};
					
						slideshow_fields.push(
							el(  wp.components.SelectControl, controlOptions )
						);
						
						// add <select> to choose the Transition
						var controlOptions = {
							label: __('Transition:'),
							value: transition,
							onChange: function( newVal ) {
								props.setAttributes({
									transition: newVal
								});
							},
							options: transition_opts,
						};
					
						slideshow_fields.push(
							el(  wp.components.SelectControl, controlOptions )
						);
						
						// add <select> to choose the Seconds between transitions
						var controlOptions = {
							label: __('Time Between Transitions:'),
							value: timer,
							onChange: function( newVal ) {
								props.setAttributes({
									timer: newVal
								});
							},
							options: seconds_opts,
						};
					
						slideshow_fields.push(
							el(  wp.components.SelectControl, controlOptions )
						);
						
						slideshow_fields.push( 
							checkbox_control( __('Pause On Hover'), pause_on_hover, function( newVal ) {
								props.setAttributes({
									pause_on_hover: newVal,
								});
							})
						);
						
						slideshow_fields.push( 
							checkbox_control( __('Auto Height'), auto_height, function( newVal ) {
								props.setAttributes({
									auto_height: newVal,
								});
							})
						);
						
						slideshow_fields.push( 
							checkbox_control( __('Show Pager Icons'), show_pager_icons, function( newVal ) {
								props.setAttributes({
									show_pager_icons: newVal,
								});
							})
						);
						
						slideshow_fields.push( 
							checkbox_control( __('Show Previous and Next Buttons'), prev_next, function( newVal ) {
								props.setAttributes({
									prev_next: newVal,
								});
							})
						);
						
						slideshow_fields.push( 
							checkbox_control( __('Position Pagers Above Testimonial'), display_pagers_above, function( newVal ) {
								props.setAttributes({
									display_pagers_above: newVal,
								});
							})
						);
						
						slideshow_fields.push( 
							checkbox_control( __('Disable Auto Transition'), paused, function( newVal ) {
								props.setAttributes({
									paused: newVal,
								});
							})
						);
						
						inspector_controls.push(							
							el (
								wp.components.PanelBody,
								{
									title: __('Slideshow Options'),
									className: 'gp-panel-body',
									initialOpen: true,
								},
								slideshow_fields
							)
						);
												
						
						var category_fields = [];
						
						// add <select> to choose the Category
						var controlOptions = {
							label: __('Select a Category:'),
							value: category,
							onChange: function( newVal ) {
								props.setAttributes({
									category: newVal
								});
							},
							options: build_category_options(props.categories),
						};
					
						category_fields.push(
							el(  wp.components.SelectControl, controlOptions )
						);

						var orderby_opts = [
							{
								label: 'Title',
								value: 'title',
							},
							{
								label: 'Random',
								value: 'rand',
							},
							{
								label: 'ID',
								value: 'id',
							},
							{
								label: 'Author',
								value: 'author',
							},
							{
								label: 'Name',
								value: 'name',
							},
							{
								label: 'Date',
								value: 'date',
							},
							{
								label: 'Last Modified',
								value: 'last_modified',
							},
							{
								label: 'Parent ID',
								value: 'parent_id',
							},
						];

						// add <select> to choose the Order By Field
						var controlOptions = {
							label: __('Order By:'),
							value: orderby,
							onChange: function( newVal ) {
								props.setAttributes({
									orderby: newVal
								});
							},
							options: orderby_opts,
						};
					
						category_fields.push(
							el(  wp.components.SelectControl, controlOptions )
						);

						var order_opts = [
							{
								label: 'Ascending (A-Z)',
								value: 'asc',
							},
							{
								label: 'Descending (Z-A)',
								value: 'desc',
							},
						];

						// add <select> to choose the Order (asc, desc)
						var controlOptions = {
							label: __('Order:'),
							value: order,
							onChange: function( newVal ) {
								props.setAttributes({
									order: newVal
								});
							},
							options: order_opts,
						};
					
						category_fields.push(
							el(  wp.components.SelectControl, controlOptions )
						);
						
						inspector_controls.push(							
							el (
								wp.components.PanelBody,
								{
									title: __('Category'),
									className: 'gp-panel-body',
									initialOpen: false,
								},
								category_fields
							)
						);
						
						// add Testimonials Per Page options panel
						var per_page_fields = [];
						var per_page_opts = [
							{
								label: __('All On One Page'),
								value: 'all'
							},
							{
								label: __('Max Per Page'),
								value: 'max'
							},
							{
								label: __('Specific Number'),
								value: 'paginate'
							},
						];
						var controlOptions = {
							label: __('Testimonials Per Page:'),
							onChange: function( newVal ) {
								props.setAttributes({
									paginate: newVal
								});
								//update_paginate_panel(newVal);
								update_paginate_panel();
							},
							options: per_page_opts,
							selected: paginate,
							className: 'field_paginate',
						};

						per_page_fields.push(
								el(  wp.components.RadioControl, controlOptions )
						);

						// add text input for Count
						var controlOptions = {
							label: __('Number To Show:'),
							value: count,
							className: 'field_count',
							onChange: function( newVal ) {
								props.setAttributes({
									count: newVal
								});
							},
						};
						
						per_page_fields.push( 
							el(  wp.components.TextControl, controlOptions )
						);

						// add text input for Per Page
						var controlOptions = {
							label: __('Number Per Page:'),
							value: testimonials_per_page,
							className: 'field_per_page',
							onChange: function( newVal ) {
								props.setAttributes({
									testimonials_per_page: newVal
								});
							},
						};

						per_page_fields.push( 
							el(  wp.components.TextControl, controlOptions )
						);

						inspector_controls.push( 
							el (
								wp.components.PanelBody,
								{
									title: __('Testimonials Per Page'),
									className: 'gp-panel-body',
									initialOpen: false,
									onToggle: update_paginate_panel,
								},
								el('div', { className: 'janus_editor_field_group janus_editor_field_group_no_heading' }, per_page_fields)
							)
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
									initialOpen: false,
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
							checkbox_control( __('Show Featured Image'), show_thumbs, function( newVal ) {
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
									title: __('Display Fields'),
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

						var inner_fields = [];
						inner_fields.push( el('h3', { className: 'block-heading' }, 'Easy Testimonials - Testimonials Cycle') );
						inner_fields.push( el('blockquote', { className: 'testimonial-cycle-placeholder' }, __('“A slideshow of testimonials from your database.”') ) );
						retval.push( el('div', {'className': 'easy-testimonials-editor-not-selected'}, inner_fields ) );

				return el( 'div', { className: 'easy-testimonials-testimonials-cycle-editor'}, retval );
				
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
			category: {
				type: 'string',
			},
			testimonials_per_slide: {
				type: 'string',
			},
			transition: {
				type: 'string',
			},
			timer: {
				type: 'string',
			},
			pause_on_hover: {
				type: 'boolean',
			},
			auto_height: {
				type: 'boolean',
			},
			show_pager_icons: {
				type: 'boolean',
			},
			prev_next: {
				type: 'boolean',
			},
			display_pagers_above: {
				type: 'boolean',
			},
			paused: {
				type: 'boolean',
			},
			paginate: {
				type: 'string',
			},
			testimonials_per_page: {
				type: 'string',
			},
			count: {
				type: 'string',
			},
			category: {
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
