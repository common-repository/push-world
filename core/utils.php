<?php

function pw_log( $message ) {
	global $wpdb;

	$table_log = $wpdb->prefix . 'pushworld_log';

	$sql = "CREATE TABLE IF NOT EXISTS $table_log (
                id INT NOT NULL AUTO_INCREMENT,
         		message TEXT NOT NULL,
         		type TEXT NOT NULL,
         		PRIMARY KEY (`id`)
	        ) ENGINE='InnoDB' CHARSET='utf8'";

	$wpdb->query( $sql );


	$wpdb->insert( $table_log, array( 'message' => $message, 'type' => 'log' ) );
}