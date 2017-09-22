<div class="wrap fast-shop-settings">
  <h2><?php _e( 'Store settings', 'fast-shop' ) ?></h2>
  <form
    action="<?php echo wp_nonce_url( '/wp-admin/edit.php?post_type=product&page=fast-shop-settings', 'fs_nonce' ); ?>"
    method="post" class="fs-option">
    <div id="fs-options-tabs">
		<?php if ( ! empty( $plugin_settings ) ): ?>
          <ul>
			  <?php foreach ( $plugin_settings as $key => $setting ): ?>
                <li><a href="#<?php echo $key ?>"><?php echo $setting['name'] ?></a></li>
			  <?php endforeach; ?>
          </ul>
		<?php endif; ?>



		<?php if ( ! empty( $plugin_settings ) ): ?>

			<?php foreach ( $plugin_settings as $key => $setting ): ?>
            <div id="<?php echo $key ?>">
				<?php if ( ! empty( $setting['fields'] ) ): ?>
					<?php foreach ( $setting['fields'] as $field ): ?>
						<?php $label_id = str_replace( array(
							'[',
							']'
						), array( '_' ), sprintf( 'fs_option[%s]', $field['name'] ) ); ?>
                    <p>
						<?php if ( $field['label'] ): ?>
                          <label for="<?php echo $label_id ?>"><?php echo $field['label'] ?></label><br>
						<?php endif; ?>
						<?php fs_form_field( sprintf( 'fs_option[%s]', $field['name'] ), array(
							'type' => $field['type'],
							'value' => $field['value']
						) ) ?>
                    </p>
					<?php endforeach; ?>
				<?php endif; ?>
            </div>
			<?php endforeach; ?>
		<?php endif; ?>
    </div>
    <input type="submit" name="fs_save_options" value="Сохранить">
  </form>
</div>