<?php

function bpcp_portfolio_project_screen() {
	global $bp;

    $get_action = ( isset($_GET['bpcp_action']) && !empty($_GET['bpcp_action']) ) ? $_GET['bpcp_action'] : '';
    switch($get_action){
        case 'add_project':
            add_action( 'bp_template_content', 'bpcp_template_add_project' );
            break;

        case 'project_content':
            add_action('wp_enqueue_scripts', 'bpcp_media_upload_scripts');
            add_action( 'bp_template_content', 'bpcp_template_project_content' );
            break;

        case 'project_cover':
            add_action('wp_enqueue_scripts', 'bpcp_media_upload_scripts');
            add_action( 'bp_template_content', 'bpcp_template_project_cover' );
            break;

        case 'project_settings':
            add_action( 'bp_template_content', 'bpcp_template_project_settings' );
            break;

        default:
            add_action( 'bp_template_content', 'bpcp_template_project_index' );
            break;
    }

    bp_core_load_template( apply_filters( 'bpcp_portfolio_project_screen', 'members/single/plugins' ) );
}

function bpcp_template_add_project(){
    do_action('template_notices');
    bpcp_portfolio_load_template( 'project/add_project' );
}

function bpcp_template_project_content(){
    do_action('template_notices');
    add_thickbox();
    bpcp_portfolio_load_template( 'project/project_content' );
}

function bpcp_template_project_cover(){
    do_action('template_notices');
    bpcp_portfolio_load_template( 'project/project_cover' );
}

function bpcp_template_project_settings(){
    do_action('template_notices');
    bpcp_portfolio_load_template( 'project/project_settings' );
}

function bpcp_template_project_index(){
    do_action('template_notices');
    bpcp_portfolio_load_template( 'project/index' );
}
