(function(){
	$('.google-map--view .google-map__map').gmap({
		onload: function(m){
			if((typeof leaf !== 'undefined') && (typeof leaf.location !== 'undefined')){
				opts = { 'markers': [] };

				if((typeof leaf.location.lat !== 'undefined') && (typeof leaf.location.lng !== 'undefined')){
					opts['markers'].push({ lat: leaf.location.lat, lng: leaf.location.lng });
				}

				if((typeof leaf.location.polygon !== 'undefined')){
					opts['geoJSON'] = JSON.parse(leaf.location.polygon);
				}

				m.loadChanges(opts, true);
			}
		},

		constraints: {
			marker: { draggable: false, max: 1 }
		},

		labels: {
			marker: false,
			polygon: false,
			delete: false,
		}
	});

	$('#create_poi #location_id').on("change",function(e){
		var $name = $('#name');
		$name.val(e.added.text);
	});

})();