<?php

namespace FS\Admin;

use Carbon_Fields\Field\Field;

class TermMetaDatastore extends \Carbon_Fields\Datastore\Datastore {

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
		$key = $this->get_key_for_field( $field );
		$meta_value=get_term_meta( $this->get_object_id(), $key, true);
		return $meta_value !== false ? $meta_value : $field->get_default_value();
	}

	/**
	 * @inheritDoc
	 */
	public function save( Field $field ) {
		$key = $this->get_key_for_field( $field );
		update_term_meta( $this->get_object_id(), $key, $field->get_value() );
	}

	/**
	 * @inheritDoc
	 */
	public function delete( Field $field ) {
		$key = $this->get_key_for_field( $field );
		delete_term_meta( $this->get_object_id(), $key );
	}
}