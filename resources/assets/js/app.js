$.ajaxSetup({
    headers: {
        'X-XSRF-TOKEN': $.cookie('XSRF-TOKEN')
    }
});

$('.select2').select2();

/** 
 * A speedy helper for RESTful linking.
 *
 * Add .restful or .ajaxify class to any link, and it will be run as a JSON request.
 *  - The default href will be used, unless an alternative data-href has been specified.
 *  - Requests will be treated as a GET, unless a data-method is specified.
 *  - If a data-modal="foobar" is set, it will look for a modal matching "#foobar". The 
 *    modal should contain an element with data-confirm="modal" which confirms the action and
 *    fires a request using the origin .resful/.ajaxify link.
 */ 
(function(){
    $('a.restful, a.ajaxify').each(function(){
        var el = $(this);

        el.on('click', function(e){
            // Configure the options for submitting the link via AJAX
            var ajaxOptions = {
                url: el.data('href') || el.attr('href'), 
                method: el.data('method') || 'GET',

                success: function(data, status, jqxhr){
                    if(typeof data.redirect_to !== 'undefined'){
                        window.location = data.redirect_to;
                    }
                },
            };

            // Check to see if a Modal is present
            var modal = false;
            if(typeof el.data('modal') !== 'undefined'){
                modal = { dialogue: $('#' + el.data('modal')).first() };
                modal.confirm = $(modal.dialogue).find('[data-confirm="modal"]').first();
            }

            // Prevent the default action
            e.preventDefault();

            // Present a modal if configured; will fire AJAX on confirmation
            if(modal && modal.dialogue){
                (modal.dialogue).modal('show');

                (modal.confirm).on('click', function(e){
                    e.preventDefault();
                    $.ajax(ajaxOptions);

                    (modal.dialogue).modal('hide');
                    (modal.confirm).off('click');
                });
            // Otherwise, just fire the AJAX right away
            } else {
                $.ajax(ajaxOptions);
            }
        });
    });


    // Grab every sortable item on the page...
    $('.sortable').each(function(){
        var el = $(this);

        // ...if we have a sortable-id, we might be allowing elements to be sorted between containers...
        var sid = el.data('sortable-id');
        if(sid){
            var connectWith = [];
            if(el.data('sortable-with')){
                connectWith = el.data('sortable-with').split(' ');
            }

            el.sortable({
                connectWith: '.sortable[data-sortable-id=' + connectWith.join('], [data-sortable-id=', connectWith) + ']',
                update: function(){
                    el.trigger('sortable:update');
                },
            });

        // ...otherwise, just make it sortable right away
        } else {
            el.sortable();
        }
    });

    $('#sync_conf').click(function(e){
        //e.preventDefault();
        $('#sync_form').submit();
        $('#sync_modal').modal('hide');
    });

    $('#sync_btn').click(function(e){
        e.preventDefault();
        $('#sync_modal').modal();
    });

})();



// Borrowed from http://stackoverflow.com/questions/19820742/turn-a-form-into-an-associative-array-in-jquery
$.fn.parseForm = function()
{
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};
$.fn.populateForm = function(data){
	$(this).find('[name]').each(function() {
		var name = $(this).attr('name');
		if(typeof data[name] !== 'undefined'){
			$(this).val(data[name]);
		} 
    });
    return true;
};
