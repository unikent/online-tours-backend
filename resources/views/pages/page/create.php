<div class="container">
    <div class="row">
        <div class="col-md-12 ">
            <div class="panel panel-default poi-panel">
                <div class="panel-heading">New Page</div>

                <div class="panel-body">
                   <form method="POST" action="<?php echo action('PageController@store'); ?>">
                       <div class="form-group<?php echo $errors->has('title')?' has-error':''; ?>">
                           <label for="title">Title</label>
                           <input type="text" name="title" class="form-control" id="title" placeholder="Page Title"  value="" required />
                           <?php if($errors->has('title')){?><p class="help-block"><?php echo $errors->first('title'); ?></p><?php } ?>
                       </div>
                       <div class="row">
                           <div class='col-md-10' id="message_area">
                           </div>
                           <div class='col-md-2'>
                               <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                               <button type="submit" class='btn btn-primary pull-right'>Save <i class='kf-chevron-right'></i></button>
                           </div>
                       </div>
                   </form>
                </div>
            </div>
        </div>
    </div>
</div>