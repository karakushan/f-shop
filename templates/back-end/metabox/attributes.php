<?php
$attributes = get_terms( [
	'taxonomy'   => \FS\FS_Config::get_data( 'features_taxonomy' ),
	'parent'     => 0,
	'hide_empty' => false
] );
$product_attributes = get_the_terms( $post->ID, \FS\FS_Config::get_data( 'features_taxonomy' ) );
?>
<h3><?php esc_html_e( 'Product attributes', 'f-shop' ); ?></h3>
<p><?php esc_html_e( 'Here you can specify what properties the product has.', 'f-shop' ); ?></p>
<div class="form-info">

</div>
<script>
    const pushTerm = (selected, childs, value) => {
        let fined = selected.find((item) => item.term_id == value)
        if (typeof fined === 'undefined') {
            let result = childs.find(item => item.term_id == value);
            if (result !== undefined) selected.push(result)
        }
    }
</script>
<div x-data='{ childs : [], term_id: null,value:null, selected: <?php echo  json_encode($product_attributes) ?? '[]' ?>,terms: <?php echo json_encode( $attributes ) ?> }'>
	<div class="fs-add-attributes" data-fs-element="item">
		<select x-model="term_id" data-fs-element="attribute-name"
		        x-on:change="term_id ? fsGetAttributes(term_id,(response)=>childs=response) :  childs =[]">
			<option value=""><?php echo esc_attr_e( 'Виберіть групу', 'f-shop' ); ?></option>
			<template x-for="attribute in terms" x-key="attribute.term_id">
				<option
					:value="attribute.term_id" x-text="attribute.name"></option>
			</template>
		</select>
		<select x-model="value" x-show="childs.length>0">
			<option value=""><?php echo esc_attr_e( 'Виберіть характеристику', 'f-shop' ); ?></option>
			<template x-for="child in childs">
				<option :value="child.term_id" x-text="child.name"></option>
			</template>
		</select>
		<button type="button" class="button button-secondary"
		        x-on:click.prevent="pushTerm(selected,childs,value)"><?php esc_html_e( 'add attribute', 'f-shop' ); ?></button>
	</div>
	<div class="fs-selected-attributes">
		<template x-for="(sChild, index) in Object.values(selected)"  :key="index">
			<div x-data="{name:sChild.name,value:sChild.term_id,parent:terms.find(item=>item.term_id===sChild.parent)}"
			     class="fs-selected-attribute"
			>
				<div x-text="parent.name" class="fs-selected-attribute__group"></div>
				<div x-text="name"></div>
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30" class="fs-selected-attribute__remove" width="20px" height="20"
				     x-on:click="selected = Object.values(selected).filter(p=>p.term_id!==sChild.term_id)">
					<path
						d="M 13 3 A 1.0001 1.0001 0 0 0 11.986328 4 L 6 4 A 1.0001 1.0001 0 1 0 6 6 L 24 6 A 1.0001 1.0001 0 1 0 24 4 L 18.013672 4 A 1.0001 1.0001 0 0 0 17 3 L 13 3 z M 6 8 L 6 24 C 6 25.105 6.895 26 8 26 L 22 26 C 23.105 26 24 25.105 24 24 L 24 8 L 6 8 z"/>
				</svg>
				<input type="hidden" name="fs_product_attributes[]" class="fs-selected-attribute__name" :value="value">
			</div>
		</template>
	</div>
</div>

