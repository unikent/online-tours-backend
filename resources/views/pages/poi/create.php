<?php $script->enqueue("poi-form-scripts","js/poi.min.js"); ?>
<?php $script->enqueue("google-maps","https://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=drawing"); ?>
<?php $script->enqueue("edit-map","js/map.min.js"); ?>

<div class="container">
	<div class="row">
		<div class="col-md-12 ">
			<div class="panel panel-default">
				<div class="panel-heading">Create POI</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-sm-12">
						<?php if($locations){?>
							<form id="create_poi" action="<?php echo action('POIController@store'); ?>" method="POST">

								<?php if(isset($parent)): ?>
									<input type="hidden" value="<?php echo $parent->id; ?>" name="parent_id" />
								<?php endif; ?>

								<input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
								<input type="hidden" id="name" name="name" value="<?php echo Input::old('name')  ?>" />

								<div class="form-group<?php echo($errors->has('location_id'))?' has-error':''; ?>">
									<label for="location_id">Select a Location</label>
									<select class="select2" id="location_id" name="location_id" placeholder="Select location" required>
										<option></option>
										<?php foreach($locations as $location): ?>
											<option value="<?php echo $location->id; ?>"><?php echo $location->name; ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="form-group">
									<button  type="submit" class="btn btn-primary pull-right">Create POI <i class="kf-chevron-right"></i></button>
								</div>

							</form>
						<?php
						}else{
						?>
						There are no locations available that are not in use within this tree.
						<?php
						}
						?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

