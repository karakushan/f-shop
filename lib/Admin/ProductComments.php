<?php

namespace FS\Admin;

/**
 * Admin page for adding product comments
 */
class ProductComments {

	/**
	 * ProductComments constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'admin_menu', [ $this, 'add_menu' ] );
			add_action( 'admin_footer', [ $this, 'admin_footer_content' ] );
		}
	}

	/**
	 * Add menu item for product comments
	 */
	public function add_menu() {
		add_submenu_page(
			'edit.php?post_type=' . \FS\FS_Config::get_data( 'post_type' ),
			__( 'Add Comment', 'f-shop' ),
			__( 'Add Comment', 'f-shop' ),
			'moderate_comments',
			'fs-add-product-comment',
			[ $this, 'render_page' ]
		);
	}

	/**
	 * Render the admin page
	 */
	public function render_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Add Product Comment', 'f-shop' ); ?></h1>
			<p><?php _e( 'Create a new comment for any product. The comment will be published immediately.', 'f-shop' ); ?></p>
			<button type="button" class="button button-primary" id="fs-open-comment-modal">
				<?php _e( 'Add New Comment', 'f-shop' ); ?>
			</button>
		</div>
		<?php
	}

	/**
	 * Render the modal form in admin footer
	 */
	public function admin_footer_content() {
		$screen = get_current_screen();
		
		// Only inject button on edit-comments.php
		if ( $screen && $screen->id === 'edit-comments' ) {
			?>
			<script type="text/javascript">
			jQuery(document).ready(function($) {
				// Add button after the page title
				$('<button type="button" class="page-title-action" id="fs-add-comment-btn"><?php echo esc_js( __( 'Add Comment', 'f-shop' ) ); ?></button>')
					.insertAfter('.wp-heading-inline');
				
				// Initialize modal when button is clicked
				$('#fs-add-comment-btn').on('click', function() {
					$('#fs-comment-modal').show();
				});
			});
			</script>
			<?php
		}
		
		// Always render modal content for the dedicated admin page
		if ( $screen && $screen->id === 'product_page_fs-add-product-comment' ) {
			$this->modal_content();
		}
		
		// Render modal for edit-comments page as well
		if ( $screen && $screen->id === 'edit-comments' ) {
			$this->modal_content();
		}
	}

	/**
	 * Render the modal form in admin footer (for dedicated page)
	 */
	public function render_modal() {
		$this->modal_content();
	}

	/**
	 * Output the modal HTML content
	 */
	private function modal_content() {
		?>
		<!-- FS Product Comment Modal -->
		<div id="fs-comment-modal" class="fs-comment-modal" style="display: none;" x-data="fsCommentForm()" x-cloak>
			<div class="fs-comment-modal-overlay" @click="closeModal()"></div>
			<div class="fs-comment-modal-content">
				<div class="fs-comment-modal-header">
					<h2><?php _e( 'Add Product Comment', 'f-shop' ); ?></h2>
					<button type="button" class="fs-comment-modal-close" @click="closeModal()">&times;</button>
				</div>
				<form id="fs-comment-form" method="post" enctype="multipart/form-data" @submit.prevent="submitForm">
					<input type="hidden" name="action" value="fs_admin_add_comment">
					<?php wp_nonce_field( 'fs_add_comment_nonce', 'fs_comment_nonce' ); ?>
					<div class="fs-comment-modal-body">
						<!-- Product Search Dropdown -->
						<div class="fs-form-row">
							<label for="fs-comment-product"><?php _e( 'Product', 'f-shop' ); ?> <span class="required">*</span></label>
							<div class="fs-product-search" style="position: relative;">
								<input type="text" 
									   x-model="productSearch" 
									   @input.debounce.300ms="searchProducts()"
									   @click="showDropdown = true"
									   @keydown.escape="showDropdown = false"
									   placeholder="<?php _e( 'Search for a product...', 'f-shop' ); ?>"
									   class="regular-text"
									   autocomplete="off">
								<input type="hidden" name="post_id" x-model="selectedProductId" required>
								<div class="fs-product-dropdown" x-show="showDropdown && (products.length > 0 || isLoading)" style="display: none;" x-transition>
									<div class="fs-product-dropdown-loading" x-show="isLoading">
										<?php _e( 'Loading...', 'f-shop' ); ?>
									</div>
									<div class="fs-product-dropdown-items">
										<template x-for="product in products" :key="product.id">
											<div class="fs-product-dropdown-item" 
												 @click="selectProduct(product)"
												 :class="{'selected': selectedProductId === product.id}">
												<span class="fs-product-title" x-text="product.title"></span>
												<span class="fs-product-price" x-text="product.price"></span>
											</div>
										</template>
									</div>
								</div>
								<div class="fs-selected-product" x-show="selectedProductId && selectedProduct">
									<span x-text="selectedProduct.title"></span>
									<button type="button" class="fs-clear-product" @click="clearProduct()">×</button>
								</div>
							</div>
						</div>
						<div class="fs-form-row">
							<label for="fs-comment-author"><?php _e( 'Name', 'f-shop' ); ?> <span class="required">*</span></label>
							<input type="text" id="fs-comment-author" name="author" class="regular-text" x-model="formData.author" required>
						</div>
						<div class="fs-form-row">
							<label for="fs-comment-email"><?php _e( 'Email', 'f-shop' ); ?> <span class="required">*</span></label>
							<input type="email" id="fs-comment-email" name="email" class="regular-text" x-model="formData.email" required>
						</div>
						<div class="fs-form-row">
							<label for="fs-comment-rating"><?php _e( 'Rating', 'f-shop' ); ?> <span class="required">*</span></label>
							<div class="fs-rating-stars" id="fs-rating-stars">
								<span class="fs-star" data-value="1" @click="formData.rating = 1" :class="{'active': formData.rating >= 1}">&#9733;</span>
								<span class="fs-star" data-value="2" @click="formData.rating = 2" :class="{'active': formData.rating >= 2}">&#9733;</span>
								<span class="fs-star" data-value="3" @click="formData.rating = 3" :class="{'active': formData.rating >= 3}">&#9733;</span>
								<span class="fs-star" data-value="4" @click="formData.rating = 4" :class="{'active': formData.rating >= 4}">&#9733;</span>
								<span class="fs-star" data-value="5" @click="formData.rating = 5" :class="{'active': formData.rating >= 5}">&#9733;</span>
							</div>
							<input type="hidden" id="fs-comment-rating" name="rating" x-model="formData.rating" required>
						</div>
						<div class="fs-form-row">
							<label for="fs-comment-content"><?php _e( 'Review', 'f-shop' ); ?> <span class="required">*</span></label>
							<textarea id="fs-comment-content" name="body" class="regular-text" rows="5" x-model="formData.body" required></textarea>
						</div>
						<div class="fs-form-row">
							<label for="fs-comment-images"><?php _e( 'Attach Images', 'f-shop' ); ?></label>
							<input type="file" id="fs-comment-images" name="files[]" multiple accept="image/*" @change="handleFiles($event)">
							<p class="description"><?php _e( 'Maximum file size: 10 MB', 'f-shop' ); ?></p>
							<div id="fs-image-preview" class="fs-image-preview">
								<template x-for="(file, index) in uploadedFiles" :key="index">
									<img :src="file.preview" alt="">
								</template>
							</div>
						</div>
						<div id="fs-comment-message" class="fs-comment-message" x-show="message" x-text="message" :class="messageClass" style="display: none;"></div>
					</div>
					<div class="fs-comment-modal-footer">
						<button type="button" class="button button-secondary" @click="closeModal()"><?php _e( 'Cancel', 'f-shop' ); ?></button>
						<button type="submit" class="button button-primary" id="fs-comment-submit" :disabled="isSubmitting">
							<span x-show="!isSubmitting"><?php _e( 'Add Comment', 'f-shop' ); ?></span>
							<span x-show="isSubmitting"><?php _e( 'Saving...', 'f-shop' ); ?></span>
						</button>
					</div>
				</form>
			</div>
		</div>

		<style>
		/* FS Comment Modal Styles */
		.fs-comment-modal {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			z-index: 1000000;
		}

		.fs-comment-modal-overlay {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.6);
		}

		.fs-comment-modal-content {
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			width: 90%;
			max-width: 600px;
			max-height: 90vh;
			overflow-y: auto;
			background: #fff;
			border-radius: 4px;
			box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
		}

		.fs-comment-modal-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 16px 20px;
			border-bottom: 1px solid #e5e5e5;
		}

		.fs-comment-modal-header h2 {
			margin: 0;
			font-size: 1.2rem;
			font-weight: 600;
		}

		.fs-comment-modal-close {
			background: none;
			border: none;
			font-size: 24px;
			cursor: pointer;
			padding: 0;
			line-height: 1;
			color: #666;
		}

		.fs-comment-modal-close:hover {
			color: #23282d;
		}

		.fs-comment-modal-body {
			padding: 20px;
		}

		.fs-comment-modal-footer {
			display: flex;
			justify-content: flex-end;
			gap: 10px;
			padding: 16px 20px;
			border-top: 1px solid #e5e5e5;
			background: #f9f9f9;
		}

		.fs-form-row {
			margin-bottom: 16px;
		}

		.fs-form-row label {
			display: block;
			margin-bottom: 6px;
			font-weight: 500;
		}

		.fs-form-row .required {
			color: #dc3232;
		}

		.fs-form-row input[type="text"],
		.fs-form-row input[type="email"],
		.fs-form-row textarea {
			width: 100%;
			box-sizing: border-box;
		}

		.fs-rating-stars {
			font-size: 28px;
			line-height: 1;
		}

		.fs-star {
			color: #ddd;
			cursor: pointer;
			transition: color 0.2s;
		}

		.fs-star:hover,
		.fs-star.active {
			color: #ffb91d;
		}

		/* Product Search Dropdown Styles */
		.fs-product-search {
			position: relative;
		}

		.fs-product-dropdown {
			position: absolute;
			top: 100%;
			left: 0;
			right: 0;
			background: #fff;
			border: 1px solid #ccc;
			border-top: none;
			max-height: 250px;
			overflow-y: auto;
			z-index: 100;
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
		}

		.fs-product-dropdown-loading {
			padding: 12px;
			text-align: center;
			color: #666;
		}

		.fs-product-dropdown-item {
			padding: 12px;
			border-bottom: 1px solid #eee;
			cursor: pointer;
			display: flex;
			justify-content: space-between;
			align-items: center;
		}

		.fs-product-dropdown-item:hover,
		.fs-product-dropdown-item.selected {
			background: #f0f0f1;
		}

		.fs-product-title {
			font-weight: 500;
		}

		.fs-product-price {
			color: #28a745;
			font-weight: 600;
		}

		.fs-selected-product {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 10px 12px;
			background: #e9ecef;
			border-radius: 4px;
			margin-top: 8px;
		}

		.fs-clear-product {
			background: none;
			border: none;
			font-size: 20px;
			cursor: pointer;
			color: #dc3232;
			line-height: 1;
			padding: 0 4px;
		}

		.fs-image-preview {
			display: flex;
			flex-wrap: wrap;
			gap: 10px;
			margin-top: 10px;
		}

		.fs-image-preview img {
			max-width: 80px;
			max-height: 80px;
			object-fit: cover;
			border-radius: 4px;
		}

		.fs-comment-message {
			padding: 12px;
			border-radius: 4px;
			margin-top: 10px;
		}

		.fs-comment-message.success {
			background: #d4edda;
			border: 1px solid #c3e6cb;
			color: #155724;
		}

		.fs-comment-message.error {
			background: #f8d7da;
			border: 1px solid #f5c6cb;
			color: #721c24;
		}

		[x-cloak] { display: none !important; }
		</style>

		<script>
		function fsCommentForm() {
			return {
				productSearch: '',
				products: [],
				selectedProductId: '',
				selectedProduct: null,
				showDropdown: false,
				isLoading: false,
				isSubmitting: false,
				message: '',
				messageClass: '',
				uploadedFiles: [],
				
				formData: {
					author: '',
					email: '',
					rating: 5,
					body: ''
				},

				searchProducts: function() {
					if (this.productSearch.length < 2) {
						this.products = [];
						this.showDropdown = false;
						return;
					}

					this.isLoading = true;
					this.showDropdown = true;
					
					var formData = new FormData();
					formData.append('action', 'fs_search_product_admin');
					formData.append('search', this.productSearch);

					fetch(ajaxurl, {
						method: 'POST',
						body: formData
					})
					.then(response => response.json())
					.then(data => {
						this.products = (data.success && data.data) ? data.data : [];
					})
					.catch(error => {
						console.error('Search error:', error);
						this.products = [];
					})
					.finally(() => {
						this.isLoading = false;
					});
				},

				selectProduct: function(product) {
					this.selectedProductId = product.id;
					this.selectedProduct = product;
					this.productSearch = '';
					this.showDropdown = false;
				},

				clearProduct: function() {
					this.selectedProductId = '';
					this.selectedProduct = null;
					this.productSearch = '';
				},

				handleFiles: function(event) {
					var files = event.target.files;
					this.uploadedFiles = [];
					
					if (files) {
						Array.from(files).forEach(file => {
							if (file.type.startsWith('image/')) {
								var reader = new FileReader();
								reader.onload = (e) => {
									this.uploadedFiles.push({
										name: file.name,
										preview: e.target.result
									});
								};
								reader.readAsDataURL(file);
							}
						});
					}
				},

				submitForm: function() {
					if (!this.selectedProductId) {
						this.message = '<?php _e( "Please select a product", "f-shop" ); ?>';
						this.messageClass = 'error';
						return;
					}

					this.isSubmitting = true;
					this.message = '';

					var form = document.getElementById('fs-comment-form');
					var formData = new FormData(form);

					fetch(ajaxurl, {
						method: 'POST',
						body: formData
					})
					.then(response => response.json())
					.then(data => {
						if (data.success) {
							this.message = data.message || '<?php _e( "Comment added successfully", "f-shop" ); ?>';
							this.messageClass = 'success';
							setTimeout(() => {
								this.closeModal();
							}, 1500);
						} else {
							var errorMsg = data.message || '<?php _e( "Error adding comment", "f-shop" ); ?>';
							if (data.data) {
								var errors = [];
								for (var key in data.data) {
									if (data.data.hasOwnProperty(key)) {
										errors.push(data.data[key]);
									}
								}
								if (errors.length) {
									errorMsg = errors.join(', ');
								}
							}
							this.message = errorMsg;
							this.messageClass = 'error';
						}
					})
					.catch(error => {
						this.message = '<?php _e( "Error adding comment", "f-shop" ); ?>';
						this.messageClass = 'error';
					})
					.finally(() => {
						this.isSubmitting = false;
					});
				},

				closeModal: function() {
					document.getElementById('fs-comment-modal').style.display = 'none';
					this.resetForm();
				},

				resetForm: function() {
					this.productSearch = '';
					this.products = [];
					this.selectedProductId = '';
					this.selectedProduct = null;
					this.showDropdown = false;
					this.isLoading = false;
					this.isSubmitting = false;
					this.message = '';
					this.messageClass = '';
					this.uploadedFiles = [];
					
					this.formData = {
						author: '',
						email: '',
						rating: 5,
						body: ''
					};
					
					document.getElementById('fs-comment-form').reset();
				}
			}
		}

		// Initialize Alpine.js modal open handlers
		jQuery(document).ready(function($) {
			// Open modal from dedicated page button
			$('#fs-open-comment-modal').on('click', function() {
				$('#fs-comment-modal').show();
			});
		});
		</script>
		<?php
	}
}
