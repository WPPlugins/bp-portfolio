<?php

/**
 * Set upload file type
 *
 * @param $mime_types
 * @return array
 */
function bpcp_myme_types($mime_types){
    //Creating a new array will reset the allowed filetypes
    $mime_types = array(
        'jpg|jpeg|jpe'                 => 'image/jpeg',
        'gif'                          => 'image/gif',
        'png'                          => 'image/png',
        'bmp'                          => 'image/bmp',
        'tif|tiff'                     => 'image/tiff',
        'ico'                          => 'image/x-icon',
    );

    return $mime_types;
}
add_filter('upload_mimes', 'bpcp_myme_types', 1, 1);


/**
 * Add specific CSS class by filter
 * @param $classes
 * @return array
 */
function bpcp_body_class_names( $classes ) {
    global $post;

    // add 'class-name' to wip page
    if( bp_portfolio()->setting( 'bpcp-projects-enable' ) == 'on' &&
        bp_portfolio()->option('all-portfolio-page') &&
        is_page( bp_portfolio()->option('all-portfolio-page') ) &&
        $post->ID == bp_portfolio()->option('all-portfolio-page')
    ){
        $classes[] = 'portfolio-global-page';
        $classes[] = 'global-page-layout';
    }

    // return the $classes array
    return $classes;
}
add_filter( 'body_class', 'bpcp_body_class_names' );
