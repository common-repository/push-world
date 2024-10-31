<?php

require_once( __DIR__ . '/push.world-api-client/PushWorldApiInterface.php' );
require_once( __DIR__ . '/push.world-api-client/PushWorldApi.php' );
require_once( __DIR__ . '/push.world-api-client/Exception/PushWorldApiException.php' );
require_once( __DIR__ . '/push.world-api-client/Exception/PushWorldApiGetTokenException.php' );
require_once( __DIR__ . '/push.world-api-client/Exception/PushWorldApiForbbidenException.php' );
require_once( __DIR__ . '/push.world-api-client/Exception/PushWorldApiValidationException.php' );

add_action( 'wp_ajax_push_test', 'pw_push_test' );


function pw_filter_multicast( $pw_multicast ) {
	$default_title_option = get_option( 'pushworld_default_title',
		__( "Don't forget setup default push title", 'push-world' ) );
	$default_text_option  = get_option( 'pushworld_default_text',
		__( "Don't forget setup default push text", 'push-world' ) );

	$multicast_default = array(
		'title'     => mb_substr( $default_title_option, 0, 50 ),
		'text'      => mb_substr( $default_text_option, 0, 125 ),
		'duration'  => get_option( 'pushworld_default_duration', '60' ),
		'life_time' => get_option( 'pushworld_default_life_time', '3600' ),
		'url'       => get_option( 'pushworld_default_url', get_site_url() ),
	);

	foreach ( $multicast_default as $key => $value ) {
		$pw_multicast[ $key ] = ( array_key_exists( $key, $pw_multicast ) && ! empty( $pw_multicast[ $key ] ) ) ?
			$pw_multicast[ $key ] :
			$multicast_default[ $key ];
	}

	$default_icon = get_option( 'pushworld_default_icon' );

	if ( ! array_key_exists( 'image', $pw_multicast ) && ! empty( $default_icon ) ) {
		$pw_multicast['image'] = get_option( 'pushworld_default_icon' );
	}

	$pw_multicast['title'] = mb_substr( $pw_multicast['title'], 0, 50, 'UTF-8' );
	$pw_multicast['text']  = mb_substr( $pw_multicast['text'], 0, 125, 'UTF-8' );

	return $pw_multicast;
}

function pw_push_notification( $pw_subscribers, $pw_multicast ) {
	$pw_client_id     = get_option( 'pushworld_client_id' );
	$pw_client_secret = get_option( 'pushworld_client_secret' );
	$pw_platform_code = get_option( 'pushworld_platform_code' );
	$multicast        = pw_filter_multicast( $pw_multicast );

	try {
		$api  = new pushworld\api\PushWorldApi( $pw_client_id, $pw_client_secret );
		$api->multicastSend( $pw_platform_code, $multicast, $pw_subscribers );
	} catch ( Exception $e ) {
	}

	return true;
}


function pw_push_test() {

	if ( ! isset( $_COOKIE['pw_deviceid'] ) ) {
		echo json_encode( array( 'success' => false, 'message' => 'device_id not found' ) );
		die;
	}

	$subscribers = array( $_COOKIE['pw_deviceid'] );

	$multicast = array();

	pw_push_notification( $subscribers, $multicast );

	echo json_encode( array( 'success' => true ) );
	die;
}