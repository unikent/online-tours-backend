(function() {
      "use strict";
	/* jshint laxcomma:false */
      /* global $ */

      // Persists Zone drag/drop sorting
      var t = null;
      $('.sortable').each(function(){
            var el = $(this);

            if(el.data('sortable-id')){
                el.on('sortable:update', function(){
                    clearTimeout(t);

                    var zid = el.data('sortable-id');
                    if(zid){
                        zid = zid.replace('zF', '');
                        zid = zid.replace('zS', '');

                        t = setTimeout(function(){
                            var payload = {
                                featured: $('.sortable[data-sortable-id=zF' + zid + ']').sortable('toArray', { attribute: 'data-sort-id' }),
                                standard: $('.sortable[data-sortable-id=zS' + zid + ']').sortable('toArray', { attribute: 'data-sort-id' })
                            };

                            $.each(payload.featured, function(i){
                              payload.featured[i] = payload.featured[i].replace('t', '');
                            });

                            $.each(payload.standard, function(i){
                              payload.standard[i] = payload.standard[i].replace('t', '');
                            });

                            $.ajax({ url: onlinetours.action('ZoneController@orderTours',{zone:zid}), method: 'PATCH', data: payload });
                        }, 1000);
                    }
                });
            }
      });

})();