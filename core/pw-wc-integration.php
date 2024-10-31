<?php

if ( ! class_exists( 'WC_PushWorld' ) ) {

	class WC_PushWorld extends WC_Integration {
		private $db_orders;
		private $db_tasks;


		public function __construct() {

			if ( $this->woocommerce_version_check( '3.0.0' ) ) {
				$this->db_orders = "pushworld_orders";
				$this->db_tasks  = "pushworld_tasks";

				$this->create_tables();

				add_action( 'wp_footer', array( $this, 'pw_user_script_enqueue' ) );

				add_action( 'woocommerce_new_order', array( $this, 'action_woocommerce_new_order' ) );
				add_action( 'woocommerce_order_status_changed', array( $this, 'action_order_status_update' ), 10, 3 );

				add_action( 'wp_ajax_check_abandoned', array( $this, 'check_abandoned' ) );
				add_action( 'wp_ajax_nopriv_check_abandoned', array( $this, 'check_abandoned' ) );

				add_action( 'wp_ajax_order_complete', array( $this, 'order_complete' ) );
				add_action( 'wp_ajax_nopriv_order_complete', array( $this, 'order_complete' ) );
			}
		}

		public function pw_user_script_enqueue() {
			global $pw_plugin_url;

			wp_register_script( "pw_abandoned_cart", $pw_plugin_url . 'assets/js/pw-abandoned-cart.js',
				array( 'jquery' ) );
			wp_localize_script( 'pw_abandoned_cart', 'pwAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'pw_abandoned_cart' );
		}


		public function action_woocommerce_new_order( $order_id ) {
			if ( ! isset( $_COOKIE['pw_deviceid'] ) ) {
				return true;
			}
			$device_id = $_COOKIE['pw_deviceid'];

			add_post_meta( $order_id, '_pw_device_id', $device_id, true );

			return true;
		}


		public function action_order_status_update( $order_id, $oldStatus, $newStatus ) {

			$device_id = get_post_meta( $order_id, '_pw_device_id', true );

			if ( ! isset( $device_id ) ) {
				return true;
			}

			$multicast = $this->get_status_multicast( $order_id, $newStatus );

			$subscribers = array(
				$device_id
			);

			pw_push_notification( $subscribers, $multicast );

			return true;
		}


		private function get_status_multicast( $order_id, $status ) {

			if ( empty( $status ) ) {
				$status = 'default';
			}

			$multicast = array();

			$matches = array(
				'title'       => 'title',
				'text'        => 'text',
				'url'         => 'url',
				'image'       => 'icon',
				'image_large' => 'image'
			);

			foreach ( $matches as $key => $value ) {
				$cur_option = get_option( 'pushworld_' . $status . '_' . $value );

				if ( ! empty( $cur_option ) ) {
					$multicast[ $key ] = $cur_option;
				}
			}

			$multicast = $this->add_status_actions( $multicast, $status );

			if ( ! empty( $order_id ) ) {
				$multicast['title'] = $this->order_multicast_replacement( $order_id, $multicast['title'] );
				$multicast['text']  = $this->order_multicast_replacement( $order_id, $multicast['text'] );
			}

			return $multicast;
		}


		private function add_status_actions( $multicast, $status ) {

			$actions = array(
				'action1_title' => 'action1_url',
				'action2_title' => 'action2_url'
			);

			foreach ( $actions as $key => $value ) {
				$cur_option_title = get_option( 'pushworld_' . $status . '_' . $key );
				$cur_option_url   = get_option( 'pushworld_' . $status . '_' . $value );

				if ( ! empty( $cur_option_title ) && ! empty( $cur_option_url ) ) {
					$multicast[ $key ]   = $cur_option_title;
					$multicast[ $value ] = $cur_option_url;
				}
			}

			return $multicast;
		}


		private function woocommerce_version_check( $version = '2.3.3' ) {
			global $woocommerce;

			if ( version_compare( $woocommerce->version, $version, ">=" ) ) {
				return true;
			}

			return false;
		}


		public function check_abandoned() {

			if ( ! isset( $_COOKIE['pw_deviceid'] ) ) {
				return false;
			}

			global $wpdb;

			$cart = WC()->instance()->cart;

			$table_tasks = $wpdb->prefix . $this->db_tasks;

			$deviceId = $_COOKIE['pw_deviceid'];

			if ( ! $cart->cart_contents ) {

				$wpdb->delete( $table_tasks,
					array(
						'device_id' => $deviceId,
						'type'      => 'cart',
						'status'    => 1
					)
				);

			} else {
				if ( $wpdb->get_row( "SELECT * FROM $table_tasks WHERE device_id = '$deviceId' AND type='cart' AND status=1;" ) ) {
					$wpdb->update( $table_tasks,
						array( 'exec_time' => time() + get_option( 'pushworld_abandoned_timeout', 30 ) * 60 ),
						array( 'device_id' => $deviceId, 'status' => 1, 'type' => 'cart' ) );
				} else {
					$multicast = $this->get_status_multicast( null, 'abandoned' );


					$wpdb->insert( $table_tasks, array(
						'exec_time' => time() + get_option( 'pushworld_abandoned_timeout', 30 ) * 60,
						'device_id' => $deviceId,
						'type'      => 'cart',
						'multicast' => json_encode( $multicast )
					) );
				}
			}

			echo json_encode( array( 'success' => true, 'data' => array( 'cart_empty' => ! $cart->cart_contents ) ) );

			die;
		}

		public function order_complete() {
			global $wpdb;

			$table_name = $wpdb->prefix . $this->db_tasks;
			$deviceId   = isset( $_COOKIE['pw_deviceid'] ) ? $_COOKIE['pw_deviceid'] : null;
			if ( $deviceId ) {
				$wpdb->delete( $table_name, array( 'device_id' => $deviceId, 'type' => 'cart', 'status' => 1 ) );
			}

			$wpdb->delete( $table_name, array( 'device_id' => $deviceId, 'type' => 'cart', 'status' => 1 ) );
			echo json_encode( array( 'success' => true, 'data' => array( 'cart_empty' => true ) ) );

			die;
		}

		public function order_multicast_replacement( $order_id, $multicast_string ) {

			$replacements = array(
				'%order_number%'   => $order_id,
				'%first_name%'     => get_post_meta( $order_id, '_billing_first_name', true ),
				'%last_name%'      => get_post_meta( $order_id, '_billing_last_name', true ),
				'%address_1%'      => get_post_meta( $order_id, '_billing_address_1', true ),
				'%address_2%'      => get_post_meta( $order_id, '_billing_address_2', true ),
				'%city%'           => get_post_meta( $order_id, '_billing_city', true ),
				'%state%'          => get_post_meta( $order_id, '_billing_state', true ),
				'%country%'        => get_post_meta( $order_id, '_billing_country', true ),
				'%postcode%'       => get_post_meta( $order_id, '_billing_postcode', true ),
				'%payment_method%' => get_post_meta( $order_id, 'payment_method_title', true ),
			);

			$str_res = $multicast_string;

			foreach ( $replacements as $key => $value ) {
				$str_res = mb_ereg_replace( $key, $value, $str_res );
			}

			return $str_res;
		}

		private function create_tables() {
			global $wpdb;
			$table_tasks = $wpdb->prefix . $this->db_tasks;

			$sql = "CREATE TABLE IF NOT EXISTS $table_tasks (
                id INT NOT NULL AUTO_INCREMENT,
                device_id VARCHAR(255) NOT NULL,
     			status INT NOT NULL DEFAULT 1,
         		exec_time INT NOT NULL,
         		multicast TEXT NOT NULL,
         		type VARCHAR(20) NOT NULL,
         		PRIMARY KEY (`id`)
	        ) ENGINE='InnoDB' CHARSET='utf8'";

			$result = $wpdb->query( $sql );

			return $result;
		}
	}
}

new WC_PushWorld();
