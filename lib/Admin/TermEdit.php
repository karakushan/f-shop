<?php

namespace FS\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use FS\FS_Config;
use FS\FS_Taxonomy;

class TermEdit {
	private $allowed_types = [
		'media_gallery',
		'text',
		'textarea',
		'checkbox',
		'radio',
		'association',
		'select',
		'html',
		'multiselect',
		'image',
		'rich_text'
	];

	public function __construct() {
		add_action( 'carbon_fields_register_fields', [ $this, 'carbon_register_term_meta' ] );
		add_action( 'saved_' . FS_Config::get_data( 'product_taxonomy' ), [
			$this,
			'saved_product_category_callback'
		] );
	}

	function carbon_register_term_meta() {
		$fields = FS_Taxonomy::get_taxonomy_fields();
		foreach ( $fields as $key => $term_fields ) {
			$container = Container::make( 'term_meta', __( 'Додаткові налаштування' ) );
			$container->set_datastore( new TermMetaDatastore() );
			$container->where( 'term_taxonomy', '=', $key );
			$fs = [];
			foreach ( $term_fields as $name => $field ) {
				if ( ! in_array( $field['type'], $this->allowed_types ) ) {
					continue;
				}

				if ( isset( $field['args']['multilang'] ) && $field['args']['multilang'] == true ) {
					foreach ( FS_Config::get_languages() as $language ) {
						if ( ! empty( $field['args']['disable_default_locale'] ) && $language['locale'] === FS_Config::default_locale() ) {
							continue;
						}
						$fs[] = $this->make_field( $field, $name . '__' . $language['locale'], $field['name'] . ' (' . $language['name'] . ')' );
					}
				} else {
					$fs[] = $this->make_field( $field, $name, $field['name'] );
				}
			}

			$container->add_fields( $fs );
		}
	}

	function make_field( $field, $name, $label ) {
		$f = Field::make( $field['type'], mb_strtolower( $name ), $label );
		if ( isset( $field['width'] ) ) {
			$f->set_width( $field['width'] );
		}

		if ( isset( $field['required'] ) ) {
			$f->set_required( $field['required'] );
		}

		if ( isset( $field['subtype'] ) ) {
			$f->set_attribute( 'type', $field['subtype'] );
		}

		if ( in_array( $field['type'], [ 'select', 'radio', 'multiselect' ] ) && isset( $field['options'] ) ) {
			$f->add_options( $field['options'] );
		}

		if ( $field['type'] == 'association' && isset( $field['types'] ) ) {
			$f->set_types( [
				$field['types']
			] );
		}

		if ( $field['type'] == 'html' && isset( $field['template'] ) ) {
			ob_start();
			include( $field['template'] );
			$f->set_html( ob_get_clean() );
		}

		if ( isset( $field['help'] ) ) {
			$f->set_help_text( $field['help'] );
		}

		return $f;
	}

	/**
	 * Fires after the term is saved to the database
	 *
	 * @return void
	 */
	function saved_product_category_callback() {
		if ( fs_option( 'fs_localize_slug' ) ) {
			flush_rewrite_rules();
		}
	}
}