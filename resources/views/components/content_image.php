<div class="row">
    <div class="well col-sm-12 col-md-5 col-lg-4">
        <div class="form-group">
            <?php if($content->exists && $content->getUri('largethumb')){ ?>
                <img class="img-responsive" src="<?php echo $content->getUri('largethumb'); ?>"><br />
            <?php } ?>
            <input type="file" id="<?php echo $content->exists?$content->id:'new'; ?>-file" name="img" class="form-control" accept="image/*">
        </div>
    </div>
    <div class="col-sm-12 col-md-7 col-lg-8">
        <div class="form-group">
            <label for="content-<?php echo $content->exists?$content->id:'new'; ?>-caption">Caption</label>
            <input type="text" id="content-<?php echo $content->exists?$content->id:'new'; ?>-caption" name="caption" class="form-control" required value="<?php echo $content->caption; ?>">
        </div>
        <div class="form-group">
            <label for="content-<?php echo $content->exists?$content->id:'new'; ?>-copyright">Copyright</label>
            <input type="text" id="content-<?php echo $content->exists?$content->id:'new'; ?>-copyright" name="copyright" class="form-control" required value="<?php echo $content->copyright; ?>">
        </div>
    </div>
</div>