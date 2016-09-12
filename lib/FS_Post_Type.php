<?php
namespace FS;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class FS_Post_Type
{
    const POST_TYPE	= "product";
    protected $config;
    public $custom_tab_title;
    public $custom_tab_body;
    public $tabs;
    public $product_id;

    /**
     * The Constructor
     */
    public function __construct()
    {

        // register actions
        add_action('init', array($this, 'init'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action( 'save_post', array($this,'save_fs_fields' ));
        $this->product_id=isset($_GET['post'])?(int)$_GET['post']:0;

        $this->config=new FS_Config();
        $this->tabs=array(
            '0'=>
                array(
                    'title'=>__('Prices','fast-shop'),
                    'on'=>true,
                    'body'=>'',
                    'template'=>''
                ),
            '1'=>
                array(
                    'title'=>__('Attributes','fast-shop'),
                    'on'=>true,
                    'body'=>'',
                    'template'=>''
                ),
            '2'=>
                array(
                    'title'=>__('Gallery','fast-shop'),
                    'on'=>true,
                    'body'=>'',
                    'template'=>''
                ),
            '3'=>
                array(
                    'title'=>__('Discounts','fast-shop'),
                    'on'=>true,
                    'body'=>'',
                    'template'=>''
                ),
        );
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
     * @param $post_id
     */
    public function save_fs_fields($post_id)
    {
        if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)  return;

        if(isset($_POST['post_type']) && $_POST['post_type'] == self::POST_TYPE && current_user_can('edit_post', $post_id))
        {
            foreach(@$this->config->meta as $field_name)
            {
                // Update the post's meta field
                switch ($field_name) {
                    case 'fs_price':
                        $price=(float)str_replace(array(','),array('.'), $_POST[$field_name]);
                        update_post_meta($post_id, $field_name,$price);
                        break;

                    default:
                        if (!empty($_POST[$field_name])){
                            update_post_meta($post_id, $field_name, $_POST[$field_name]);
                        }else{
                            delete_post_meta($post_id, $field_name);
                        }
                        break;
                }
            }
        }
        // if($_POST['post_type'] == self::POST_TYPE && current_user_can('edit_post', $post_id))
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
     * @param $post
     */
    public function add_inner_meta_boxes($post)
    {
        $this->product_id=$post->ID;
        echo '<div id="fs-tabs" class="fs-metabox">';
        if ($this->tabs){
            echo '<ul>';
            foreach ($this->tabs as $key=>$tab) {
                echo '<li><a href="#tab-'.$key.'">'.$tab['title'].'</a></li>';

            }
            echo '</ul>';
            foreach ($this->tabs as $key_body=>$tab_body) {

                $template_default = PLUGIN_PATH.'templates/back-end/metabox\tab-'.$key_body.'.php';
                $template_file=empty($tab_body['template']) ? $template_default : $tab_body['template'];

                echo '<div id="tab-'.$key_body.'">';
                if (empty($tab_body['body'])){
                    if (file_exists($template_file)){
                        include($template_file);
                    }
                }else{
                    echo $tab_body['body'];
                }
                echo '</div>';

            }

        }
        echo '</div>';
    }



} // END class Post_Type_Template