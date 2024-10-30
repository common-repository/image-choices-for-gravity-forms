<?php
/*
Plugin Name: Image Picker For Gravity Forms
Plugin Url: https://pluginscafe.com
Version: 1.1.1
Description: A simple and nice plugin to add images easily on gravity forms radio and checkbox field.
Author: KaisarAhmmed
Author URI: https://pluginscafe.com
License: GPLv2 or later
Text Domain: gravityforms
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}


define( 'GF_PC_IMAGE_CHOICES_ADDON_VERSION', '1.1.1' );
add_action( 'gform_loaded', array( 'GF_IC_AddOn_Bootstrap', 'load' ), 5 );
class GF_IC_AddOn_Bootstrap
{
    public static function load()
    {
        if ( !method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }
        // are we on GF 2.5+
        define( 'PC_IC_GF_MIN_2_5', version_compare( GFCommon::$version, '2.5-dev-1', '>=' ) );
        require_once 'class-gfImgChoice.php';
        GFAddOn::register( 'GFImgChoiceAddon' );
    }

}
function GF_Img_Choice_Field() 
{
    return GFImgChoiceAddon::get_instance();
}

