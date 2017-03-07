<?php $script->enqueue("tour","js/tour.min.js"); ?>
<?php $script->enqueue("google-maps","https://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=drawing"); ?>
<?php $script->enqueue("edit-map","js/map.min.js"); ?>

<form role="form" method="post" action="<?php echo $tour->exists ? action('TourController@update', [$zone_id, $tour->id]) : action('TourController@store', [$zone_id] ); ?>">
    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
    <?php if($tour->exists):?>
        <input type="hidden" name="_method" value="PATCH">
    <?php endif; ?>

    <input id="map-center" type="hidden" name="leaf_id" value="<?php echo $tour->leaf_id; ?>" data-lat="<?php echo $tour->leaf->location->lat; ?>" data-lng="<?php echo $tour->leaf->location->lng; ?>">
    <input type="hidden" name="items" id="items" value="<?php echo implode($tour->items, ','); ?>">
    <textarea style='display:none;' name="polyline" id="polygon-data"><?php echo $tour->polyline; ?></textarea>

    <div class="form-group<?php echo($errors->has('name'))?' has-error':''; ?>">
        <label id="tName" for="name">Name</label>
        <input type="text" class="form-control" id="name" name="name" aria-labelledby="tName" value="<?php echo Input::old('name',$tour->name); ?>">
        <?php if($errors->has('name')): ?><p class="help-block"><?php echo $errors->first('name'); ?></p><?php endif; ?>
    </div>


    <div class="form-group<?php echo($errors->has('description'))?' has-error':''; ?>">
        <label id="tDesc" for="description">Description</label>
        <input type="text" class="form-control" id="description" name="description" aria-labelledby="tDesc" value="<?php echo Input::old('description',$tour->description); ?>">
        <?php if($errors->has('description')): ?><p class="help-block"><?php echo $errors->first('description'); ?></p><?php endif; ?>
    </div>

    <div class="form-group<?php echo($errors->has('duration'))?' has-error':''; ?>">
        <label id="tDur" for="duration">Duration</label>
        <div class="input-group">
            <input type="text" class="form-control" id="duration" name="duration" aria-labelledby="tDur" value="<?php echo Input::old('duration',$tour->duration); ?>">
            <span class="input-group-addon">mins</span>
        </div>
        <?php if($errors->has('duration')): ?><p class="help-block"><?php echo $errors->first('duration'); ?></p><?php endif; ?>
    </div>

    <div class="row">

        <div class="col-sm-6<?php echo($errors->has('items'))?' has-error':''; ?>">
            <?php if($errors->has('items')): ?><p class="help-block"><?php echo $errors->first('items'); ?></p><?php endif; ?>
            <div class="row">
                <div class="col-sm-12 form form-horizontal">
                    <label>Search: <input id="poi-search" class="form-control" type="text" placeholder="Search POIs"></label>
                </div>
            </div>
            <div class="poi-tree scrollable-pois" data-leaf-id-input="items" data-expand-all="true" data-enable-add-child="false" data-disable-drag-and-drop="true" data-enable-multi-select="true">
                <?php
                    echo App\Http\Helpers\TreeHelper::printTree(
                        (int)$tour->leaf_id,
                        ['selected_leafs' => Input::old('items',$tour->items)]
                    );
                ?>
            </div>
        </div>

        <div class="col-sm-6 map-area">
            <div id='google-maps' ></div>
            <p class="clearfix">Please ensure the tour route forms a loop</p>
        </div>


    </div>

    <p>&nbsp;</p>

    <div class="row">
        <div class='col-md-10' id="message_area">
        </div>
        <div class='col-md-2'>
            <button class='btn btn-primary pull-right'>
                <?php if($tour->exists):?>Update<?php else: ?>Save<?php endif; ?>
                <i class='kf-chevron-right'></i>
             </button>
        </div>
    </div>
</form>
