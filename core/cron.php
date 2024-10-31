<?php

add_filter( 'cron_schedules', 'add_minutes' );

function add_minutes( $intervals ) {
	$intervals['one_minute'] = array(
		'interval' => 60,
		'display'  => __( 'Every minute', 'push-world' )
	);

	return $intervals;
}

$abandoned_timeout = get_option( 'pushworld_abandoned_timeout', 60 );

if ( ! wp_next_scheduled( 'cron_check_abandoned' ) ) {
	wp_schedule_event( time() + $abandoned_timeout, 'one_minute', 'cron_check_abandoned' );
}

add_action( 'cron_check_abandoned', 'pw_check_abandoned' );

function pw_check_abandoned() {
	global $wpdb;

	$jobs = pw_cron_get_jobs();

	$table_name = $wpdb->prefix . "pushworld_tasks";

	foreach ( $jobs as $job ) {
		if ( $job->type == 'cart' ) {
			pw_push_notification( array( $job->device_id ), json_decode( $job->multicast, true ) );

			$wpdb->update( $table_name, array( 'status' => 10 ), array( 'id' => $job->id ) );
		}
	}
}

function pw_cron_get_jobs() {
	global $wpdb;

	$table_name = $wpdb->prefix . "pushworld_tasks";

	$jobs = $wpdb->get_results( "SELECT * FROM $table_name WHERE status=1 AND exec_time <= " . time() );

	foreach ( $jobs as $job ) {
		$wpdb->update( $table_name, array( 'status' => 2 ), array( 'id' => $job->id ) );
	}

	return $jobs;
}
