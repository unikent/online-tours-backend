<?php
namespace App\Http\Helpers;

use App\Models\Leaf;

class TreeHelper {

	public static function printTree($tree=null, $args=null)
	{
		// get arguments
		$defaults=array(
			'selected_leafs'		=>	null
		);

		$args = array_merge($defaults, (array) $args);
		extract($args);

		// markup to return
		$html = '';

		//if our tree is an id, load it and its children
		if(is_numeric($tree)){
			$tree = Leaf::where('id', '=', $tree)->with('location')->firstOrFail();
			$tree = $tree->getDescendantsAndSelf()->sort(function ($leaf_a, $leaf_b)
			{
				return strnatcmp($leaf_a->name, $leaf_b->name);
			})->toHierarchy();
		}

		// if our tree is empty, grab everything
		$tree = empty($tree) ? Leaf::with('location')->get()->sort(function ($leaf_a, $leaf_b)
		{
			return strnatcmp($leaf_a->name, $leaf_b->name);
		})->toHierarchy() : $tree;

		if($tree->count() <= 0){
			return $html;
		}

		$html = '<ul>';

		if(!is_array($selected_leafs)) {
			$selected_leafs = array_filter(explode(',', $selected_leafs));
		}

		foreach ($tree as $leaf) {
			$selected = false;

			if(!empty($selected_leafs)){
				$selected = in_array($leaf->id, $selected_leafs);
			}
			
			$selected_text = $selected ? ', "selected": true ' : '';
			$opened_text = $selected ? ', "opened": true ' : '';

			$html .= '<li data-leaf_id="'.$leaf->id.'" data-lat="'.$leaf->location->lat.'" data-lng="'.$leaf->location->lng.'" data-jstree=\'{"icon":"fa fa-map-marker"' . $selected_text . $opened_text . '}\'><a href="' . action('POIController@edit',[$leaf->id]) . '"><i class="kf-plus"></i> ' . $leaf->name . '</a>';


			// recurse on the leaf if it has children
			if(count($leaf->children) > 0) {
				$html .= self::printTree($leaf->children,$args);
			}

			$html .= '</li>';
		}

		$html .= '</ul>';

		return $html;
	}


}