<?php

defined( 'ABSPATH' ) || exit;

class StagingUp_Settings {
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_options_page' ) );

        $page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
        if ( $page == 'staging-upseller' ) {
            add_action( 'admin_enqueue_scripts', function() {
                wp_enqueue_style( 'wp-color-picker' );
                wp_enqueue_script(
                    'wp-color-picker-alpha',
                    STAGINGUP_URL . '/lib/wp-color-picker-alpha-3-0-2/dist/wp-color-picker-alpha.min.js',
                    array( 'wp-color-picker' )
                );

                wp_enqueue_script(
                    'stagingup-prism',
                    STAGINGUP_URL . '/lib/prism-1.28.0/prism.js'
                );

                wp_register_script(
                    'stagingup-settings',
                    STAGINGUP_URL . '/includes/admin/assets/js/stagingup-settings.js',
                    array( 'jquery', 'wp-color-picker-alpha', 'stagingup-prism' )
                );

                wp_localize_script(
                    'stagingup-settings',
                    'localized_data',
                    array(
                        'ajax_url' => admin_url( 'admin-ajax.php' ),
                        'stagingup_import_settings_nonce' => wp_create_nonce( 'stagingup_import_settings_nonce' ),
                        'stagingup_export_settings_nonce' => wp_create_nonce( 'stagingup_export_settings_nonce' )
                    )
                );

                wp_enqueue_script( 'stagingup-settings' );

                wp_enqueue_style(
                    'stagingup-settings',
                    STAGINGUP_URL . '/includes/admin/assets/css/stagingup-settings.css',
                );
            } );
        }

        if ( ! empty ( $GLOBALS['pagenow'] )
            and ( 'options-general.php' === $GLOBALS['pagenow']
                or 'options.php' === $GLOBALS['pagenow']
            )
        ) {
            add_action( 'admin_init', array( __CLASS__, 'add_login_settings_section' ) );
        }
    }

    public static function add_options_page() {
        add_options_page(
            'Staging Upseller',
            'Staging Upseller',
            'manage_options',
            'staging-upseller',
            array( __CLASS__, 'render_staging_upseller_page' )
        );
    }

    public static function render_staging_upseller_page() {
        ?>
        <div class="wrap">
            <img src="<?php echo esc_url( STAGINGUP_URL . '/assets/images/staging-upseller-logo-full.svg' ); ?>" alt="Staging Upseller" style="width: 400px; height: auto; padding-top: 16px;" />
            <p><i>Developed by <a href="https://yorkcs.com/">York Computer Solutions LLC</a></i></p>
            <p>Please click the <i>Save Changes</i> button at the bottom of the page when you're finished changing settings.</p>

            <form action="options.php" method="post">
                <?php settings_fields( 'staging_upseller' ); ?>
                <?php do_settings_sections( 'staging-upseller' ); ?>

                <input class="button button-primary" name="Update" type="submit" value="<?php esc_attr_e( 'Save Changes', 'staging_upseller' ); ?>" />
            </form>
        </div>
        <?php
    }

    public static function add_login_settings_section() {
        register_setting( 'staging_upseller', 'staging_upseller' );


        $options = get_option( 'staging_upseller', array() );


        add_settings_section(
            'stagingup_import_export_section',
            'Import / Export',
            array( __CLASS__, '__return_false' ),
            'staging-upseller'
        );

        add_settings_field(
            'import_export',
            'Import / Export',
            function() {
                ?>
                <table class="form-table" style="max-width: 480px;">
                    <tbody>
                        <tr>
                            <td><label for="import">Import Settings</label></td>
                            <td>
                                <input class="stagingup-settings-uploader" id="stagingup-settings-import-uploader" type="file" />
                                <br>
                                <input class="button button-secondary" id="stagingup-settings-import" type="button" value="Import" />
                                <p id="stagingup-settings-import-uploader-status"></p>
                            </td>
                        </tr>

                        <tr>
                            <td><label for="export">Export Settings</label></td>
                            <td><input class="button button-secondary" id="stagingup-settings-export" type="button" value="Export Settings" /></td>
                            <p id="stagingup-settings-export-uploader-status"></p>
                        </tr>
                    </tbody>
                </table>
                    
                <?php
            },
            'staging-upseller',
            'stagingup_import_export_section'
        );



        add_settings_section(
            'stagingup_login_settings_section',
            'Login Settings',
            array( __CLASS__, 'render_login_settings_section' ),
            'staging-upseller'
        );

        // Show login to visitors
        add_settings_field(
            'show_login_to_visitors',
            'Show Login to Visitors?',
            array( __CLASS__, 'render_checkbox_field' ),
            'staging-upseller',
            'stagingup_login_settings_section',
            array(
                'name' => 'staging_upseller[show_login_to_visitors]',
                'value' => $options['show_login_to_visitors'] ?? false,
            )
        );

        // Logo upload
        add_settings_field(
            'login_logo',
            'Logo',
            array( __CLASS__, 'render_image_upload_base64_field' ),
            'staging-upseller',
            'stagingup_login_settings_section',
            array(
                'id'   => 'login-logo-uploader',
                'name' => 'staging_upseller[login_logo]',
                'value' => $options['login_logo'],
            )
        );

        // Header text
        add_settings_field(
            'login_headertext',
            'Header Text',
            array( __CLASS__, 'render_text_field' ),
            'staging-upseller',
            'stagingup_login_settings_section',
            array(
                'name' => 'staging_upseller[login_headertext]',
                'value' => $options['login_headertext'] ?? ''
            )
        );
        
        // Header URL
        add_settings_field(
            'login_headerurl',
            'Header Text URL',
            array( __CLASS__, 'render_text_field' ),
            'staging-upseller',
            'stagingup_login_settings_section',
            array(
                'name' => 'staging_upseller[login_headerurl]',
                'value' => $options['login_headerurl'] ?? ''
            )
        );

        // Username label
        add_settings_field(
            'login_username_label',
            'Username Label',
            array( __CLASS__, 'render_text_field' ),
            'staging-upseller',
            'stagingup_login_settings_section',
            array(
                'name' => 'staging_upseller[login_username_label]',
                'value' => $options['login_username_label'] ?? 'Username or Email Address'
            )
        );

        // Password label
        add_settings_field(
            'login_password_label',
            'Password Label',
            array( __CLASS__, 'render_text_field' ),
            'staging-upseller',
            'stagingup_login_settings_section',
            array(
                'name' => 'staging_upseller[login_password_label]',
                'value' => $options['login_password_label'] ?? 'Password'
            )
        );

        // Remember me label
        add_settings_field(
            'login_rememberme_label',
            'Remember Me Label',
            array( __CLASS__, 'render_text_field' ),
            'staging-upseller',
            'stagingup_login_settings_section',
            array(
                'name' => 'staging_upseller[login_rememberme_label]',
                'value' => $options['login_rememberme_label'] ?? 'Remember Me'
            )
        );

        // Log in button label
        add_settings_field(
            'login_log_in_button_label',
            'Log In Button Label',
            array( __CLASS__, 'render_text_field' ),
            'staging-upseller',
            'stagingup_login_settings_section',
            array(
                'name' => 'staging_upseller[login_log_in_button_label]',
                'value' => $options['login_log_in_button_label'] ?? 'Log In'
            )
        );

        // Text after log in button
        add_settings_field(
            'login_log_in_button_after_text',
            'Text After Log In Button',
            array( __CLASS__, 'render_text_field' ),
            'staging-upseller',
            'stagingup_login_settings_section',
            array(
                'name' => 'staging_upseller[login_log_in_button_after_text]',
                'value' => $options['login_log_in_button_after_text'] ?? ''
            )
        );

        // Text after log in button URL
        add_settings_field(
            'login_log_in_button_after_url',
            'Text After Log In Button URL',
            array( __CLASS__, 'render_text_field' ),
            'staging-upseller',
            'stagingup_login_settings_section',
            array(
                'name' => 'staging_upseller[login_log_in_button_after_url]',
                'value' => $options['login_log_in_button_after_url'] ?? ''
            )
        );
        
        

        // Login page layout inputs
        add_settings_field(
            'login_layout_area',
            'Login Page Layout',
            array( __CLASS__, 'render_login_page_layout_inputs' ),
            'staging-upseller',
            'stagingup_login_settings_section'
        );

        // Input label placement
        add_settings_field(
            'input_label_placement',
            'Input Label Placement',
            array( __CLASS__, 'render_select_field' ),
            'staging-upseller',
            'stagingup_login_settings_section',
            array(
                'name' => 'staging_upseller[login_input_label_placement]',
                'value' => $options['login_input_label_placement'],
                'options' => array(
                    'above_input' => 'Above Input',
                    'placeholder' => 'Placeholder'
                )
            )
        );

        // Body color
        add_settings_field(
            'body_color',
            'Body Color',
            array( __CLASS__, 'render_color_field' ),
            'staging-upseller',
            'stagingup_login_settings_section',
            array(
                'name' => 'staging_upseller[login_body_color]',
                'value' => $options['login_body_color'],
            )
        );

        // Sidebar color
        add_settings_field(
            'sidebar_color',
            'Sidebar Color',
            array( __CLASS__, 'render_color_field' ),
            'staging-upseller',
            'stagingup_login_settings_section',
            array(
                'name' => 'staging_upseller[login_sidebar_color]',
                'value' => $options['login_sidebar_color'],
            )
        );

        // Ad area settings
        for ( $i = 0; $i < 8; $i++ ) {
            // heading
            $ad_area_field_name = 'login_ad_area_' . $i . '_subheading';
            add_settings_field(
                $ad_area_field_name,
                '',
                array( __CLASS__, 'render_subsection_heading' ),
                'staging-upseller',
                'stagingup_login_settings_section',
                array(
                    'value' => 'Ad Area ' . ( $i + 1 ),
                )
            );

            // iframe
            $ad_area_field_name = 'login_ad_area_' . $i . '_iframe_url';
            add_settings_field(
                $ad_area_field_name,
                'iFrame URL',
                array( __CLASS__, 'render_login_ad_area_iframe_url_field' ),
                'staging-upseller',
                'stagingup_login_settings_section',
                array(
                    'name' => 'staging_upseller[' . $ad_area_field_name . ']',
                    'value' => $options[ $ad_area_field_name ] ?? '',
                )
            );

            // enable scrolling
            $ad_area_field_name = 'login_ad_area_' . $i . '_scrolling_enabled';
            add_settings_field(
                $ad_area_field_name,
                'Enable Scrolling?',
                array( __CLASS__, 'render_checkbox_field' ),
                'staging-upseller',
                'stagingup_login_settings_section',
                array(
                    'name' => 'staging_upseller[' . $ad_area_field_name . ']',
                    'value' => $options[ $ad_area_field_name ] ?? '',
                )
            );

            // area background color
            $ad_area_field_name = 'login_ad_area_' . $i . '_background_color';
            add_settings_field(
                $ad_area_field_name,
                'Area Background Color',
                array( __CLASS__, 'render_color_field' ),
                'staging-upseller',
                'stagingup_login_settings_section',
                array(
                    'name' => 'staging_upseller[' . $ad_area_field_name . ']',
                    'value' => $options[ $ad_area_field_name ],
                )
            );
        }

        // heading
        add_settings_field(
            'login_css_editor_subheading',
            '',
            array( __CLASS__, 'render_subsection_heading' ),
            'staging-upseller',
            'stagingup_login_settings_section',
            array(
                'value' => '&nbsp;'
            )
        );

        add_settings_field(
            'login_css',
            'Login Page CSS',
            array( __CLASS__, 'render_css_editor_field' ),
            'staging-upseller',
            'stagingup_login_settings_section',
            array(
                'name' => 'staging_upseller[login_css]',
                'value' => $options[ 'login_css' ]
            )
        );
    }

    public static function render_image_upload_base64_field( $args ) {
        ?>
        <input class="stagingup-image-upload-base64" id="<?php echo esc_attr( $args['id'] ); ?>" type="file" />
        <input name="<?php echo esc_attr( $args['name'] ); ?>" type="hidden" value="<?php echo esc_attr( $args['value'] ); ?>" />
        <img class="stagingup-image-upload-preview" style="background-color: #e9e9e9;" src="<?php echo esc_attr( $args['value'] ); ?>" />
        <?php
    }

    public static function render_css_editor_field( $args ) {
        ?>
        <div class="stagingup-css-editor-wrap" style="position: relative; height: 500px;">
            <textarea placeholder="Enter Login Page CSS" id="editing" name="<?php echo esc_attr( $args['name'] ); ?>" spellcheck="false" oninput="update(this.value); sync_scroll(this);" onscroll="sync_scroll(this);" onkeydown="check_tab(this, event);"><?php echo htmlspecialchars_decode( esc_html( $args['value'] ) ); ?></textarea>
            <pre id="highlighting" aria-hidden="true">
                <code class="language-css" id="highlighting-content">
                </code>
            </pre>
        </div>
        <?php
    }

    public static function render_subsection_heading( $args ) {
        ?>
        <hr>
        <h3 style="margin-bottom: 0;"><?php echo esc_html( $args['value'] ); ?></h3>
        <?php
    }
    
    public static function render_text_field( $args ) {
        $name = esc_attr( $args['name'] );
        $value = esc_attr( $args['value'] );
        ?>
        <input id="<?php echo $name; ?>" name="<?php echo $name; ?>" type="text" value="<?php echo $value; ?>" <?php echo 'size="' . esc_attr( $args['size'] ?? '' ) . '"'; ?> />
        <?php
    }

    public static function render_checkbox_field( $args ) {
        $name = esc_attr( $args['name'] );
        $value = esc_attr( $args['value'] ) == 'on' ? true : false;
        ?>
        <input id="<?php echo esc_attr( $name ); ?>" name="<?php echo $name; ?>" type="checkbox" <?php checked( $value, true ); ?>" />
        <?php
    }

    public static function render_color_field( $args ) {
        $name = esc_attr( $args['name'] );
        $value = esc_attr( $args['value'] );
        ?>
        <input id="<?php echo $name; ?>" class="color-field" name="<?php echo $name; ?>" data-alpha-enabled="true" type="text" value="<?php echo $value; ?>" />
        <?php
    }

    public static function render_select_field( $args ) {
        $name = esc_attr( $args['name'] );
        $value = esc_attr( $args['value'] );
        ?>
        <select name="<?php echo $name; ?>">
            <?php
            foreach ( $args['options'] as $option_value => $option_label ) {
                ?>
                <option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $value, $option_value ); ?> ><?php echo esc_html( $option_label ); ?></option>
                <?php
            }
            ?>
        </select>
        <?php
    }

    public static function render_login_page_layout_inputs() {
        $options = get_option( 'staging_upseller' );

        $layout_options = array(
            array(
                'label' => 'Empty',
                'value' => ''
            ),
            array(
                'label' => 'Login Sidebar',
                'value' => 'login_sidebar'
            ),
            array(
                'label' => 'Ad Area 1',
                'value' => 'ad_area_0'
            ),
            array(
                'label' => 'Ad Area 2',
                'value' => 'ad_area_1'
            ),
            array(
                'label' => 'Ad Area 3',
                'value' => 'ad_area_2'
            ),
            array(
                'label' => 'Ad Area 4',
                'value' => 'ad_area_3'
            ),
            array(
                'label' => 'Ad Area 5',
                'value' => 'ad_area_4'
            ),
            array(
                'label' => 'Ad Area 6',
                'value' => 'ad_area_5'
            ),
            array(
                'label' => 'Ad Area 7',
                'value' => 'ad_area_6'
            ),
            array(
                'label' => 'Ad Area 8',
                'value' => 'ad_area_7'
            ),
        );

        for ( $i = 0; $i < 9; $i++ ) {
            ?>
            <select id="login_layout_area_<?php echo esc_attr( $i ); ?>" name="staging_upseller[login_layout_area_<?php echo esc_attr( $i ); ?>]">
                <?php
                foreach ( $layout_options as $option ) {
                    ?>
                    <option value="<?php echo esc_attr( $option['value'] ); ?>" <?php selected( $options['login_layout_area_' . esc_attr( $i )], $option['value'] ); ?> ><?php echo esc_html( $option['label'] ); ?></option>
                    <?php
                }
                ?>
            </select>
            <?php

            if ( ($i + 1) % 3 == 0 && $i > 1 ) {
                ?>
                <br>
                <?php
            }
        }

        ?>
        <p>Quick Configurations:</p>
        <input class="button button-secondary" type="button" id="btn-sidebar-left-ad-area-1-right" value="Sidebar Left, Ad Area 1 Right" />
        <input class="button button-secondary" type="button" id="btn-sidebar-right-ad-area-1-left" value="Sidebar Right, Ad Area 1 Left" />
        <input class="button button-secondary" type="button" id="btn-sidebar-center-ad-area-1-left-ad-area-2-right" value="Sidebar Center, Ad Area 1 Left, Ad Area 2 Right" />
        <?php
    }

    public static function render_login_ad_area_iframe_url_field( $args ) {
        $name = esc_attr( $args['name'] );
        $value = esc_attr( $args['value'] );
        ?>
        <input id="<?php echo $name ?>" name="<?php echo $name; ?>" type="text" value="<?php echo $value; ?>" />
        <?php
    }
}
StagingUp_Settings::init();
