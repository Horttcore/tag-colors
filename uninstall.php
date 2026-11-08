<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$wpdb->delete( $wpdb->termmeta, array( 'meta_key' => 'tag_color' ), array( '%s' ) );
