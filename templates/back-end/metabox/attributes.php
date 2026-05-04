<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $_GET['post'] ) || ! isset( $_GET['action'] ) || $_GET['action'] !== 'edit' ) {
	return;
}

$post_id    = absint( $_GET['post'] );
$preset_categories = \FS\FS_Product::get_product_attribute_preset_categories( $post_id );

$attributes = get_terms( [
	'taxonomy'   => \FS\FS_Config::get_data( 'features_taxonomy' ),
	'hide_empty' => false,
	'parent'     => 0
] );
?>

<div class="fs-attributes"
     data-post-id="<?php echo $post_id ?>"
     x-data='{
	        selectedAttribute : null,
	        addErrors: [],
	        addErrorsChild: [],
	        addSuccessMessage: "",
	        createAttribute: {
	            postId: <?php echo $post_id ?>,
	            name: "",
	            value: ""
	        },
			presetCategories: <?php echo json_encode( $preset_categories, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) ?>,
			selectedPresetCategory: <?php echo ! empty( $preset_categories ) ? (int) $preset_categories[0]['id'] : 'null' ?>,
			presetError: "",
			presetSuccessMessage: "",
			presetLoading: false,
			attributes: <?php echo json_encode( \FS\FS_Product::get_attributes_hierarchy( $post_id ) ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) ?>,
			showAddForm: false,
			getAttributes(){
				 return $store.FS?.getAttributes(<?php echo $post_id ?>)
				    .then((response) => { this.attributes = response.data; });
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
					.then((response) => {
						if(response.success){
							this.createAttribute.name = "";
							this.createAttribute.value = "";
							this.attributes.push(response.data.term);
						}
					});
				}
			},
			addChildAttribute(parentId,key){
				if(this.validateAttribute("addErrorsChild",["name"])){
					this.$store.FS?.insertChildAttribute(this.createAttribute.postId, this.createAttribute.value,parentId)
					.then((response) => {
						if(response.success){
							this.createAttribute.name = "";
							this.createAttribute.value = "";
							this.attributes[key]["children"].push(response.data.term);
						}
					});
				}
			},
			attachAttribute(id=null){
				if(id){
					this.$store.FS?.attachAttribute(this.createAttribute.postId,id)
					.then((response) => {
						if(response.success){
							this.selectedAttribute = "";
							this.getAttributes()
						}
					});
				}else{
					this.showAddForm = true;
				}
			},
			loadPresetAttributes(){
				if(!this.selectedPresetCategory || this.attributes.length > 0 || this.presetLoading){
					return;
				}

				this.presetError = "";
				this.presetSuccessMessage = "";
				this.presetLoading = true;

				this.$store.FS?.loadCategoryAttributePreset(this.createAttribute.postId, this.selectedPresetCategory)
					.then((response) => {
						if(response.success){
							this.presetSuccessMessage = response.data?.message || "";
							this.getAttributes();
							return;
						}

						this.presetError = response.data?.message || "Не вдалося завантажити атрибути.";
					})
					.catch((error) => {
						this.presetError = error?.data?.message || error?.message || "Не вдалося завантажити атрибути.";
					})
					.finally(() => {
						this.presetLoading = false;
					});
			},
			detachAttribute(attributeId){
				$store.FS?.detachAttribute(this.createAttribute.postId,attributeId)
					.then((response) => {  this.getAttributes() });
			}
}'
>
	<div class="fs-attributes__preset fs-flex fs-flex-items-end fs-flex-wrap fs-gap-0-5"
	     x-show="attributes.length === 0 && presetCategories.length > 0">
		<div class="fs-attributes__preset-field fs-flex-1">
			<label for="fs-attributes-preset-category"><?php _e('Категорія з предустановленими атрибутами', 'f-shop'); ?></label>
			<select id="fs-attributes-preset-category" class="fs-attributes__select" x-model="selectedPresetCategory">
				<template x-for="category in presetCategories" :key="'preset-category-' + category.id">
					<option :value="category.id" x-text="category.label"></option>
				</template>
			</select>
		</div>
		<div class="fs-attributes__preset-action">
			<button class="button button-secondary button-large"
			        x-bind:disabled="presetLoading"
			        x-on:click.prevent="loadPresetAttributes()"
			        x-text="presetLoading ? '<?php echo esc_js( __( 'Завантаження...', 'f-shop' ) ); ?>' : '<?php echo esc_js( __( 'Завантажити предустановлені атрибути', 'f-shop' ) ); ?>'"></button>
		</div>
		<p class="fs-attributes__preset-note fs-width-100">
			<?php _e('Панель відображається лише поки у товару ще немає налаштованих атрибутів.', 'f-shop'); ?>
		</p>
		<p class="fs-field__error fs-width-100 fs-m-0" x-show="presetError.length > 0" x-text="presetError"></p>
		<p class="fs-field__success fs-width-100 fs-m-0" x-show="presetSuccessMessage.length > 0" x-text="presetSuccessMessage"></p>
	</div>

	<div class="fs-attributes__add" >
		<select class="fs-attributes__select" x-model="selectedAttribute">
			<option><?php _e('Add a new attribute','f-shop'); ?></option>
			<?php foreach ( $attributes as $attribute ): ?>
				<option value="<?php echo $attribute->term_id ?>"><?php echo $attribute->name ?></option>
			<?php endforeach; ?>
		</select>
		<button  class="button button-primary button-large"
		        x-on:click.prevent="selectedAttribute ? attachAttribute(selectedAttribute) : showAddForm=true"><?php _e( 'Add', 'f-shop' ) ?></button>
	</div>

	<div class="fs-attributes__create fs-flex fs-flex-items-center fs-flex-beetween fs-flex-wrap" x-show="showAddForm"
	     x-transition>
		<div class="fs-attributes__create-field fs-width-1-4">
			<label for=""><?php _e('Name','f-shop'); ?></label>
			<input type="text" class="fs-attributes__input" x-model="createAttribute.name">
		</div>
		<div class="fs-attributes__create-field fs-flex-1">
			<label for=""><?php _e('Value','f-shop'); ?></label>
			<input type="text" class="fs-attributes__input"
			       x-model="createAttribute.value">
		</div>
		<div class="fs-attributes__create-field">
			<div>&nbsp;</div>
			<button class="button button-primary button-large"
			        x-on:click.prevent="addAttribute()"><?php _e( 'Save', 'f-shop' ) ?></button>
		</div>

		<!--Here we show the errors that occur when adding attributes-->
		<ul class="fs-width-100 fs-m-0" x-show="addErrors.length>0">
			<template x-for="(error,index) in addErrors" :key="'error-'+index">
				<li x-text="error" class="fs-field__error"></li>
			</template>
		</ul>
	</div>

	<div class="fs-attributes__list" data-fs-attribute-groups-container>
		<template x-for="attribute in attributes" :key="'attr-' + attribute.id">
			<div class="fs-attributes__item" x-data="{ open:false }" x-bind:data-attribute-id="attribute.id">
				<div class="fs-attributes__item-header fs-flex fs-flex-items-center fs-flex-beetween fs-flex-wrap"
				     x-bind:data-attribute-id="attribute.id">
					<div class="fs-attributes__item-name fs-flex fs-flex-1 fs-flex-items-center fs-gap-0-5">
						<div class="fs-attributes__item-drag">
							<span class="dashicons dashicons-menu-alt3"></span>
						</div>
						<span class="dashicons dashicons-category"></span>
						<span x-text="attribute.name+' ('+attribute.children.length+')'"></span>
					</div>
					<div class="fs-attributes__item-actions">
						<button class="fs-text-error" title="<?php _e('Delete the attribute and all its values','f-shop'); ?>"
						        x-on:click.prevent="detachAttribute(attribute.id)"><span
								class="dashicons dashicons-trash"></span></button>
						<button
							x-on:click.prevent="open=!open"><span
								class="dashicons dashicons-arrow-down-alt2"></span>
						</button>
					</div>
				</div>
				<div class="fs-attributes__item-values" x-show="open" x-transition
			     data-fs-attribute-values-container>
					<template x-for="value in attribute.children" :key="'val-' + value.id">
						<div
							x-data="{show:true}" x-show="show"
							class="fs-attributes__item-value fs-flex fs-flex-items-center fs-flex-beetween fs-flex-wrap"
							x-bind:data-value-id="value.id">
							<div class="fs-attributes__item-value-drag">
								<span class="dashicons dashicons-menu-alt3"></span>
							</div>
							<div class="fs-attributes__item-value-name fs-flex-1">
								<span x-text="value.name"></span>
							</div>
							<div class="fs-attributes__item-value-actions">
								<button class="fs-text-error" title="<?php _e('Delete value','f-shop'); ?>"
								        x-on:click.prevent="detachAttribute(value.id);show=false">
									<span class="dashicons dashicons-trash"></span>
								</button>
							</div>
						</div>
					</template>
					<div class="fs-attributes__add-child fs-flex fs-flex-items-center fs-flex-wrap fs-gap-0-5"
					     x-data="{createNew:false, id:null}">
						<div class="fs-width-100 fs-flex fs-flex-items-center">
							<input type="checkbox" x-model="createNew" value="true"
							       :id="'create-checkbox-'+attribute.id">
							<label :for="'create-checkbox-'+attribute.id"><?php _e('Create new ones','f-shop'); ?></label>
						</div>
						<input type="text" placeholder="<?php _e('Enter a name','f-shop'); ?>" x-model="createAttribute.value" x-show="createNew" class="fs-width-1-4">
						<select x-show="!createNew" class="fs-width-1-4" x-model="id">
							<option value=""><?php _e('Select from the list','f-shop'); ?></option>
							<template x-for="att in attribute.children_all" :key="'children_all'+att.id">
								<option :value="att.id" x-text="att.name"></option>
							</template>
						</select>
						<button class="button button-large" x-show="createNew"
						        x-on:click.prevent="addChildAttribute(attribute.id, attributes.indexOf(attribute));">
							<?php _e( 'Add value', 'f-shop' ) ?>
						</button>
						<button class="button button-large" x-show="!createNew"
						        x-on:click.prevent="attachAttribute(id);
						      attribute.children.push(attribute.children_all.find(item=>item.id===parseInt(id)));">
							<?php _e( 'Add value', 'f-shop' ) ?>
						</button>
					</div>
				</div>
			</div>
		</template>
	</div>
</div>
