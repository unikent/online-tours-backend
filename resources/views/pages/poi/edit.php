<?php $script->enqueue("poi-form-scripts","js/poi.min.js"); ?>
<?php $script->enqueue("content-form-scripts","js/content.min.js"); ?>

<?php $script->enqueue("google-maps","https://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=drawing"); ?>
<?php $script->enqueue("edit-map","js/map.min.js"); ?>

<script>
	APP_DATA.owner = "<?php echo $leaf->id; ?>";
	APP_DATA.owner_type = "leaf";

	leaf = <?php echo json_encode($leaf->toArray()); ?>;
</script>

<div class="container">
	<div class="row">
		<div class="col-sm-12 actions">
			<a href="<?php echo URL::action('POIController@destroy', array($leaf->id)); ?>" class="restful btn btn-danger pull-right" data-method="delete" data-modal="poiDeleteConfirm">Delete POI <i class="fa fa-trash"></i></a>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 ">
		<form method="POST" action="<?php echo action('POIController@update',[$leaf->id]); ?>">
			<div class="panel panel-default">
				<div class="panel-heading">POI Details</strong></div>
				<div class="panel-body">
					<div class="form-group<?php echo($errors->has('name'))?' has-error':''; ?>">
						<label>
							Name<br />
							<input type="text" id="name" name="name" class="form-control form-location__name" placeholder="Building/Place name"  value="<?php echo Input::old('name',$leaf->name)  ?>" required />
						</label>

						<?php if($errors->has('name')): ?><p class="help-block"><?php echo $errors->first('name'); ?></p><?php endif; ?>
					</div>
					<div class="form-group">
						<button type="submit" class="btn btn-primary pull-right">Update POI <i class="kf-chevron-right"></i></button>
					</div>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading">Location used by this POI</div>
				<div class="panel-body" class="">
						<div class="form-location__maparea google-map google-map--view">
							<div class="form-location__map google-map__map"></div>
						</div>
						<?php if($leaf->location->isLocal()){ ?>
							<a href="<?php echo action('LocationController@edit', $leaf->location->id); ?>" class=""> Edit <em><?php echo $leaf->location->name; ?></em> Location</a>
						<?php }else{ ?>
							<span class="help-inline">The <em><?php echo $leaf->location->name; ?></em> Location is not editable as it has been imported from the Kent Maps API.</span>
						<?php } ?>
						<span class="link pull-right" data-toggle="collapse" data-target="#edit_location">Change Location</span>
						<div id="edit_location" class="collapse">
							<div class="form-group">
								<label for="location_id">Location</label>
								<select class='select2' id="location_id" name="location_id" required>
									<option></option>
									<?php foreach($locations as $location): ?>
										<option value="<?php echo $location->id; ?>" <?php echo $location->id===$leaf->location_id?'selected':''; ?>><?php echo $location->name; ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="form-group">
								<input type="hidden" name="_method" value="PATCH">
								<input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
								<button type="submit" class="btn btn-primary pull-right">Update Location <i class="kf-chevron-right"></i></button>
							</div>
						</div>

				</div>
            </div>
		</form>


			<div class="panel panel-default">
				<div class="panel-heading">Manage Content</strong></div>
				<div class="panel-body">
                    <?php echo view('components.form_content',['owner' => $leaf])->render(); ?>
				</div>
            </div>
            <div id="new-content-panel" class="panel panel-default" style="display: none">
                <div class="panel-heading">New Content</div>
                <div class="panel-body">
                    <?php echo view('components.form_content_new', [ 'owner' => $leaf ])->render(); ?>
                </div>
                <div class="panel-footer"><a id="new-content-back" href="#" class="btn btn-default"><i class="kf-chevron-left"></i> Cancel</a></div>
            </div>
		</div>
	</div>
</div>

<?php echo view('pages.poi.components.delete_modal')->render(); ?>