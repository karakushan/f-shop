<?php
/**
 * Tests for cart product name normalization in AJAX responses.
 *
 * @package F_Shop
 */

use FS\FS_Cart;

class FS_Cart_Product_Name_Normalization_Test extends WP_UnitTestCase {

	public function test_normalize_product_name_decodes_html_entities() {
		$encoded_name = 'Кульки для Bubble Tea TM &#8220;Bon Classic&#8221; Кавун 520 г';

		$this->assertSame(
			'Кульки для Bubble Tea TM “Bon Classic” Кавун 520 г',
			FS_Cart::normalize_product_name_for_response( $encoded_name )
		);
	}
}
