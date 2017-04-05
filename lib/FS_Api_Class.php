<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 05.04.2017
 * Time: 13:44
 */

namespace FS;


class FS_Api_Class {

	function __construct() {
		add_action( 'init', array( $this, 'plugin_migrate' ) );
	}

	function plugin_migrate() {
		if ( ! is_admin() && ! isset( $_REQUEST['fs-api'] ) ) {
			return;
		}
		if ( $_REQUEST['fs-api'] == 'migrate' ) {
			FS_Migrate_Class::import_option_attr();
		}
	}

}