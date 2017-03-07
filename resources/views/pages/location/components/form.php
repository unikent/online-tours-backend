<form action="<?php echo $location->exists ? action('LocationController@update', $location->id) : action('LocationController@store'); ?>" method="POST" class="form-location">
	<div class="col-md-6">
		<input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">

		<input type="hidden" name="_method" value="<?php echo $location->exists ? 'PUT' : 'POST'; ?>">

		<div class="form-group<?php echo($errors->has('name'))?' has-error':''; ?>">
			<label>
				Name<br />
				<input type="text" name="name" class="form-control form-location__name" placeholder="Building/Place name"  value="<?php echo Input::old('name',$location->name); ?>" required />
			</label>

            <?php if($errors->has('name')): ?><p class="help-block"><?php echo $errors->first('name'); ?></p><?php endif; ?>
		</div>
		<div class="row">
			<div class="form-group col-md-6<?php echo($errors->has('lat'))?' has-error':''; ?>">
				<label>
					Latitude<br />
					<input name="lat" type="number" step="any" class="form-control form-location__lat" placeholder="23.000" value="<?php echo Input::old('lat',$location->lat); ?>" required />
				</label>
                <?php if($errors->has('lat')): ?><p class="help-block"><?php echo $errors->first('lat'); ?></p><?php endif; ?>
			</div>
			<div class="form-group col-md-6<?php echo($errors->has('lng'))?' has-error':''; ?>">
				<label>
					Longitude<br />
					<input name="lng" type="number" step="any" class="form-control form-location__lng" placeholder="23.0000" value="<?php echo Input::old('lng',$location->lng); ?>" required />
				</label>

                <?php if($errors->has('lng')): ?><p class="help-block"><?php echo $errors->first('lng'); ?></p><?php endif; ?>
			</div>
		</div>
		<div class="form-group<?php echo($errors->has('disabled_go_url'))?' has-error':''; ?>">
			<label>
				Disabled Go URL<br />
				<input type="url" name="disabled_go_url" class="form-control form-location__name" placeholder="http://www.disabledgo.com/access-guide/university-of-kent/..."  value="<?php echo Input::old('disabled_go_url',$location->disabled_go_url); ?>" />
			</label>

			<?php if($errors->has('disabled_go_url')): ?><p class="help-block"><?php echo $errors->first('disabled_go_url'); ?></p><?php endif; ?>
		</div>
		<div class="row">
			<div class="col-md-4">
				<button class="btn btn-primary">
					Save <i class=kf-chevron-right></i>
				</button>
			</div>
		</div>
	</div>
	<div class="col-md-6 form-location__maparea google-map google-map--edit google-map--with-controls">
		<div class="form-location__map google-map__map"></div>
	</div>

	<div class='col-md-8' id="message_area">
	</div>
</form>
