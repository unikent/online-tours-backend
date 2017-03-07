<div class="container">
	<div class="row">
		<div class="col-md-12 ">
			<div class="panel panel-default poi-panel">
				<div class="panel-heading">Manage POI's</div>

				<div class="panel-body">
					<div class="row">
						<div class="col-sm-12 form form-horizontal">
							<label>Search: <input id="poi-search" class="form-control" type="text" placeholder="Search POIs"></label>
						</div>
					</div>
					<p>
						<a href="<?php echo action('POIController@create');?>" title="add top level POI" class="btn btn-add-top-level-poi"><i class="fa fa-plus"></i></a>
					</p>
					
					<div class="poi-tree">
						<?php echo App\Http\Helpers\TreeHelper::printTree(null, array('selected_leafs' => $id)); ?>
					</div>

					<p>
						<a href="<?php echo action('POIController@create');?>" title="add top level POI" class="btn btn-add-top-level-poi"><i class="fa fa-plus"></i></a>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>

