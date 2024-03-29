(function(){

	var read_form = function(){
		var markers = [];
		var poly = '';

		var items = $('#items').val().split(',');

		for (var i = items.length - 1; i >= 0; i--) {
			var item_element = $('li[data-leaf_id="'+items[i]+'"]');
			if (item_element.length > 0) {
				markers.push({"lng": item_element.data().lng, "lat": item_element.data().lat});
			}
		};

		// TODO: get the current polyline (from a designated input) var and return it
		var poly = $("#polygon-data").val();
		try{
			if(poly=='') poly='[]';
			poly = JSON.parse(poly);
		}catch(e){
			poly = JSON.parse('[]');
		}

		return {"markers": markers, "polygons": poly };
	}

	var gmapp = $("#google-maps").gmap({
		center: function(){

		},
		onload: function(m){
			m.loadChanges(read_form(), true);
		},
		onchange: function(m){
			// sync polygon changes back?
			var data = m.exportData();
			$("#polygon-data").val(JSON.stringify(data.polygons));
		},
		constraints: {
			"polygon": {"draggable": false, "max": 1},
			"marker": {"draggable": false} 
		},
		labels: {
			"marker": false,
			"polygon": "Draw Route",
			"delete": "Delete",
		}
	});

	$("#items").change(function(){
		gmapp.loadChanges(read_form(),true);
	}); 

	window.gmapp = gmapp;

})();