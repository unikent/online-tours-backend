$(function () {
	window.poi_trees = [];
	var to=false;
	$('#poi-search').keyup(function () {
		if(to) { clearTimeout(to); }
		to = setTimeout(function () {
			var v = $('#poi-search').val();
			if(v.length < 3) {
				v='';
			}
			$('.poi-tree').each(function(){$(this).jstree(true).search(v);});
		}, 250);
	});


	$('.poi-tree').each(function () {

		var plugins = ['dnd','search'];
		var multiple = false;

		// remove drag and drop support if its been disabled
		if (typeof $(this).data('disable-drag-and-drop') !== 'undefined' && $(this).data('disable-drag-and-drop') == true) {
			plugins.splice(plugins.indexOf('dnd'), 1);
		}

		// enable multiple selection if specified
		if (typeof $(this).data('enable-multi-select') !== 'undefined' && $(this).data('enable-multi-select') == true) {
			multiple = true;
			plugins.push('checkbox');
		}
		
		/**
		 * Create POI tree with jstree
		 */
		var poi_tree = $(this).jstree({
			'core': {
				'themes': {
					'name': 'proton',
					'responsive': true
				},
				'multiple': multiple,
				"check_callback" : true
			},
			'checkbox': {
				'three_state': false
			},
			"search" : {case_sensitive:false,show_only_matches:true},
			'plugins': plugins
		});

		/**
		 * Update whatever input specified with the leaf_id's of the selected nodes
		 */
		poi_tree.update_selection = function (selected_node_ids) {
			// populate leaf_id input if its set
			// data-leaf-id-input="leaf_id"
			if(typeof poi_tree.data('leaf-id-input') !== 'undefined' && $('#'+poi_tree.data('leaf-id-input')).length > 0){
				
				var select_leafs = []; 
				for (var i = selected_node_ids.length - 1; i >= 0; i--) {
					var node = poi_tree.find('#'+selected_node_ids[i]);
					select_leafs.push(node.data().leaf_id);
				};
				
				$('#'+poi_tree.data('leaf-id-input')).val(select_leafs.join(','));
				if($('#'+poi_tree.data('leaf-id-input')).is('input[type="hidden"]')){
					$('#'+poi_tree.data('leaf-id-input')).trigger('change');
				}
				
			}
		};

		/**
		 * Add POI tree events
		 */
		poi_tree
			/**
			 * Ready event
			 */
			.on('ready.jstree', function () {
				// expand the entire tree if its been requested
				if(typeof poi_tree.data('expand-all') !== 'undefined' && poi_tree.data('expand-all') == true)
					poi_tree.jstree('open_all');
			})

			/**
			 * Change the parent of a leaf
			 */
			.on('move_node.jstree', function (e, tree_data) {

				// dont procees if we're reverting a move
				if (poi_tree.reverting) {
					poi_tree.reverting = false;
					return true;
				}
				
				var leaf_id = tree_data.node.data.leaf_id;

				var old_parent_leaf_id = tree_data.old_parent == '#' ? null : $('#'+tree_data.old_parent).data('leaf_id');
				var new_parent_leaf_id = tree_data.parent == '#' ? 0 : $('#'+tree_data.parent).data('leaf_id');

				// funtion to revert this move
				var revert_move = function () {
					poi_tree.reverting = true;

					// move_node assumes we're moving the node beneath another item,
					// (which is the usual behaviour when moving a node between parents)
					// we need to compensate for this by adding one to the previous position
					// where we're reverting within the same parent
					var new_position = tree_data.old_parent == tree_data.parent && tree_data.position < tree_data.old_position ? tree_data.old_position + 1 : tree_data.old_position;
					
					$.jstree.reference(poi_tree).move_node(tree_data.node.id, tree_data.old_parent, new_position);
					$.jstree.reference(poi_tree).deselect_all();
					$.jstree.reference(poi_tree).select_node(tree_data.node.id);
				}
				
				// select the moved leaf
				var select_moved_node = function () {
					$.jstree.reference(poi_tree).deselect_all();
					$.jstree.reference(poi_tree).select_node(tree_data.node.id);
				}

				if (tree_data.old_parent == tree_data.parent) {
					revert_move();
					return false;
				}

				// do ajax to change parent as per below
				$.ajax({
					type: 'PATCH',
					url: onlinetours.action('POIController@update',{poi:leaf_id}),
					data: {
						'parent_id': new_parent_leaf_id,
						//'old_parent_id': old_parent_leaf_id
					},
					success: function (response_data, textStatus, jqXHR) {
						if(response_data.success == false){ 
							revert_move();
							return false;
						}
						
						select_moved_node();
					},
					error: function (jqXHR, textStatus, errorThrown) {
						revert_move();
					}
				});

			})

			/**
			 * Selected node event
			 */
			.on('select_node.jstree', function (e, data) {
				poi_tree.update_selection(data.selected);
			})

			/**
			 * Deselected node event
			 */
			.on('deselect_node.jstree', function (e, data) {
				poi_tree.update_selection(data.selected);
			})

			/**
			 * Add a button to add a child leaf on hover
			 */
			.on('hover_node.jstree', function (e, data) {
				// be default, adding children will be enabled unless otherwise set by the data-enable-add-child attribute
				if(typeof poi_tree.data('enable-add-child') === 'undefined' || poi_tree.data('enable-add-child') == true){
					var parent_leaf = data.node.data.leaf_id;
					var add_child_button = $('<button></button>')
						.addClass('btn-add-child-poi')
						.attr('title', 'add child POI')
						.append('<i class="fa fa-plus"></i>')
						.click(function (argument) {
							window.location.href = onlinetours.action('POIController@create',{id:parent_leaf});
						});
					$('#' + data.node.id + ' > a').append(add_child_button);
				}
					
			})

			/**
			 * Remove add child button on dehover
			 */
			.on('dehover_node.jstree', function (e, data) {
				$('#' + data.node.id + ' > a button').remove();
			})

			/**
			 * Edit a leaf when double clicked
			 */
			.on('dblclick.jstree', function (e) {
				var node = $(e.target).closest("li");
				var leaf_id = node.data("leaf_id");
				
				window.location.href = onlinetours.action('POIController@edit',{poi:leaf_id});
			});

		/**
		 * make trees accessible globally
		 */
		window.poi_trees.push(poi_tree);
	});

});