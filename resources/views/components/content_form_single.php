<?php if ($content->exists){ ?>
<h3 id="content-<?php echo $content->id; ?>-heading" class="content-title"><i class="handle pull-left fa fa-arrows"></i><span class="pull-left content-heading-name"><?php echo $content->name; ?></span><span class="content-heading-type pull-right"><?php echo $content::TYPE ?></span></h3>
<?php } ?>
<form id="content-<?php echo $content->exists?$content->id:'new'; ?>" class="content-<?php echo $content::TYPE; ?>" data-type="<?php echo $content::TYPE; ?>">
    <div class="form-group">
        <label for="content-<?php echo $content->exists?$content->id:'new'; ?>-name">Identifier <em class="small">(internal use only)</em></label>
        <input type="text" id="content-<?php echo $content->exists?$content->id:'new'; ?>-name" name="name" class="form-control" value="<?php echo $content->name; ?>" required>
    </div>
    <?php echo view('components.content_' . $content::TYPE, ['content'=>$content])->render(); ?>
    <div class="form-group">
        <?php if ($content->exists){ ?>
        <input type="hidden" name="id" class="content-id" id="content-<?php echo $content->id; ?>-id" value="<?php echo $content->id; ?>">
        <?php } ?>
        <input type="hidden" name="type" class="content-type" id="content-<?php echo $content->exists?$content->id:'new'; ?>-type" value="<?php echo $content::TYPE ?>">
        <?php if ($content->exists){ ?><input type="hidden" name="_method" value="PATCH"><?php } ?>
        <div class="row"><?php if ($content->exists){ ?><div class="col-sm-4 col-md-3 col-lg-2"><button class="btn btn-danger btn-block content-delete"><i class='fa fa-times'></i> Remove</button></div><?php } ?><div class="<?php echo $content->exists?'col-sm-4 col-md-6 col-lg-8':'col-sm-8 col-md-9 col-lg-10'; ?> content-alerts"> </div><div class="col-sm-4 col-md-3 col-lg-2"><button type="submit" class="btn btn-primary btn-block content-save" <?php echo $content->exists?'disabled':''; ?>><?php echo $content->exists?'Update':'Save'; ?> <i class='kf-chevron-right'></i></button></div></div>
    </div>
</form>