<div class="container">
    <div class="row">
        <div class="col-xs-12 ">

            <?php if (count($errors) > 0): ?>
            <div class="alert alert-danger">

                <p>Please fix the following errors:</p>

                <ul class="errors">
                    <?php foreach ($errors->all('<li>:message</li>') as $error) { echo $error; } ?>
                </ul>

            </div>
            <?php endif; ?>


            <form role="form" method="post" action="<?php echo url("/tours/" . $tour->id); ?>">
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="leafID" value="<?php echo $tour->leaf_id; ?>">

                <div class="panel panel-default poi-panel">

                    <div class="panel-heading">
                        <h3 class="panel-title">Edit Tour: <?php echo $tour->name ?></h3>
                    </div>
                    <div class="panel-body">


                        <div class="form-group">
                            <label id="tName" for="tourName">Name</label>
                            <input type="text" class="form-control" name="tourName" aria-labelledby="tName" value="<?php echo $tour->name ?>">
                        </div>


                        <div class="form-group">
                            <label id="tDesc" for="tourDescription">Description</label>
                            <input type="text" class="form-control" name="tourDescription" aria-labelledby="tDesc" value="<?php echo $tour->description ?>">
                        </div>

                        <div class="form-group">
                            <label id="tDur" for="tourDuration">Duration</label>
                            <input type="text" class="form-control" name="tourDuration" aria-labelledby="tDur" value="<?php echo $tour->duration ?>">
                            <span class="input-group-addon">mins</span>
                        </div>

                        <div class="col-sm-6">
                            <div class="poi-tree">
                                <?php
                                    echo App\Http\Helpers\TreeHelper::printTree(
                                        (int)$tour->leaf_id,
                                        ['selected_leafs' => $tour->leaf_id]
                                    );
                                ?>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <img src="http://placehold.it/600x600" alt="MAP GOES HERE" class="img-responsive">
                        </div>
                    </div>
                </div><!-- /.panel -->


                <div class="panel panel-default">
                    <div class="panel-heading">Manage Content</div>
                    <div class="panel-body">
                        <div class="form-group">
                            <textarea name="tourContent" class="form-control" rows="3" value="<?php echo $tour->content ?>"></textarea>
                        </div>
                    </div>
                </div><!-- /.panel -->


                <div class="pull-right">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>

            </form>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div>
