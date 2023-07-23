<?php

namespace FS\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use FS\FS_Config;

class ProductEdit {

	protected $post_id = null;

	private $allowed_types = [ 'media_gallery', 'text','textarea', 'checkbox', 'radio', 'association', 'select', 'html' ];

	public function __construct() {
		add_action( 'carbon_fields_register_fields', [ $this, 'product_metabox_handle' ] );
		add_action( 'carbon_fields_post_meta_container_saved', [ $this, 'save_product_meta' ], 10, 2 );
	}

	function product_metabox_handle() {
		$this->post_id = $_GET['post'] ?? null;
		$container     = Container::make( 'post_meta', 'fs_metabox', __( 'Настройки товара' ) );
		$container->set_datastore(new PostMetaDatastore());
		$container->where( 'post_type', '=', FS_Config::get_data( 'post_type' ) );
		$container->set_classes( 'fs-vertical-tabs' );
		foreach ( $this->get_product_tabs() as $key => $tab ) {
			$fields   = [];
			$fields[] = Field::make( 'separator', 'crb_separator_' . $key, $tab['title'] );
			if ( empty( $tab['fields'] ) ) {
				continue;
			}
			foreach ( $tab['fields'] as $name => $field ) {
				if ( ! isset( $field['type'] ) || ! in_array( $field['type'], $this->allowed_types ) ) {
					continue;
				}
				$f = Field::make( $field['type'], $name, $field['label'] );

				if ( isset( $field['width'] ) ) {
					$f->set_width( $field['width'] );
				}

				if ( isset( $field['required'] ) ) {
					$f->set_required( $field['required'] );
				}

				if ( in_array( $field['type'], [ 'select','radio' ] ) && isset( $field['options'] ) ) {
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

				$fields[] = $f;
			}
			$container->add_tab( $tab['title'], $fields );
		}
	}
	/**
	 * Gets the array that contains the list of product settings tabs.
	 *
	 * @return array
	 */
	public function get_product_tabs() {
		$product_fields = FS_Config::get_product_field();
		$tabs           = array(
			'basic'        => array(
				'title'       => __( 'Basic', 'f-shop' ),
				'on'          => true,
				'description' => __( 'In this tab you can adjust the prices of goods.', 'f-shop' ),
				'fields'      => array(
					FS_Config::get_meta( 'product_type' ) => array(
						'label'      => __( 'Product type', 'f-shop' ),
						'type'       => 'radio',
						'required'   => false,
						'options'    => array(
							'physical' => __( 'Physical', 'f-shop' ),
							'virtual'  => __( 'Virtual', 'f-shop' )
						),
						'value'      => 'physical',
						'attributes' => [
							'x-model' => 'productType',
						],
						'help'       => __( 'Check this box if you are selling a non-physical item.', 'f-shop' )
					),
					FS_Config::get_meta( 'price' )        => array(
						'label'      => __( 'Base price', 'f-shop' ),
						'type'       => 'text',
						'required'   => true,
						'attributes' => [
							'min'  => 0,
							'step' => 0.01
						],
						'help'       => __( 'This is the main price on the site. Required field!', 'f-shop' )
					),
					FS_Config::get_meta( 'action_price' ) => array(
						'label' => __( 'Promotional price', 'f-shop' ),
						'type'  => 'text',
						'help'  => __( 'If this field is filled, the base price loses its relevance. But you can display it on the site.', 'f-shop' )
					),

					$product_fields['sku']['key']      => $product_fields['sku'],
					$product_fields['quantity']['key'] => $product_fields['quantity'],
					FS_Config::get_meta( 'currency' )  => array(
						'label'    => __( 'Item Currency', 'f-shop' ),
						'on'       => (bool) fs_option( 'multi_currency_on' ),
						'type'     => 'select',
						'max'      => 1,
						'options'  => 'fs_get_currencies',
						'help'     => __( 'The field is active if you have enabled multicurrency in settings.', 'f-shop' ),
						'taxonomy' => FS_Config::get_data( 'currencies_taxonomy' )
					),

				)
			),
			'gallery'      => array(
				'title'  => __( 'Gallery', 'f-shop' ),
				'on'     => true,
				'fields' => [
					'fs_galery' => [
						'label'     => __( 'Gallery', 'f-shop' ),
						'type'      => 'media_gallery',
						'help'      => __( 'Add images to the gallery.', 'f-shop' ),
						'multilang' => false
					]
				]
			),
			'attributes'   => array(
				'title'  => __( 'Attributes', 'f-shop' ),
				'on'     => true,
				'fields' => [
					'fs_attributes' => [
						'label' => __( 'Характеристики товара', 'f-shop' ),
						'type'  => 'html',
						'template' => FS_PLUGIN_PATH . '/templates/back-end/metabox/variants.php',
					]
				]
			),
			'related'      => array(
				'title'  => __( 'Associated', 'f-shop' ),
				'on'     => true, // Сейчас в разработке
				'fields' => [
					'fs_upsell_products'        => [
						'label' => __( 'Upsell products', 'f-shop' ),
						'type'  => 'association',
						'types' => [
							'type'      => 'post',
							'post_type' => FS_Config::get_data( 'post_type' ),
						],
						'help'  => __( 'товари схожі на основний', 'f-shop' )
					],
					'fs_cross_selling_products' => [
						'label' => __( 'Cross-selling products', 'f-shop' ),
						'type'  => 'association',
						'types' => [
							'type'      => 'post',
							'post_type' => FS_Config::get_data( 'post_type' ),
						],
						'help'  => __( 'додаткові товари, які можна запропонувати з основним', 'f-shop' )
					]
				]

			),
			'variants'     => array(
				'title'  => __( 'Variation', 'f-shop' ),
				'on'     => true,
				'fields' => [
					'fs_variants' => [
						'label'    => __( 'This tab is relevant if you have several varieties of a single product.', 'f-shop' ),
						'type'     => 'html',
						'template' => FS_PLUGIN_PATH . '/templates/back-end/metabox/variants.php',
					]
				]
			),
			'delivery'     => array(
				'title'  => __( 'Shipping and payment', 'f-shop' ),
				'on'     => true,
				'body'   => '',
				'fields' => array(
					'_fs_delivery_description' => array(
						'label' => __( 'Shipping and Payment Details', 'f-shop' ),
						'type'  => 'textarea',
						'help'  => ''
					),
				)
			),
			'seo'          => array(
				'title'  => __( 'SEO', 'f-shop' ),
				'on'     => true,
				'body'   => '',
				'fields' => array(
					'fs_seo_slug' => array(
						'label'     => __( 'SEO slug', 'f-shop' ),
						'type'      => 'text',
						'multilang' => true,
						'help'      => __( 'Allows you to set multilingual url', 'f-shop' )
					),

				)
			),
			'additionally' => array(
				'title'  => __( 'Additionally', 'f-shop' ),
				'on'     => true,
				'body'   => '',
				'fields' => array(
					$product_fields['exclude_archive']['key']  => $product_fields['exclude_archive'],
					$product_fields['label_bestseller']['key'] => $product_fields['label_bestseller'],
					$product_fields['label_promotion']['key']  => $product_fields['label_promotion'],
					$product_fields['label_novelty']['key']    => $product_fields['label_novelty'],

				)
			),
			'virtual'      => array(
				'title'          => __( 'Virtual Product', 'f-shop' ),
				'on'             => true,
				'nav_attributes' => [
					'x-show' => 'productType === \'virtual\''
				],
				'body'           => '',
				'fields'         => array(
					'_fs_virtual_product_url' => array(
						'label' => __( 'Link to the product', 'f-shop' ),
						'type'  => 'text',
						'help'  => ''
					),
				)
			),
		);

		$tabs = array_filter( $tabs, function ( $tab ) {
			return isset( $tab['on'] ) && $tab['on'] == true;
		} );

		return apply_filters( 'fs_product_tabs_admin', $tabs );
	}

	/**
	 * Assigns product characteristics from meta fields
	 *
	 * @param $post_id
	 * @param $context
	 *
	 * @return void
	 */
	function save_product_meta( $post_id, $context ) {

		$fields = (array) $_POST['carbon_fields_compact_input']['_fs_attributes'];

		$attributes = array_map( function ( $item ) {
			$atts = explode( ':', $item );

			return intval( $atts[ count( $atts ) - 1 ] );
		}, $fields );

		wp_set_post_terms( $post_id, $attributes, FS_Config::get_data( 'features_taxonomy' ) );


		if (isset($_POST['carbon_fields_compact_input']['_fs_currency'])) {
			wp_set_post_terms( $post_id, intval($_POST['carbon_fields_compact_input']['_fs_currency']), FS_Config::get_data( 'currencies_taxonomy' ) );
		}
	}
}