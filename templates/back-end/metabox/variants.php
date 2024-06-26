<?php
$post_id    = (int) $_GET['post'];
$variations = get_post_meta( $post_id, \FS\FS_Config::get_meta( 'variations' ), 1 ) ?? [];
?>

<div x-data='{variations: <?php echo json_encode( $variations ) ?>,productAttributes: [] }'
     x-init="()=>{$store.FS?.getAttributes(<?php echo $post_id ?>)
				    .then((r) => productAttributes = r.data);}" class="fs-variations">
    <div class="fs-variations__top">
        <button type="button" class="button" x-on:click="variations.push({
        name: '#' + ( variations.length + 1 ),
        price: '',
        sale_price: '',
        sku: '',
        })">
			<?php esc_html_e( 'add variant', 'f-shop' ) ?>
        </button>
    </div>

    <div class="fs-variations__items">
        <template x-for="(variation, index) in variations" :key="index">
            <div class="fs-variations__item" :class="{open:open}" x-data="{open:false, item: variation}"
                 x-init="$watch('variation', (variation) => item = variation)">
                <div class="fs-variations__item-top">
                    <div class="fs-variations__item-top-left">
                        <span class="dashicons dashicons-category"></span>
                        <div class="fs-variations__item-name" x-text="item.name"></div>
                    </div>
                    <div class="fs-variations__item-top-right">
                        <button class="fs-variations__item-delete fs-text-error"
                                x-on:click.prevent="variations.splice(index, 1)">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                        <button class="fs-variations__item-open" x-on:click.prevent="open=!open"><span
                                    class="dashicons dashicons-arrow-down-alt2"></span>
                        </button>
                    </div>

                </div>
                <div class="fs-variations__item-body" x-show="open">
                    <div class="fs-variations__item-row">
                        <div class="fs-variations__item-col-full">
                            <label for="">
								<?php esc_html_e( 'Name', 'f-shop' ) ?>
                            </label>
                            <input type="text" :name="'variations['+index+'][name]'" x-model="item.name">
                        </div>

                    </div>
                    <div class="fs-variations__item-row">
                        <div class="fs-variations__item-col">
                            <label for="">
								<?php esc_html_e( 'Price', 'f-shop' ) ?>
                            </label>
                            <input type="number" step=".01" min="0" :name="'variations['+index+'][price]'"
                                   x-model="item.price">
                        </div>
                        <div class="fs-variations__item-col">
                            <label for="">
								<?php esc_html_e( 'Promo Price', 'f-shop' ) ?>
                            </label>
                            <input type="number" step=".01" min="0" :name="'variations['+index+'][sale_price]'"
                                   x-model="item.sale_price">
                        </div>
                    </div>
                    <div class="fs-variations__item-row">
                        <div class="fs-variations__item-col">
                            <label for="">
								<?php esc_html_e( 'SKU', 'f-shop' ) ?>
                            </label>
                            <input type="text" :name="'variations['+index+'][sku]'" x-model="item.sku">
                        </div>
                        <div class="fs-variations__item-col">
                            <label for="">
								<?php esc_html_e( 'Stock', 'f-shop' ) ?>
                            </label>
                            <input type="number" :name="'variations['+index+'][stock]'" x-model="item.stock">
                        </div>
                    </div>
                    <div class="fs-variations__item-row">
                        <div class="fs-variations__item-col-full">
                            <label for="">
								<?php esc_html_e( 'Associated attributes', 'f-shop' ) ?>
                            </label>
                            <div class="fs-variations__item-attributes">
                                <template x-for="productAttribute in productAttributes" >
                                    <div class="fs-variations__item-attribute" x-show="productAttribute.children.length>0">
                                        <div x-text="productAttribute.name" class="fs-variations__item-attribute-name"></div>
                                        <ul>
                                            <template x-for="(children,i) in productAttribute.children">
                                                <li>
                                                    <input type="checkbox" :name="'variations['+index+'][attributes][]'"
                                                           :id="'att-'+'+index+'+'-'+productAttribute.id+'-'+children.id"
                                                           :checked="typeof variations[index].attributes==='object' && variations[index].attributes.includes(children.id.toString())"
                                                           :value="children.id">
                                                    <label  x-text="children.name" :for="'att-'+'+index+'+'-'+productAttribute.id+'-'+children.id"></label>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

</div>
