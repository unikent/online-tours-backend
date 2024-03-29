$(document).ready(function() {

    $form = $("#page_edit");

    $form.dirtyFields({
        denoteDirtyForm: true,
        denoteDirtyFields:true,
        trimText:true,
        formChangeCallback:function($dirty,$fields){
            var $form = $(this);
            var $save_btn = $form.find('.page-save').first();
            if($dirty){
                $form.addClass('dirtyForm');
                $save_btn.prop('disabled',false);
            }else{
                $form.removeClass('dirtyForm');
                $save_btn.prop('disabled',true);
            }
        }
    });

    $form.on('keyup','input, textarea, select',function(){
        $.fn.dirtyFields.updateFormState($(this).closest('form'));
    });


    $form.submit(function (e) {
        e.preventDefault();

        $form.find('button[type="submit"]').prop('disabled',true);
        // if everything was good, get form
        var form = $(this);

        // grab form data
        var payload = form.parseForm();

        // Error helper
        var showError = function () {
            showResponseMessage($('#message_area'), 'Unable to save changes.', 'danger');
            $form.find('.page-save').first().prop('disabled',false);
        };

        // Submit it
        $.ajax({
            type: 'POST',
            url: APP_DATA.restfulURL('page','update', payload.id),
            data: payload,
            success: function (data, textStatus, jqXHR) {
                if (data.success === true) {
                    var $form = $("#page_edit");
                    $form.populateForm(data.page);
                    $.fn.dirtyFields.formSaved($form);
                    showResponseMessage($('#message_area'), 'Page Updated.');
                } else {
                    showError();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showError();
            }
        });
    });

});