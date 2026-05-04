<?php

use FS\FS_Config;
use FS\FS_Product;

class ProductAttributeOrderTest extends WP_UnitTestCase
{
    private string $features_taxonomy;
    private string $catalog_taxonomy;

    public function set_up(): void
    {
        parent::set_up();

        $this->features_taxonomy = FS_Config::get_data('features_taxonomy');
        $this->catalog_taxonomy = FS_Config::get_data('product_taxonomy');
    }

    public function test_category_order_has_priority_over_product_order(): void
    {
        $product_id = self::factory()->post->create(['post_type' => 'product']);

        $volume = $this->create_attribute_with_value('Обʼєм', '400 мл');
        $country = $this->create_attribute_with_value('Країна виробник', 'Україна');
        $quantity = $this->create_attribute_with_value('Кількість в упаковці', '50 шт');

        wp_set_object_terms(
            $product_id,
            [$volume['child_id'], $country['child_id'], $quantity['child_id']],
            $this->features_taxonomy
        );

        update_post_meta($product_id, '_fs_attribute_group_order', [
            $country['parent_id'],
            $volume['parent_id'],
            $quantity['parent_id'],
        ]);

        $category = wp_insert_term('Стакани', $this->catalog_taxonomy);
        wp_set_object_terms($product_id, [(int) $category['term_id']], $this->catalog_taxonomy);
        update_term_meta((int) $category['term_id'], '_catalog_attribute_order', [
            $quantity['parent_id'],
            $country['parent_id'],
            $volume['parent_id'],
        ]);

        $attributes = FS_Product::get_attributes_hierarchy($product_id);

        $this->assertSame(
            [$quantity['parent_id'], $country['parent_id'], $volume['parent_id']],
            array_column($attributes, 'id')
        );
    }

    public function test_product_order_is_used_when_category_order_is_empty(): void
    {
        $product_id = self::factory()->post->create(['post_type' => 'product']);

        $volume = $this->create_attribute_with_value('Обʼєм', '400 мл');
        $country = $this->create_attribute_with_value('Країна виробник', 'Україна');
        $quantity = $this->create_attribute_with_value('Кількість в упаковці', '50 шт');

        wp_set_object_terms(
            $product_id,
            [$volume['child_id'], $country['child_id'], $quantity['child_id']],
            $this->features_taxonomy
        );

        update_post_meta($product_id, '_fs_attribute_group_order', [
            $country['parent_id'],
            $volume['parent_id'],
            $quantity['parent_id'],
        ]);

        $category = wp_insert_term('Стакани', $this->catalog_taxonomy);
        wp_set_object_terms($product_id, [(int) $category['term_id']], $this->catalog_taxonomy);
        update_term_meta((int) $category['term_id'], '_catalog_attribute_order', []);

        $attributes = FS_Product::get_attributes_hierarchy($product_id);

        $this->assertSame(
            [$country['parent_id'], $volume['parent_id'], $quantity['parent_id']],
            array_column($attributes, 'id')
        );
    }

    public function test_parent_category_order_is_used_for_child_category_products(): void
    {
        $product_id = self::factory()->post->create(['post_type' => 'product']);

        $volume = $this->create_attribute_with_value('Обʼєм', '400 мл');
        $country = $this->create_attribute_with_value('Країна виробник', 'Україна');
        $quantity = $this->create_attribute_with_value('Кількість в упаковці', '50 шт');

        wp_set_object_terms(
            $product_id,
            [$volume['child_id'], $country['child_id'], $quantity['child_id']],
            $this->features_taxonomy
        );

        $parent_category = wp_insert_term('HoReCa', $this->catalog_taxonomy);
        $child_category = wp_insert_term('Паперові стакани', $this->catalog_taxonomy, [
            'parent' => (int) $parent_category['term_id'],
        ]);

        wp_set_object_terms($product_id, [(int) $child_category['term_id']], $this->catalog_taxonomy);
        update_term_meta((int) $parent_category['term_id'], '_catalog_attribute_order', [
            $volume['parent_id'],
            $country['parent_id'],
            $quantity['parent_id'],
        ]);

        $attributes = FS_Product::get_attributes_hierarchy($product_id);

        $this->assertSame(
            [$volume['parent_id'], $country['parent_id'], $quantity['parent_id']],
            array_column($attributes, 'id')
        );
    }

    private function create_attribute_with_value(string $group_name, string $value_name): array
    {
        $group = wp_insert_term($group_name, $this->features_taxonomy);
        $value = wp_insert_term($value_name, $this->features_taxonomy, [
            'parent' => (int) $group['term_id'],
        ]);

        return [
            'parent_id' => (int) $group['term_id'],
            'child_id' => (int) $value['term_id'],
        ];
    }
}
