<?php

namespace FS\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
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
		'image'
	];

	public function __construct() {
		add_action( 'carbon_fields_register_fields', [ $this, 'carbon_register_term_meta' ] );
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

				$f = Field::make( $field['type'], mb_strtolower( $name ), $field['name'] );
				if ( isset( $field['width'] ) ) {
					$f->set_width( $field['width'] );
				}

				if ( isset( $field['required'] ) ) {
					$f->set_required( $field['required'] );
				}

				if(isset($field['subtype'])){
					$f->set_attribute('type',$field['subtype']);
				} 

				if ( in_array( $field['type'], [ 'select', 'radio','multiselect' ] ) && isset( $field['options'] ) ) {
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

				$fs[] = $f;

			}

			$container->add_fields( $fs );
		}
	}

}