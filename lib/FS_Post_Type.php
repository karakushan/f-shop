<?php
namespace FS;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class FS_Post_Type
{
  const POST_TYPE	= "product";
  private $fs= array(
    'fs_price',
    'fs_discount',
    'fs_availability',
    'fs_action',
    'fs_displayed_price',
    'fs_attributes',
    'fs_galery'
    );

    	/**
    	 * The Constructor
    	 */
    	public function __construct()
    	{
    		// register actions
    		add_action('init', array($this, 'init'));
    		add_action('admin_init', array($this, 'admin_init'));
          add_action( 'save_post', array($this,'save_fs_fields' ));
          // add_action('save_post', array($this, 'fs_save_galery'));
    	} // END public function __construct()

    	/**
    	 * hook into WP's init action hook
    	 */
    	public function init()
    	{
    		// Initialize Post Type
    		$this->create_post_type();
    		
    	} // END public function init()

    	/**
    	 * Create the post type
    	 */
    	public function create_post_type()
    	{
    		register_post_type(self::POST_TYPE,
    			array(
    				'labels' => array(
    					'name' =>__( 'Products', 'fast-shop' ),
    					'singular_name' => __( 'product', 'fast-shop' ),
                        'add_new'=>__( 'Add product', 'fast-shop' ),
                        'add_new_item'       => '', 
                        'edit_item'          => __( 'Edit product', 'fast-shop' ), 
                        'new_item'           => '', 
                        'view_item'          => '', 
                        'search_items'       => '', 
                        'not_found'          => '', 
                        'not_found_in_trash' => '', 
                        'parent_item_colon'  => '', 
                        'menu_name'          =>__( 'Products', 'fast-shop' ), 
                        ),
                    'public' => true,
                    'show_in_menu' =>true,
                    'publicly_queryable' => true,
                    'show_ui' => true,
                    'capability_type' => 'post',
                    'menu_icon' =>'dashicons-products',
                    'map_meta_cap' => true,
                    'show_in_nav_menus' => true,
                    'menu_position' => 5,
                    'can_export' => true,
                    'has_archive' => true,
                    'rewrite'             => true,
                    'query_var'           => true,
                    'taxonomies' => array('catalog','manufacturer','countries'),
                    'description' => __("Здесь размещены товары вашего сайта."),

                    'supports' => array(
                     'title', 'editor', 'excerpt','thumbnail','comments'
                     ),
                    )
              );
    	}

    	/**
    	 * Save the metaboxes for this custom post type
    	 */
    	public function save_fs_fields($post_id)
    	{
            if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)  return;

            if(isset($_POST['post_type']) && $_POST['post_type'] == self::POST_TYPE && current_user_can('edit_post', $post_id))
            {

                foreach($this->fs as $field_name)
                {
    				// Update the post's meta field

                    switch ($field_name) {
                        case 'fs_price':
                        $price=(float)str_replace(array(','),array('.'), $_POST[$field_name]);
                        update_post_meta($post_id, $field_name,$price);
                        break;

                        default:
                        if (isset( $_POST[$field_name])){
                            update_post_meta($post_id, $field_name, $_POST[$field_name]);
                        }else{
                           delete_post_meta($post_id, $field_name); 
                        }
                        break;
                    }        
                }
            }
            else
            {
             return;
    		} // if($_POST['post_type'] == self::POST_TYPE && current_user_can('edit_post', $post_id))
    	} // END public function save_post($post_id)

    	/**
    	 * hook into WP's admin_init action hook
    	 */
    	public function admin_init()
    	{			
    		// Add metaboxes
    		add_action('add_meta_boxes', array(&$this, 'add_meta_boxes'));
    	} // END public function admin_init()

    	/**
    	 * hook into WP's add_meta_boxes action hook
    	 */
    	public function add_meta_boxes()
    	{
    	   // Add this metabox to every selected post
            add_meta_box( 
                sprintf('wp_plugin_template_%s_section', self::POST_TYPE),
                'Данные товара',
                array(&$this, 'add_inner_meta_boxes'),
                self::POST_TYPE
                );	

               // Add this metabox to every selected post

    	} // END public function add_meta_boxes()

		/**
		 * called off of the add meta box
		 */		
		public function add_inner_meta_boxes($post)
		{		
           global $post;
           $post_id=$post->ID;
           $mft=get_post_meta( $post_id, 'fs_galery', false);
           // print_r($mft);

			// Render the job order metabox
           include(sprintf("%s/../templates/back-end/products_metabox.php", dirname(__FILE__), self::POST_TYPE));			
		} // END public function add_inner_meta_boxes($post)

	} // END class Post_Type_Template