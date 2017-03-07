 /**
  * MapHelper - Wrapper around google maps API to make drawing a little easier
  *
  * Note: ensure google maps include has libraries=drawing enabled.
  */
  (function(){
	 var MapHelper = function(container, config){
	 	// this for callbacks.
		var that = this;

		// vars
		this.config = config;
		this.labels = {
			"marker": "Add Marker",
			"polygon": "Add Polygon",
			"delete": "Delete",
		};

		// internals
		this.container = container;
		this.mapNode = null;
		this.map = null;
		this.buttons = {};
		this.drawingManager = null;

		// objects
		this.data = {"polygon":[], "marker": [], "geoJSON":null, "features":[]}
		this.constraints = {};

		// callbacks
		this.onchange = function(helper) {};

		// Selected
		this.selected = null;

		/**
		 * Init - set up new helper
		 * Creates Map plus additional UI buttons + hooks everything up
		 *
		 */
		this.init = function(params){
			// Render UI
			this.renderUi();

			// Create the map
			options = $.extend({
				zoom: 15,
				minZoom: 10,
				maxZoom: 18,
				center: new google.maps.LatLng(51.2973229, 1.0665176),
				disableDefaultUI: true,
				zoomControl: true,
				styles: [{
					featureType: "poi",
					elementType: "labels",
					stylers: [{ visibility: "off" }]
				}],
			}, params);

			this.map = new google.maps.Map(this.mapNode[0], options);

			// Create Drawing manager (no UI)
			this.drawingManager = new google.maps.drawing.DrawingManager({drawingControl: false});
			this.drawingManager.setMap(this.map);

			// Enable Polygon function
			if(this.buttons['polygon']){
				this.buttons['polygon'].click(function(e){
					e.preventDefault();
					that.drawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYGON);
				});
			}
			
			// Enable Point function
			if(this.buttons['marker']){
				this.buttons['marker'].click(function(e){
					e.preventDefault();
					that.drawingManager.setDrawingMode(google.maps.drawing.OverlayType.MARKER);
				});
			}

			// Add the delete button
			if(this.buttons['del']){
				this.buttons['del'].click(function(e){
					e.preventDefault();
					that.deleteSelected();
				});
			}

			// Polygon draw complete
			google.maps.event.addListener(that.drawingManager, 'overlaycomplete', function(e) {
				// Switch back to non-drawing mode after drawing a shape.
				that.drawingManager.setDrawingMode(null);
				that.addMapItem(e.overlay, e.type);
			});

			// change drawing mode
			google.maps.event.addListener(this.drawingManager, 'drawingmode_changed', function(){
				that.clearSelection();
			});

			// click on map (deselect)
			google.maps.event.addListener(this.map, 'click', function(){
				that.clearSelection();
			});
		};

		/**
		 * AddMapItem
		 * Configures a map item "overlay" to enable interaction with it + track changes
		 *
		 * @param item "overlay" object
		 * @param type marker|polygon
		 */
		this.addMapItem = function(item, type, select){

			// Set basics
			item.type = type;

			// Set draggable (on by default) 
			if(typeof this.constraints[type] !== 'undefined' && typeof this.constraints[type].draggable !== 'undefined'){
				item.setDraggable(this.constraints[type].draggable);
			}else{
				item.setDraggable(true);
			}

			// Hook up additional events for poly (ignore for markers)
			if(type !== 'marker'){

				// Dashed line?
				item.setOptions({
					'fillOpacity':0,
					'strokeOpacity':1,  
				});

				google.maps.event.addListener(item, 'click', function() {
					that.setSelection(item);
				});
				
				if(typeof select === 'undefined' || select === true) this.setSelection(item);
			}

			// Set sync up so we can track changes
			google.maps.event.addListener(item, 'mouseup', function() {
				that.syncChanges();
			});
			

			// Add to data store
			that.data[type].push(item);
			item.index_id = (that.data[type].length-1);

			// check if button add is needed (enforces "max")
			if(this.buttons[type]){
				if(typeof this.constraints[type] !== 'undefined' && typeof this.constraints[type].max !== 'undefined'){
					if(that.getCountOfType(type) >= this.constraints[type].max) that.buttons[type].attr('disabled', 'disabled');
				}
			}

			// Sync this change
			that.syncChanges();
		};


		this.getCountOfType = function(type){
			var count = 0;
			for(var c in this.data[type]){
				if(this.data[type][c] !== false) count++;
			}
			return count;
		}

		/**
		 * AddMapItem
		 * Removes a map "overlay"
		 *
		 * @param item "overlay" object
		 */
		this.removeMapItem = function(item){
			// clear data
			that.data[item.type][item.index_id] = false;
			// Remove from map
			item.setMap(null);
			// Sync change
			that.syncChanges();
		};

		/**
		 * setSelection
		 * Selects a map object
		 *
		 * @param item "overlay" object
		 */
		this.setSelection = function(shape) {
			this.clearSelection();
			this.selected = shape;
			shape.setEditable(true);
			shape.setDraggable(this.constraints[shape.type].draggable);

			if(this.buttons['del']){
				this.buttons['del'].attr('disabled', 'disabled');
			}
		} ;

		/**
		 * clearSelection
		 * deselects a map object
		 */
		this.clearSelection = function() {
			if (this.selected) {
				this.selected.setEditable(false);
				this.selected.setDraggable(false);
				this.selected = null;

				if(this.buttons['del']){
					this.buttons['del'].attr('disabled', 'disabled');
				}
			}
		};

		/**
		 * deleteSelected
		 * Removes the selected overlay
		 */
		this.deleteSelected = function(){
			if (this.selected) {
				this.removeMapItem(this.selected);
				this.clearSelection();

				// check if button add is needed (enforces "max")
				if(typeof this.buttons['polygon'] !== 'undefined'){
					if((typeof this.constraints['polygon'] !== 'undefined') && (typeof this.constraints['polygon'].max !== 'undefined')){
						if(that.getCountOfType('polygon') < this.constraints['polygon'].max){
							that.buttons['polygon'].removeAttr('disabled');
						} 
					}
				}
			}
		};

		/**
		 * syncChanges
		 * Sync's changes to overlays to an external callback
		 */
		this.syncChanges = function(){
			// Guessing gmaps is being a bit asyncy - sometimes update triggers before objects are actually updated
			// minimum set timeout seems to fix
			setTimeout(function(){
				that.onchange(that);
			},1);
				
		};

		/**
		 * load Changes
		 * Reloads map data from an external data source
		 *
		 * @param data {"lat": int, "lng": int "poly": []}
		 * @param center - true|false center on the lng/lat
		 */
		this.loadChanges = function(data, center){
			// Clear out existing polys
			if(this.data.polygon.length !== 0){
				for(var ply in this.data.polygon){
					if(this.data.polygon[ply]) this.removeMapItem(this.data.polygon[ply]);
				}
			}
			// Clear out existing markers
			if(this.data.marker.length !== 0){
				for(var mkr in this.data.marker){
					if(this.data.marker[mkr]) this.removeMapItem(this.data.marker[mkr]);
				}
			}

            if(this.data.features.length !== 0){
                for (var i = 0; i < this.data.features.length; i++) {
                    this.map.data.remove(this.data.features[i]);
                }
                this.data.features=[];
            }

			// get new markers
			if(data.markers.length !== 0){
				// loop through creating new markers on the map
				for(var m in data.markers){
					var mkr = new google.maps.Marker({
						position: new google.maps.LatLng(data.markers[m].lat, data.markers[m].lng),
						map: this.map
					});
					this.addMapItem(mkr, "marker");	
				}
			}

			// get the new polygons
			var polygons = data.polygons;
			// if there are some..
			if(typeof polygons !=='undefined' && polygons.length !== 0){
				// loop through creating new polys on the map (and syncing them to our save object)
				for(var poly in polygons){
					var current_poly = polygons[poly];
					var points = [];
					for(var ll in current_poly){
						points.push(new google.maps.LatLng(current_poly[ll].lat,current_poly[ll].lng));
					}
					this.addMapItem(new google.maps.Polygon({'paths': points, 'map': this.map}), "polygon", false);
				}
			}

            if(typeof this.data.geoJSON ==='object'){
                this.data.features = this.map.data.addGeoJson(data.geoJSON);
            }

			// If map wants to be centered 
			if(typeof center !== 'undefined' && center===true){
				this.centerMap();
			}	
		};

		/**
		 * CenterMap
		 * Centers map to show all polygons and points.
		 */
		this.centerMap = function(){
			// create bounds object
			var bounds = new google.maps.LatLngBounds();


			// Add all the poly coords
			for(var poly in this.data.polygon){
				if(this.data.polygon[poly]===false) continue;

				var coords = this.data.polygon[poly].getPath().getArray();
				for(var coord in coords){
					bounds.extend(coords[coord]);
				}
			}

			// Add all the marker cords
			for(var mkr in this.data.marker){
				if(this.data.marker[mkr]===false) continue;

				bounds.extend(this.data.marker[mkr].position);
			}

            this.map.data.forEach(function(feature) {
                processPoints(feature.getGeometry(), bounds.extend, bounds);
            });


			if(bounds.isEmpty()){
				$center = $('#map-center');
				bounds.extend(new google.maps.LatLng($center.data('lat'),$center.data('lng')));
			}
            // fit it
			this.map.fitBounds(bounds);
		};

         /**
          * Process each point in a Geometry, regardless of how deep the points may lie.
          * @param {google.maps.Data.Geometry} geometry The structure to process
          * @param {function(google.maps.LatLng)} callback A function to call on each
          *     LatLng point encountered (e.g. Array.push)
          * @param {Object} thisArg The value of 'this' as provided to 'callback' (e.g.
          *     myArray)
          */
         function processPoints(geometry, callback, thisArg) {
             if (geometry instanceof google.maps.LatLng) {
                 callback.call(thisArg, geometry);
             } else if (geometry instanceof google.maps.Data.Point) {
                 callback.call(thisArg, geometry.get());
             } else {
                 geometry.getArray().forEach(function(g) {
                     processPoints(g, callback, thisArg);
                 });
             }
         }


         /**
		 * exportData
		 * Exports data in the {"markers": [], "poly": []} format.
		 */
		this.exportData = function(){

			var payload = {"polygons": [], "markers": []};

			// get polygons
			for(var i in this.data.polygon){

				// ignore deleted (falses)
				if(!this.data.polygon[i]) continue;

				var poly = this.data.polygon[i];
				var points = poly.getPath().getArray();

				var tmpPoly = [];

				for(var p in points){
					tmpPoly.push({"lat": points[p].lat(), "lng": points[p].lng()})
				}
		
				payload.polygons.push(tmpPoly);
			}

			// Get markers
			for(var i in this.data.marker){
				if(!this.data.marker[i]) continue;
				var tmpMarker = {"lng": this.data.marker[i].position.lng(), "lat": this.data.marker[i].position.lat()}
				payload.markers.push(tmpMarker);
			}
			return payload;
		}

		/**
		 * Generates UI buttons
		 */
		this.renderUi = function(){
			// Add buttons
			if(this.labels.polygon) this.buttons.polygon = $("<button class='btn btn-default'>"+this.labels.polygon+"</button>");
			if(this.labels.marker) this.buttons.marker = $("<button class='btn btn-default'>"+this.labels.marker+"</button>");
			if(this.labels.delete) this.buttons.del = $("<button disabled='disabled' class='btn pull-right btn-default'>"+this.labels.delete+"</button>");

			var controls = $(document.createElement('div'));
			this.mapNode = $(document.createElement('div'));
			this.mapNode.css('width', '100%');
			this.mapNode.css('height', '400px');

			for(var x in this.buttons){
				controls.append(this.buttons[x]);
			}

			this.container.append(this.mapNode);
			this.container.append(controls);
		};
	}

	/**
	 * jQuery integration
	 *
	 * $("#map").gmap({});
	 */
	$.fn.gmap = function(data){
		// make the helper
		var helper = new MapHelper($(this), data);	

		// Set constraints
		if(typeof data.constraints === 'object') helper.constraints = data.constraints;
		if(typeof data.labels === 'object') helper.labels = data.labels;

		// Make map
		var gMapOptions = !(typeof data.gMapOptions == 'undefined') ? data.gMapOptions : {};
		helper.init(gMapOptions);

		// Attach callbacks
		if(typeof data.onload === 'function') data.onload(helper);
		if(typeof data.onchange === 'function') helper.onchange = data.onchange;

		return helper;
	};
})();