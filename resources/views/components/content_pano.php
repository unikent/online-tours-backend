<div class="form-group">
    <label for="content-<?php echo $content->exists?$content->id:'new'; ?>-value">Value</label>
    <textarea id="content-<?php echo $content->exists?$content->id:'new'; ?>-value" name="value" class="form-control" rows="3" required ><?php echo $content->value; ?></textarea>
</div>