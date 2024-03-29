<?php
class WPSPARKCONNECTOR_Admin_Menu
{
    private static $instance;
    private $wpdb;
    private $table_name;
    private $wordpress_ip;
    public static function init(){
        if(null == self::$instance){
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __construct(){
        global $wpdb;
		$this->wpdb = $wpdb;

		$this->table_name = $this->wpdb->prefix . 'spark_build';
        $this->wordpress_ip = do_shortcode('[show_ip]');
        add_action("admin_init", array($this, "wpsparkconnector_display_options"));
        add_action('admin_menu', array($this, 'wpsparkconnector_admin_menu_init'));
        add_action('admin_bar_menu', array($this, "wpsparkconnector_add_toolbar_items"), 80);
    }


    public function wpsparkconnector_admin_menu_init()
    {
        /**
         * syntax to add menu page
         * add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position)
         */
        add_menu_page('Spark', 'Spark', 'manage_options', 'spark', array($this, 'wpsparkconnector_admin_menu'), plugin_dir_url(__DIR__). '/assets/images/wpspark-icon-25x.png', 2);

    }
    public function wpsparkconnector_admin_menu(){
        ?>
        <div class="tg-app-connector uk-padding">
            <div class="wrap">
                <div id="icon-options-general" class="icon32"></div>

                <div class="registration">
                    <div class="uk-card uk-card-default uk-card-body uk-background-muted">
                        <div class="uk-child-width-expand@s uk-flex uk-flex-middle">
                            <div class="left logo">
                                <img src="<?php echo plugin_dir_url(__DIR__). '/assets/images/wpspark-logo.png';?>" width="200px" alt="">
                            </div>
                            <div class="right uk-text-right status">
                                <?php if(get_option('spark_app_token')):?>
                                    
                                    <?php 
                                        $build_data = $this->wpsparkconnector_get_build_data(get_option('spark_app_token'));
                                        $last_build_data = $this->wpsparkconnector_get_last_build_row();
                                    ?>
                                    <p class="uk-form-horizontal">
                                        <?php if($this->wordpress_ip !== '127.0.0.1'):?>

                                            <?php if( $last_build_data):?>
                                                <button 
                                                type="submit" 
                                                name="spark-build" 
                                                id="spark-build" 
                                                <?php echo ($last_build_data->status == 'null')  || ($last_build_data->message == 'building') ? 'disabled=true' : '' ;  ?>
                                                class="uk-button uk-button-primary uk-button-medium" 
                                                >Build</button>
                                            <?php else:?>
                                                <button 
                                                type="submit" 
                                                name="spark-build" 
                                                id="spark-build" 
                                                class="uk-button uk-button-primary uk-button-medium">
                                                    <?php esc_html_e('Build', 'wpsparkconnector');?>
                                                </button>
                                            <?php endif; ?>

                                        <?php else: ?>
                                            <p class="uk-alert-warning uk-alert"><?php esc_html_e('You can not build from local', 'wpsparkconnector');?></p>
                                        <?php endif;?>
                                    </p>
                                <?php else:?>
                                    <p>
                                        <span class="uk-label uk-label-danger uk-padding-small"><?php esc_html_e('Not connected', 'wpsparkconnector')?></span>
                                    </p>
                                <?php endif;?>
                            </div>
                        </div>
                    </div>

                    

                    <div class="uk-card uk-card-default uk-card-body">
                        <?php 
                        $token = get_option('spark_app_token');
                        if(! empty($token)): ?>
                            <div class="uk-child-width-expand@s uk-grid" id="spark_auth_state" uk-grid>

                                <div class="uk-padding">
                                    <input 
                                    id="spark-app-token"
                                    class="uk-input uk-form-width-large" 
                                    type="text" readonly placeholder="form-success" 
                                    value="<?php echo get_option('spark_app_token'); ?>">
                                    <button href="#" id="disconnect_application" class="uk-button uk-button-danger uk-button-medium"><?php esc_html_e('Disconnect', 'wpsparkconnector')?></button>
                                    
                                    <div class="build-status" id="build-status">
                                        <div class="uk-alert-primary uk-alert uk-margin-small-top" style="display:none">
                                            <a class="uk-alert-close" uk-close></a>
                                            <p> <?php esc_html_e('Your build request has been sent. Please wait for a while .....', 'wpsparkconnector')?> </p>
                                        </div>

                                        <div class="uk-alert-warning uk-alert uk-margin-small-top ftp-details" style="display:none">
                                            <a class="uk-alert-close" uk-close></a>
                                            <p><?php esc_html_e('Please setup your FTP/S3 configurations.', 'wpsparkconnector')?></p>
                                        </div>
                                        
                                        <div class="uk-alert-primary uk-alert uk-margin-small-top build-details" style="display:none"></div>

                                        <div class="uk-alert-success uk-alert uk-margin-small-top" style="display:none">
                                            <a class="uk-alert-close" uk-close></a>
                                            <p><?php esc_html_e('Congrutulatio! Your site has been successfully build for the new change.', 'wpsparkconnector');?></p>
                                        </div>

                                        <div class="uk-alert-danger uk-alert uk-margin-small-top" style="display:none">
                                            <a class="uk-alert-close" uk-close></a>
                                            <p><?php esc_html_e('There are some problem occurs while build process is happenning. Please contact with support.', 'wpsparkconnector')?></p>
                                        </div>
                                    </div>

                                    
                                    <?php if($build_data): ?>
                                    <table class="uk-table uk-table-small uk-table-middle uk-table-hover uk-table-divider uk-table-striped ">
                                        <thead>
                                            <tr>
                                                <th><?php esc_html_e('Id', 'wpsparkconnector')?></th>
                                                <th class="uk-width-small"><?php esc_html_e('Time', 'wpsparkconnector')?></th>
                                                <th class="uk-width-small"><?php esc_html_e('Token', 'wpsparkconnector')?></th>
                                                <th><?php esc_html_e('Message', 'wpsparkconnector')?></th>
                                                <th><?php esc_html_e('Status Code', 'wpsparkconnector')?></th>
                                                <th><?php esc_html_e('Status', 'wpsparkconnector')?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($build_data as $data):?>
                                            <tr class="build-data-row-<?php echo esc_html($data->id); ?> ">
                                                <td class="build-id"><?php echo esc_html($data->id); ?></td>
                                                <td class="build-time"><?php echo esc_html($data->time); ?></td>
                                                <td class="build-token"><?php echo esc_html($data->token); ?></td>
                                                <td class="build-message">
                                                    <span class="
                                                        <?php 
                                                            if($data->message == 'published'): 
                                                                echo 'uk-text-success';
                                                            elseif($data->message == 'building'):
                                                                echo 'uk-text-primary';
                                                            elseif($data->status == '500'):
                                                                echo 'uk-text-danger';
                                                            else:
                                                                echo 'uk-text-primary';
                                                            endif;

                                                        ?>
                                                    ">
                                                        <?php echo ucwords($data->message); ?>
                                                    </span>
                                                </td>
                                                <td class="build-status"><?php echo ucwords($data->status); ?></td>
                                                <?php if($data->message == 'published'): ?>
                                                    <td class="check-status-button uk-text-truncate"><span class="uk-text-success"><?php esc_html_e('Done', 'wpsparkconnector')?></span></td>
                                                <?php elseif($data->status == '500'): ?>
                                                    <td class="check-status-button uk-text-truncate"><span class="uk-text-danger"><?php esc_html_e('Build Failed', 'wpsparkconnector')?></span></td>
                                                <?php else: ?>
                                                    <td class="check-status-button uk-text-truncate"><span id="check-build-status" class="check-build-status uk-button uk-button-default uk-alert-primary" type="button"><?php esc_html_e('Check Status', 'wpsparkconnector')?></span></td>
                                                <?php endif;?>
                                            </tr>
                                            <?php endforeach;?>
                                        </tbody>
                                    </table>
                                    <?php endif;?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="uk-child-width-expand@s uk-grid" id="spark_annonymus" uk-grid>
                                <div class="uk-padding uk-width-2-3">
                                    
                                    <p class="uk-text-large uk-text-bold">
                                        <?php esc_html_e('To build your site', 'wpsparkconnector')?>
                                    </p>
                                    
                                    <ul class="uk-list uk-list-bullet">
                                        <li><a target="_blank" href="http://app.wpspark.io/login"><?php esc_html_e('Login', 'wpsparkconnector')?></a> <?php esc_html_e('to our portal', 'wpsparkconnector')?></li>
                                        <li><?php esc_html_e('Add your domain', 'wpsparkconnector')?></li>
                                        <li><?php esc_html_e('Built your site', 'wpsparkconnector')?></li>
                                    </ul>
                                </div>
                                <div class="uk-padding uk-padding-remove-right uk-flex uk-flex-middle">
                                    <div class="uk-width-1-1">
                                        <div class="uk-margin" id="email-for-register" style="display:none;">
                                            <input class="uk-input uk-form-large" type="email" placeholder="Your email address to register"/>
                                        </div>
                                        <?php if($this->wordpress_ip !== '127.0.0.1'):?>
                                        <a href="http://app.wpspark.io/register" target="_blank" id="register-input" class="uk-width-1-1 uk-button uk-button-danger uk-button-large uk-margin-small-bottom"><?php esc_html_e('Register For API keys', 'wpsparkconnector')?></a>
                                        <br/>
                                        <a href="#" id="already-has-token" class="uk-width-1-1 uk-button uk-button-primary uk-button-large"><?php esc_html_e('Already have API keys', 'wpsparkconnector')?></a>
                                        <?php else:?>
                                            <a href="http://app.wpspark.io/register" target="_blank" class="uk-width-1-1 uk-button uk-button-default uk-label-danger uk-button-large uk-margin-small-bottom">
                                                <?php esc_html_e('Register to Build', 'wpsparkconnector')?>
                                            </a>
                                        <?php endif;?>
                                        
                                    </div>
                                </div>
                            </div>

                            <div class="uk-child-width-expand@s uk-grid" id="spark_auth_state" uk-grid style="display:none;">
                                
                                <div class="uk-padding">
                                    <ul class="uk-breadcrumb">
                                        <li><a class="show_resgistration_state" href="#"><?php esc_html_e('Register Account', 'wpsparkconnector')?></a></li>
                                        <li><a href="#"><?php esc_html_e('Connect Account', 'wpsparkconnector')?></a></li>
                                    </ul>
                                    <form method="post" action="options.php" class="connect-app-form">
                                        <?php

                                            /**
                                             * add_settings_section
                                             * ====================
                                             * add_settings_section callback is displayed here. 
                                             * For every new section we need to call settings_fields.
                                             * settings_fields($option_group)
                                             * settings_fields("header_section");
                                             */
                                            
                                            /**
                                             * all the add_settings_field callbacks is displayed here
                                             * do_settings_fields($page, $section)
                                             * do_settings_sections($page)
                                             */                                            
                                            do_settings_sections("spark");
                                            
                                            /**
                                             * syntax of submit_button function
                                             * submit_button($text, $type, $name, $wrap, $other_attributes)
                                             */
                                            submit_button('Connect App'); 
                                            
                                        ?>          
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                    
                </div>

                    <!-- <h1> Options</h1> -->
                    
                </div>
            </div>
        </div>
        <?
    }

    /**
     * 1. Define the section heading to describe your section by add_settings_section() function
     * 2. Add your settings field name by add_settings_field()
     * 3. Register settings fields to that settings fields by register_setting()
     */
    public function wpsparkconnector_display_options()
    {
        /**
         * section name, display name, callback to print description of section, page to which section is attached.
         * add_settings_section($id, $title, $callback, $page)
         */
        add_settings_section("header_section", "", array($this, "wpsparkconnector_display_header_options_content"), "spark");

        /**
         * setting name, display name, callback to print form element, page in which field is displayed, section to which it belongs.
         * last field section is optional.
         * add_settings_field($id, $title, $callback, $page, $section, $args);
         * in case if you need to add woocommerce settings field then add below code
         * add_settings_field("wpsparkconnector_woo_token", "WooCommerce Key", array($this, "wpsparkconnector_woo_token"), "spark", "header_section");
         * add_settings_field("wpsparkconnector_woo_secret", "WooCommerce Secret", array($this, "wpsparkconnector_woo_secret"), "spark", "header_section");
         */
        add_settings_field("spark_app_token", "Token", array($this, "wpsparkconnector_token"), "spark", "header_section");

        /**
         * section name, form element name, callback for sanitization
         * register_setting($option_group, $option_name, $sanitize_callback)
         */
        register_setting("header_section", "spark_app_token");
        register_setting("header_section", "tg_woo_key");
        register_setting("header_section", "tg_woo_secret");
    }
    
    /**
     * for heading section 
     * Heading title and
     * description text 
     */
    public function wpsparkconnector_display_header_options_content(){echo "";}

    /**
     * For settings body fields
     */
    public function wpsparkconnector_token()
    {
        /**
         * id and name of form element should be same as the setting name.
         */
        ?>
        <input type="text" 
            name="spark_app_token" 
            <?php echo get_option('spark_app_token') ? 'readonly': ''; ?> 
            id="spark_app_token" class="uk-input uk-form-width-large" style="width:60%" value="<?php echo get_option('spark_app_token'); ?>" 
        />
        <?php
    }
    public function wpsparkconnector_woo_token()
    {   
        ?>
        <input type="text" name="tg_woo_key" id="tg_woo_key" readonly style="width:60%" value="<?php echo get_option('tg_woo_key'); ?>" />
        <?php
    }
    
    public function wpsparkconnector_woo_secret()
    {   
        ?>
        <input type="text" name="tg_woo_secret" id="tg_woo_secret" readonly style="width:60%" value="<?php echo get_option('tg_woo_secret'); ?>" />
        <?php
    }


    public function wpsparkconnector_add_toolbar_items($admin_bar){
        if(get_option('spark_app_token')){
            $admin_bar->add_menu( array(
                'id'    => 'tg-connector-build',
                'title' => 'Build',
                'href'  => '#',
                'meta'  => array(
                    'title' => __('Build'),    
                    'class' => __('spark-build-button')
                ),
            ));
        }
    }

    public function wpsparkconnector_get_build_data($token){
        return $this->wpdb->get_results( $this->wpdb->prepare("SELECT * FROM {$this->table_name} WHERE token='%s' ORDER BY id DESC ", $token) );
    }
    public function wpsparkconnector_get_last_build_row(){
        return $this->wpdb->get_row( $this->wpdb->prepare("SELECT * FROM {$this->table_name} ORDER BY id DESC LIMIT %d", 1 ));
    }

}

?>