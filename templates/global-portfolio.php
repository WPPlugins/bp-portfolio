<?php
/**
 * @package WordPress
 * @subpackage BP Portfolio
 *
 */

/*
 * The template file to display the content of 'portfolio page'.
 * Making changes to this file is not advised.
 * To override this template file:
 *  - create a folder 'bp-portfolio' inside your active theme (or child theme)
 *  - copy this file and place in the folder mentioned above
 *  - and make changes to the new file (the one you just copied into your theme).
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<?php echo do_shortcode('[project_loop/]'); ?>
