<?php
/*
 * Amazing dialog for file uploading.
 **/

if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}


class bpcp_file_dialog {

	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct() {
		add_action("init",array($this,"hooks"),5);
	}

	public function hooks() {

		//On Init
		add_action("wp_ajax_upload_bpcp_photos_dialog",array($this,"dialog_content"));
		add_action("wp_ajax_upload_portofolio_user_media_lib",array($this,"portofolio_user_media_lib"));
		add_action("wp_ajax_upload_photos_lib",array($this,"upload_lib_attachment"),6);
		add_action( 'wp_ajax_nopriv_upload_photos_lib', array($this,"upload_lib_attachment"),6 );
		add_action("wp_ajax_upload_photos_attachment",array($this,"upload_attachment"),6);
		add_action( 'wp_ajax_nopriv_upload_photos_attachment', array($this,"upload_attachment"),6 );



	}
        
        /*
	 * Output the media library content.
         */
        public function portofolio_user_media_lib() {
            ?>
                <!DOCTYPE html>
		<html>
		<head>
                    <meta charset="utf-8">
                        <title><?php _e("Library","bp-portfolio"); ?></title>
                    <style>
                        .pf-user-lib-wrap {
                            height: 308px;
                            overflow-y: auto;
                        }
                        .pf-user-lib-wrap img { height: auto; width: 145px; }
                        .pf-lib-empty { text-align: center; font-size: 20px; }
                    </style>
                    <script type='text/javascript' src='//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js'></script>
                    <script type="text/javascript">
                        jQuery("#document").ready(function() {
                            jQuery('.pf-user-lib-wrap a').on('click',function() {
                                var data = {
                                            action: 'upload_photos_lib',
                                            img_id: jQuery(this).data('imgid')
                                        };

                                jQuery.ajax( {
                                    type: "POST",
                                    url: '<?php echo admin_url('admin-ajax.php'); ?>?action=upload_photos_lib&type=photo',
                                    data: data,
                                    success: function ( response ) {
                                        var data_obj = jQuery.parseJSON(response);
                                        parent.bpcp_photo_dialog_callback(data_obj);
                                    }
                                } );    

                          });
                        });
                    </script>
                </head>
                <body class="m-upload-lib">
                    <?php
                    $pid = $_GET['pid'];
                    $user_images =bpcp_get_attachment_detail($pid, 'portfolio-thumbnail', 'all');
                    
                    if ( !empty($user_images) ) { ?>
                        <div class="pf-user-lib-wrap"><?php
                            foreach ( $user_images as $user_image ) {
                                ?>
                                <a href="#" data-imgid="<?php echo $user_image['ID']; ?>"><img src="<?php echo $user_image['src']; ?>" /></a>
                                <?php
                            } ?>
                        </div><?php
                    } else{
                        ?><p class="pf-lib-empty"><?php _e('You have not uploaded any photos yet','bp-portfolio'); ?></p><?php
                    }?>
                </body>
		</html>
            <?php
            die();
        }

	/*
	 * Output the uploader dialog content.
	 **/
	public function dialog_content() {
		global $section_cpt;

		?>
		<!DOCTYPE html>
		<html>
		<head>
        <meta charset="utf-8">
		<title><?php _e("Photo","bp-portfolio"); ?></title>
        <style>
        textarea,input,button,label,#dragzone {
            font-family: sans-serif;
        }
		/* Below CSS is Important */
		div#uploaded_media_details {
			z-index: 1000000;
			position: relative;
		}
        </style>
        <link rel="stylesheet" id="mo-photos-style-css" href="<?php echo apply_filters('bpcp_popup_stylesheet', BP_PORTFOLIO_PLUGIN_URL."/assets/css/bp-portfolio.min.css"); ?>" type="text/css" media="all">
		<script type='text/javascript' src='//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js'></script>
		<script type='text/javascript' src='<?php echo BP_PORTFOLIO_PLUGIN_URL."/assets/js/plupload/plupload.full.min.js"; ?>'></script>
		<script type="text/javascript">
		<!--
			jQuery("#document").ready(function() {

				var uploader = new plupload.Uploader({
					runtimes : 'html5,flash,silverlight,html4',
					browse_button : 'dragzone', // you can pass an id...
					drop_element : 'dragzone',
					multi_selection : false,
					url : '<?php echo admin_url('admin-ajax.php'); ?>?action=upload_photos_attachment&type=<?php echo $_GET["type"]; ?>',
					flash_swf_url : '<?php echo BP_PORTFOLIO_PLUGIN_URL; ?>/assets/js/plupload/Moxie.swf',
					silverlight_xap_url : '<?php echo BP_PORTFOLIO_PLUGIN_URL; ?>/assets/js/plupload/Moxie.xap',

					filters : {
						max_file_size : '10mb',
						mime_types: [
						]
					},
					init: {
						PostInit: function() {

						},
						FilesAdded: function(up, files) {
							uploader.start();
						},
						UploadProgress: function(up, file) {
							jQuery(".progress").show();
							jQuery(".progress .bar").css("width",file.percent);
						},
						FileUploaded : function(up,file,response) {
							try {
							jQuery.globalEval(response.response);
							} catch(e) {}
						},
						Error: function(up, err) {
							jQuery(".progress").hide();
						}
					}
				});
				uploader.init();


				jQuery("#photo_submit_button").click(function() {
					if (typeof window.media_data.id == 'undefined') {
						alert(jQuery("#enter_media_upload_txt").text());
						return false;
					}
					window.media_data.caption = jQuery(".description").val();
					parent.bpcp_photo_dialog_callback(window.media_data);
				});

			});
		//-->
		</script>
		<?php do_action('bp_portfolio_upload_scripts'); ?>
		</head>
		<div id="upload_media_messages"></div>
		<body class="m-upload-dialog">
		<form target="_self" method="post" enctype="multipart/form-data" id="photo_form_dialog">

            <?php wp_nonce_field('mod-photos-form','mod-photos-form'); ?>

			<div id="upload_photo_attachment_message"></div>

            <?php $type = ( $_GET["type"] == 'song' )?'audio':$_GET["type"]; ?>

            <?php if($_GET["caption"] == "1"): ?>
			<div class="photofieldset">
            <label><?php _e('Say something about the '.$type.'...', "bp-portfolio"); ?></label>
			<textarea name="photo_caption" class="input description"><?php echo @$_POST["photo_description"]; ?></textarea>
			</div>
            <?php endif; ?>

			<div class="photofieldset">

                <div id="uploaded_media_details">

				</div>

				<div id="dragzone">
				  <div class="legend">
                    <div class="text">
                        <?php _e("Drop file anywhere to upload", "bp-portfolio"); ?>
                    </div>
                    <div class="icon"></div>
                    <div class="link">
                        <?php _e("or select file", "bp-portfolio"); ?>
                    </div>
				  </div>

				  <!-- You can also use <progress> tag of HTML5: -->
				  <p class="progress">
				    <span class="bar"></span>
				  </p>
				</div>

			</div>

			<div class="photofieldset">
			    <button id="photo_submit_button" type="button"><?php echo _e("Upload ".$type,"bp-portfolio"); ?></button>
			</div>

			<div style="display:none">
                            <span id="enter_media_upload_txt"><?php $this->core_alerts(__('Please select the file to upload.','bp-portfolio'),'error',true); ?></span>
			</div>

			<input type="hidden" name="upload_photo_form_verify" value="1" />
		</form>

		</body>
		</html>
		<?php

		die();
	}

	function upload_attachment_allowed_mimes($existing_mimes){

            if($_GET["type"] == "song") {

                return array(
                            'mp3|m4a|m4b'                  => 'audio/mpeg',
                            'ra|ram'                       => 'audio/x-realaudio',
                            'wav'                          => 'audio/wav',
                            'ogg|oga'                      => 'audio/ogg',
                            'mid|midi'                     => 'audio/midi',
                            'wma'                          => 'audio/x-ms-wma',
                            'wax'                          => 'audio/x-ms-wax',
                            'mka'                          => 'audio/x-matroska'
                            );

            } else {

                return array(
                            // Image formats
                            'jpg|jpeg|jpe'                 => 'image/jpeg',
                            'gif'                          => 'image/gif',
                            'png'                          => 'image/png'
                );

            }
	}
        
        function upload_lib_attachment() {

                get_currentuserinfo();

                @session_start();
                
                if ( isset($_REQUEST['img_id']) ) {
                    $attachment_id = $_REQUEST['img_id'];
                } else {
                    die();
                }

                # lets check if current user is logged in.
                if(!is_user_logged_in() ) {
                        $html = core_alerts(__('You must be logged in to add content.','bp-portfolio'),'error');
                        echo 'jQuery("#upload_photo_attachment_message").html('.json_encode($html).');';
                        # Quick return and terminate more execution
                        die();
                }


                $thumbnail = wp_get_attachment_image_src( $attachment_id );
                $medium = wp_get_attachment_image_src( $attachment_id , 'medium');
                $large = wp_get_attachment_image_src( $attachment_id , 'large');
                $full = wp_get_attachment_image_src( $attachment_id , 'full');

                $details = array(
                  'id'  =>  $attachment_id,
                  'medium'  => $medium,
                  'large'   => $large,
                  'full'    => $full,
                  'thumbnail'    => $thumbnail,
                  'url' => wp_get_attachment_url( $attachment_id )
                );

                $bpcp_uploaded_image = wp_get_attachment_image_src( $attachment_id, apply_filters('bpcp_filter_lib_image', 'thumbnail') );

                $html = '<img src="'. $bpcp_uploaded_image[0] .'" alt="thumbnail" class="thumbnail" /> <a href="#" class="remove_media">'.__("Remove", "bp-portfolio").'</a>';

                echo json_encode($details);
                die();
        }

	function upload_attachment() {
            global $section_cpt , $current_user;

            get_currentuserinfo();

            @session_start();

            # lets check if current user is logged in.
            if(!is_user_logged_in() ) {
                    $html = core_alerts(__('You must be logged in to add content.','bp-portfolio'),'error');
                    echo 'jQuery("#upload_photo_attachment_message").html('.json_encode($html).');';
                    # Quick return and terminate more execution
                    die();
            }

            if (!empty($_FILES['file']) and is_uploaded_file($_FILES['file']['tmp_name'])) {
              // Regular multipart/form-data upload.
              $name = $_FILES['file']['name'];
              $data = file_get_contents($_FILES['file']['tmp_name']);
            } else {
              // Raw POST data.
              $name = urldecode(@$_SERVER['HTTP_X_FILE_NAME']);
              $data = file_get_contents("php://input");
            }

            if (empty($name)) {
                    $html = $this->core_alerts(__('File you have uploaded is corrupt try another file.','bp-portfolio'),'error');
                    echo 'jQuery("#upload_photo_attachment_message").html('.json_encode($html).');';
                    return false;
            }

            $tmpfname = wp_tempnam($name);

            //Add file data to temp file
            add_filter('upload_mimes', array($this,'upload_attachment_allowed_mimes'));
            file_put_contents($tmpfname,$data);

            //convert to $_post
            $file_array['name'] = $name;
            $file_array['tmp_name'] = $tmpfname;


	    if($_GET["type"] != "song") { //if its not song type then must be image.

		list($width, $height) = getimagesize($tmpfname);

		$min_width_allowed = apply_filters("bp-portfolio-min-image-width",300);
		$min_height_allowed = apply_filters("bp-portfolio-min-image-height",250);

		if($width < $min_width_allowed OR $height < $min_height_allowed) {
		    $html = $this->core_alerts(sprintf(sprintf(__('Image must be at least %1$sx%2$s','bp-portfolio'),$min_width_allowed,$min_height_allowed),$_GET["type"]),'error');
		    echo 'jQuery("#upload_photo_attachment_message").html('.json_encode($html).');
		    setTimeout(function(){
		    jQuery("#upload_photo_attachment_message").html("");
		     },2000);
		    ';
		    return false;
		}

	    }

            $attachment_id = media_handle_sideload( $file_array, 0, '' );


            if ( is_wp_error( $attachment_id ) ) {
                    $types = __('jpg, jpeg, jpe, png, gif', 'bp-portfolio');
                    if($_GET["type"] == 'song' )
                        $types = __('mp3, m4a, m4b, ra, ram, ogg, oga, mid, midi, wav, wma, wax, mka', 'bp-portfolio');

                    $html = $this->core_alerts(sprintf(__('%s has failed to upload due to an error. Sorry, this file type is not permitted. Please upload one of the following file types: %s.','bp-portfolio'),
                        $_FILES['file']['name'],
                        $types
                        ),'error');
                    echo 'jQuery("#upload_photo_attachment_message").html('.json_encode($html).');';
                    return false;
            }


            $thumbnail = wp_get_attachment_image_src( $attachment_id );
            $medium = wp_get_attachment_image_src( $attachment_id , 'medium');
            $large = wp_get_attachment_image_src( $attachment_id , 'large');
            $full = wp_get_attachment_image_src( $attachment_id , 'full');

            $details = array(
              'id'  =>  $attachment_id,
              'medium'  => $medium,
              'large'   => $large,
              'full'    => $full,
              'thumbnail'    => $thumbnail,
              'url' => wp_get_attachment_url( $attachment_id )
            );

            $bpcp_uploaded_image = wp_get_attachment_image_src( $attachment_id, apply_filters('bpcp_filter_uploaded_image', 'thumbnail') );

            $html = '<img src="'. $bpcp_uploaded_image[0] .'" alt="thumbnail" class="thumbnail" /> <a href="#" class="remove_media">'.__("Remove", "bp-portfolio").'</a>';

            if($_GET["type"] == "song") {
                $html = '<!--[if lt IE 9]><script>document.createElement(\'audio\');</script><![endif]--><audio class="wp-audio-shortcode" preload="none" controls="controls"><source type="audio/mpeg" src="'.$details["url"].'?_=1" /><a href="'.$details["url"].'">'.$details["url"].'</a></audio>';
            }

            echo '
            jQuery("#dragzone").hide(); jQuery("#uploaded_media_details").show().html('.json_encode($html).');
            jQuery("#uploaded_media_details").find(".remove_media").click(function() {
                    jQuery("#dragzone").show();
                    jQuery("#uploaded_media_details").html("").hide();
            });

            window.media_data =  '.json_encode($details).';

            ';

            die();
    }

    function core_alerts($msg,$type) {

        return  '<div class="alertmessage '.$type.'"><p>'.$msg.'</p></div>';

    }


}


new bpcp_file_dialog();
