<?php


namespace FS;
include_once ABSPATH . 'wp-includes/class-wp-customize-control.php';


class FS_Theme_Customizer {
	public $options = [];

	public function __construct( $options = array() ) {
		$this->setOptions( $options );
		add_action( 'customize_register', array( $this, 'fs_customize_register' ) );
	}


	/**
	 * @param array $options
	 */
	public function setOptions( array $options ): void {
		$this->options = apply_filters( 'fs_theme_customizer_options', $options );
	}

	/**
	 * @return array
	 */
	public function getOptions(): array {
		return $this->options;
	}

	function fs_customize_register( $wp_customize ) {

		$settings = $this->getOptions();
		if ( $settings ) {
			foreach ( $settings as $key => $setting ) {
				$wp_customize->add_section( $key, array( 'title' => $setting['title'] ) );
				if ( !empty($setting['fields'] )) {
					foreach ( $setting['fields'] as $sek => $setting ) {
						$wp_customize->add_setting( $sek, array(
							'default'     => $setting['default'],
							'type'        => 'theme_mod',
							'description' => $setting['description'],
							'capability'  => 'edit_theme_options'
						) );
						switch ( $setting['type'] ) {
							case 'text':
								$wp_customize->add_control( $sek, array(
									'label'       => $setting['name'],
									'description' => $setting['description'],
									'section'     => $key,
									'type'        => 'text'
								) );
								break;
							case 'textarea':
								$wp_customize->add_control( new FS_Customize_Textarea_Control( $wp_customize, $sek, array(
									'label'       => $setting['name'],
									'description' => $setting['description'],
									'section'     => $key,
									'settings'    => $sek,
								) ) );
								break;
							case 'checkbox':
								$wp_customize->add_control( new FS_Checkbox_Customize_Control( $wp_customize, $sek, array(
									'label'       => $setting['name'],
									'description' => $setting['description'],
									'section'     => $key,
									'settings'    => $sek,
								) ) );
								break;
							case 'image':
								$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $sek, array(
									'label'       => $setting['name'],
									'description' => $setting['description'],
									'section'     => $key,
									'settings'    => $sek
								) ) );
								break;
							case 'media':
								$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, $sek,
									array(
										'settings'      => $sek,
										'label'         => __( 'Default Media Control' ),
										'description'   => $setting['description'],
										'section'       => $key,
										'mime_type'     => 'image',
										// Required. Can be image, audio, video, application, text
										'button_labels' => array( // Optional
											'select'       => __( 'Select File' ),
											'change'       => __( 'Change File' ),
											'default'      => __( 'Default' ),
											'remove'       => __( 'Remove' ),
											'placeholder'  => __( 'No file selected' ),
											'frame_title'  => __( 'Select File' ),
											'frame_button' => __( 'Choose File' ),
										)
									)
								) );
								break;
							case 'date':
								$wp_customize->add_control( new WP_Customize_Date_Time_Control( $wp_customize, $sek,
									array(
										'label'              => $setting['name'],
										'description'        => $setting['description'],
										'section'            => $key,
										'settings'           => $sek,
										'include_time'       => false, // Optional. Default: true
										'allow_past_date'    => false, // Optional. Default: true
										'twelve_hour_format' => false, // Optional. Default: true
									)
								) );
								break;
							case 'datetime':
								$wp_customize->add_control( new FS_Date_Time_Customize_Control( $wp_customize, $sek,
									array(
										'label'              => $setting['name'],
										'description'        => $setting['description'],
										'section'            => $key,
										'settings'           => $sek,
										'include_time'       => true, // Optional. Default: true
										'allow_past_date'    => false, // Optional. Default: true
										'twelve_hour_format' => false, // Optional. Default: true
									)
								) );
								break;
							default:
								$wp_customize->add_control( $sek, array(
									'label'       => $setting['name'],
									'description' => $setting['description'],
									'section'     => $key,
									'type'        => 'text'
								) );
								break;
						}
					}
				}
			}
		}
	}

}

class FS_Date_Time_Customize_Control extends \WP_Customize_Control {
	public function render_content() {
		?>
        <label>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <input type="datetime-local" <?php $this->link(); ?>
                   value="<?php echo esc_html( $this->value() ) ?>">
            <span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
        </label>
		<?php
	}
}

class FS_Checkbox_Customize_Control extends \WP_Customize_Control {
	public function render_content() {
		?>
        <label>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <input type="checkbox" <?php $this->link(); ?> value="1">
            <span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
        </label>
		<?php
	}

}

class FS_Customize_Textarea_Control extends \WP_Customize_Control {
	public $type = 'textarea';

	public function render_content() {
		?>
        <label>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <textarea rows="5"
                      style="width:100%;" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
            <span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
        </label>
		<?php
	}
}

