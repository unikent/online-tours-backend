    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="content-<?php echo $content->exists?$content->id:'new'; ?>-title">Title</label>
                <input type="text" id="content-<?php echo $content->exists?$content->id:'new'; ?>-title" name="title" class="form-control" required value="<?php echo $content->title; ?>">
            </div>
            <div class="form-group">
                <label for="content-<?php echo $content->exists?$content->id:'new'; ?>-value">Video URL</label>
                <input id="content-<?php echo $content->exists?$content->id:'new'; ?>-value" class="form-control youtube-control" type="url" value="<?php echo $content->value; ?>">
                <input type="hidden" class="youtube-save" name="value" value="<?php echo $content->value; ?>">
            </div>
        </div>
        <div class="col-sm-6">
            <iframe width="492" height="277" src="<?php echo $content->value ?>" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>
