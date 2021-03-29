<?php


namespace FS;


class FS_Customers_List extends \WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'Customer', 'f-shop' ), //singular name of the listed records
			'plural'   => __( 'Customers', 'f-shop' ), //plural name of the listed records
			'ajax'     => true //should this table support ajax?

		] );
	}

	/**
	 * Retrieve customer’s data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_customers( $per_page = 30, $page_number = 1 ) {

		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}fs_customers";

		if ( ! empty( $_REQUEST['s'] ) && ! empty( $_REQUEST['field'] ) && in_array( $_REQUEST['field'],
				[ 'email', 'phone', 'first_name', 'last_name', 'city', 'id' ]
			) ) {
			$field = $_REQUEST['field'];
			$s     = esc_sql( $_REQUEST['s'] );
			if ( $field == 'phone' ) {
				$s = preg_replace( "/[^0-9]/", '', $s );
			}
			if ( $field == 'id' ) {
				$sql .= " WHERE $field = " . absint( $s );
			} else {
				$sql .= " WHERE $field LIKE '%$s%'";
			}

		}

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		} else {
			$sql .= ' ORDER BY id DESC';
		}

		$sql .= " LIMIT %d  OFFSET %d";

		return $wpdb->get_results( $wpdb->prepare( $sql, $per_page, ( $page_number - 1 ) * $per_page ), 'ARRAY_A' );
	}

	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_customer( $id ) {
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}fs_customers",
			[ 'id' => $id ],
			[ '%d' ]
		);
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}fs_customers";

		return $wpdb->get_var( $sql );
	}

	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No customers avaliable.', 'f-shop' );
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {

		// create a nonce
		$delete_nonce = wp_create_nonce( 'sp_delete_customer' );

		$title = '<strong>' . $item['first_name'] . '</strong>';

		$actions = [
			'delete' => sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce )
		];

		return $title . $this->row_actions( $actions );
	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'first_name':
				$customer_orders_url = add_query_arg( [
					'post_type'   => 'orders',
					's'   => '',
					'customer_id' => $item['id']
				], admin_url( 'edit.php' ) );

				return $item[ $column_name ] . '<br><a href="' . esc_url( $customer_orders_url ) . '">' . __( 'Go to orders','f-shop' ) . '</a>';
			case 'subscribe_news':
				return $item[ $column_name ] == 1 ? __( 'Yes', 'f-shop' ) : __( 'No', 'f-shop' );
			default:
				return $item[ $column_name ]; //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%d" />', $item['id']
		);
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			'cb'             => '<input type="checkbox" />',
			'id'             => __( 'ID', 'f-shop' ),
			'first_name'     => __( 'First name', 'f-shop' ),
			'last_name'      => __( 'Last name', 'f-shop' ),
			'phone'          => __( 'Phone', 'f-shop' ),
			'email'          => __( 'E-mail', 'f-shop' ),
			'group'          => __( 'Group', 'f-shop' ),
			'city'           => __( 'City', 'f-shop' ),
			'address'        => __( 'Address', 'f-shop' ),
			'subscribe_news' => __( 'Subscribe', 'f-shop' ),
			'creation_date'  => __( 'Registration date', 'f-shop' ),
		];

		return $columns;
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return [
			'phone'          => array( 'phone', true ),
			'id'             => array( 'id', true ),
			'group'          => array( 'group', true ),
			'city'           => array( 'city', true ),
			'subscribe_news' => array( 'subscribe_news', true ),
			'last_name'      => array( 'last_name', true ),
			'first_name'     => array( 'first_name', true ),
			'creation_date'  => array( 'creation_date', true )
		];
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete'
		];

		return $actions;
	}

	/**
	 * Displays the search box.
	 *
	 * @param string $text The 'submit' button label.
	 * @param string $input_id ID attribute value for the search input field.
	 *
	 * @since 3.1.0
	 *
	 */
	public function search_box( $text, $input_id ) {
//		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
//			return;
//		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['detached'] ) ) {
			echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
		}
		?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
            <select name="field">
                <option value="id" <?php if ( isset( $_REQUEST['field'] ) )
					selected( 'id', $_REQUEST['field'] ) ?>><?php esc_html_e( 'ID', 'f-shop' ); ?></option>
                <option value="phone" <?php if ( isset( $_REQUEST['field'] ) )
					selected( 'phone', $_REQUEST['field'] ) ?>><?php esc_html_e( 'Phone number', 'f-shop' ); ?></option>
                <option value="email" <?php if ( isset( $_REQUEST['field'] ) )
					selected( 'email', $_REQUEST['field'] ) ?>><?php esc_html_e( 'E-mail', 'f-shop' ); ?></option>
                <option value="city" <?php if ( isset( $_REQUEST['field'] ) )
					selected( 'city', $_REQUEST['field'] ) ?>><?php esc_html_e( 'City', 'f-shop' ); ?></option>
                <option value="first_name" <?php if ( isset( $_REQUEST['field'] ) )
					selected( 'first_name', $_REQUEST['field'] ) ?>><?php esc_html_e( 'First Name', 'f-shop' ); ?></option>
                <option value="last_name" <?php if ( isset( $_REQUEST['field'] ) )
					selected( 'last_name', $_REQUEST['field'] ) ?>><?php esc_html_e( 'Last Name', 'f-shop' ); ?></option>
            </select>
            <input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s"
                   value="<?php _admin_search_query(); ?>"
                   placeholder="<?php esc_attr_e( 'Contains', 'f-shop' ); ?>"/>
			<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
        </p>
        <input type="hidden" name="post_type" value="orders">
		<?php
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = parent::get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'customers_per_page', 30 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = $this->get_customers( $per_page, $current_page );
	}

	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'sp_delete_customer' ) ) {
				die( 'Go get a life script kiddies' );
			} else {
				self::delete_customer( absint( $_REQUEST['customer'] ) );

				wp_redirect( esc_url( add_query_arg() ) );
				exit;
			}

		}

		// If the delete bulk action is triggered
		if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'bulk-delete' )
		     || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'bulk-delete' )
		) {

			$delete_ids = esc_sql( $_REQUEST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_customer( $id );

			}
		}
	}
}