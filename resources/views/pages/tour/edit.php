<?php $script->enqueue("content-form-scripts","js/content.min.js"); ?>
<script>
    APP_DATA.owner = "<?php echo $tour->id; ?>",
    APP_DATA.owner_type = "tour"
</script>
<div class="container">
    <div class="row">
        <div class="col-sm-12 actions">
            <a href="<?php echo URL::action('TourController@destroy', array($zone_id, $tour->id)); ?>" class="restful btn btn-danger pull-right" data-method="delete" data-modal="tourDeleteConfirm">Delete Tour <i class="fa fa-trash"></i></a>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 ">
        	<div class="panel panel-default tour-panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Edit Tour</h3>
                </div>
                <div class="panel-body">
                    <?php echo view('pages.tour.components.form', ['tour' => $tour, 'errors'=> $errors, 'zone_id' => $zone_id ])->render(); ?>
                </div>
            </div><!-- /.panel -->
            <div class="panel panel-default">
                <div class="panel-heading">Manage Content</div>
                <div class="panel-body">
                    <?php echo view('components.form_content',['owner'=>$tour])->render(); ?>
                </div>
            </div>
            <div id="new-content-panel" class="panel panel-default" style="display: none">
                <div class="panel-heading">New Content</div>
                <div class="panel-body">
                    <?php echo view('components.form_content_new',['owner'=>$tour])->render(); ?>
                </div>
                <div class="panel-footer"><a id="new-content-back" href="#" class="btn btn-default"><i class="kf-chevron-left"></i> Cancel</a></div>
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div>

<?php echo view('pages.tour.components.delete_modal')->render(); ?>