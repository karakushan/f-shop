<?php

/**
 * Image field template.
 *
 * @var array $args
 * @var string $name
 */

?>

<div x-data="{
    imageUrl: '<?php echo !empty($args['value']) ? esc_url(wp_get_attachment_image_url($args['value'])) : ''; ?>',
    handleFile(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        if (file.type.match('image.*')) {
            this.imageUrl = URL.createObjectURL(file);
        }
    }
}" class="fs-field-image">
        <!-- Превью изображения -->
        <div class="fs-field-image__preview">
                <template x-if="imageUrl">
                        <img :src="imageUrl"
                                class="fs-field-image__preview-img"
                                alt="<?php esc_attr_e('Превью', 'f-shop'); ?>">
                </template>
                <template x-if="!imageUrl">
                        <img src="<?php echo esc_url(plugin_dir_url(FS_PLUGIN_FILE) . 'assets/img/add-img.svg'); ?>"
                                class="fs-field-image__preview-img"
                                alt="<?php esc_attr_e('Заглушка', 'f-shop'); ?>">
                </template>
        </div>

        <!-- Поле загрузки -->
        <label class="fs-field-image__button">
                <span><?php esc_html_e('Виберіть фотографію', 'f-shop'); ?></span>
                <input type="file"
                        @change="handleFile($event)"
                        class="fs-field-image__input"
                        name="<?php echo esc_attr($name); ?>"
                        accept="image/*"
                        <?php echo !empty($args['attributes']) ? fs_parse_attr($args['attributes']) : ''; ?>>
        </label>

        <!-- Скрытое поле для текущего значения -->
        <input type="hidden"
                name="<?php echo esc_attr($name); ?>_current"
                value="<?php echo esc_attr($args['value']); ?>">
</div>

<script>
        document.addEventListener('alpine:init', () => {
                Alpine.data('imagePreview', () => ({
                        imageUrl: '<?php echo !empty($args['value']) ? esc_url(wp_get_attachment_image_url($args['value'])) : ''; ?>',

                        handleFile(event) {
                                const file = event.target.files[0];
                                console.log('File:', file);
                                if (!file) return;

                                if (file.type.match('image.*')) {
                                        this.imageUrl = URL.createObjectURL(file);
                                        console.log('New image URL:', this.imageUrl);
                                }
                        }
                }));
        });
</script>