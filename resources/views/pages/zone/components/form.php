<?php if($zone->exists){ ?>
<div class="actions row">
    <div class="container">
        <a href="<?php echo action('ZoneController@destroy', ['id' => $zone->leaf_id]); ?>" class="restful btn btn-danger pull-right" data-method="delete" data-modal="zoneDeleteConfirm">Delete Zone</a>
    </div>
</div>
<?php } ?>
<div class="panel panel-default zone-panel">
	<div class="panel-heading"><?php echo $zone->exists ? 'Edit' : 'Create'; ?> Zone</div>
	<div class="panel-body">
		<form method="POST" action="<?php echo $zone->exists ? action('ZoneController@update',[$zone->leaf_id]):action('ZoneController@store'); ?>">
			<div class='col-sm-12'>

				<div class="form-group<?php echo $errors->has('name')?' has-error':''; ?>">
					<label for="zone-name">Name</label>
					<input type="text" name="name" class="form-control" id="zone-name" placeholder="Zone name"  value="<?php echo Input::old('name',$zone->name); ?>" required />
                    <?php if($errors->has('name')){?><p class="help-block"><?php echo $errors->first('name'); ?></p><?php } ?>
                </div>

				<div class="form-group<?php echo $errors->has('leaf_id')?' has-error':''; ?>">
                    <?php if(!$zone->exists){ ?>
                    <?php if($errors->has('leaf_id')){?><p class="help-block"><?php echo $errors->first('leaf_id'); ?></p><?php } ?>
                    <label for="leaf_id">Select root POI for this zone</label>
					<input type="hidden" name="leaf_id" id="leaf_id" value="<?php echo $zone->leaf_id; ?>">
					<div class="poi-tree" data-leaf-id-input="leaf_id" data-disable-drag-and-drop="true" data-expand-all="true" data-enable-add-child="false">
						<?php echo App\Http\Helpers\TreeHelper::printTree(null, array('selected_leafs' => $zone->leaf_id)); ?>
					</div>
                    <?php }else{ ?>
                        <label for="leaf_id">Root POI for this zone</label>
                        <p class="form-control-static"><a href="<?php echo action('POIController@edit',$zone->leaf_id); ?>"><?php echo $zone->leaf->name; ?></a></p>
                    <?php } ?>
				</div>

				<div class="row">
					<div class='col-md-10' id="message_area">
					</div>
					<div class='col-md-2'>
                        <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="_method" value="<?php echo ($zone->exists)?'PATCH':'POST'; ?>">
						<button class='btn btn-primary pull-right'>
							<?php echo ($zone->exists)?'Update':'Save'; ?>
							<i class='kf-chevron-right'></i>
						 </button>
					</div>
				</div>
			</div>
		</form>

	</div>
</div>