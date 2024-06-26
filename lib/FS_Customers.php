<?php

namespace FS;
class FS_Customers {
	/**
	 * @var FS_Customers_List
	 */
	private $customers;

	public function __construct() {
		// Инициализирует подпункт меню "клиенты" в меню "заказы"
		add_action( 'admin_menu', array( $this, 'customers_list_menu_item' ) );
	}

	/**
	 * Создаем страницу "Клиенты"
	 */
	public function customers_list_menu_item() {
		// Регистрация страницы API
		$hook = add_submenu_page(
			'edit.php?post_type=orders',
			__( 'Customers', 'f-shop' ),
			__( 'Customers', 'f-shop' ),
			'manage_options',
			'fs-customers',
			array( $this, 'customers_settings_page' )
		);

		add_action( "load-$hook", [ $this, 'screen_option' ] );
	}

	/**
	 * Screen options
	 */
	public function screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => __( 'Customers', 'f-shop' ),
			'default' => 30,
			'option'  => 'customers_per_page'
		];

		add_screen_option( $option, $args );

		$this->customers = new FS_Customers_List();
	}

	/**
	 * Страница отображения списка клиентов
	 */
	public function customers_settings_page() {
		?>
        <div class="wrap">
            <h2><?php esc_html_e( 'Customers', 'f-shop' ); ?></h2>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-1">
                    <div id="post-body-content">

                        <div class="meta-box-sortables ui-sortable">
                            <form method="get" action="">
                                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
								<?php
								$this->customers->search_box( __( 'Find', 'f-shop' ), 'search_client' );
								$this->customers->prepare_items();
								$this->customers->display();
								?>
                            </form>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
		<?php
	}
}