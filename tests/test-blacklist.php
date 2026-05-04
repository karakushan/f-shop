<?php
/**
 * Tests for blacklist helpers.
 *
 * @package F_Shop
 */

use FS\FS_Blacklist;

class FS_Blacklist_Test extends WP_UnitTestCase {

	public function test_prepare_phone_strips_non_digits() {
		$this->assertSame( '380991112233', FS_Blacklist::prepare_phone( '+38 (099) 111-22-33' ) );
	}

	public function test_prepare_email_normalizes_case_and_spacing() {
		$this->assertSame( 'test@example.com', FS_Blacklist::prepare_email( ' Test@Example.com ' ) );
	}

	public function test_prepare_ip_trims_value() {
		$this->assertSame( '192.168.0.10', FS_Blacklist::prepare_ip( ' 192.168.0.10 ' ) );
	}
}
