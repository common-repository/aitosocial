<?php

/*
 * Plugin Name:		AItoSocial
 * Plugin URI:      https://wordpress.org/plugins/aitosocial
 * Description: 	AI2Social revolutionizes the way you manage social media posts by seamlessly converting and scheduling your WordPress blog posts into engaging social media content using advanced AI technology like ChatGPT and integrated scheduling tools.
 * Version:			1.0.2
 * Author:			puredevs
 * Author URI:     	https://puredevs.com
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:     aitosocial
 * Domain Path:     /languages
 */
namespace FSPoster;

use FSPoster\App\Providers\Bootstrap;
defined( 'ABSPATH' ) or exit;
// modified plugin code
if ( function_exists( 'aits_fs' ) ) {
    aits_fs()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( 'aits_fs' ) ) {
        // Create a helper function for easy SDK access.
        function aits_fs() {
            global $aits_fs;
            if ( !isset( $aits_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $aits_fs = fs_dynamic_init( array(
                    'id'             => '13948',
                    'slug'           => 'aitosocial',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_fe942a36793f7d39c2b6e393ed06e',
                    'is_premium'     => false,
                    'premium_suffix' => 'Pro',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'trial'          => array(
                        'days'               => 30,
                        'is_require_payment' => false,
                    ),
                    'menu'           => array(
                        'slug'    => 'ai-poster',
                        'contact' => false,
                        'support' => false,
                    ),
                    'is_live'        => true,
                ) );
            }
            return $aits_fs;
        }

        // Init Freemius.
        aits_fs();
        // Signal that SDK was initiated.
        do_action( 'aits_fs_loaded' );
    }
    require_once __DIR__ . '/vendor/autoload.php';
    $networks = [
        'facebook',
        'instagram',
        'threads',
        'twitter',
        'planly',
        'linkedin',
        'pinterest',
        'webhook'
    ];
    foreach ( $networks as $network ) {
        require_once __DIR__ . '/App/SocialNetworks/' . $network . '/init.php';
    }
    new Bootstrap();
}