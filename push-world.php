<?php

/**
 * Plugin Name: Push World
 * Plugin URI: https://push.world
 * Description: This plugin adds Push World code to site and has the function of returning the user to the forgotten shopping cart through push messages and some other cool features.
 * Version: 2.0.2
 * Author: Push World
 * Author URI: https://push.world
 * Text Domain: push-world
 * Domain Path: /languages
 * License: GPL2
 */

$pw_plugin_url = plugin_dir_url( __FILE__ );

require_once 'core/core.php';


/**
 * Initialize Push World plugin
 */
function pushworld_init() {

	load_plugin_textdomain( 'push-world', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	$website_url = get_site_url();

	// Initialize Core
	$admin = new Sunrise7( array(
		'file'       => __FILE__,
		'slug'       => 'push-world',
		'prefix'     => 'pushworld_',
		'textdomain' => 'push-world',
		'css'        => '',
		'js'         => '',
	) );

	$options = array(

		array(
			'type' => 'opentab',
			'name' => __( 'Settings', 'push-world' ),
		),

		/*array(
			'id'      => 'protocol',
			'type'    => 'radio',
			'default' => 'http',
			'name'    => __( 'Website protocol', 'push-world' ),
			'desc'    => __( 'Choose website protocol', 'push-world' ),
			'options' => array(
				array(
					'value' => 'http',
					'label' => __( 'Http', 'push-world' ),
				),
				array(
					'value' => 'https',
					'label' => __( 'Https', 'push-world' ),
				)
			)
		),

		array(
			'class'    => 'pw-check-file',
			'type'     => 'tablerow',
			'name'     => 'manifest.json',
			'filename' => 'manifest.json',
			'desc'     => __( 'Upload your manifest.json to root directory of your site', 'push-world' ),
			'content'  => '<p class="pw-file__success">' . __( 'File exists',
					'push-world' ) . '</p><p class="pw-file__error">' . __( 'File not found',
					'push-world' ) . '</p><button type="button" class="button button-secondary hide-if-no-js pw-btn__check" data-check="manifest.json">' . __( 'Recheck',
					'push-world' ) . '</button>',
		),

		array(
			'class'    => 'pw-check-file',
			'type'     => 'tablerow',
			'name'     => 'serviceworker.js',
			'filename' => 'serviceworker.js',
			'desc'     => __( 'Upload your serviceworker.js to root directory of your site', 'push-world' ),
			'content'  => '<p class="pw-file__success">' . __( 'File exists',
					'push-world' ) . '</p><p class="pw-file__error">' . __( 'File not found',
					'push-world' ) . '</p><button type="button" class="button button-secondary hide-if-no-js pw-btn__check" data-check="serviceworker.js">' . __( 'Recheck',
					'push-world' ) . '</button>',
		),*/

		array(
			'id'      => 'embed_code',
			'type'    => 'textarea',
			'default' => '',
			'rows'    => 10,
			'name'    => __( 'Embed Code:', 'push-world' ),
			'desc'    => __( 'Paste your embed code here', 'push-world' ),
		),

		// Close tab: Regular fields
		array(
			'type' => 'closetab',
		),

		array(
			'type' => 'opentab',
			'name' => __( 'API Settings', 'push-world' ),
		),

		array(
			'id'      => 'platform_code',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Platform code', 'push-world' ),
			'desc'    => __( 'Enter your Platformcode', 'push-world' ),
		),

		array(
			'id'      => 'client_id',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Client Id', 'push-world' ),
			'desc'    => __( 'Enter your Client Id', 'push-world' ),
		),

		array(
			'id'      => 'client_secret',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Client Secret', 'push-world' ),
			'desc'    => __( 'Enter your Client Secret', 'push-world' ),
		),

		array(
			'type' => 'closetab',
		),

		array(
			'type' => 'opentab',
			'name' => __( 'Default Push Settings', 'push-world' ),
		),

		array(
			'id'      => 'default_duration',
			'type'    => 'text',
			'default' => '60',
			'name'    => __( 'Push view duration', 'push-world' ),
			'desc'    => __( 'Time that push will be displayed. (in seconds)', 'push-world' ),
		),

		array(
			'id'      => 'default_life_time',
			'type'    => 'text',
			'default' => '3600',
			'name'    => __( 'Push Lifetime', 'push-world' ),
			'desc'    => __( 'Time that push will be waiting for user while that appear online. (in seconds)',
				'push-world' ),
		),

		array(
			'id'      => 'default_icon',
			'type'    => 'media',
			'default' => '',
			'name'    => __( 'Default Icon', 'push-world' ),
			'desc'    => __( 'Set Default Icon for your push notifications', 'push-world' ),
		),

		array(
			'id'      => 'default_title',
			'type'    => 'text',
			'default' => __( "Don't forget setup default push title", 'push-world' ),
			'name'    => __( 'Push title', 'push-world' ),
			'desc'    => __( 'Enter text for push title', 'push-world' ),
		),

		array(
			'id'      => 'default_text',
			'type'    => 'textarea',
			'default' => __( "Don't forget setup default push text", 'push-world' ),
			'name'    => __( 'Push text', 'push-world' ),
			'desc'    => __( 'Enter push text', 'push-world' ),
		),

		array(
			'id'      => 'default_url',
			'type'    => 'text',
			'default' => $website_url,
			'name'    => __( 'Push url', 'push-world' ),
			'desc'    => __( 'Url for push', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '<div class="b-content__row">
				<div class="b-content__col">
					<div class="b-button b-button--blue js-push-test">' . __( 'Test Push', 'push-world' ) . '</div>
				</div>
			</div> '
		),

		array(
			'type' => 'closetab',
		),


		array(
			'type' => 'opentab',
			'name' => __( 'WooCommerce Integration', 'push-world' ),
		),

		// Checkbox
		array(
			'id'      => 'woocommerce_enable',
			'type'    => 'checkbox',
			'default' => 'off',
			'name'    => __( 'Enable WooCommerce Integration', 'push-world' ),
			'label'   => '',
			'class'   => 'pw-enable-woocommerce'
		),

		array(
			'type'    => 'html',
			'content' => '<div class="b-tabs js-tabs b-tabs--woo">
				<span class="b-tabs__label is-active" data-for="#abandoned_cart">' . __( 'Abandoned cart',
					'push-world' ) . '</span>
				<span class="b-tabs__label" data-for="#pending_order">' . __( 'Pending order', 'push-world' ) . '</span>
				<span class="b-tabs__label" data-for="#processing_order">' . __( 'Processing order', 'push-world' ) . '</span>
				<span class="b-tabs__label" data-for="#onhold_order">' . __( 'On hold order', 'push-world' ) . '</span>
				<span class="b-tabs__label" data-for="#completed_order">' . __( 'Completed order', 'push-world' ) . '</span>
				<span class="b-tabs__label" data-for="#cancelled_order">' . __( 'Cancelled order', 'push-world' ) . '</span>
				<span class="b-tabs__label" data-for="#refunded_order">' . __( 'Refunded order', 'push-world' ) . '</span>
				<span class="b-tabs__label" data-for="#failed_order">' . __( 'Failed order', 'push-world' ) . '</span>
				</div>
				',
		),


		array(
			'type'    => 'html',
			'content' => '<div id="abandoned_cart" class="b-tabs__pane is-active">'
		),

		array(
			'type'    => 'html',
			'content' => '<h3>' . __( 'Abandoned cart', 'push-world' ) . '</h3>',
		),

		array(
			'id'      => 'abandoned_timeout',
			'type'    => 'text',
			'default' => '30',
			'name'    => __( 'Abandoned cart timeout (minutes)', 'push-world' ),
			'desc'    => __( 'After what time to send the push about the abandoned cart? (in minutes)', 'push-world' ),
		),

		array(
			'id'      => 'abandoned_icon',
			'type'    => 'media',
			'default' => '',
			'name'    => __( 'Icon for abandoned cart', 'push-world' ),
		),

		array(
			'id'      => 'abandoned_title',
			'type'    => 'text',
			'default' => __( 'You forgot something in your cart!', 'push-world' ),
			'name'    => __( 'Push title', 'push-world' ),
		),

		array(
			'id'      => 'abandoned_text',
			'type'    => 'textarea',
			'default' => __( 'Your products is steel waiting for you in the shopping cart', 'push-world' ),
			'name'    => __( 'Push text', 'push-world' ),
		),

		array(
			'id'      => 'abandoned_url',
			'type'    => 'text',
			'default' => $website_url,
			'name'    => __( 'Push url', 'push-world' ),
		),

		array(
			'id'      => 'abandoned_image',
			'type'    => 'media',
			'default' => '',
			'name'    => __( 'Image', 'push-world' ),
			'desc'    => __( 'Big image will be showing on Chrome browser on Windows platform', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '<div class="b-content__inline">'
		),

		array(
			'id'      => 'abandoned_action1_title',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 1 title', 'push-world' ),
		),

		array(
			'id'      => 'abandoned_action1_url',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 1 url', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '</div>'
		),

		array(
			'type'    => 'html',
			'content' => '<div class="b-content__inline">'
		),

		array(
			'id'      => 'abandoned_action2_title',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 2 title', 'push-world' ),
		),

		array(
			'id'      => 'abandoned_action2_url',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 2 url', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '</div>'
		),

		array(
			'type'    => 'html',
			'content' => '</div><!-- End Abandoned Cart -->'
		),

		array(
			'type'    => 'html',
			'content' => '<div id="pending_order" class="b-tabs__pane">'
		),

		array(
			'type'    => 'html',
			'content' => '<h3>' . __( 'Pending order', 'push-world' ) . '</h3>',
		),

		array(
			'id'      => 'pending_icon',
			'type'    => 'media',
			'default' => '',
			'name'    => __( 'Icon', 'push-world' ),
		),

		array(
			'id'      => 'pending_title',
			'type'    => 'text',
			'default' => __( '%first_name%! Your order status was changed', 'push-world' ),
			'name'    => __( 'Push title', 'push-world' ),
		),

		array(
			'id'      => 'pending_text',
			'type'    => 'textarea',
			'default' => __( 'Order №%order_number% is waiting for payment', 'push-world' ),
			'name'    => __( 'Push text', 'push-world' ),
		),

		array(
			'id'      => 'pending_url',
			'type'    => 'text',
			'default' => $website_url,
			'name'    => __( 'Push url', 'push-world' ),
		),

		array(
			'id'      => 'pending_image',
			'type'    => 'media',
			'default' => '',
			'name'    => __( 'Image', 'push-world' ),
			'desc'    => __( 'Big image will be showing on Chrome browser on Windows platform', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '<div class="b-content__inline">'
		),

		array(
			'id'      => 'pending_action1_title',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 1 text', 'push-world' ),
		),

		array(
			'id'      => 'pending_action1_url',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 1 url', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '</div>'
		),

		array(
			'type'    => 'html',
			'content' => '<div class="b-content__inline">'
		),

		array(
			'id'      => 'pending_action2_title',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 2 title', 'push-world' ),
		),

		array(
			'id'      => 'pending_action2_url',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 2 url', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '</div>'
		),

		array(
			'type'    => 'html',
			'content' => '</div><!-- End Pending Order -->'
		),

		array(
			'type'    => 'html',
			'content' => '<div id="processing_order" class="b-tabs__pane">'
		),

		array(
			'type'    => 'html',
			'content' => '<h3>' . __( 'Processing order', 'push-world' ) . '</h3>',
		),

		array(
			'id'      => 'processing_icon',
			'type'    => 'media',
			'default' => '',
			'name'    => __( 'Icon', 'push-world' ),
		),

		array(
			'id'      => 'processing_title',
			'type'    => 'text',
			'default' => __( '%first_name%! Your order status was changed', 'push-world' ),
			'name'    => __( 'Push title', 'push-world' ),
		),

		array(
			'id'      => 'processing_text',
			'type'    => 'textarea',
			'default' => __( 'Order №%order_number% is being processed', 'push-world' ),
			'name'    => __( 'Push text', 'push-world' ),
		),

		array(
			'id'      => 'processing_url',
			'type'    => 'text',
			'default' => $website_url,
			'name'    => __( 'Push url', 'push-world' ),
		),

		array(
			'id'      => 'processing_image',
			'type'    => 'media',
			'default' => '',
			'name'    => __( 'Image', 'push-world' ),
			'desc'    => __( 'Big image will be showing on Chrome browser on Windows platform', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '<div class="b-content__inline">'
		),

		array(
			'id'      => 'processing_action1_title',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 1 text', 'push-world' ),
		),

		array(
			'id'      => 'processing_action1_url',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 1 url', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '</div>'
		),

		array(
			'type'    => 'html',
			'content' => '<div class="b-content__inline">'
		),

		array(
			'id'      => 'processing_action2_title',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 2 title', 'push-world' ),
		),

		array(
			'id'      => 'processing_action2_url',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 2 url', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '</div>'
		),

		array(
			'type'    => 'html',
			'content' => '</div><!-- End Processing Order -->'
		),

		array(
			'type'    => 'html',
			'content' => '<div id="onhold_order" class="b-tabs__pane">'
		),

		array(
			'type'    => 'html',
			'content' => '<h3>' . __( 'On hold order', 'push-world' ) . '</h3>',
		),

		array(
			'id'      => 'on-hold_icon',
			'type'    => 'media',
			'default' => '',
			'name'    => __( 'Icon', 'push-world' ),
		),

		array(
			'id'      => 'on-hold_title',
			'type'    => 'text',
			'default' => __( '%first_name%! Your order status was changed', 'push-world' ),
			'name'    => __( 'Push title', 'push-world' ),
		),

		array(
			'id'      => 'on-hold_text',
			'type'    => 'textarea',
			'default' => __( 'Order №%order_number% is on hold', 'push-world' ),
			'name'    => __( 'Push text', 'push-world' ),
		),

		array(
			'id'      => 'on-hold_url',
			'type'    => 'text',
			'default' => $website_url,
			'name'    => __( 'Push url', 'push-world' ),
		),

		array(
			'id'      => 'on-hold_image',
			'type'    => 'media',
			'default' => '',
			'name'    => __( 'Image', 'push-world' ),
			'desc'    => __( 'Big image will be showing on Chrome browser on Windows platform', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '<div class="b-content__inline">'
		),

		array(
			'id'      => 'on-hold_action1_title',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 1 text', 'push-world' ),
		),

		array(
			'id'      => 'on-hold_action1_url',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 1 url', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '</div>'
		),

		array(
			'type'    => 'html',
			'content' => '<div class="b-content__inline">'
		),

		array(
			'id'      => 'on-hold_action2_title',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 2 title', 'push-world' ),
		),

		array(
			'id'      => 'on-hold_action2_url',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 2 url', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '</div>'
		),

		array(
			'type'    => 'html',
			'content' => '</div><!-- End On Hold Order -->'
		),

		array(
			'type'    => 'html',
			'content' => '<div id="completed_order" class="b-tabs__pane">'
		),

		array(
			'type'    => 'html',
			'content' => '<h3>' . __( 'Completed order', 'push-world' ) . '</h3>',
		),

		array(
			'id'      => 'completed_icon',
			'type'    => 'media',
			'default' => '',
			'name'    => __( 'Icon', 'push-world' ),
		),

		array(
			'id'      => 'completed_title',
			'type'    => 'text',
			'default' => __( '%first_name%! Your order status was changed', 'push-world' ),
			'name'    => __( 'Push title', 'push-world' ),
		),

		array(
			'id'      => 'completed_text',
			'type'    => 'textarea',
			'default' => __( 'Order №%order_number% is completed', 'push-world' ),
			'name'    => __( 'Push text', 'push-world' ),
		),

		array(
			'id'      => 'completed_url',
			'type'    => 'text',
			'default' => $website_url,
			'name'    => __( 'Push url', 'push-world' ),
		),

		array(
			'id'      => 'completed_image',
			'type'    => 'media',
			'default' => '',
			'name'    => __( 'Image', 'push-world' ),
			'desc'    => __( 'Big image will be showing on Chrome browser on Windows platform', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '<div class="b-content__inline">'
		),

		array(
			'id'      => 'completed_action1_title',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 1 text', 'push-world' ),

		),

		array(
			'id'      => 'completed_action1_url',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 1 url', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '</div>'
		),

		array(
			'type'    => 'html',
			'content' => '<div class="b-content__inline">'
		),

		array(
			'id'      => 'completed_action2_title',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 2 title', 'push-world' ),
		),

		array(
			'id'      => 'completed_action2_url',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 2 url', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '</div>'
		),


		array(
			'type'    => 'html',
			'content' => '</div><!-- End Completed Order -->'
		),

		array(
			'type'    => 'html',
			'content' => '<div id="cancelled_order" class="b-tabs__pane">'
		),


		array(
			'type'    => 'html',
			'content' => '<h3>' . __( 'Cancelled order', 'push-world' ) . '</h3>',
		),

		array(
			'id'      => 'cancelled_icon',
			'type'    => 'media',
			'default' => '',
			'name'    => __( 'Icon', 'push-world' ),
		),

		array(
			'id'      => 'cancelled_title',
			'type'    => 'text',
			'default' => __( '%first_name%! Your order status was changed', 'push-world' ),
			'name'    => __( 'Push title', 'push-world' ),
		),

		array(
			'id'      => 'cancelled_text',
			'type'    => 'textarea',
			'default' => __( 'Order №%order_number% is cancelled', 'push-world' ),
			'name'    => __( 'Push text', 'push-world' ),
		),

		array(
			'id'      => 'cancelled_url',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Push url', 'push-world' ),
		),

		array(
			'id'      => 'cancelled_image',
			'type'    => 'media',
			'default' => '',
			'name'    => __( 'Image', 'push-world' ),
			'desc'    => __( 'Big image will be showing on Chrome browser on Windows platform', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '<div class="b-content__inline">'
		),

		array(
			'id'      => 'cancelled_action1_title',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 1 text', 'push-world' ),
		),

		array(
			'id'      => 'cancelled_action1_url',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 1 url', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '</div>'
		),

		array(
			'type'    => 'html',
			'content' => '<div class="b-content__inline">'
		),

		array(
			'id'      => 'cancelled_action2_title',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 2 title', 'push-world' ),
		),

		array(
			'id'      => 'cancelled_action2_url',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 2 url', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '</div>'
		),

		array(
			'type'    => 'html',
			'content' => '</div><!-- End Cancelled Order -->'
		),

		array(
			'type'    => 'html',
			'content' => '<div id="refunded_order" class="b-tabs__pane">'
		),

		array(
			'type'    => 'html',
			'content' => '<h3>' . __( 'Refunded order', 'push-world' ) . '</h3>',
		),

		array(
			'id'      => 'refunded_icon',
			'type'    => 'media',
			'default' => '',
			'name'    => __( 'Icon', 'push-world' ),
		),

		array(
			'id'      => 'refunded_title',
			'type'    => 'text',
			'default' => __( '%first_name%! Your order status was changed', 'push-world' ),
			'name'    => __( 'Push title', 'push-world' ),
		),

		array(
			'id'      => 'refunded_text',
			'type'    => 'textarea',
			'default' => __( 'Order №%order_number% is refunded', 'push-world' ),
			'name'    => __( 'Push text', 'push-world' ),
		),

		array(
			'id'      => 'refunded_url',
			'type'    => 'text',
			'default' => $website_url,
			'name'    => __( 'Push url', 'push-world' ),
		),

		array(
			'id'      => 'refunded_image',
			'type'    => 'media',
			'default' => '',
			'name'    => __( 'Image', 'push-world' ),
			'desc'    => __( 'Big image will be showing on Chrome browser on Windows platform', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '<div class="b-content__inline">'
		),

		array(
			'id'      => 'refunded_action1_title',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 1 text', 'push-world' ),
		),

		array(
			'id'      => 'refunded_action1_url',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 1 url', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '</div>'
		),

		array(
			'type'    => 'html',
			'content' => '<div class="b-content__inline">'
		),

		array(
			'id'      => 'refunded_action2_title',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 2 title', 'push-world' ),
		),

		array(
			'id'      => 'refunded_action2_url',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 2 url', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '</div>'
		),

		array(
			'type'    => 'html',
			'content' => '</div><!-- End Refunded Order -->'
		),

		array(
			'type'    => 'html',
			'content' => '<div id="failed_order" class="b-tabs__pane">'
		),

		array(
			'type'    => 'html',
			'content' => '<h3>' . __( 'Failed order', 'push-world' ) . '</h3>',
		),

		array(
			'id'      => 'failed_icon',
			'type'    => 'media',
			'default' => '',
			'name'    => __( 'Icon', 'push-world' ),
		),

		array(
			'id'      => 'failed_title',
			'type'    => 'text',
			'default' => __( '%first_name%! Your order status was changed', 'push-world' ),
			'name'    => __( 'Push title', 'push-world' ),
		),

		array(
			'id'      => 'failed_text',
			'type'    => 'textarea',
			'default' => __( 'Order №%order_number% is failed', 'push-world' ),
			'name'    => __( 'Push text', 'push-world' ),
		),

		array(
			'id'      => 'failed_url',
			'type'    => 'text',
			'default' => $website_url,
			'name'    => __( 'Push url', 'push-world' ),
		),

		array(
			'id'      => 'failed_image',
			'type'    => 'media',
			'default' => '',
			'name'    => __( 'Image', 'push-world' ),
			'desc'    => __( 'Big image will be showing on Chrome browser on Windows platform', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '<div class="b-content__inline">'
		),

		array(
			'id'      => 'failed_action1_title',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 1 text', 'push-world' ),
		),

		array(
			'id'      => 'failed_action1_url',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 1 url', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '</div>'
		),

		array(
			'type'    => 'html',
			'content' => '<div class="b-content__inline">'
		),

		array(
			'id'      => 'failed_action2_title',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 2 title', 'push-world' ),
		),

		array(
			'id'      => 'failed_action2_url',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Action 2 url', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '</div>'
		),

		array(
			'type'    => 'html',
			'content' => '</div><!-- End Failed Order -->'
		),

		array(
			'type' => 'closetab',
		),
		// -----------------------------------


		// Open tab: Extra fields
		array(
			'type' => 'opentab',
			'name' => __( 'Help', 'push-world' ),
		),

		array(
			'type' => 'title',
			'name' => __( 'Replacements list', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '<p>' . __( 'You can replace following variables in order statuses title and text (not for abandoned cart messages):',
					'push-world' ) . ' <br>
			<i>%order_number%, %first_name%, %last_name%, %address_1%, %address_2%, %city%, %state%, %country%, %postcode%, %payment_method%</i></p></br>',
		),

		array(
			'type' => 'title',
			'name' => __( 'Step 1', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => __( 'Register at <a href="http://push-world.local/user/register">Push.World</a> through social networks or email',
				'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '<p><img class="pw-image" src="' . plugin_dir_url( __FILE__ ) . 'assets/imgs/step_1_email.jpg"></p><p><img class="pw-image" src="' . plugin_dir_url( __FILE__ ) . 'assets/imgs/step_1_soc.jpg"></p>',
		),

		array(
			'type' => 'title',
			'name' => __( 'Step 2', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => __( 'Create platform, choose your website protocol and encoding, upload your website default icon',
				'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '<p><img class="pw-image" src="' . plugin_dir_url( __FILE__ ) . 'assets/imgs/step_2_http.jpg"></p>',
		),

		array(
			'type' => 'title',
			'name' => __( 'Step 3', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => __( 'Choose and set up widgets which you want to use.', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '<p><img class="pw-image" src="' . plugin_dir_url( __FILE__ ) . 'assets/imgs/step_3_http.jpg"></p>',
		),

		array(
			'type' => 'title',
			'name' => __( 'Step 4', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => __( 'Copy embed code and paste it to WordPress plugin <i>For https website don\'t forget put serviceworker.js and manifest.json to website root directory via ftp or hosting panel file manager.</i>',
				'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '<p><img class="pw-image" src="' . plugin_dir_url( __FILE__ ) . 'assets/imgs/step_4_http.jpg"></p><p><img class="pw-image" src="' . plugin_dir_url( __FILE__ ) . 'assets/imgs/step_4_https.jpg"></p>',
		),

		array(
			'type' => 'title',
			'name' => __( 'Step 5', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => __( 'Push "Save changes" button and enjoy.', 'push-world' ),
		),

		array(
			'type'    => 'html',
			'content' => '<p><img class="pw-image" src="' . plugin_dir_url( __FILE__ ) . 'assets/imgs/step_5.jpg"></p>',
		),

		array(
			'type'    => 'html',
			'content' => __( 'For more help visit <a href="http://push.world/help">Push.World</a>', 'push-world' ),
		),


		array(
			'type' => 'closetab',
		)
	);

	$admin->add_menu( array(
		'page_title' => __( 'Pushworld Plugin Settings', 'push-world' ),
		'menu_title' => __( 'Push World', 'push-world' ),
		'capability' => 'manage_options',
		'slug'       => 'plugin-pushworld-settings',
		'icon_url'   => plugin_dir_url( __FILE__ ) . 'assets/imgs/logo.svg',
		'position'   => '80',
		'options'    => $options,
	) );

	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pushworld_plugin_links' );
}

add_action( 'plugins_loaded', 'pushworld_init' );


function pushworld_plugin_links( $links ) {
	$settings_link = "<a href='admin.php?page=plugin-pushworld-settings'>Settings</a>";
	array_unshift( $links, $settings_link );
	$faqLink = "<a href='https://push.world/?utm_source=woordpress-dashboard' target='_blank'>FAQ</a>";
	array_unshift( $links, $faqLink );

	return $links;
}


function pushworld_embed_code() {
	$embed_code = stripslashes( get_option( 'pushworld_embed_code' ) );
	echo wp_kses( $embed_code, array( 'script' => array() ) );
}

function pushworld_manifest_link() {
	echo '<link rel="manifest" href="/manifest.json">';
}

if ( ! is_admin() ) {
	add_action( 'wp_footer', 'pushworld_embed_code' );

	if ( get_option( 'pushworld_protocol' ) === 'https' ) {
		add_action( 'wp_head', 'pushworld_manifest_link' );
	}
}


function pushworld_enqueue_scripts_admin( $hook ) {
	if ( 'toplevel_page_plugin-pushworld-settings' != $hook ) {
		return;
	}

	global $pw_plugin_url;

	wp_register_script( "pw_material", $pw_plugin_url . 'assets/js/pw-theme.js', array( 'jquery' ) );

	wp_localize_script( 'pw_material', 'pwPushTestAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

	wp_enqueue_script( 'pw_material' );
}


function pushworld_enqueue_styles_admin( $hook ) {
	if ( 'toplevel_page_plugin-pushworld-settings' != $hook ) {
		return;
	}

	global $pw_plugin_url;

	wp_register_style( 'pw_material', $pw_plugin_url . 'assets/css/pw-theme.css', false, '1.0.0', 'all' );

	wp_enqueue_style( 'pw_material' );
}


if ( is_admin() ) {
	add_action( 'admin_enqueue_scripts', 'pushworld_enqueue_scripts_admin' );
	add_action( 'admin_enqueue_scripts', 'pushworld_enqueue_styles_admin' );
}

add_action( 'plugins_loaded', 'add_pw_integration' );

function add_pw_integration() {
	require_once 'core/push.php';

	if ( get_option( 'pushworld_woocommerce_enable' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			require_once 'core/pw-wc-integration.php';
			require_once 'core/cron.php';
		}
	}

}
