<div class="container">
    <div class="row">
        <div class="col-xs-12 ">
            <div class="panel panel-default tour-panel">

                <div class="panel-heading">
                    <h3 class="panel-title">Add Tour</h3>
                </div>
                <div class="panel-body">
                    <?php echo view('pages.tour.components.form', ['tour' => $tour, 'errors'=> $errors, 'zone_id'=>$zone_id])->render(); ?>
                </div>
            </div><!-- /.panel -->
            <div class="panel panel-default">
                <div class="panel-heading">Manage Content</div>
                <div class="panel-body">
                    You need to save the new tour before adding content.
                </div>
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div>