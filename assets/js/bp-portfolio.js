// buddypress portfolio main js

jQuery(document).ready(function () {
    jQuery('select.auto_submit_pform').live('change', function(){
        jQuery(this).closest('form').submit();
    });

    /* Media Library */
    jQuery(document).on('click', '#upload_image_lib', function (e) {
        var id = jQuery(this).attr('id');
        jQuery.magnificPopup.open({
            items: {
                src: '<div class="white-popup" style="height: 348px;"><iframe id="'+ id +'_frame" class="photo-dialog" src="'+ajaxurl+'?action=upload_portofolio_user_media_lib&pid='+bpcp_object.bpcp_project_id+'" width="100%" /></div>',
                type: 'inline'
            }
        });
    });

    /* File Dialog Core */
    function bpcp_media_uploader($btn,func,showcaption,type) {
        
        if (typeof type == 'undefined') {
            type = 'photo';
        }
   
        jQuery($btn).click(function(e){
            var id = jQuery(this).attr('id');
            jQuery.magnificPopup.open({
                items: {
                    src: '<div class="white-popup" style="height: 450px;"><iframe id="'+ id +'_frame" class="photo-dialog" src="'+ajaxurl+'?action=upload_bpcp_photos_dialog&caption='+showcaption+'&type='+type+'" width="100%" /></div>',
                    type: 'inline'
                }
            });
        });
        
        jQuery($btn).click(function(){
            window.bpcp_media_uploader_callback = func;
        });
        
                
    }

    jQuery('.bpcp_project_photo').magnificPopup({
        delegate: 'a',
        type: 'image',
        tLoading: 'Loading image #%curr%...',
        mainClass: 'mfp-img-mobile',
        gallery: {
            enabled: true,
            navigateByImgClick: true,
            preload: [0,1] // Will preload 0 - before current, and 1 after the current image
        },
        image: {
            tError: '<a href="%url%">The image #%curr%</a> could not be loaded.'
        }
    });
        
    
    window.bpcp_photo_dialog_callback = function(data) {
        
        jQuery.magnificPopup.close();
        window.bpcp_media_uploader_callback(data);
        
    };
    /* File Dialog Core */

    // project single page all tasks here
    jQuery('.bpcp-modal').magnificPopup({
        type: 'inline',
        preloader: false
    });
    jQuery(document).on('click', '.bpcp-modal-cancel', function (e) {
        e.preventDefault();
        jQuery.magnificPopup.close();
    });


    // form required field validation
    jQuery('.add-project-details #create_project').on('click', function() {
        jQuery('.add-project-details').find('small.error').remove();
        var valid1 = validation_empty('.add-project-details #project_title', bpcp_object.project_title_missing);
        var valid2 = true;
        if( bpcp_object.project_category_required == 'yes' && jQuery('.bp-category-multiselect').length ){
            valid2 = validation_empty('.add-project-details #project-category', bpcp_object.project_category_missing);
        }
        
        if (valid1 === false || valid2===false) {
            return false;
        } else {
            return true;
        }
    });
    jQuery('.add-project-details #project_finish').on('click', function() {
        jQuery('.add-project-details').find('small.error').remove();
        var valid1 = validation_empty('.add-project-details #project_visibility', bpcp_object.project_visibility_missing);
        if (valid1 === false) {
            return false;
        } else {
            return true;
        }
    });


    // upload image from lib
    bpcp_media_uploader(jQuery("#upload_image_lib"),function(data){
        
        jQuery('#display_cover_image').attr("src",data.thumbnail[0]);
        jQuery(".chosen-cover").val(data.id);
        
    },0);
    
    // upload image
    bpcp_media_uploader(jQuery("#upload_image"),function(data){
        
        
         var prepare_html = '<ul class="bpcp-li-left"><li>' +
                        '<img class="display-image" src="'+data.medium[0]+'"/></li>' +
                        '<li><input class="chosen_file" type="hidden" name="chosen_file[]" value="'+data.id+'" />' +
                        '<input type="hidden" name="atachment_id" value="'+data.id+'" />' +
                        '<a href="#" class="delete-image" value="'+bpcp_object.delete_btn+'</a>' +
                        '</li></ul>';
                        
        jQuery('.uploaded-images').append(prepare_html);
        
        jQuery('.uploaded-images').parent().find('.bpcp-media-upload-title').html(bpcp_object.upload_another_image);
        
    },0);

    //cover upload
    bpcp_media_uploader(jQuery("#upload_cover"),function(data){
                        
        jQuery('#display_cover_image').attr("src",data.thumbnail[0]);
        jQuery(".chosen-cover").val(data.id);
        
    },0);
   
    // remove image
    jQuery('a.delete-image').live('click', function (e) {
        e.preventDefault();
        // remove attachment by ajax
        var get_attachment_ID = jQuery(this).parent().find('input[name="atachment_id"]').val();
        var data = {
            action: 'bbpajax',
            task: 'remove_project_attachment',
            attachment_ID: get_attachment_ID
        };
        jQuery.post(ajaxurl, data, function (response) {
            // do whatever you want to do
        });

        jQuery(this).parent().parent().hide('slow', function(){
            jQuery(this).remove();
        });

    });


    // get query string values by name
    function getParameterByName(name) {
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(location.search);
        return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    }


    // empty check/validation
    function validation_empty(selector, message) {
        jQuery(selector).removeClass('error');
        if (jQuery(selector).val() == '' || jQuery(selector).val() == null ) {
            jQuery(selector).addClass('error');
            jQuery(selector).after('<small class="error">' + message + '</small>');
            return false;
        }
        else {
            return true;
        }
    }

    // email validation
    function validateEmail(selector, message) {
        jQuery(selector).removeClass('error');
        var email = jQuery(selector).val();
        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        if (!emailReg.test(email) || jQuery(selector).val() == '') {
            jQuery(selector).addClass('error');
            jQuery(selector).after('<small class="error">' + message + '</small>');
            return false;
        } else {
            return true;
        }
    }
    if(jQuery('.bp-category-multiselect').length) {
        jQuery('.bp-category-multiselect').select2({
          placeholder: bpcp_object.multiselect_placeholder
        });
    }
    // filter work
    jQuery('.bp-work-filter').change(function() {
        window.location.href = $(this).val();
    });
    
});


