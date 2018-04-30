<?php
/**
 * Автозагрузка классов
 *
 * @param $class_name
 */
function fs_autoload( $class_name ) {
	if ( strpos( $class_name, 'FS' ) === 0 ) {
		$class_name = str_replace( 'FS\\', '', $class_name );
		$class_path = __DIR__ . '/lib/' . $class_name . '.php';
		if ( file_exists( $class_path ) ) {
			require_once $class_path;
		} else {
			global $fs_error;
			$fs_error->add( FS_PLUGIN_PREFIX . 'not_found', __( 'file not found', 'fast-shop' ) );
		}
	}
}

spl_autoload_register( 'fs_autoload' );