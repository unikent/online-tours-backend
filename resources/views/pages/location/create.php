<?php $script->enqueue("location-form-scripts","js/location.min.js"); ?>
<?php $script->enqueue("google-maps","https://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=drawing"); ?>
<?php $script->enqueue("edit-map","js/map.min.js"); ?>

<div class="container">
	<div class="row">
		<div class="col-md-12 ">
			<div class="panel panel-default">
				<div class="panel-heading">Create Custom Location</div>
				<div class="panel-body">
					<div class="row">
	                    <?php echo view('pages.location.components.form', [ 'location' => $location ])->render(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>