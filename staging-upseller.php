<?php
/**
 * Plugin Name:       Staging Upseller
 * Plugin URI:        https://yorkcs.com/staging-upseller
 * Description:       Enables upsell opportunities on the WordPress login page.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            York Computer Solutions LLC
 * Author URI:        https://yorkcs.com/
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       staging-upseller
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'STAGINGUP_FILE',      __FILE__ );
define( 'STAGINGUP_FILE_BASE', plugin_basename( STAGINGUP_FILE ) );
define( 'STAGINGUP_DIR',       dirname( STAGINGUP_FILE_BASE ) );
define( 'STAGINGUP_PATH',      untrailingslashit( plugin_dir_path( STAGINGUP_FILE ) ) );
define( 'STAGINGUP_URL',       untrailingslashit( plugin_dir_url( STAGINGUP_FILE ) ) );


if ( ! class_exists( 'Staging_Upseller' ) ) {
    include_once dirname( STAGINGUP_FILE ) . '/includes/class-staging-upseller.php';
}

function staging_upseller() {
    return Staging_Upseller::instance();
}

staging_upseller();

