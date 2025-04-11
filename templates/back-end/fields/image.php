<?php

/**
 * Image field template.
 *
 * @var array  $args
 * @var string $name
 */

// Ensure WordPress media is loaded
wp_enqueue_media();

// Check: if the value is not a number, it might be a filename or another string
$image_id = !empty($args['value']) ? (int) $args['value'] : 0;
$image_url = !empty($args['value']) ? esc_url(wp_get_attachment_image_url($args['value'], 'medium')) : '';
$show_placeholder = empty($args['value']) ? 'true' : 'false';

// Get maximum upload size from php.ini
$max_upload_size = min(
    wp_max_upload_size(),
    (int) ini_get('upload_max_filesize') * 1024 * 1024,
    (int) ini_get('post_max_size') * 1024 * 1024
);
$max_upload_size_mb = round($max_upload_size / (1024 * 1024), 2);
?>

<div x-data="{
    imageUrl: '<?php echo $image_url; ?>',
    imageId: <?php echo $image_id; ?>,
    showPlaceholder: <?php echo $show_placeholder; ?>,
    isUploading: false,
    errorMessage: '',
    showError: false,
    maxFileSize: <?php echo $max_upload_size; ?>,
    maxFileSizeMB: <?php echo $max_upload_size_mb; ?>,
    
    openMediaLibrary(useCustomFile = false, event = null) {
        // Reset errors on new attempt
        this.showError = false;
        this.errorMessage = '';
        
        // If a file is passed, create a file object for upload
        let customFile = null;
        if (useCustomFile && event) {
            customFile = event.target.files[0];
            if (!customFile) return;
            
            // Check file type
            if (!customFile.type.match('image.*')) {
                this.showError = true;
                this.errorMessage = '<?php echo esc_js(__('Invalid file format. Please select an image', 'f-shop')); ?>';
                return;
            }
            
            // Check file size
            if (customFile.size > this.maxFileSize) {
                this.showError = true;
                this.errorMessage = '<?php echo esc_js(__('File is too large. Maximum size:', 'f-shop')); ?> ' + this.maxFileSizeMB + ' MB';
                return;
            }
            
            this.isUploading = true;
        }
        
        const mediaFrame = wp.media({
            title: '<?php esc_attr_e('Select Image', 'f-shop'); ?>',
            button: {
                text: '<?php esc_attr_e('Use This Image', 'f-shop'); ?>'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });
        
        // If we have a file to upload, add it directly to the media library
        if (useCustomFile && customFile) {
            // Create FormData for file upload
            const formData = new FormData();
            formData.append('action', 'upload-attachment');
            formData.append('_wpnonce', '<?php echo wp_create_nonce('media-form'); ?>');
            formData.append('async-upload', customFile);
            
            // Send AJAX request for upload
            fetch('<?php echo admin_url('async-upload.php'); ?>', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('<?php echo esc_js(__('Network error during file upload', 'f-shop')); ?>');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const attachment_id = data.data.id;
                    
                    // Set ID and URL of the uploaded image
                    this.imageId = attachment_id;
                    this.imageUrl = data.data.url;
                    this.showPlaceholder = false;
                    document.getElementById('<?php echo esc_attr($name); ?>_id').value = attachment_id;
                    
                    // Update upload status
                    this.isUploading = false;
                } else {
                    throw new Error(data.data && data.data.message ? data.data.message : '<?php echo esc_js(__('Error uploading file', 'f-shop')); ?>');
                }
            })
            .catch(error => {
                console.error('Error uploading file:', error);
                this.isUploading = false;
                this.showError = true;
                
                // Handle common errors
                if (error.message.includes('upload_max_filesize')) {
                    this.errorMessage = '<?php echo esc_js(__('File exceeds maximum upload size limit', 'f-shop')); ?> (' + this.maxFileSizeMB + ' MB)';
                } else if (error.message.includes('post_max_size')) {
                    this.errorMessage = '<?php echo esc_js(__('File is too large for server processing', 'f-shop')); ?>';
                } else {
                    this.errorMessage = error.message || '<?php echo esc_js(__('An error occurred while uploading the file', 'f-shop')); ?>';
                }
                
                // Clear file selection field to let the user select a file again
                document.getElementById('<?php echo esc_attr($name); ?>_file').value = '';
            });
            
            return;
        }
        
        // Standard handler for selecting from media library
        mediaFrame.on('select', () => {
            const attachment = mediaFrame.state().get('selection').first().toJSON();
            this.imageUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
            this.imageId = attachment.id;
            this.showPlaceholder = false;
            document.getElementById('<?php echo esc_attr($name); ?>_id').value = attachment.id;
            document.getElementById('<?php echo esc_attr($name); ?>_file').value = '';
            this.showError = false;
            this.errorMessage = '';
        });
        
        mediaFrame.open();
    },
    
    handleFile(event) {
        // Use WordPress media uploader for file
        this.openMediaLibrary(true, event);
    },
    
    removeImage() {
        this.imageUrl = '';
        this.imageId = 0;
        this.showPlaceholder = true;
        this.showError = false;
        this.errorMessage = '';
        document.getElementById('<?php echo esc_attr($name); ?>_id').value = '';
        document.getElementById('<?php echo esc_attr($name); ?>_file').value = '';
    },
    
    closeError() {
        this.showError = false;
        this.errorMessage = '';
    }
}" class="fs-field-image">
    <!-- Image preview -->
    <div class="fs-field-image__preview">
        <template x-if="!showPlaceholder && !isUploading">
            <img :src="imageUrl"
                class="fs-field-image__preview-img"
                alt="<?php esc_attr_e('Preview', 'f-shop'); ?>">
        </template>
        <template x-if="isUploading">
            <div class="fs-field-image__uploading">
                <span class="spinner is-active"></span>
                <span class="fs-field-image__uploading-text"><?php esc_html_e('Uploading...', 'f-shop'); ?></span>
            </div>
        </template>
        <template x-if="showPlaceholder && !isUploading">
            <div class="fs-field-image__placeholder">
                <img src="<?php echo esc_url(plugin_dir_url(FS_PLUGIN_FILE).'assets/img/add-img.svg'); ?>"
                    class="fs-field-image__placeholder-icon"
                    alt="<?php esc_attr_e('Placeholder', 'f-shop'); ?>">
                <span class="fs-field-image__placeholder-text"><?php esc_html_e('No image selected', 'f-shop'); ?></span>
            </div>
        </template>
    </div>

    <!-- Error message -->
    <div x-show="showError" class="fs-field-image__error">
        <div class="fs-field-image__error-content">
            <span class="fs-field-image__error-icon dashicons dashicons-warning"></span>
            <span class="fs-field-image__error-message" x-text="errorMessage"></span>
            <button type="button" class="fs-field-image__error-close" @click="closeError()">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="fs-field-image__error-help">
            <?php printf(
                esc_html__('Current maximum file size: %s MB. Contact your server administrator to increase limits.', 'f-shop'),
                $max_upload_size_mb
            ); ?>
        </div>
    </div>

    <!-- Control buttons -->
    <div class="fs-field-image__controls">
        <!-- Upload file button -->
        <label class="fs-field-image__button fs-field-image__button--upload" :class="{'fs-field-image__button--disabled': isUploading}">
            <span class="dashicons dashicons-upload"></span>
            <span class="fs-field-image__button-text"><?php esc_html_e('Upload', 'f-shop'); ?></span>
            <input type="file"
                id="<?php echo esc_attr($name); ?>_file"
                @change="handleFile($event)"
                class="fs-field-image__input"
                accept="image/*"
                :disabled="isUploading"
                <?php echo !empty($args['attributes']) ? fs_parse_attr($args['attributes']) : ''; ?>>
        </label>
        
        <!-- Media library selection button -->
        <button type="button" 
            class="fs-field-image__button fs-field-image__button--library" 
            @click="openMediaLibrary(false)"
            :disabled="isUploading">
            <span class="dashicons dashicons-admin-media"></span>
            <span class="fs-field-image__button-text"><?php esc_html_e('Media Library', 'f-shop'); ?></span>
        </button>
        
        <!-- Remove button -->
        <button type="button" 
            class="fs-field-image__button fs-field-image__button--remove" 
            @click="removeImage()"
            x-show="!showPlaceholder && !isUploading">
            <span class="dashicons dashicons-trash"></span>
            <span class="fs-field-image__button-text"><?php esc_html_e('Remove', 'f-shop'); ?></span>
        </button>
    </div>

    <!-- Maximum file size information -->
    <div class="fs-field-image__info">
        <span class="dashicons dashicons-info"></span>
        <?php printf(
            esc_html__('Maximum file size: %s MB', 'f-shop'),
            $max_upload_size_mb
        ); ?>
    </div>

    <!-- Hidden field for image ID -->
    <input type="hidden"
        id="<?php echo esc_attr($name); ?>_id"
        name="<?php echo esc_attr($name); ?>"
        value="<?php echo esc_attr($args['value']); ?>">
</div>