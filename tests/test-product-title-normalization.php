<?php
/**
 * Tests for product title normalization.
 *
 * @package F_Shop
 */

use FS\FS_Product;

class FS_Product_Title_Normalization_Test extends WP_UnitTestCase {

	public function test_normalize_title_decodes_html_entities() {
		$encoded_title = 'Кульки для Bubble Tea TM &#8220;Bon Classic&#8221; Кавун 520 г';

		$this->assertSame(
			'Кульки для Bubble Tea TM “Bon Classic” Кавун 520 г',
			FS_Product::normalize_title_for_display( $encoded_title )
		);
	}
}
