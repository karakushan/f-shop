<?php

namespace FS\CLI;

use FS\Api\FS_YML_Import;
use WP_CLI;
use WP_CLI\ExitException;

class Init_Commands {
	public function __construct() {
		add_action( 'init', [ $this, 'init_wp_cli_command' ] );
	}

	/**
	 * Registrable namespace WP_CLI
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function init_wp_cli_command() {
		if ( ! class_exists( 'WP_CLI' ) ) {
			return;
		}
		WP_CLI::add_command( 'fs', [ $this, 'cli_command' ] );
	}

	/**
	 * Routing WP_CLI commands
	 *
	 * @param $args
	 * @param $assoc_args
	 *
	 * @return void
	 * @throws ExitException
	 */
	function cli_command( $args, $assoc_args ) {
		if ( ! isset( $args[0] ) ) {
			WP_CLI::error( 'Missing command' );
		}
		switch ( $args[0] ) {
			case 'yml_import':
				FS_YML_Import::run( $assoc_args );
				break;
		}
		exit;
	}
}