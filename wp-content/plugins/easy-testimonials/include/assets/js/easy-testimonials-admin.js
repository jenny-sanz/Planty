var ezt_replace_last_instance = function (srch, repl, str) {
	n = str.lastIndexOf(srch);
	if (n >= 0 && n + srch.length >= str.length) {
		str = str.substring(0, n) + repl;
	}
	return str;
}

/* Demo Content Buttons */
jQuery(function () {
	var show_message = function (msg) {
		jQuery('#ezt_ajax_demo_message').html('<p>' + msg + '</p>')
										.css({
											'border': '1px solid green',
											'border-left-width': '4px',
											'color': 'green',
											'margin': '25px 0 5px',
											'padding': '0px 15px'
										})
										.fadeIn();
	};
	
	jQuery('#ezt_btn_delete_demo_testimonials').on('click', function (e) {
		jQuery(this).attr('disabled', 'true');

		var btn = this;
		var params = {
			'action': 'ezt_delete_demo_testimonials'
		};
			
		jQuery.post(ajaxurl, params, function (resp) {
			jQuery(btn).removeAttr('disabled');
			show_message(easy_testimonials_admin_strings.str_demo_content_deleted);
		});
		
		e.preventDefault();
		return false;
	});
	
	jQuery('#ezt_btn_create_demo_testimonials').on('click', function (e) {		
		jQuery(this).attr('disabled', 'true');

		var btn = this;
		var params = {
			'action': 'ezt_create_demo_testimonials'
		};
			
		jQuery.post(ajaxurl, params, function (resp) {
			jQuery(btn).removeAttr('disabled');
			show_message(easy_testimonials_admin_strings.str_demo_content_created);
		});
		
		e.preventDefault();
		return false;		
	});
});

/* Review Request */
jQuery(function () {

	/*
	 * Send an AJAX message to the server to tell it not to show the
	 * review alert box again.
	 */
	var record_alert_dismissed = function (dismissable_id) {
		jQuery.ajax({
			type:"GET",
			url: ajaxurl,
			data: { 
				action: "easy_testimonials_dismiss_review_alert",
			},
			success: function (data) {				
			}
		});
	};
	
	/*
	 * Wire up the dismiss button to alert the server when it's clicked
	 */
	var setup_review_ask = function () {
		var box = jQuery('#easy_testimonials_review_request_alert');
		if ( box.length == 0 ) {
			return;
		}
		box.on('click', '.notice-dismiss', function () {
			record_alert_dismissed();			
		});
	};	
	
	easy_t_setup_ajax_forms();
	setup_review_ask();
});

/* Link Upgrade Labels */
function ezt_link_upgrade_labels()
{
	if (jQuery('.plugin_is_not_registered').length == 0) {
		return;
	}
	jQuery('.easy-t-radio-button').each(function (index) {
		var my_radio = jQuery(this).find('input[type=radio]');
		if (my_radio)
		{
			var disabled = (my_radio.attr('disabled') && my_radio.attr('disabled').toLowerCase() == 'disabled');
			if (disabled) {
				var my_em = jQuery(this).find('label em:first');
				var my_img = jQuery(this).find('label img');
				if (my_em.length > 0 || my_img.length > 0) {
					var my_id = my_radio.attr('id');
					var buy_url = 'https://goldplugins.com/our-plugins/easy-testimonials-details/upgrade-to-easy-testimonials-pro/?utm_campaign=upgrade_themes&utm_source=theme_selection&utm_banner=' + my_id;
					var link_template = '<a href="@buy_url" target="_blank"></a>';
					var link = link_template.replace(/@buy_url/g, buy_url);
					my_em.wrap(link);
					my_img.wrap(link);
				}				
			}
		}
	});
}

/* Theme Preview */
jQuery(document).ready(function() {

	var refresh_theme_preview = function() {

		var new_theme = jQuery('#testimonials_style').val();
		
		// probably running when it should not be
		if ( !new_theme ) {
			return;
		}
		
		var pro_required = 0;
		
		if (new_theme.indexOf("-disabled") >= 0){
			new_theme = new_theme.replace("-disabled", "");
			pro_required = 1;
		}
		
		new_theme = new_theme.replace("-style","");
		
		
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		post_data = {
			'action': 'easy_testimonials_render_preview_html',
			'theme': new_theme
		};
		jQuery.post(ajaxurl, post_data, function(response) {
			$('#easy_t_preview_inner').html(response);
		});		
		
		if(pro_required){
			jQuery('#easy_t_preview .easy_testimonials_not_registered').show();
			jQuery('.submit input[type="submit"]').prop('disabled', true);
		} else {
			jQuery('#easy_t_preview .easy_testimonials_not_registered').hide();
			jQuery('.submit input[type="submit"]').prop('disabled', false);
		}
	};

	if ( jQuery('#testimonials_style').length > 0 ) {
		refresh_theme_preview();
		jQuery('#testimonials_style').change(refresh_theme_preview);
	}
});

/* Setup AJAX Forms */
 var easy_t_setup_ajax_forms = function() {
	$ = jQuery;
	var forms = $('div[data-gp-ajax-form="1"]');
	if (forms.length > 0) {
		forms.each(function () {
			var f = this;
			var btns = $(this).find('.button[type="submit"]').on('click', 
				function () {
					easy_t_submit_ajax_form(f);
					return false;
				} 
			);
		});
	}
}; 

/* Submit AJAX Form */
var easy_t_submit_ajax_form = function (f) {
	var msg = jQuery('<p><span class="fa fa-refresh fa-spin"></span><em> One moment..</em></p>');	
	var f = jQuery(f).after(msg).detach();
	var enc = f.attr('enctype');
	var act = f.attr('action');
	var meth = f.attr('method');
	var submit_with_ajax = ( f.data('ajax-submit') == 1 );
	var ok_to_send_site_details = ( f.find('input[name="include_wp_info"]:checked').length > 0 );
	
	if ( !ok_to_send_site_details ) {
		f.find('.gp_galahad_site_details').remove();
	}
	
	var wrap = f.wrap('<form></form>').parent();
	wrap.attr('enctype', f.attr('enctype'));
	wrap.attr('action', f.attr('action'));
	wrap.attr('method', f.attr('method'));
	wrap.find('#submit').attr('id', '#notsubmit');

	if ( !submit_with_ajax ) {
		jQuery('body').append(wrap);
		setTimeout(function () {
			wrap.submit();
		}, 500);	
		return false;
	}
	
	data = wrap.serialize();
	
	$.ajax(act,
	{
		crossDomain: true,
		method: 'post',
		data: data,
		dataType: "json",
		success: function (ret) {
			var r = jQuery(ret)[0];
			msg.html('<p class="ajax_response_message">' + r.msg + '</p>');
		}
	});		
};