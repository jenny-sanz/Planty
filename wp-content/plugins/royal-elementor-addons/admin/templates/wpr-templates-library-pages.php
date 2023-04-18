<?php
namespace WprAddons\Admin\Templates;
use WprAddons\Classes\Utilities;
use WprAddons\Admin\Templates\WPR_Templates_Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WPR_Templates_Library_Pages setup
 *
 * @since 1.0
 */
class WPR_Templates_Library_Pages {

	/**
	** Constructor
	*/
	public function __construct() {

		// Template Library Popup
		add_action( 'wp_ajax_render_library_templates_pages', [ $this, 'render_library_templates_pages' ] );

	}

	/**
	** Template Library Popup
	*/
	public static function render_library_templates_pages() {
		$kits = WPR_Templates_Data::get_available_kits_for_pages();
		
		?>

		<div class="wpr-tplib-sidebar">
			<div class="wpr-tplib-price" data-filter="mixed">
				<h3>
					<span><?php esc_html_e( 'Mixed', 'wpr-addons' ); ?></span>
					<i class="fas fa-angle-down"></i>
				</h3>
				
				<div class="wpr-tplib-price-list">
					<ul>
						<li data-filter="mixed"><?php esc_html_e( 'Mixed', 'wpr-addons' ); ?></li>
						<li data-filter="free"><?php esc_html_e( 'Free', 'wpr-addons' ); ?></li>
						<li data-filter="pro"><?php esc_html_e( 'Premium', 'wpr-addons' ); ?></li>
					</ul>
				</div>
			</div>
			<div class="wpr-tplib-search">
				<input type="text" placeholder="Search Template">
				<i class="eicon-search"></i>
			</div>
		</div>

		<div class="wpr-tplib-template-gird elementor-clearfix">
			<div class="wpr-tplib-template-gird-inner">

			<?php
			
			foreach( $kits as $kit => $data ) :
				
				foreach( $data['pages'] as $key => $page ) :

					$template_title = $data['name'] .' - '. str_replace('-', ' ', ucwords($page));
					$template_price = ('pro' === $data['price'] && !wpr_fs()->can_use_premium_code()) ? 'pro' : 'free';
					$template_class = 'pro' === $template_price ? ' wpr-tplib-pro-wrap' : '';
					$preview_url = $data['preview'][$key];

			?>

				<div class="wpr-tplib-template-wrap<?php echo esc_attr($template_class); ?>" data-title="<?php echo esc_attr(strtolower($template_title)); ?>" data-price="<?php echo esc_attr($template_price); ?>">
						<div class="wpr-tplib-template" data-slug="<?php echo esc_attr($page); ?>" data-kit="<?php echo esc_attr($kit); ?>" data-preview-type="iframe" data-preview-url="<?php echo esc_attr($preview_url); ?>">
							<div class="wpr-tplib-template-media">
								<img src="<?php echo esc_url('https://royal-elementor-addons.com/library/templates-kit/'. $kit .'/'. $page .'.jpg'); ?>">
								<div class="wpr-tplib-template-media-overlay">
									<i class="eicon-eye"></i>
								</div>
							</div>
							<div class="wpr-tplib-template-footer elementor-clearfix">
								<h3>
									<span><?php echo esc_html($data['name']) ?></span>
									<span><?php echo '&nbsp;- '. esc_html(ucwords($page)); ?></span>
								</h3>

								<?php if ( 'pro' === $data['price'] && !wpr_fs()->can_use_premium_code() ) : ?>
									<span class="wpr-tplib-insert-template wpr-tplib-insert-pro"><i class="eicon-star"></i> <span><?php esc_html_e( 'Go Pro', 'wpr-addons' ); ?></span></span>
								<?php else : ?>
									<span class="wpr-tplib-insert-template"><i class="eicon-file-download"></i> <span><?php esc_html_e( 'Insert', 'wpr-addons' ); ?></span></span>
								<?php endif; ?>
							</div>
						</div>
					</div>

				<?php endforeach; ?>

			<?php endforeach; ?>

			</div>
		</div>
		
		<?php

		wp_die();
	}

}
