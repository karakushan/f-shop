<?php


namespace FS;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\StringLoaderExtension;

class FS_Template {
	protected $environment;
	protected $template_extension = '.twig';


	/**
	 * FS_Template constructor.
	 */
	public function __construct() {
		$directories       = [
			FS_PLUGIN_PATH . '/templates/front-end'
		];
		$loader            = new FilesystemLoader( $directories );
		$this->environment = new Environment( $loader, [
			'debug' => true
		] );
		$this->environment->addExtension( new StringLoaderExtension() );

		if ( current_user_can( 'administrator' ) ) {
			$this->environment->addExtension( new DebugExtension() );
		}
	}

	/**
	 * @param string $html
	 * @param array $params
	 *
	 * @return string
	 * @throws LoaderError
	 * @throws SyntaxError
	 */
	public function get_from_string( string $html, array $params ) {
		$template = $this->environment->createTemplate( $html );

		return $template->render( $params );
	}

	/**
	 * @param string $template
	 * @param array $params
	 *
	 * @return string
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	public function get( string $template, array $params ) {
		return $this->environment->render( $template . $this->template_extension, $params );
	}

	/**
	 * @param string $template
	 * @param array $params
	 *
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	public function render( string $template, array $params ) {
		echo $this->environment->render( $template . $this->template_extension, $params );
	}

	/**
	 * @param string $template_extension
	 */
	public function setTemplateExtension( string $template_extension ): void {
		$this->template_extension = $template_extension;
	}
}