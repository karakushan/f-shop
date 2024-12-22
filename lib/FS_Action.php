<?php

namespace FS;
/**
 *  Класс для регистрации событий плагина
 */
class FS_Action {

	function __construct() {
		add_action( 'init', array( &$this, 'fs_catch_action' ), 2 );
		add_action( 'init', array( &$this, 'register_plugin_action' ), 10 );
		add_action( 'init', array( &$this, 'register_plugin_filters' ), 10 );
		add_action( 'admin_menu', array( $this, 'remove_admin_submenus' ), 999 );
		add_action( 'fs_live_search', array( $this, 'live_search' ) );
		add_action( 'fs_before_range_slider', array( $this, 'fs_before_range_slider_callback' ) );
		add_action( 'fs_after_range_slider', array( $this, 'fs_after_range_slider_callback' ) );
	}

	public
	function fs_catch_action() {
		if ( isset( $_REQUEST['fs_action'] ) ) {
			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'fs_action' ) ) {
				exit( 'неправильный код проверки' );
			}
			$action = $_REQUEST['fs_action'];
			switch ( $action ) {
				case "delete-cart":
					unset( $_SESSION['cart'] );
					wp_redirect( remove_query_arg( array( 'fs_action', '_wpnonce' ) ) );
					exit();
					break;
				case "export_yml":
					FS_Export_Class::products_to_yml( true );
					break;

				default:
					exit;
					break;
			}
		}


	}


	/**
	 * метод регистрирует хуки-события плагина
	 */
	function register_plugin_action() {
		$fs_product = new FS_Product();

		//===== GENERAL =====
		/* Сохранение настроек плагина */
		add_action( 'fs_save_options', array( $this, 'action_save_options' ), 10 );

		//===== SINGLE PRODUCT =====
		/* Hooks in this section only work on the product page. */

		/* отображение скидки в процентах */
		add_action( 'fs_discount_percent', 'fs_discount_percent', 10, 3 );
		/* отображение артикула товара */
		add_action( 'fs_product_code', 'fs_product_code', 10, 2 );
		/* отображение кнопки добавления в корзину */
		add_action( 'fs_add_to_cart', 'fs_add_to_cart', 10, 3 );
		/* рейтинг товара */
		add_action( 'fs_product_rating', array( $fs_product, 'product_rating' ), 10, 2 );
		/* Табы в товаре */
		add_action( 'fs_product_tabs', array( 'FS\FS_Product', 'product_tabs' ), 10, 2 );
		/* Выводит список всех установленных атрибутов товара в виде списка ul. Данные выводятся в  в виде: группа : свойство (свойства) */
		add_action( 'fs_the_atts_list', 'fs_the_atts_list', 10, 2 );
		/* отображение фактической цены */
		add_action( 'fs_the_price', 'fs_the_price', 10, 3 );
		/* отображение базовой цены без учёта скидки */
		add_action( 'fs_base_price', 'fs_base_price', 10, 3 );
		/* выводит надпись , метку товара */
		add_action( 'fs_product_label', 'fs_product_label', 10, 1 );
		/* выводит кнопку голосования за комментарии */
		add_action( 'fs_product_comment_likes', [ 'FS\FS_Product', 'product_comment_likes' ], 10, 1 );
		/* выводит форму комментариев, отзывов о товаре */
		add_action( 'fs_product_comments', [ 'FS\FS_Product', 'product_comments_form' ], 10, 1 );

		//===== PRODUCT CATEGORY  =====
		/* выводит select для сортировки по параметрам (обычно применяется в каталоге или на страницах категорий) */
		add_action( 'fs_types_sort_filter', array( 'FS\FS_Filters', 'fs_types_sort_filter' ), 10, 1 );
		/* выводит select для указания к-ва выводимых постов на странице (обычно применяется в каталоге или на страницах категорий) */
		add_action( 'fs_per_page_filter', array( 'FS\FS_Filters', 'per_page_filter' ), 10, 1 );
		/*выводит список группы свойств и сортирует при выборе свойства*/
		add_action( 'fs_attr_filter', 'fs_attr_filter', 10, 2 );
		/*выводит фильтр для сортировки по диапазону цены (слайдер цены)*/
		add_action( 'fs_range_slider', 'fs_range_slider', 10 );

		//===== WISHLIST =====
		/* отображает кнопку добавления в список желаний */
		add_action( 'fs_wishlist_button', 'fs_add_to_wishlist', 10, 3 );
		/* отображает виджет (блок) со списком желаний */
		add_action( 'fs_wishlist_widget', 'fs_wishlist_widget', 10, 1 );
		/* удаляет товар из списка желаний */
		add_action( 'fs_delete_wish_list_item', array( $this, 'delete_wish_list_item' ), 10, 1 );

		//===== CART =====
		/* выводит корзину в определёном месте */
		add_action( 'fs_cart_widget', 'fs_cart_widget', 10, 1 );
		/* Выводит поле для изменения к-ва товаров в корзине */
		add_action( 'fs_cart_quantity', 'fs_cart_quantity', 10, 3 );
		/* Выводит поле для изменения к-ва товаров добавляемых в корзину */
		add_action( 'fs_quantity_product', 'fs_quantity_product', 10, 3 );
		/* Выводит кнопку для удаления всех товаров в корзине */
		add_action( 'fs_delete_cart', 'fs_delete_cart', 10, 1 );
		/* Выводит кнопку для удаления определёного товара из корзины */
		add_action( 'fs_delete_position', 'fs_delete_position', 10, 2 );
		/* Выводит общую сумму всех товаров в корзине */
		add_action( 'fs_total_amount', 'fs_total_amount', 10, 2 );

		//===== CHECKOUT =====
		/* Выводит форму заполнения личных данных при отправке заказа */
		add_action( 'fs_order_form', 'fs_order_send_form', 10, 1 );
		/* Показывает поля адреса. Срабатывает при условии что способ доставки требует поля адреса */
		add_action( 'fs_shipping_fields', array( 'FS\FS_Cart', 'show_shipping_fields' ) );

		//===== USERS =====
		/* Выводит форму авторизации на сайте */
		add_action( 'fs_login_form', array( 'FS\FS_Users', 'login_form' ), 10, 1 );
		/* Выводит всю информацию о текущем пользователе в виде списка */
		add_action( 'fs_user_info', array( 'FS\FS_Users', 'user_info_show' ), 10 );
		/* Выводит форму редактирования профиля */
		add_action( 'fs_profile_edit', array( 'FS\FS_Users', 'profile_edit' ), 10, 1 );
		/* Выводит иконку или аватар пользователя с линком на форму редактирования профиля*/
		add_action( 'fs_profile_widget', array( 'FS\FS_Users', 'profile_widget' ), 10, 1 );


		// Add the field to user profile editing screen.
		add_action(
			'edit_user_profile',
			'wporg_usermeta_form_field_birthday'
		);

		//===== COMPARISON LIST ====
		add_action( 'fs_add_to_comparison', 'fs_add_to_comparison', 10, 3 );

		// ==== API ====
		/* удаляет все термины из таксономий плагина */
		add_action( 'fs_delete_taxonomy_terms', array( 'FS\FS_Taxonomy', 'delete_taxonomy_terms' ), 10, 1 );
		/* удаляет все заказы */
		add_action( 'fs_delete_orders', array( 'FS\FS_Orders', 'delete_orders' ) );
		/* удаляет все товары */
		add_action( 'fs_delete_products', array( 'FS\FS_Product', 'delete_products' ) );

		// Templates
		add_action( 'fs_select_product', function () {
			if ( file_exists( FS_PLUGIN_PATH . 'templates/back-end/metabox/select-product.php' ) ) {
				include FS_PLUGIN_PATH . 'templates/back-end/metabox/select-product.php';
			}
		} );
	}


	/**
	 * Function registers plug-in hook filters
	 */
	function register_plugin_filters() {
		/* Фильтр возвращает начальный тег формы и скрытые поля необходимые для безопасности */
		add_filter( 'fs_form_header', 'fs_form_header', 10, 2 );
	}

	/**
	 * Удаляет ненужные подпункты навигации в админке
	 */
	function remove_admin_submenus() {
		// удаляем подпункт "добавить заказ"
		remove_submenu_page( 'edit.php?post_type=orders', 'post-new.php?post_type=orders' );
	}


	/**
	 * Удаляет одну позициию из списка желаний
	 *
	 * @param int $product_id
	 *
	 * @return bool
	 */
	function delete_wish_list_item( $product_id = 0 ) {
		if ( ! empty( $_REQUEST['product_id'] ) ) {
			$product_id = intval( $_REQUEST['product_id'] );

			unset( $_SESSION['fs_wishlist'][ $product_id ] );
			wp_safe_redirect( remove_query_arg( [ 'fs-api', 'product_id' ] ) );

			exit;
		} else {
			if ( isset( $_SESSION['fs_wishlist'][ $product_id ] ) ) {
				unset( $_SESSION['fs_wishlist'][ $product_id ] );
			}

			return true;
		}

	}

	function action_save_options( $data ) {
		if ( ! isset( $_GET['page'] ) ) {
			return;
		}

		if ( $_GET['page'] != 'f-shop-settings' ) {
			return;
		}


		flush_rewrite_rules();
	}

	/**
	 * Показывает форму живого поиска по товарам
	 *
	 * @return void
	 */
	public function live_search() {
		echo fs_frontend_template( 'search/livesearch' );
	}

	/**
	 * Initializes and renders the range slider for filtering with price input synchronization.
	 *
	 * @param array $args {
	 *     Array of arguments to configure the range slider.
	 *
	 * @type array $data Additional data for the range slider.
	 * @type string $wrapper_class CSS class applied to the wrapper element of the slider. Default 'noUiSlider-wrapper'.
	 * }
	 *
	 * @return void The function outputs HTML and JavaScript for the range slider.
	 */
	public function fs_before_range_slider_callback( $args ) {
		$args    = wp_parse_args( $args, [
			'data'          => [],
			'wrapper_class' => 'noUiSlider-wrapper'
		] );
		$term_id = get_queried_object_id();
		?>
        <script>
            const initSlider = async () => {
                const slider = document.getElementById('fsRangeSlider');
                const maxMinPrices = await Alpine.store('FS').getMaxMinPrice(<?php echo esc_attr( $term_id ) ?>).then(r => r.data);
                const currentUrl = new URL(window.location.href);
                const fsPriceStartInput = document.getElementById('fsPriceStartInput');
                const fsPriceEndInput = document.getElementById('fsPriceEndInput');
                let inputTimeout;
                let isBlocked = false;

                // set initial values from url or default
                fsPriceStartInput.value = currentUrl.searchParams.get('price_start') || maxMinPrices.min;
                fsPriceEndInput.value = currentUrl.searchParams.get('price_end') || maxMinPrices.max;

                const applyFilters = () => {
                    if (isBlocked) return;

                    clearTimeout(inputTimeout);
                    inputTimeout = setTimeout(() => {
                        currentUrl.searchParams.set('price_start', fsPriceStartInput.value);
                        currentUrl.searchParams.set('price_end', fsPriceEndInput.value);
                        window.location.href = currentUrl.toString();
                    }, 1000);

                }


                fsPriceStartInput.addEventListener('input', applyFilters);
                fsPriceEndInput.addEventListener('input', applyFilters);

                const fsRangeSlider = noUiSlider.create(slider, {
                    start: [fsPriceStartInput.value, fsPriceEndInput.value],
                    connect: true,
                    range: {
                        'min': maxMinPrices.min,
                        'max': maxMinPrices.max
                    }
                });
                fsRangeSlider.on('update', function (values, handle) {
                    isBlocked = true;
                    fsPriceStartInput.value = values[0];
                    fsPriceEndInput.value = values[1];
                    isBlocked = false;
                });
                fsRangeSlider.on('change', function (values, handle) {
                    applyFilters();
                });
            }

            document.addEventListener('DOMContentLoaded', initSlider);
        </script>
        <div class="<?php echo esc_attr( $args['wrapper_class'] ) ?>">
		<?php
	}

	/**
	 * Callback after range slider
	 */
	public function fs_after_range_slider_callback() {
		echo '</div>';
	}
}