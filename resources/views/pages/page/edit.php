<?php $script->enqueue("page-form-scripts","js/page.min.js"); ?>
<script>
    APP_DATA.owner = "<?php echo $page->id; ?>",
    APP_DATA.owner_type = "page"
</script>
<div class="container">
    <div class="row">
        <div class="col-md-12 ">
            <div class="actions row">
                <div class="container">
                    <a href="<?php echo action('PageController@destroy', array('id' => $page->id)); ?>" class="restful btn btn-danger pull-right" data-method="delete" data-modal="pageDeleteConfirm">Delete Page <i class="fa fa-trash"></i></a>
                </div>
            </div>
            <div class="panel panel-default poi-panel">
                <div class="panel-heading">Edit Page</div>

                <div class="panel-body">
                    <form id="page_edit" method="POST" action="<?php echo action('PageController@update',[$page->id]); ?>">
                        <div class="form-group<?php echo $errors->has('title')?' has-error':''; ?>">
                            <label for="title">Name</label>
                            <input type="text" name="title" class="form-control" id="title" placeholder="Page Title"  value="<?php echo Input::old('title',$page->title); ?>" required />
                            <?php if($errors->has('title')){?><p class="help-block"><?php echo $errors->first('title'); ?></p><?php } ?>
                        </div>
                        <div class="form-group">
                            <label for="slug">Slug</label>
                            <p class="form-control-static"><em><?php echo $page->slug; ?></em></p>
                        </div>
                        <div class="row">
                            <div class="col-md-10" id="message_area">
                            </div>
                            <div class="col-md-2">
                                <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                                <input type="hidden" name="id" value="<?php echo $page->id; ?>">
                                <input type="hidden" name="_method" value="PATCH">
                                <button type="submit" class="btn btn-primary pull-right page-save" disabled>Save <i class="kf-chevron-right"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Manage Content</div>
                <div class="panel-body">
                    <?php echo view('components.form_content',['owner'=>$page])->render(); ?>
                </div>
            </div>
            <div id="new-content-panel" class="panel panel-default" style="display: none">
                <div class="panel-heading">New Content</div>
                <div class="panel-body">
                    <?php echo view('components.form_content_new',['owner'=>$page])->render(); ?>
                </div>
                <div class="panel-footer"><a id="new-content-back" href="#" class="btn btn-default"><i class="kf-chevron-left"></i> Cancel</a></div>
            </div>
        </div>
    </div>
</div>
<?php echo view('pages.page.components.delete_modal')->render(); ?>
