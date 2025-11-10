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
?>

<div class="fs-attributes"
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
			newAttributeName: "",
			showCreateNewForm: false,
			attributes: <?php echo htmlentities(json_encode( \FS\FS_Product::get_attributes_hierarchy( $post_id ) ?? [])) ?>,
			showAddForm: false,
			draggedIndex: null,
			dragOverIndex: null,
			getAttributes(){
				 $store.FS?.getAttributes(<?php echo $post_id ?>)
				    .then((response) => { this.attributes = response.data; });
			},
			handleDragStart(index){
				this.draggedIndex = index;
			},
			handleDragOver(event, index){
				event.preventDefault();
				this.dragOverIndex = index;
			},
			handleDragLeave(){
				this.dragOverIndex = null;
			},
			handleDrop(event, dropIndex){
				event.preventDefault();
				if(this.draggedIndex === null || this.draggedIndex === dropIndex){
					this.dragOverIndex = null;
					this.draggedIndex = null;
					return;
				}
				
				// Reorder attributes array
				const draggedItem = this.attributes[this.draggedIndex];
				this.attributes.splice(this.draggedIndex, 1);
				this.attributes.splice(dropIndex, 0, draggedItem);
				
				// Update positions
				this.updatePositions();
				
				this.dragOverIndex = null;
				this.draggedIndex = null;
			},
			updatePositions(){
				this.attributes.forEach((attr, index) => {
					if(attr.position !== index){
						this.$store.FS?.updateAttributePosition(attr.id, index);
					}
				});
				// Refresh list after a short delay to ensure positions are saved
				setTimeout(() => {
					this.getAttributes();
				}, 300);
			},
			createNewAttribute(){
				if(this.newAttributeName.trim().length < 1){
					alert("<?php _e('Attribute name cannot be empty', 'f-shop'); ?>");
					return;
				}
				this.$store.FS?.createNewAttribute(this.createAttribute.postId, this.newAttributeName)
					.then((response) => {
						if(response.success){
							this.newAttributeName = "";
							this.showCreateNewForm = false;
							this.getAttributes();
						} else {
							alert(response.data?.message || "<?php _e('Error creating attribute', 'f-shop'); ?>");
						}
					});
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
			detachAttribute(attributeId){
				$store.FS?.detachAttribute(this.createAttribute.postId,attributeId)
					.then((response) => {  this.getAttributes() });
			}
}'
>
	<div class="fs-attributes__add" >
		<select class="fs-attributes__select" x-model="selectedAttribute">
			<option><?php _e('Add a new attribute','f-shop'); ?></option>
			<?php foreach ( $attributes as $attribute ): ?>
				<option value="<?php echo $attribute->term_id ?>"><?php echo $attribute->name ?></option>
			<?php endforeach; ?>
		</select>
		<button  class="button button-primary button-large"
		        x-on:click.prevent="selectedAttribute ? attachAttribute(selectedAttribute) : showAddForm=true"><?php _e( 'Add', 'f-shop' ) ?></button>
		<button class="button button-large" 
		        x-on:click.prevent="showCreateNewForm = true"
		        style="margin-left: 10px;"><?php _e('Create New', 'f-shop'); ?></button>
	</div>

	<div class="fs-attributes__create-new fs-flex fs-flex-items-center fs-flex-beetween fs-flex-wrap" 
	     x-show="showCreateNewForm"
	     x-transition
	     style="margin-top: 15px; padding: 15px; background: #f5f5f5; border-radius: 4px; gap: 15px;">
		<div class="fs-attributes__create-field fs-flex-1">
			<label for="new-attribute-name" style="display: block; margin-bottom: 5px;"><?php _e('Attribute Name:', 'f-shop'); ?></label>
			<input type="text" 
			       id="new-attribute-name"
			       class="fs-attributes__input" 
			       x-model="newAttributeName"
			       @keyup.enter="createNewAttribute()"
			       placeholder="<?php _e('Enter attribute name', 'f-shop'); ?>"
			       style="width: 100%;">
		</div>
		<div class="fs-attributes__create-field" style="display: flex; flex-direction: column; gap: 10px; justify-content: flex-start; align-items: flex-start;">
			<label style="display: block; margin-bottom: 5px; visibility: hidden;">&nbsp;</label>
			<div style="display: flex; gap: 10px;">
				<button class="button button-primary button-large"
				        x-on:click.prevent="createNewAttribute()"><?php _e('OK', 'f-shop'); ?></button>
				<button class="button button-large"
				        x-on:click.prevent="showCreateNewForm = false; newAttributeName = ''"><?php _e('Cancel', 'f-shop'); ?></button>
			</div>
		</div>
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

	<div class="fs-attributes__list">
		<template x-for="(attribute, attributeIndex) in attributes" :key="'attribute-'+attributeIndex">
			<div class="fs-attributes__item" 
			     x-data="{ open:false, isDragging: false }"
			     draggable="true"
			     @dragstart="handleDragStart(attributeIndex); isDragging = true"
			     @dragend="isDragging = false; draggedIndex = null"
			     @dragover.prevent="handleDragOver($event, attributeIndex)"
			     @dragleave="handleDragLeave()"
			     @drop="handleDrop($event, attributeIndex)"
			     :class="{ 'fs-attributes__item--dragging': isDragging, 'fs-attributes__item--drag-over': dragOverIndex === attributeIndex }"
			     style="cursor: move; position: relative;">
				<div class="fs-attributes__item-header fs-flex fs-flex-items-center fs-flex-beetween fs-flex-wrap">
					<div class="fs-attributes__item-name fs-flex fs-flex-1 fs-flex-items-center fs-gap-0-5">
						<span class="fs-attributes__drag-handle dashicons dashicons-menu-alt" 
						      style="cursor: move; opacity: 0; transition: opacity 0.2s; color: #666;"
						      @mouseenter="$el.style.opacity = '1'"
						      @mouseleave="$el.style.opacity = '0'"
						      title="<?php _e('Drag to reorder', 'f-shop'); ?>"></span>
						<span class="dashicons dashicons-category"></span>
						<span x-text="attribute.name+' ('+attribute.children.length+')'"></span>
					</div>
					<div class="fs-attributes__item-position fs-flex fs-flex-items-center fs-gap-0-5" style="margin-right: 10px;">
						<label style="margin: 0; white-space: nowrap;"><?php _e('Position:', 'f-shop'); ?></label>
						<input type="number" 
						       :value="attribute.position || 0" 
						       @change="$store.FS?.updateAttributePosition(attribute.id, $event.target.value).then(() => { getAttributes(); })"
						       style="width: 60px; padding: 2px 5px;"
						       min="0"
						       step="1">
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
				<div class="fs-attributes__item-values" x-show="open" x-transition>
					<template x-for="(value,index) in attribute.children" :key="'child-'+index">
						<div
							x-data="{show:true}" x-show="show"
							class="fs-attributes__item-value fs-flex fs-flex-items-center fs-flex-beetween fs-flex-wrap">
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
						        x-on:click.prevent="addChildAttribute(attribute.id,attributeIndex);">
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