(function(){
	$('.google-map--edit .google-map__map').each(function(){
		var el = $(this);

		var form = el.parents('.form-location');
		var lat = form.find('.form-location__lat').first();
		var lng = form.find('.form-location__lng').first();

		el.gmap({
			onload: function(m){
				opts = { 'markers': [] };

				var marker = { 
					'lat': lat.val(), 
					'lng': lng.val() 
				};

				if((marker['lng'] !== '') && (marker['lat'] !== '')){
					opts['markers'].push(marker);
					m.loadChanges(opts, true);
				}
			},

			onchange: function(m){
				var data = m.exportData();
				lat.val(data.markers[0].lat);
				lng.val(data.markers[0].lng);
			},

			constraints: {
				marker: { draggable: true, max: 1 }
			},

			labels: {
				marker: 'Mark Location',
				polygon: false,
				delete: false,
			}
		});
	});
})();