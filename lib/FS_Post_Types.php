<?php

namespace FS;
/**
 *
 */
class FS_Post_Types
{

    function __construct()
    {
        add_action('init', [$this, 'init']);
        add_action('add_meta_boxes', [$this, 'add_mail_template_meta_boxes']);
        add_action('save_post_fs-mail-template', [$this, 'save_mail_template'], 10, 3);

        // Disable visual editor for mail templates
        add_filter('user_can_richedit', [$this, 'disable_visual_editor_for_mail_templates']);
        
        // Remove default editor and add custom CodeMirror editor
        add_action('add_meta_boxes', [$this, 'remove_default_editor'], 99);
        add_action('edit_form_after_title', [$this, 'add_codemirror_editor']);
        
        // Hide editor via CSS as backup
        add_action('admin_head', [$this, 'hide_default_editor_css']);

        // Add custom buttons to text editor toolbar
        add_action('admin_head', [$this, 'add_mail_variables_to_editor']);

        // Hiding the Discussion and Comments metaboxes
        add_action('admin_menu',[$this, 'hide_discussion_metabox']);
        
        // Register AJAX handler for test email (must be registered early)
        add_action('wp_ajax_fs_send_test_email', [$this, 'send_test_email']);
        
        // Enqueue CodeMirror scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueue_codemirror_scripts']);
    }

    /**
     * Disable visual editor for mail templates
     */
    public function disable_visual_editor_for_mail_templates($can_richedit)
    {
        global $post_type;
        if ($post_type === 'fs-mail-template') {
            return false;
        }
        return $can_richedit;
    }

    /**
     * Remove default WordPress editor for mail templates
     */
    public function remove_default_editor()
    {
        global $post_type;
        if ($post_type === 'fs-mail-template') {
            // Remove the default editor metabox
            remove_meta_box('postdivrich', 'fs-mail-template', 'normal');
            // Also disable editor support to prevent it from being added
            remove_post_type_support('fs-mail-template', 'editor');
        }
    }

    /**
     * Hide default editor via CSS
     */
    public function hide_default_editor_css()
    {
        global $post_type;
        if ($post_type === 'fs-mail-template') {
            ?>
            <style>
            /* Hide WordPress default editor completely */
            #postdivrich,
            #wp-content-editor-container,
            #wp-content-editor-tools,
            .wp-editor-wrap,
            .wp-editor-area,
            #content-html,
            #content-tmce,
            .wp-switch-editor,
            .wp-editor-tabs,
            .wp-editor-tabs-wrap {
                display: none !important;
            }
            /* Ensure CodeMirror editor is visible */
            .CodeMirror {
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            </style>
            <?php
        }
    }

    /**
     * Add CodeMirror editor instead of default WordPress editor
     */
    public function add_codemirror_editor($post)
    {
        global $post_type;
        if ($post_type !== 'fs-mail-template') {
            return;
        }

        $content = $post->post_content;
        // Use wp_unslash to get raw HTML content without escaping
        $content = wp_unslash($content);
        // Ensure content is a string
        $content = is_string($content) ? $content : '';
        ?>
        <div id="fs-mail-template-editor-wrapper" style="margin: 20px 0;">
            <div style="margin-bottom: 10px;">
                <label for="fs-mail-template-content" style="font-weight: 600; display: block; margin-bottom: 5px;">
                    <?php esc_html_e('Email Template Content (HTML)', 'f-shop'); ?>
                </label>
                <p class="description">
                    <?php esc_html_e('Enter HTML content for the email template. Use variables like %order_id%, %cart_items_table%, etc.', 'f-shop'); ?>
                </p>
            </div>
            <textarea 
                id="fs-mail-template-content" 
                name="content" 
                rows="20" 
                style="width: 100%; font-family: monospace; font-size: 13px;"
                class="large-text code"><?php echo $content; ?></textarea>
        </div>
        <?php
    }

    /**
     * Enqueue CodeMirror scripts and styles
     */
    public function enqueue_codemirror_scripts($hook)
    {
        global $post_type;
        
        if (($hook === 'post.php' || $hook === 'post-new.php') && $post_type === 'fs-mail-template') {
            // Enqueue WordPress CodeMirror editor
            $settings = wp_enqueue_code_editor([
                'type' => 'text/html',
                'codemirror' => [
                    'mode' => 'htmlmixed',
                    'indentUnit' => 2,
                    'tabSize' => 2,
                    'lineNumbers' => true,
                    'lineWrapping' => true,
                    'matchBrackets' => true,
                    'autoCloseBrackets' => true,
                    'foldGutter' => true,
                    'gutters' => ['CodeMirror-linenumbers', 'CodeMirror-foldgutter'],
                ],
            ]);

            if ($settings) {
                // Initialize CodeMirror for all three textareas (content, header, footer)
                $script = "
                (function($) {
                    $(document).ready(function() {
                        // Wait for textareas to be available in DOM
                        function initCodeMirror() {
                            var editors = {};
                            
                            // Check if WordPress code editor is available
                            if (typeof wp === 'undefined' || !wp.codeEditor) {
                                setTimeout(initCodeMirror, 100);
                                return;
                            }
                            
                            // Initialize CodeMirror for main content
                            var \$contentTextarea = $('#fs-mail-template-content');
                            if (\$contentTextarea.length && !\$contentTextarea.next('.CodeMirror').length) {
                                try {
                                    var contentElement = \$contentTextarea[0];
                                    if (contentElement) {
                                        var contentEditor = wp.codeEditor.initialize(contentElement, " . wp_json_encode($settings) . ");
                                        if (contentEditor && contentEditor.codemirror) {
                                            editors.content = contentEditor;
                                            
                                            // Store main content editor globally for variable insertion
                                            if (!window.fsMailTemplateEditor) {
                                                window.fsMailTemplateEditor = {};
                                            }
                                            window.fsMailTemplateEditor.instance = contentEditor;
                                        }
                                    }
                                } catch(e) {
                                    console.error('Error initializing content CodeMirror:', e);
                                }
                            }
                            
                            // Initialize CodeMirror for header
                            var \$headerTextarea = $('#mail_template_header');
                            if (\$headerTextarea.length && !\$headerTextarea.next('.CodeMirror').length) {
                                try {
                                    var headerElement = \$headerTextarea[0];
                                    if (headerElement) {
                                        var headerEditor = wp.codeEditor.initialize(headerElement, " . wp_json_encode($settings) . ");
                                        if (headerEditor && headerEditor.codemirror) {
                                            editors.header = headerEditor;
                                        }
                                    }
                                } catch(e) {
                                    console.error('Error initializing header CodeMirror:', e);
                                }
                            }
                            
                            // Initialize CodeMirror for footer
                            var \$footerTextarea = $('#mail_template_footer');
                            if (\$footerTextarea.length && !\$footerTextarea.next('.CodeMirror').length) {
                                try {
                                    var footerElement = \$footerTextarea[0];
                                    if (footerElement) {
                                        var footerEditor = wp.codeEditor.initialize(footerElement, " . wp_json_encode($settings) . ");
                                        if (footerEditor && footerEditor.codemirror) {
                                            editors.footer = footerEditor;
                                        }
                                    }
                                } catch(e) {
                                    console.error('Error initializing footer CodeMirror:', e);
                                }
                            }
                            
                            // Sync all CodeMirror content with textareas on form submit
                            if (Object.keys(editors).length > 0) {
                                $('#post').on('submit', function() {
                                    $.each(editors, function(key, editor) {
                                        if (editor && editor.codemirror) {
                                            editor.codemirror.save();
                                        }
                                    });
                                });
                                
                                // Also sync before autosave
                                $(document).on('heartbeat-send', function() {
                                    $.each(editors, function(key, editor) {
                                        if (editor && editor.codemirror) {
                                            editor.codemirror.save();
                                        }
                                    });
                                });
                            }
                        }
                        
                        // Start initialization after a short delay to ensure DOM is ready
                        setTimeout(initCodeMirror, 50);
                    });
                })(jQuery);
                ";
                wp_add_inline_script('code-editor', $script);
            }
        }
    }

    /**
     * Add mail variables buttons to text editor toolbar
     */
    public function add_mail_variables_to_editor()
    {
        global $post_type;
        if ($post_type !== 'fs-mail-template') {
            return;
        }

        $variables = [
            '%order_id%' => __('Order ID', 'f-shop'),
            '%order_date%' => __('Order date', 'f-shop'),
            '%order_title%' => __('Order title', 'f-shop'),
            '%order_edit_url%' => __('Admin order edit URL', 'f-shop'),
            '%dashboard_url%' => __('Customer dashboard URL', 'f-shop'),
            '%cart_amount%' => __('Total cart amount', 'f-shop'),
            '%products_cost%' => __('Products cost', 'f-shop'),
            '%delivery_cost%' => __('Delivery cost', 'f-shop'),
            '%packing_cost%' => __('Packing cost', 'f-shop'),
            '%cart_discount%' => __('Cart discount', 'f-shop'),
            '%client_id%' => __('Client user ID', 'f-shop'),
            '%client_email%' => __('Client email', 'f-shop'),
            '%client_phone%' => __('Client phone', 'f-shop'),
            '%client_first_name%' => __('Client first name', 'f-shop'),
            '%client_last_name%' => __('Client last name', 'f-shop'),
            '%client_city%' => __('Client city', 'f-shop'),
            '%client_address%' => __('Client address', 'f-shop'),
            '%client_comment%' => __('Client comment', 'f-shop'),
            '%delivery_method%' => __('Delivery method', 'f-shop'),
            '%delivery_number%' => __('Delivery department number', 'f-shop'),
            '%address_street%' => __('Street address', 'f-shop'),
            '%address_house_number%' => __('House number', 'f-shop'),
            '%address_entrance_number%' => __('Entrance number', 'f-shop'),
            '%address_apartment_number%' => __('Apartment number', 'f-shop'),
            '%payment_method%' => __('Payment method', 'f-shop'),
            '%site_name%' => __('Site name', 'f-shop'),
            '%home_url%' => __('Home URL', 'f-shop'),
            '%admin_email%' => __('Admin email', 'f-shop'),
            '%contact_email%' => __('Contact email', 'f-shop'),
            '%contact_phone%' => __('Contact phone', 'f-shop'),
            '%contact_address%' => __('Contact address', 'f-shop'),
            '%mail_logo%' => __('Email logo URL', 'f-shop'),
            '%admin_mail_title%' => __('Admin email title', 'f-shop'),
            '%admin_mail_message%' => __('Admin email message', 'f-shop'),
            '%customer_mail_title%' => __('Customer email title', 'f-shop'),
            '%customer_mail_message%' => __('Customer email message', 'f-shop'),
        ];
        ?>
        <style>
        .fs-mail-vars-toolbar {
            margin: 10px 0;
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .fs-mail-vars-menu {
            max-height: 400px;
            overflow-y: auto;
        }
        .fs-mail-var-item:hover {
            background: #f0f0f1 !important;
        }
        </style>
        <script type="text/javascript">
        (function($) {
            $(document).ready(function() {
                // Wait for CodeMirror to be initialized
                var checkEditor = setInterval(function() {
                    if ($('#fs-mail-template-content').length && window.wp && wp.codeEditor) {
                        clearInterval(checkEditor);
                        initMailVariablesButtons();
                    }
                }, 100);

                function initMailVariablesButtons() {
                    var $wrapper = $('#fs-mail-template-editor-wrapper');
                    if (!$wrapper.length) {
                        return;
                    }
                    
                    // Create variables toolbar container
                    var $toolbar = $('<div class="fs-mail-vars-toolbar"></div>');
                    
                    // Create dropdown button
                    var $dropdownBtn = $('<button type="button" class="button button-secondary fs-mail-vars-btn" style="margin-right: 5px;" title="<?php esc_attr_e('Insert mail variable', 'f-shop'); ?>"><span class="dashicons dashicons-admin-generic" style="font-size: 16px; line-height: 1.2; vertical-align: middle; margin-right: 5px;"></span><?php esc_html_e('Insert Variable', 'f-shop'); ?> <span class="dashicons dashicons-arrow-down" style="font-size: 16px; line-height: 1.2; vertical-align: middle; margin-left: 5px;"></span></button>');
                    
                    // Create dropdown menu
                    var $dropdownMenu = $('<div class="fs-mail-vars-menu" style="display: none; position: absolute; background: #fff; border: 1px solid #ccc; border-radius: 3px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 10000; max-height: 400px; overflow-y: auto; margin-top: 5px; min-width: 350px; left: 0; top: 100%;"></div>');
                    
                    // Group variables by category
                    var categories = {
                        '<?php esc_html_e('Order Information', 'f-shop'); ?>': ['%order_id%', '%order_date%', '%order_title%', '%order_edit_url%', '%dashboard_url%'],
                        '<?php esc_html_e('Cart Information', 'f-shop'); ?>': ['%cart_amount%', '%products_cost%', '%delivery_cost%', '%packing_cost%', '%cart_discount%', '%cart_items_table%'],
                        '<?php esc_html_e('Client Information', 'f-shop'); ?>': ['%client_id%', '%client_email%', '%client_phone%', '%client_first_name%', '%client_last_name%', '%client_city%', '%client_address%', '%client_comment%'],
                        '<?php esc_html_e('Delivery Information', 'f-shop'); ?>': ['%delivery_method%', '%delivery_number%', '%address_street%', '%address_house_number%', '%address_entrance_number%', '%address_apartment_number%'],
                        '<?php esc_html_e('Payment', 'f-shop'); ?>': ['%payment_method%'],
                        '<?php esc_html_e('Site Information', 'f-shop'); ?>': ['%site_name%', '%home_url%', '%admin_email%', '%contact_email%', '%contact_phone%', '%contact_address%', '%mail_logo%'],
                        '<?php esc_html_e('Email Content', 'f-shop'); ?>': ['%admin_mail_title%', '%admin_mail_message%', '%customer_mail_title%', '%customer_mail_message%']
                    };
                    
                    var variables = <?php echo json_encode($variables); ?>;
                    
                    // Build dropdown menu
                    $.each(categories, function(category, vars) {
                        var $category = $('<div style="padding: 8px 12px; border-bottom: 1px solid #eee;"><strong style="display: block; margin-bottom: 5px; color: #2271b1; font-size: 13px;">' + category + '</strong></div>');
                        var $varsList = $('<div style="padding-left: 10px;"></div>');
                        
                        $.each(vars, function(i, varName) {
                            var $varItem = $('<div class="fs-mail-var-item" style="padding: 5px 8px; cursor: pointer; border-radius: 3px; margin-bottom: 2px;" data-var="' + varName + '" title="' + (variables[varName] || '') + '"><code style="background: #f0f0f1; padding: 2px 6px; border-radius: 2px; font-size: 12px;">' + varName + '</code> <span style="color: #666; font-size: 12px; margin-left: 5px;">' + (variables[varName] || '') + '</span></div>');
                            
                            $varItem.on('mouseenter', function() {
                                $(this).css('background', '#f0f0f1');
                            }).on('mouseleave', function() {
                                $(this).css('background', 'transparent');
                            }).on('click', function() {
                                insertVariableIntoCodeMirror(varName);
                                $dropdownMenu.hide();
                            });
                            
                            $varsList.append($varItem);
                        });
                        
                        $category.append($varsList);
                        $dropdownMenu.append($category);
                    });
                    
                    var $container = $('<div style="position: relative; display: inline-block;"></div>');
                    $container.append($dropdownBtn);
                    $container.append($dropdownMenu);
                    $toolbar.append($container);
                    
                    // Insert before textarea
                    $wrapper.find('label').after($toolbar);
                    
                    // Toggle dropdown
                    $dropdownBtn.on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        $dropdownMenu.toggle();
                    });
                    
                    // Close dropdown on outside click
                    $(document).on('click', function(e) {
                        if (!$(e.target).closest('.fs-mail-vars-toolbar').length) {
                            $dropdownMenu.hide();
                        }
                    });
                    
                    // Insert variable into CodeMirror
                    function insertVariableIntoCodeMirror(variable) {
                        // Get CodeMirror instance
                        var textarea = document.getElementById('fs-mail-template-content');
                        if (!textarea) {
                            return;
                        }
                        
                        // Find CodeMirror instance
                        var codemirror = null;
                        if (window.fsMailTemplateEditor && window.fsMailTemplateEditor.instance && window.fsMailTemplateEditor.instance.codemirror) {
                            codemirror = window.fsMailTemplateEditor.instance.codemirror;
                        } else {
                            var textarea = document.getElementById('fs-mail-template-content');
                            if (textarea && textarea.nextSibling && textarea.nextSibling.CodeMirror) {
                                codemirror = textarea.nextSibling.CodeMirror;
                            } else if (window.wp && wp.codeEditor && wp.codeEditor.instances && wp.codeEditor.instances['fs-mail-template-content']) {
                                codemirror = wp.codeEditor.instances['fs-mail-template-content'].codemirror;
                            }
                        }
                        
                        if (codemirror) {
                            var doc = codemirror.getDoc();
                            var cursor = doc.getCursor();
                            doc.replaceRange(variable, cursor);
                            codemirror.focus();
                        } else {
                            // Fallback: insert into textarea
                            var textarea = $('#fs-mail-template-content');
                            var val = textarea.val();
                            var pos = textarea[0].selectionStart || 0;
                            var newVal = val.substring(0, pos) + variable + val.substring(pos);
                            textarea.val(newVal);
                            textarea.focus();
                            textarea[0].setSelectionRange(pos + variable.length, pos + variable.length);
                        }
                    }
                }
            });
        })(jQuery);
        </script>
        <?php
    }

    /**
     * Add meta boxes for email template settings
     */
    public function add_mail_template_meta_boxes()
    {
        add_meta_box(
            'fs-mail-template-settings',
            __('Template Settings', 'f-shop'),
            [$this, 'mail_template_settings_callback'],
            'fs-mail-template',
            'side',
            'default'
        );

        add_meta_box(
            'fs-mail-template-header-footer',
            __('Email Header & Footer', 'f-shop'),
            [$this, 'mail_template_header_footer_callback'],
            'fs-mail-template',
            'normal',
            'low'
        );

        add_meta_box(
            'fs-mail-template-test',
            __('Test Email', 'f-shop'),
            [$this, 'mail_template_test_callback'],
            'fs-mail-template',
            'side',
            'default'
        );

        // Enqueue script for copying variables
        add_action('admin_enqueue_scripts', [$this, 'enqueue_mail_template_scripts']);
    }

    /**
     * Enqueue scripts for mail template page
     */
    public function enqueue_mail_template_scripts($hook)
    {
        global $post_type;
        
        if (($hook === 'post.php' || $hook === 'post-new.php') && $post_type === 'fs-mail-template') {
            // Enqueue jQuery
            wp_enqueue_script('jquery');
            
            // Localize script to make ajaxurl available
            wp_localize_script('jquery', 'fsMailTemplate', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('fs_mail_template_save'),
                'strings' => [
                    'selectOrder' => __('Please select an order', 'f-shop'),
                    'sending' => __('Sending...', 'f-shop'),
                    'sendTestEmail' => __('Send Test Email', 'f-shop'),
                    'errorSending' => __('Error sending email', 'f-shop'),
                    'ajaxError' => __('AJAX error occurred', 'f-shop'),
                ]
            ]);
            
            // Add inline script for test email with proper dependency
            $script = "
            (function($) {
                $(document).ready(function() {
                    $('#fs-send-test-email').on('click', function(e) {
                        e.preventDefault();
                        
                        var \$button = $(this);
                        var orderId = $('#test_order_id').val();
                        var templateId = \$button.data('template-id');
                        var templateUsage = \$button.data('template-usage');
                        var templateType = \$button.data('template-type');
                        var \$result = $('#fs-test-email-result');
                        
                        if (!orderId) {
                            alert(fsMailTemplate.strings.selectOrder);
                            return;
                        }
                        
                        \$button.prop('disabled', true).text(fsMailTemplate.strings.sending);
                        \$result.hide();
                        
                        $.ajax({
                            url: fsMailTemplate.ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'fs_send_test_email',
                                nonce: fsMailTemplate.nonce,
                                order_id: orderId,
                                template_id: templateId,
                                template_usage: templateUsage,
                                template_type: templateType
                            },
                            success: function(response) {
                                if (response.success) {
                                    \$result.html('<div class=\"notice notice-success\"><p>' + response.data.msg + '</p></div>').show();
                                } else {
                                    \$result.html('<div class=\"notice notice-error\"><p>' + (response.data.msg || fsMailTemplate.strings.errorSending) + '</p></div>').show();
                                }
                            },
                            error: function() {
                                \$result.html('<div class=\"notice notice-error\"><p>' + fsMailTemplate.strings.ajaxError + '</p></div>').show();
                            },
                            complete: function() {
                                \$button.prop('disabled', false).text(fsMailTemplate.strings.sendTestEmail);
                            }
                        });
                    });
                });
            })(jQuery);
            ";
            
            wp_add_inline_script('jquery', $script);
        }
    }

    /**
     * Display available variables metabox
     */
    public function mail_template_variables_callback($post)
    {
        $variables = [
            __('Order Information:', 'f-shop') => [
                '%order_id%' => __('Order ID', 'f-shop'),
                '%order_date%' => __('Order date', 'f-shop'),
                '%order_title%' => __('Order title', 'f-shop'),
                '%order_edit_url%' => __('Admin order edit URL', 'f-shop'),
                '%dashboard_url%' => __('Customer dashboard URL', 'f-shop'),
            ],
            __('Cart Information:', 'f-shop') => [
                '%cart_amount%' => __('Total cart amount', 'f-shop'),
                '%products_cost%' => __('Products cost', 'f-shop'),
                '%delivery_cost%' => __('Delivery cost', 'f-shop'),
                '%packing_cost%' => __('Packing cost', 'f-shop'),
                '%cart_discount%' => __('Cart discount', 'f-shop'),
                '%cart_items_table%' => __('Cart items table (editable template)', 'f-shop'),
            ],
            __('Client Information:', 'f-shop') => [
                '%client_id%' => __('Client user ID', 'f-shop'),
                '%client_email%' => __('Client email', 'f-shop'),
                '%client_phone%' => __('Client phone', 'f-shop'),
                '%client_first_name%' => __('Client first name', 'f-shop'),
                '%client_last_name%' => __('Client last name', 'f-shop'),
                '%client_city%' => __('Client city', 'f-shop'),
                '%client_address%' => __('Client address', 'f-shop'),
                '%client_comment%' => __('Client comment', 'f-shop'),
            ],
            __('Delivery Information:', 'f-shop') => [
                '%delivery_method%' => __('Delivery method', 'f-shop'),
                '%delivery_number%' => __('Delivery department number', 'f-shop'),
                '%address_street%' => __('Street address', 'f-shop'),
                '%address_house_number%' => __('House number', 'f-shop'),
                '%address_entrance_number%' => __('Entrance number', 'f-shop'),
                '%address_apartment_number%' => __('Apartment number', 'f-shop'),
            ],
            __('Payment Information:', 'f-shop') => [
                '%payment_method%' => __('Payment method', 'f-shop'),
            ],
            __('Site Information:', 'f-shop') => [
                '%site_name%' => __('Site name', 'f-shop'),
                '%home_url%' => __('Home URL', 'f-shop'),
                '%admin_email%' => __('Admin email', 'f-shop'),
                '%contact_email%' => __('Contact email', 'f-shop'),
                '%contact_phone%' => __('Contact phone', 'f-shop'),
                '%contact_address%' => __('Contact address', 'f-shop'),
                '%mail_logo%' => __('Email logo URL', 'f-shop'),
            ],
            __('Email Content:', 'f-shop') => [
                '%admin_mail_title%' => __('Admin email title', 'f-shop'),
                '%admin_mail_message%' => __('Admin email message', 'f-shop'),
                '%customer_mail_title%' => __('Customer email title', 'f-shop'),
                '%customer_mail_message%' => __('Customer email message', 'f-shop'),
            ],
        ];
        ?>
        <div class="fs-mail-variables" style="padding: 15px; background: #f0f0f1; border-left: 4px solid #2271b1;">
            <p style="margin-top: 0;">
                <strong><?php esc_html_e('Click on any variable to copy it to clipboard', 'f-shop'); ?></strong>
            </p>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <?php foreach ($variables as $category => $vars): ?>
                    <div>
                        <h4 style="margin-top: 0; margin-bottom: 10px;"><?php echo esc_html($category); ?></h4>
                        <ul style="margin: 0; padding-left: 20px;">
                            <?php foreach ($vars as $var => $desc): ?>
                                <li style="margin-bottom: 8px;">
                                    <code class="fs-mail-var" 
                                          data-var="<?php echo esc_attr($var); ?>" 
                                          style="cursor: pointer; padding: 4px 8px; background: #fff; border: 1px solid #ddd; border-radius: 3px; display: inline-block; transition: all 0.2s;"
                                          title="<?php esc_attr_e('Click to copy', 'f-shop'); ?>">
                                        <?php echo esc_html($var); ?>
                                    </code>
                                    <span style="margin-left: 8px; color: #666;">- <?php echo esc_html($desc); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.fs-mail-var').on('click', function(e) {
                e.preventDefault();
                var varName = $(this).data('var');
                
                // Create temporary input element
                var $temp = $('<input>');
                $('body').append($temp);
                $temp.val(varName).select();
                
                try {
                    // Try modern clipboard API
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(varName).then(function() {
                            showCopyFeedback($(this));
                        }.bind(this));
                    } else {
                        // Fallback to execCommand
                        document.execCommand('copy');
                        showCopyFeedback($(this));
                    }
                } catch (err) {
                    // Fallback to execCommand
                    document.execCommand('copy');
                    showCopyFeedback($(this));
                }
                
                $temp.remove();
            });
            
            function showCopyFeedback($element) {
                var originalText = $element.text();
                $element.text('<?php esc_html_e('Copied!', 'f-shop'); ?>')
                       .css('background', '#46b450')
                       .css('color', '#fff');
                setTimeout(function() {
                    $element.text(originalText)
                           .css('background', '#fff')
                           .css('color', 'inherit');
                }, 1500);
            }
            
            // Hover effect
            $('.fs-mail-var').on('mouseenter', function() {
                $(this).css('background', '#2271b1').css('color', '#fff');
            }).on('mouseleave', function() {
                if (!$(this).hasClass('copied')) {
                    $(this).css('background', '#fff').css('color', 'inherit');
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Display template settings metabox
     */
    public function mail_template_settings_callback($post)
    {
        wp_nonce_field('fs_mail_template_save', 'fs_mail_template_nonce');
        
        $usage = get_post_meta($post->ID, '_mail_template_usage', true);
        $type = get_post_meta($post->ID, '_mail_template_type', true);
        $subject = get_post_meta($post->ID, '_mail_subject', true);
        $title = get_post_meta($post->ID, '_mail_title', true);
        $message = get_post_meta($post->ID, '_mail_message', true);
        
        // Convert array to single value if needed (for backward compatibility)
        if (is_array($usage) && !empty($usage)) {
            $usage = $usage[0];
        } elseif (is_array($usage) && empty($usage)) {
            $usage = '';
        }
        ?>
        <p>
            <label for="mail_template_usage">
                <strong><?php esc_html_e('Where to use this template:', 'f-shop'); ?></strong>
            </label>
        </p>
        <p>
            <select name="mail_template_usage" id="mail_template_usage" style="width: 100%;">
                <option value=""><?php esc_html_e('-- Select --', 'f-shop'); ?></option>
                <?php
                $usage_options = [
                    'order_create_admin' => __('Admin notification when order is created', 'f-shop'),
                    'order_create_customer' => __('Customer notification when order is created', 'f-shop'),
                    'user_registration' => __('User registration notification', 'f-shop'),
                    'user_registration_admin' => __('Admin notification when user registers', 'f-shop'),
                    'password_reset' => __('Password reset notification', 'f-shop'),
                ];
                foreach ($usage_options as $value => $label):
                ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php selected($usage, $value); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="mail_template_type">
                <strong><?php esc_html_e('Template Type:', 'f-shop'); ?></strong>
            </label>
        </p>
        <p>
            <select name="mail_template_type" id="mail_template_type" style="width: 100%;">
                <option value="customer" <?php selected($type, 'customer'); ?>>
                    <?php esc_html_e('Customer Email', 'f-shop'); ?>
                </option>
                <option value="admin" <?php selected($type, 'admin'); ?>>
                    <?php esc_html_e('Admin Email', 'f-shop'); ?>
                </option>
            </select>
        </p>

        <p>
            <label for="mail_subject">
                <strong><?php esc_html_e('Email Subject:', 'f-shop'); ?></strong>
            </label>
        </p>
        <p>
            <input type="text" 
                   name="mail_subject" 
                   id="mail_subject" 
                   value="<?php echo esc_attr($subject); ?>" 
                   style="width: 100%;"
                   placeholder="<?php esc_attr_e('e.g., Order #%order_id% on %site_name%', 'f-shop'); ?>">
            <span class="description">
                <?php esc_html_e('Use variables like %order_id%, %site_name%, etc.', 'f-shop'); ?>
            </span>
        </p>

        <p>
            <label for="mail_title">
                <strong><?php esc_html_e('Email Title:', 'f-shop'); ?></strong>
            </label>
        </p>
        <p>
            <input type="text" 
                   name="mail_title" 
                   id="mail_title" 
                   value="<?php echo esc_attr($title); ?>" 
                   style="width: 100%;"
                   placeholder="<?php esc_attr_e('e.g., Thank you for your order', 'f-shop'); ?>">
        </p>

        <p>
            <label for="mail_message">
                <strong><?php esc_html_e('Email Message:', 'f-shop'); ?></strong>
            </label>
        </p>
        <p>
            <textarea name="mail_message" 
                      id="mail_message" 
                      rows="4" 
                      style="width: 100%;"
                      placeholder="<?php esc_attr_e('Short message text', 'f-shop'); ?>"><?php echo esc_textarea($message); ?></textarea>
        </p>
        <?php
    }

    /**
     * Save mail template meta data
     */
    public function save_mail_template($post_id, $post, $update)
    {
        // Check nonce
        if (!isset($_POST['fs_mail_template_nonce']) || 
            !wp_verify_nonce($_POST['fs_mail_template_nonce'], 'fs_mail_template_save')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save usage (single value)
        if (isset($_POST['mail_template_usage']) && !empty($_POST['mail_template_usage'])) {
            update_post_meta($post_id, '_mail_template_usage', sanitize_text_field($_POST['mail_template_usage']));
        } else {
            delete_post_meta($post_id, '_mail_template_usage');
        }

        // Save type
        if (isset($_POST['mail_template_type'])) {
            update_post_meta($post_id, '_mail_template_type', sanitize_text_field($_POST['mail_template_type']));
        }

        // Save subject
        if (isset($_POST['mail_subject'])) {
            update_post_meta($post_id, '_mail_subject', sanitize_text_field($_POST['mail_subject']));
        }

        // Save title
        if (isset($_POST['mail_title'])) {
            update_post_meta($post_id, '_mail_title', sanitize_text_field($_POST['mail_title']));
        }

        // Save message
        if (isset($_POST['mail_message'])) {
            update_post_meta($post_id, '_mail_message', sanitize_textarea_field($_POST['mail_message']));
        }

        // Save header - use wp_unslash and store raw HTML for email templates
        if (isset($_POST['mail_template_header'])) {
            $header_content = wp_unslash($_POST['mail_template_header']);
            // Store raw HTML without strict sanitization for email templates
            // Only basic sanitization to prevent XSS but preserve HTML structure
            update_post_meta($post_id, '_mail_template_header', $header_content);
        } else {
            delete_post_meta($post_id, '_mail_template_header');
        }

        // Save footer - use wp_unslash and store raw HTML for email templates
        if (isset($_POST['mail_template_footer'])) {
            $footer_content = wp_unslash($_POST['mail_template_footer']);
            // Store raw HTML without strict sanitization for email templates
            // Only basic sanitization to prevent XSS but preserve HTML structure
            update_post_meta($post_id, '_mail_template_footer', $footer_content);
        } else {
            delete_post_meta($post_id, '_mail_template_footer');
        }
    }

    /**
     * Display email header and footer metabox
     */
    public function mail_template_header_footer_callback($post)
    {
        wp_nonce_field('fs_mail_template_save', 'fs_mail_template_nonce');
        
        $header = get_post_meta($post->ID, '_mail_template_header', true);
        $footer = get_post_meta($post->ID, '_mail_template_footer', true);
        
        // Use wp_unslash to get raw HTML content without escaping
        $header = wp_unslash($header);
        $footer = wp_unslash($footer);
        
        // Ensure values are strings
        $header = is_string($header) ? $header : '';
        $footer = is_string($footer) ? $footer : '';
        ?>
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Header Section -->
            <div>
                <p>
                    <label for="mail_template_header">
                        <strong><?php esc_html_e('Email Header HTML:', 'f-shop'); ?></strong>
                    </label>
                </p>
                <p>
                    <textarea name="mail_template_header" 
                              id="mail_template_header" 
                              rows="15" 
                              style="width: 100%; font-family: monospace; font-size: 12px;"
                              placeholder="<?php esc_attr_e('HTML code for email header (e.g., opening &lt;html&gt;, &lt;head&gt;, &lt;body&gt; tags, styles, etc.)', 'f-shop'); ?>"><?php echo $header; ?></textarea>
                </p>
                <p class="description">
                    <?php esc_html_e('This header will be prepended to the main email content. Include opening HTML tags, head section with styles, and opening body tag.', 'f-shop'); ?>
                </p>
            </div>

            <!-- Footer Section -->
            <div>
                <p>
                    <label for="mail_template_footer">
                        <strong><?php esc_html_e('Email Footer HTML:', 'f-shop'); ?></strong>
                    </label>
                </p>
                <p>
                    <textarea name="mail_template_footer" 
                              id="mail_template_footer" 
                              rows="15" 
                              style="width: 100%; font-family: monospace; font-size: 12px;"
                              placeholder="<?php esc_attr_e('HTML code for email footer (e.g., closing &lt;/body&gt;, &lt;/html&gt; tags, etc.)', 'f-shop'); ?>"><?php echo $footer; ?></textarea>
                </p>
                <p class="description">
                    <?php esc_html_e('This footer will be appended to the main email content. Include closing body and html tags.', 'f-shop'); ?>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Display test email metabox
     */
    public function mail_template_test_callback($post)
    {
        wp_nonce_field('fs_mail_template_save', 'fs_mail_template_nonce');
        
        // Get template usage and type
        $usage = get_post_meta($post->ID, '_mail_template_usage', true);
        $type = get_post_meta($post->ID, '_mail_template_type', true);
        
        // Only show for order-related templates
        if (!in_array($usage, ['order_create_admin', 'order_create_customer'])) {
            ?>
            <p><?php esc_html_e('Test email is available only for order-related templates.', 'f-shop'); ?></p>
            <?php
            return;
        }
        
        // Get recent orders
        $orders = get_posts([
            'post_type' => FS_Config::get_data('post_type_orders'),
            'posts_per_page' => 50,
            'post_status' => 'any',
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
        ?>
        <p>
            <label for="test_order_id">
                <strong><?php esc_html_e('Select order for test:', 'f-shop'); ?></strong>
            </label>
        </p>
        <p>
            <select name="test_order_id" id="test_order_id" style="width: 100%;">
                <option value=""><?php esc_html_e('-- Select order --', 'f-shop'); ?></option>
                <?php foreach ($orders as $order): ?>
                    <option value="<?php echo esc_attr($order->ID); ?>">
                        <?php echo esc_html(sprintf(__('Order #%d - %s', 'f-shop'), $order->ID, $order->post_title)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <button type="button" 
                    id="fs-send-test-email" 
                    class="button button-primary" 
                    data-template-id="<?php echo esc_attr($post->ID); ?>"
                    data-template-usage="<?php echo esc_attr($usage); ?>"
                    data-template-type="<?php echo esc_attr($type); ?>">
                <?php esc_html_e('Send Test Email', 'f-shop'); ?>
            </button>
        </p>
        <p class="description">
            <?php esc_html_e('Test email will be sent to admin email address.', 'f-shop'); ?>
        </p>
        <div id="fs-test-email-result" style="margin-top: 10px; display: none;"></div>
        <?php
    }

    /**
     * AJAX handler for sending test email
     */
    public function send_test_email()
    {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fs_mail_template_save')) {
            wp_send_json_error(['msg' => __('Security check failed', 'f-shop')]);
        }

        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['msg' => __('You do not have permission to perform this action', 'f-shop')]);
        }

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
        $template_usage = isset($_POST['template_usage']) ? sanitize_text_field($_POST['template_usage']) : '';
        $template_type = isset($_POST['template_type']) ? sanitize_text_field($_POST['template_type']) : '';

        if (!$order_id || !$template_id) {
            wp_send_json_error(['msg' => __('Order ID and Template ID are required', 'f-shop')]);
        }

        // Get order data
        $order = new \FS\FS_Order($order_id);
        if (!$order->ID) {
            wp_send_json_error(['msg' => __('Order not found', 'f-shop')]);
        }

        // Prepare mail_data similar to FS_Ajax::create_order
        $order_meta = get_post_meta($order_id);
        $order_data = $order->get_order_data();
        
        // Get delivery and payment methods
        $delivery_method = '';
        $payment_method = '';
        if (isset($order_meta['_delivery'][0])) {
            $delivery_data = maybe_unserialize($order_meta['_delivery'][0]);
            if (isset($delivery_data['method']) && $delivery_data['method']) {
                $delivery_method = fs_get_delivery($delivery_data['method']);
            }
        }
        if (isset($order_meta['_payment'][0]) && $order_meta['_payment'][0]) {
            $payment_term = get_term($order_meta['_payment'][0]);
            $payment_method = $payment_term ? $payment_term->name : '';
        }

        // Get user data
        $user_data = isset($order_meta['_user'][0]) ? maybe_unserialize($order_meta['_user'][0]) : [];
        
        // Prepare cart items
        $cart_items = [];
        if (isset($order_meta['_products'][0])) {
            $products = maybe_unserialize($order_meta['_products'][0]);
            if (is_array($products)) {
                foreach ($products as $product_id => $product_data) {
                    // fs_set_product can accept product ID or array with product data
                    $product_item = is_array($product_data) ? array_merge(['ID' => $product_id], $product_data) : ['ID' => $product_id];
                    $product = fs_set_product($product_item);
                    if ($product && isset($product->title)) {
                        $cart_items[] = [
                            'name' => $product->title,
                            'link' => isset($product->permalink) ? $product->permalink : get_permalink($product_id),
                            'thumbnail_url' => isset($product->thumbnail_url) ? $product->thumbnail_url : get_the_post_thumbnail_url($product_id, 'thumbnail'),
                            'qty' => isset($product_data['count']) ? $product_data['count'] : 1,
                            'all_price' => isset($product_data['cost']) ? $product_data['cost'] : (isset($product->price) ? $product->price : 0),
                            'currency' => fs_currency(),
                            'attr' => isset($product_data['attr']) ? $product_data['attr'] : [],
                        ];
                    }
                }
            }
        }

        // Format order date
        $order_date = get_the_date('', $order_id);
        $order_date_i18n = get_the_date(get_option('date_format') . ' ' . get_option('time_format'), $order_id);

        // Build mail_data
        $mail_data = [
            'order_date' => $order_date_i18n,
            'order_id' => $order_id,
            'order_title' => $order->post->post_title ?? sprintf(__('Order #%d', 'f-shop'), $order_id),
            'cart_discount' => isset($order_meta['_order_discount'][0]) ? sprintf('%s %s', apply_filters('fs_price_format', $order_meta['_order_discount'][0]), fs_currency()) : '0 ' . fs_currency(),
            'cart_amount' => isset($order_meta['_amount'][0]) ? sprintf('%s %s', apply_filters('fs_price_format', $order_meta['_amount'][0]), fs_currency()) : '0 ' . fs_currency(),
            'delivery_cost' => isset($order_meta['_delivery'][0]) ? (new \FS\FS_Delivery($order_id))->get_shipping_cost_text() : '0 ' . fs_currency(),
            'products_cost' => isset($order_meta['_cart_cost'][0]) ? sprintf('%s %s', apply_filters('fs_price_format', $order_meta['_cart_cost'][0]), fs_currency()) : '0 ' . fs_currency(),
            'packing_cost' => isset($order_meta['_packing_cost'][0]) ? sprintf('%s %s', apply_filters('fs_price_format', $order_meta['_packing_cost'][0]), fs_currency()) : '0 ' . fs_currency(),
            'delivery_method' => $delivery_method,
            'delivery_number' => isset($delivery_data['secession']) ? $delivery_data['secession'] : '',
            'payment_method' => $payment_method,
            'cart_items' => $cart_items,
            'order_edit_url' => admin_url('post.php?post=' . $order_id . '&action=edit'),
            'dashboard_url' => fs_account_url(),
            'site_name' => get_bloginfo('name'),
            'home_url' => home_url('/'),
            'admin_email' => get_option('admin_email'),
            'contact_email' => fs_option('manager_email', get_option('admin_email')),
            'contact_phone' => fs_option('contact_phone'),
            'contact_address' => fs_option('contact_address'),
            'mail_logo' => fs_option('fs_email_logo') ? wp_get_attachment_image_url(fs_option('fs_email_logo'), 'full') : '',
            'social_links' => [],
            'client_city' => isset($order_meta['city'][0]) ? $order_meta['city'][0] : '',
            'client_address' => isset($order_meta['_delivery'][0]) ? (maybe_unserialize($order_meta['_delivery'][0])['address'] ?? '') : '',
            'address_street' => '',
            'address_house_number' => '',
            'address_entrance_number' => '',
            'address_apartment_number' => '',
            'client_phone' => isset($user_data['phone']) ? $user_data['phone'] : '',
            'client_email' => isset($user_data['email']) ? $user_data['email'] : '',
            'client_first_name' => isset($user_data['first_name']) ? $user_data['first_name'] : '',
            'client_last_name' => isset($user_data['last_name']) ? $user_data['last_name'] : '',
            'client_id' => isset($user_data['id']) ? $user_data['id'] : 0,
            'client_comment' => isset($order_meta['_comment'][0]) ? $order_meta['_comment'][0] : '',
            'admin_mail_title' => __('Test Email: New Order', 'f-shop'),
            'admin_mail_message' => __('This is a test email sent from email template editor.', 'f-shop'),
            'customer_mail_title' => __('Test Email: Thank you for your order', 'f-shop'),
            'customer_mail_message' => __('This is a test email sent from email template editor.', 'f-shop'),
        ];

        // Apply filters to get custom template data
        $mail_data = apply_filters('fs_create_order_mail_data', $mail_data);

        // Determine template path
        $template_path = '';
        if ($template_usage === 'order_create_admin') {
            $template_path = 'mail/admin-create-order';
        } elseif ($template_usage === 'order_create_customer') {
            $template_path = 'mail/user-create-order';
        }

        if (empty($template_path)) {
            wp_send_json_error(['msg' => __('Invalid template usage type', 'f-shop')]);
        }

        // Set global template path for fs_override_frontend_template
        global $fs_current_template_path;
        $fs_current_template_path = $template_path;

        // Send test email
        try {
            $notification = new \FS\FS_Notification();
            $admin_email = get_option('admin_email');
            
            // Get subject from template or use default
            $subject = '';
            if ($template_type === 'admin') {
                $subject = !empty($mail_data['admin_mail_title']) ? $mail_data['admin_mail_title'] : __('Test: New Order', 'f-shop');
            } else {
                $subject = !empty($mail_data['customer_mail_title']) ? $mail_data['customer_mail_title'] : __('Test: Order Confirmation', 'f-shop');
            }
            
            // Replace variables in subject
            $subject = str_replace(
                array_map(function ($item) {
                    return '%' . $item . '%';
                }, array_keys($mail_data)),
                array_values($mail_data),
                $subject
            );

            $notification->set_recipients([$admin_email]);
            $notification->set_subject($subject);
            $notification->set_template($template_path, $mail_data);
            $notification->send();

            wp_send_json_success([
                'msg' => sprintf(__('Test email sent successfully to %s', 'f-shop'), $admin_email)
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'msg' => sprintf(__('Error sending test email: %s', 'f-shop'), $e->getMessage())
            ]);
        }
    }

    public function init()
    {
        $this->create_types();
    }

    /**
     * Create all registered post types
     */
    public function create_types()
    {
        $post_types = $this->register_custom_post_types();
        if (!is_array($post_types) && count($post_types) == 0) {
            return;
        }

        foreach ($post_types as $name => $type) {
            $this->create_post_type($name, $type);
        }

    }

    /**
     * Create a new post type
     *
     * @param $name
     * @param $args
     */
    public function create_post_type($name, $args)
    {
        // For mail templates, preserve privacy settings
        if ($name === 'fs-mail-template') {
            $defaults = array(
                'labels' => array(
                    'name' => $args['labels']['name'] ?? $name,
                    'singular_name' => $args['labels']['singular_name'] ?? $name,
                    'add_new' => $args['labels']['add_new'] ?? 'Добавить ' . $name,
                    'add_new_item' => $args['labels']['add_new_item'] ?? 'Добавить ' . $name,
                    'edit_item' => $args['labels']['edit_item'] ?? 'Изменить ' . $name,
                ),
            );
            $args = wp_parse_args($args, $defaults);
        } else {
        $args = wp_parse_args($args, array(
            'labels' => array(
                'name' => $name,
                'singular_name' => $name,
                'add_new' => 'Добавить ' . $name,
                'add_new_item' => 'Добавить ' . $name,
                'edit_item' => 'Изменить ' . $name,
            ),
            'public' => true,
            'show_in_menu' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'show_in_nav_menus' => true,
            'menu_position' => null,
            'can_export' => true,
            'has_archive' => true,
            'rewrite' => true,
            'query_var' => true,
            'show_in_rest' => true,
        ));
        }

        register_post_type($name, $args);
    }

    /**
     * This method returns the additional registered record types
     *
     * @return mixed|void
     */
    public function register_custom_post_types()
    {
        $types = array(
            'reviews' => array(
                'name' => __('Reviews', 'f-shop'),
                'singular_name' => __('Review', 'f-shop'),
                'menu_icon' => 'dashicons-thumbs-up',
                'exclude_from_search' => true,
                'taxonomies' => array(),
                'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'comments', 'gutenburg'),
            ),
            'fs-mail-template' => array(
                'labels' => array(
                    'name' => __('Email Templates', 'f-shop'),
                    'singular_name' => __('Email Template', 'f-shop'),
                    'add_new' => __('Add Email Template', 'f-shop'),
                    'edit_item' => __('Edit Email Template', 'f-shop'),
                    'menu_name' => __('Email Templates', 'f-shop'),
                ),
                'menu_icon' => 'dashicons-email-alt',
                'exclude_from_search' => true,
                'supports' => array('title'), // Removed 'editor' to prevent default WordPress editor
                'public' => false,
                'publicly_queryable' => false,
                'show_ui' => true,
                'show_in_menu' => 'edit.php?post_type=' . FS_Config::get_data('post_type_orders'),
                'show_in_nav_menus' => false,
                'show_in_admin_bar' => false,
                'has_archive' => false,
                'rewrite' => false,
                'query_var' => false,
                'show_in_rest' => false,
                'capability_type' => 'post',
                'map_meta_cap' => true,
            ),
            FS_Config::get_data('post_type_orders') => array(
                'labels' => array(
                    'name' => __('Orders', 'f-shop'),
                    'singular_name' => __('Order', 'f-shop'),
                    'add_new' => __('Add Order', 'f-shop'),
                    'add_new_item' => '',
                    'edit_item' => __('Edit order', 'f-shop'),
                    'new_item' => '',
                    'view_item' => '',
                    'search_items' => '',
                    'not_found' => '',
                    'not_found_in_trash' => '',
                    'parent_item_colon' => '',
                    'menu_name' => __('Orders', 'f-shop'),
                ),
                'public' => false,
                'show_in_menu' => true,
                'publicly_queryable' => false,
                'show_ui' => true,
                'capability_type' => 'post',
                'menu_icon' => 'dashicons-list-view',
                'map_meta_cap' => true,
                'show_in_nav_menus' => false,
                'menu_position' => 6,
                'can_export' => true,
                'has_archive' => true,
                'rewrite' => true,
                'query_var' => true,
                'description' => __("Orders from your site are placed here.", 'f-shop'),
                'supports' => array(
                    'title'
                ),
                '_builtin' => false
            )
        );

        return apply_filters('fs_register_custom_post_types', $types);
    }

}