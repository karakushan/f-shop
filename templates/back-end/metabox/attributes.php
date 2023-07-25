<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $_GET['post'] ) || ! isset( $_GET['action'] ) || $_GET['action'] !== 'edit' ) {
	return;
}

$post_id    = absint( $_GET['post'] );
$attributes = get_terms( [
	'taxonomy'   => \FS\FS_Config::get_data( 'features_taxonomy' ),
	'hide_empty' => false,
	'parent'     => 0
] );

$product_attributes = json_encode( \FS\FS_Product::get_attributes_hierarchy( $post_id ) ?? [] );
?>

<div class="fs-attributes"
     x-data='{
	        selectedAttribute : "",
	        addErrors : [],
	        addErrorsChild : [],
	        addSuccessMessage : "",
	        createAttribute : {
	            postId: <?php echo $post_id ?>,
	            name: "",
	            value: ""
	        },
			attributes: <?php echo json_encode( \FS\FS_Product::get_attributes_hierarchy( $post_id ) ) ?>,
			showAddForm: false,
			getAttributes(){
				 $store.FS?.getAttributes(this.createAttribute.postId)
				    .then((response) => response.json())
				    .then((response) => { this.attributes = response.data; this.$refresh() });
			},
			validateAttribute(ctx,exclude = []){
				this[ctx] = [];
				if(!exclude.includes("name") && this.createAttribute.name.length < 1) this[ctx].push("Назва атрибута не може бути порожньою");
				if(!exclude.includes("value") && this.createAttribute.value.length < 1) this[ctx].push("Значення атрибута не може бути порожнім");

				return this[ctx].length < 1;
			},
			addAttribute(){
				if(this.validateAttribute("addErrors")){
					this.$store.FS?.insertAttribute(this.createAttribute.postId, this.createAttribute.name, this.createAttribute.value)
					.then((response) => response.json())
					.then((response) => {
						if(response.success){
							this.createAttribute.name = "";
							this.createAttribute.value = "";
							this.getAttributes();
						}
					});
				}
			},
			addChildAttribute(parentId,key){
				if(this.validateAttribute("addErrorsChild",["name"])){
					this.$store.FS?.insertChildAttribute(this.createAttribute.postId, this.createAttribute.value,parentId)
					.then((response) => response.json())
					.then((response) => {
						if(response.success){
							this.createAttribute.name = "";
							this.createAttribute.value = "";
							this.attributes[key]["children"].push(response.data.term);
						}
					});
				}
			},
			attachAttribute(){
				if(this.selectedAttribute!==""){
					$store.FS?.attachAttribute(this.createAttribute.postId,this.selectedAttribute)
					.then((response) => response.json())
					.then((response) => {
						if(response.success){
							this.selectedAttribute = "";
							this.getAttributes();
						}
					});
				}else{
					this.showAddForm = true;
				}
			},
			detachAttribute(attributeId){
				$store.FS?.detachAttribute(this.createAttribute.postId,attributeId)
					.then((response) => response.json())
					.then((response) => {  this.getAttributes() });
			}
}'
>
	<div class="fs-attributes__add">
		<select class="fs-attributes__select" x-model="selectedAttribute">
			<option value="">Додати новий атрибут</option>
			<?php foreach ( $attributes as $attribute ): ?>
				<option value="<?php echo $attribute->term_id ?>"><?php echo $attribute->name ?></option>
			<?php endforeach; ?>
		</select>
		<button href="#" class="button button-primary button-large"
		        x-on:click.prevent="attachAttribute()"><?php _e( 'Додати', 'f-shop' ) ?></button>
	</div>

	<div class="fs-attributes__create fs-flex fs-flex-items-center fs-flex-beetween fs-flex-wrap" x-show="showAddForm"
	     x-transition>
		<div class="fs-attributes__create-field fs-width-1-4">
			<label for="">Назва</label>
			<input type="text" class="fs-attributes__input" x-model="createAttribute.name">
		</div>
		<div class="fs-attributes__create-field fs-flex-1">
			<label for="">Значення</label>
			<input type="text" class="fs-attributes__input"
			       x-model="createAttribute.value"
			       placeholder="можна додати декілька значень, приклад: синій|червоний ">
		</div>
		<div class="fs-attributes__create-field">
			<div>&nbsp;</div>
			<button class="button button-primary button-large"
			        x-on:click.prevent="addAttribute()"><?php _e( 'Зберегти', 'f-shop' ) ?></button>
		</div>

		<!--Here we show the errors that occur when adding attributes-->
		<ul class="fs-width-100 fs-m-0" x-show="addErrors.length>0">
			<template x-for="(error,index) in addErrors" :key="'error-'+index">
				<li x-text="error" class="fs-field__error"></li>
			</template>
		</ul>
	</div>

	<div class="fs-attributes__list">
		<template x-for="(attribute, attributeIndex) in attributes" :key="attribute.id">
			<div class="fs-attributes__item " x-data="{ open:false }">
				<div class="fs-attributes__item-header fs-flex fs-flex-items-center fs-flex-beetween fs-flex-wrap">
					<div class="fs-attributes__item-name fs-flex fs-flex-1 fs-flex-items-center fs-gap-0-5">
						<span class="dashicons dashicons-category"></span>
						<span x-text="attribute.name+' ('+attribute.children.length+')'"></span>
					</div>
					<div class="fs-attributes__item-actions">
						<button class="fs-text-error" title="Видалити атрибут і всі його значення"
						        x-on:click.prevent="detachAttribute(attribute.id)"><span
								class="dashicons dashicons-trash"></span></button>
						<button
							x-on:click.prevent="open=!open"><span
								class="dashicons dashicons-arrow-down-alt2"></span>
						</button>
					</div>
				</div>
				<div class="fs-attributes__item-values" x-show="open" x-transition>
					<template x-for="value in attribute.children" :key="value.term_id">
						<div
							x-data="{show:true}" x-show="show"
							class="fs-attributes__item-value fs-flex fs-flex-items-center fs-flex-beetween fs-flex-wrap">
							<div class="fs-attributes__item-value-name fs-flex-1">
								<span x-text="value.name"></span>
							</div>
							<div class="fs-attributes__item-value-actions">
								<button class="fs-text-error" title="Видалити значення"
								        x-on:click.prevent="detachAttribute(value.term_id);show=false">
									<span class="dashicons dashicons-trash"></span>
								</button>
							</div>
						</div>
					</template>
					<div class="fs-attributes__add-child fs-flex fs-flex-items-center fs-gap-0-5">
						<input type="text" x-model="createAttribute.value">
						<button class="button button-large"
						        x-on:click.prevent="addChildAttribute(attribute.id,attributeIndex)"><?php _e( 'Додати значення', 'f-shop' ) ?>
						</button>
					</div>
				</div>
			</div>
		</template>
	</div>
</div>