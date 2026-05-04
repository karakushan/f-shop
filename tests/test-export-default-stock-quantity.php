<?php

use FS\FS_Export_Class;

class ExportDefaultStockQuantityTest extends WP_UnitTestCase
{
    public function test_default_stock_quantity_uses_saved_option(): void
    {
        update_option('_fs_default_export_stock_quantity', '37');

        $export = new FS_Export_Class();

        $this->assertSame(37, $export->get_default_export_stock_quantity());
    }

    public function test_default_stock_quantity_falls_back_to_100_for_invalid_value(): void
    {
        update_option('_fs_default_export_stock_quantity', 'invalid');

        $export = new FS_Export_Class();

        $this->assertSame(100, $export->get_default_export_stock_quantity());
    }
}
