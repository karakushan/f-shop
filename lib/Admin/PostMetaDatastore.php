<?php

namespace FS\Admin;

use Carbon_Fields\Field\Field;
use FS\FS_Config;

class PostMetaDatastore extends \Carbon_Fields\Datastore\Datastore {

	/**
	 * @inheritDoc
	 */
	public function init() {
		// TODO: Implement init() method.
	}

	protected function get_key_for_field( Field $field ) {
		return $field->get_base_name();
	}

	/**
	 * @inheritDoc
	 */
	public function load( Field $field ) {
		$key        = $this->get_key_for_field( $field );
		$meta_value = get_post_meta( $this->get_object_id(), $key, true );

		if ( $this->is_default_language_product_slug( $key ) && $meta_value === '' ) {
			return (string) get_post_field( 'post_name', $this->get_object_id() );
		}

		return $meta_value !== false ? $meta_value : $field->get_default_value();
	}

	protected function is_default_language_product_slug( $key ) {
		return $key === 'fs_seo_slug__' . mb_strtolower( FS_Config::default_locale() );
	}

	/**
	 * @inheritDoc
	 */
	public function save( Field $field ) {
		$key = $this->get_key_for_field( $field );
		update_post_meta( $this->get_object_id(), $key, $field->get_value() );
	}

	/**
	 * @inheritDoc
	 */
	public function delete( Field $field ) {
		$key = $this->get_key_for_field( $field );
		delete_post_meta( $this->get_object_id(), $key );
	}
}
