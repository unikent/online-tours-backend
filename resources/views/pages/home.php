<?php $script->enqueue("tour","js/zone.min.js"); ?>
<div class="container">
	<div class="row">
		<div class="col-xs-12">
            <p><a href="<?php echo action('ZoneController@create'); ?>" class="btn btn-success">Add new zone</a></p>

			<?php foreach ($zones as $zone): ?>
            <div class="panel panel-default tour-panel">

                <div class="panel-heading">
                    <div class="col-xs-8">
                        <h3 class="panel-title"><?php echo $zone->name; ?></h3>
                    </div>
                    <div class="col-xs-4">
                        <div class="pull-right">
                            <a href="<?php echo action('ZoneController@edit', [$zone->slug]); ?>" class="btn btn-primary">Edit</a>
                            <span data-toggle="collapse" data-target="#zone-<?php echo $zone->leaf_id; ?>" class="zone-toggle collapsed fa"></span>
                        </div>
                    </div>
                </div>

                <div id="zone-<?php echo $zone->leaf_id; ?>" class="panel-body collapse">

                    <div class="col-xs-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Featured Tours</h3>
                            </div>

                            <div class="panel-body">
                                <ol class="sortable" data-sortable-id="zF<?php echo $zone->leaf_id; ?>" data-sortable-with="zS<?php echo $zone->leaf_id; ?>">
                                    <?php foreach ($zone->tours as $tour): ?>
                                        <?php if ($tour->featured): ?>
                                            <li data-sort-id="t<?php echo $tour->id ?>"><?php echo $tour->name ?> <a href="<?php echo url('tours'); ?>/<?php echo $tour->id ?>/edit" class="btn btn-link btn-sm"><i class="fa fa-pencil"></i></a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ol>
                            </div>

                        </div><!-- /.panel -->

                    </div><!-- /.col -->


                    <div class="col-xs-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">More Tours</h3>
                            </div>

                            <div class="panel-body">
                                <ol class="sortable" data-sortable-id="zS<?php echo $zone->leaf_id; ?>" data-sortable-with="zF<?php echo $zone->leaf_id; ?>">
                                    <?php foreach ($zone->tours as $tour): ?>
                                        <?php if (!$tour->featured): ?>
                                            <li data-sort-id="t<?php echo $tour->id ?>">
                                                <?php echo $tour->name ?>
                                                <a href="<?php echo action('TourController@edit', [ $zone->leaf_id, $tour->id]); ?>" class="btn btn-link btn-sm"><i class="fa fa-pencil"></i></a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ol>
                            </div>

                        </div><!-- /.panel -->

                    </div><!-- /.col -->

                    <div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2">
                        <a href="<?php echo action('TourController@create', [ $zone->leaf_id]); ?>" class="btn btn-success btn-block">Add Tour</a>
                    </div>
                </div><!-- /.panel-body -->
            </div><!-- /.panel -->
            <?php endforeach; ?>
		</div>
	</div>
</div>