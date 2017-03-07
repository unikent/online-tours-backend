/*
 alert messages for ajax responses
 */
APP_DATA.content_messages = {
    updateSuccess: 'Content Updated',
    updateFail: 'Failed to update! Your changes have not been saved, please check your input and try again.',
    storeFail: 'Failed to save! Please check your input and try again.',
    deleteFail: 'Deletion Failed! Please try again.',
    detachFail: 'Detach Failed! Please try again.'
};

/*
 dirty fields plugin config
 */
APP_DATA.content_df_options= {
    denoteDirtyForm: true,
    denoteDirtyFields:true,
    trimText:true,
    formChangeCallback:function($dirty,$fields){
       APP_UTIL.content.updateContentFormState($(this),$dirty,$fields);
    }
};

/*
 medium editor options
 */
APP_DATA.medium_options = {
    autoLink: true,
    imageDragging: false,
    targetBlank: true,
    toolbar: {
        buttons: [
            'bold', 
            'italic', 
            'anchor',
            {
                name: 'h1',
                action: 'append-h3',
                aria: 'header type 1',
                tagNames: ['h3'],
                contentDefault: '<b>H1</b>',
                classList: ['custom-class-h1'],
                attrs: {
                    'data-custom-attr': 'attr-value-h1'
                }
            },
            {
                name: 'h2',
                action: 'append-h4',
                aria: 'header type 2',
                tagNames: ['h4'],
                contentDefault: '<b>H2</b>',
                classList: ['custom-class-h2'],
                attrs: {
                    'data-custom-attr': 'attr-value-h2'
                }
            },
            'orderedlist', 
            'unorderedlist'
        ]
    },
    paste:{
        forcePlainText: true
    },
    buttonLabels:'fontawesome'
};


$(document).ready(function(){


    // init all forms
    $('.content-item form').each(function(){
        APP_UTIL.content.initForm($(this));
    });


    $content_list = $('.content-list');
    

    // update dirty fields state instantly on change (keyup, rather than blur/submit)
    $content_list.on('keyup','.content-item form input, .content-item form textarea, .content-item form select',function(){
        $.fn.dirtyFields.updateFormState($(this).closest('form'));
    });


    /*
     submit update of existing content form
     */
    $content_list.on('submit','.content-item form',function(e){
        e.preventDefault();
        $form = $(this).closest('form');

        var formData = new FormData($form[0]); //create multipart formdata object to include any file(s) upload(s)

        $id = $form.find('input[name="id"]').first().val();
        $url = onlinetours.action('ContentController@update',{content:$id});
        $.ajax({
            url: $url,
            type:'POST',  //POST rather than PATCH because looks like laravel cant handle multipart form submit on patch
            dataType:'json',
            contentType: false,
            processData:false,
            mimeType:"multipart/form-data",
            data:formData,
            success:function($data){
                if(typeof $data !=='undefined') {
                    if (typeof $data.success !== 'undefined' && $data.success) {
                        $p = $form.parent();
                        $p.html($data.html);
                        $form = $p.find('form')
                        APP_UTIL.content.initForm($form);
                        APP_UTIL.content.showResponseMessage($form.find('.content-alerts').first(), APP_DATA.content_messages.updateSuccess, 'success')
                    } else {
                        APP_UTIL.content.showResponseMessage($form.find('.content-alerts').first(), APP_DATA.content_messages.updateFail, 'danger',$data);
                    }
                }
            },
            error:function(xhr){
                if(xhr.status=='405'){
                    xhr.responseJSON = {errors:"Upload too large."};
                }
                APP_UTIL.content.showResponseMessage($form.find('.content-alerts').first(),APP_DATA.content_messages.updateFail,'danger',xhr.responseJSON);
            }
        });
    });

    /*
       delete/cancel button button handler
     */
    $content_list.on('click','.content-item form .content-delete',function(e) {
        e.preventDefault();
        APP_DATA.deleting_content = null;
        $form = $(this).closest('form');

        if($form.hasClass('dirtyForm')){
            $id = $form.find('input[name="id"]').first().val();
            $url = onlinetours.action('ContentController@edit',{content:$id});

            $.ajax({
                url: $url,
                type:'GET',
                dataType:'json',
                success:function($data){
                    if($data.success) {
                        $p = $form.parent();
                        $p.html($data.html);
                        $new = $p.find('form').first();
                        APP_UTIL.content.initForm($new);
                    }else{

                    }
                },
                error:function(){
                }
            });
        }else{
            $m = $('#contentDeleteConfirm');
            $m.modal('show');
            $id = $form.find('input[name="id"]').first().val();
            APP_DATA.deleting_content = $id;
        }
        return false;
    });

    // add content button
    $('#add-content-btn').click(function(e){
        e.preventDefault();
        $(this).slideUp();
        $('#new-content-panel').slideDown(400,function(){APP_UTIL.content.scrollIntoView($('#new-content-panel'))});
        $('#choose-content-type').addClass('open');
    });

    //back button in new content form area
    $('#new-content-back').click(function(e) {
        e.preventDefault();
        if($('#choose-content-type').hasClass('open')){
            $('#new-content-panel').slideUp();
            $('#add-content-btn').slideDown();
        }
        if($('#new-content-form').hasClass('open')){
            $(this).html('<i class="kf-chevron-left"></i> Cancel');
            $('#new-content-form').removeClass('open').slideUp();
            $('#choose-content-type').addClass('open').slideDown();
        }
    });

    // get correct form for chosen new content type
    $('#choose-content-type-next').click(function(e){
        e.preventDefault();

        $('#new-content-back').html('<i class="kf-chevron-left"></i> Back');

        $('#editContent > div').html('');

        $type = $('#new-content-type').first().val();
        $.ajax({
            url: onlinetours.action('ContentController@create'),
            type:'GET',
            data:{type:$type},
            dataType:'json',
            success:function($data){
                if($data.success) {
                    $($data.html).appendTo($('#editContent > div'));
                    $form = $('#editContent > div form');
                    APP_UTIL.content.initForm($form);
                    $('#existingContent .content-search').select2("val", "");
                    $('#new-content-form').find('span.typeName').html($data.type_name);
                    $('#new-content-form').addClass('open').slideDown();
                    $('#choose-content-type').removeClass('open').slideUp(400,function(){APP_UTIL.content.scrollIntoView($('#new-content-panel'));});
                }else{

                }
            },
            error:function(){
            }
        });
    });

    // new content form submit
    $('#new-content-form').on('submit','#editContent form',function(e){
        e.preventDefault();

        $form = $(this).closest('form');

        var formData = new FormData($form[0]);

        formData.append('owner', APP_DATA.owner);
        formData.append('owner_type', APP_DATA.owner_type);

        $form.find('.content-save').prop('disabled',true);

        $('#new-content-back').prop('disabled',true);

        $.ajax({
            url: onlinetours.action('ContentController@store'),
            type:'POST',
            dataType:'json',
            contentType: false,
            processData:false,
            mimeType:"multipart/form-data",
            data: formData,
            success:function($data){
                if(typeof $data !=='undefined') {
                    if (typeof $data.success !== 'undefined' && $data.success) {
                        APP_UTIL.content.insertContentItem($data);
                    } else {
                        APP_UTIL.content.showResponseMessage($form.find('.content-alerts').first(), APP_DATA.content_messages.storeFail, 'danger', $data);
                        $('#new-content-back').prop('disabled', false);
                        $form.find('.content-save').prop('disabled', false);
                    }
                }
            },
            error:function(xhr){
                if(xhr.status=='405'){
                    xhr.responseJSON = {errors:"Upload too large."};
                }
                APP_UTIL.content.showResponseMessage($form.find('.content-alerts').first(),APP_DATA.content_messages.storeFail,'danger', xhr.responseJSON);
                $('#new-content-back').prop('disabled',false);
                $form.find('.content-save').prop('disabled',false);
            }
        });
    });


    //make content items sortable and submit order via ajax to persist
    $content_list.sortable({
        handle: "h3.content-title",
        update: function( event, ui ) {
            $.ajax({
                url: onlinetours.action('ContentController@order'),
                type:'POST',
                dataType:'json',
                data:$(this).sortable('serialize') + '&owner=' + APP_DATA.owner + '&owner_type=' + APP_DATA.owner_type,
                success:function($data){
                    if($data.success) {

                    }else{
                        $('.content-list').sortable('cancel');
                    }
                },
                error:function(){
                    $('.content-list').sortable('cancel');
                }
            });
        }
    });

    // existing content search with select2
    $('#existingContent .content-search').select2({
        minimumInputLength: 1,
        ajax: {
            url: onlinetours.action('ContentController@search'),
            dataType: 'json',
            quietMillis: 250,
            type:'POST',
            data: function (term, page) {
                return {
                    search: term, // search term
                    page: page,
                    type: $('#editContent form').first().data('type'),
                    owner: APP_DATA.owner,
                    owner_type: APP_DATA.owner_type
                };
            },
            results: function (data, page) {
                return { results: data.items, more:data.more};
            }
        },
        escapeMarkup: function (m) { return m; },
        formatResult : function(object, container, query){
            $return='<div>';
            if(typeof object.thumb !=='undefined'){
                $return += '<img class="select2-thumb pull-left" src="' + object.thumb +'">';
            }
            $return += '<strong>' + object.text + '</strong>';
            if(typeof object.detail !=='undefined') {
                $return += '<br><i>' + object.detail + '</i>';
            }
            $return += '</div>';
            return $return;
        }
    }).on("change",function(e){
        if(e.val.length > 0){
            $('#add-existing-content').prop('disabled',false);
        }else{
            $('#add-existing-content').prop('disabled',true);
        }
    });


    // add existing content submit
    $('#add-existing-content').click(function(e){
        e.preventDefault();
        $id = $('#existingContent .content-search').select2('val');
        $.ajax({
            url: onlinetours.action('ContentController@attach',{id:$id}),
            type:'POST',
            dataType:'json',
            data: {
                owner: APP_DATA.owner,
                owner_type: APP_DATA.owner_type
            },
            success:function($data){
                if($data.success) {
                    APP_UTIL.content.insertContentItem($data);
                }else{

                }
            },
            error:function(){
            }
        });
    });

    // content delete (via modal button)
    $('#contentDeleteConfirmButton').click(function(e){
        $form = $('#content_' + APP_DATA.deleting_content + ' form');
        $.ajax({
            url: onlinetours.action('ContentController@destroy',{content:APP_DATA.deleting_content}),
            type:'DELETE',
            dataType:'json',
            success:function($data){
                if($data.success) {
                    $container = $('#content_' + APP_DATA.deleting_content).fadeOut(300, function(){
                        $container.remove();
                    });
                    APP_DATA.deleting_content = null;
                }else{
                    APP_DATA.deleting_content = null;
                    APP_UTIL.content.showResponseMessage($form.find('.content-alerts').first(),APP_DATA.content_messages.deleteFail,'danger');
                }
            },
            error:function(xhr){
                APP_DATA.deleting_content = null;
                APP_UTIL.content.showResponseMessage($form.find('.content-alerts').first(),APP_DATA.content_messages.deleteFail,'danger');
            }
        });
        $('#contentDeleteConfirm').modal('hide');
    });

    // content detach (via modal button)
    $('#contentDetachConfirmButton').click(function(e){
        $form = $('#content_' + APP_DATA.deleting_content + ' form');
        $.ajax({
            url: onlinetours.action('ContentController@detach',{id:APP_DATA.deleting_content}),
            type:'POST',
            dataType:'json',
            data: {
                owner: APP_DATA.owner,
                owner_type: APP_DATA.owner_type
            },
            success:function($data){
                if($data.success) {
                    $container = $('#content_' + APP_DATA.deleting_content).fadeOut(300, function(){
                        $container.remove();
                    });
                    APP_DATA.deleting_content = null;
                }else{
                    APP_DATA.deleting_content = null;
                    APP_UTIL.content.showResponseMessage($form.find('.content-alerts').first(),APP_DATA.content_messages.detachFail,'danger');
                }
            },
            error:function(){
                APP_DATA.deleting_content = null;
                APP_UTIL.content.showResponseMessage($form.find('.content-alerts').first(),APP_DATA.content_messages.detachFail,'danger');
            }
        });
        $('#contentDeleteConfirm').modal('hide');
    });

});

if(typeof APP_UTIL.content ==='undefined'){
    APP_UTIL.content = {};
}

APP_UTIL.content.showResponseMessage = function($target,$msg,$type,$validation_msgs){
    if(typeof $type == 'undefined'){
        $type = 'success';
    }
    $validation_msgs = typeof $validation_msgs === 'undefined' ? {} : $validation_msgs;
    $validation_msgs_html = "";
    if (Object.keys($validation_msgs).length > 0) {
        $validation_msgs_html = '<ul>';
        jQuery.each($validation_msgs, function (key, msgs) {
            msgs = jQuery.type("msgs") === 'array' ? msgs : [msgs];
            jQuery.each(msgs, function (key2, msg) {
                $validation_msgs_html += '<li>'+msg+'</li>';
            });
        });
    }
    $target.html('<div class="alert alert-'+$type+' alert-dismissible" role="alert">' +
    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
    $msg + $validation_msgs_html +
    '</div>');
    $target.children().delay(7000).fadeOut();
};

APP_UTIL.content.scrollIntoView = function($elem){
    $pos = $elem.position();
    $h = $elem.height();
    if($pos){
        if($pos.top + $h > jQuery(window).scrollTop() - (window.innerHeight || document.documentElement.clientHeight)){
            jQuery('html,body').animate({scrollTop:$pos.top + (window.innerHeight || document.documentElement.clientHeight) + $h + 25}, 1000);
        }
    }
};

APP_UTIL.content.activateMedium = function($textarea) {

    $textarea.removeClass('form-control').wrap('<div class="wysiwyg-wrapper"><div class="wysiwyg-area"></div></div>');
    if(!$textarea.is(':disabled')) {
        if($textarea.val()===''){
            $textarea.val('<p><br></p>');
        }
        var m_editor = new MediumEditor($textarea, APP_DATA.medium_options);
        m_editor.subscribe('editableInput', function (event, editable) {
            $ta = $(editable).parent().find('textarea');
            if($('body').hasClass('ie-compat')) {
                $ta.html(document.createTextNode($('<div/>').text($(editable).html()).html()));
            }
            $.fn.dirtyFields.updateFormState($ta.closest('form'));
        });
    }
};

APP_UTIL.content.initForm = function($form){
    $type  = $form.data('type');

    switch ($type){
        case "text":
            APP_UTIL.content.activateMedium($form.find('textarea').first());
            break;
        case "image":
            break;
        case "audio":
            APP_UTIL.content.activateMedium($form.find('textarea').first());
            break;
        case "video":
            var $saveField = $form.find(".youtube-save").first();
            $saveField.data('initial',$saveField.val());
            $form.on("change", ".youtube-control", function() {
                var input = $(this).val();
                // ignore if not a valid youtube link
                $code='';

                // https://www.youtube.com/watch?v=z2iwQoKD6mg
                if (input.indexOf("v=") >-1) {
                    $code = input.split('v=')[1].split('&')[0];
                }

                // https://youtu.be/z2iwQoKD6mg
                var matches = input.match(/^https?:\/\/youtu\.be\/([a-zA-Z0-9-_]{11})$/);
                if(matches!==null) {
                    $code = matches[1];
                }

                // https://www.youtube.com/embed/z2iwQoKD6mg
                var matches2 = input.match(/^https?:\/\/www\.youtube\.com\/embed\/([a-zA-Z0-9-_]{11})$/);
                if(matches2!==null) {
                    $code = matches2[1];
                }

                if($code.length ===11) {
                    var $iframe = $form.find("iframe");
                    var embedURL = "https://www.youtube.com/embed/" + $code;
                    // Set iframe src
                    $iframe.attr("src", embedURL);
                    $saveField.val(embedURL);
                    $(this).val(embedURL);
                }

                if($saveField.val()!==$saveField.data('initial')){
                    APP_UTIL.content.updateContentFormState($form, true);
                }
            });
            break;
    }

    $form.dirtyFields(APP_DATA.content_df_options);

};

APP_UTIL.content.insertContentItem = function($data){
    $new = $('<li class="content-item" id="content_' + $data.id + '">' + $data.html + '</li>').appendTo('.content-list');
    APP_UTIL.content.showResponseMessage($new.find('.content-alerts'),'Content Added!','success');
    $form = $new.find('form');
    APP_UTIL.content.initForm($form);
    $('#editContent > div').html('');
    $('#new-content-form').removeClass('open').slideUp();
    $('#new-content-back').prop('disabled',false);
    $('#choose-content-type').addClass('open').slideDown();
    $('#new-content-panel').slideUp();
    $('#add-content-btn').slideDown();
};

APP_UTIL.content.updateContentFormState = function($form,$dirty,$fields){
    $save_btn = $form.find('.content-save').first();
    $delete_btn =$form.find('.content-delete').first();
    if($dirty){
        $form.addClass('dirtyForm');
        $save_btn.prop('disabled',false);
        $delete_btn.html('<i class="kf-chevron-left"></i> Cancel').removeClass('btn-danger').addClass('btn-default');
    }else{
        $form.removeClass('dirtyForm');
        $save_btn.prop('disabled',true);
        $delete_btn.html('<i class="fa fa-times"></i> Remove').addClass('btn-danger').removeClass('btn-default');
    }
};