<div class="row">
    <div class="well well-left col-sm-12 col-md-5 col-lg-4">
        <div class="form-group">
            <label for="<?php echo $content->exists?$content->id:'new'; ?>-mp3">MP3</label>
            <?php if($content->exists && $content->getFileUri('mp3')){ ?>
            <audio src="<?php echo $content->getFileUri('mp3'); ?>" controls="controls"></audio>
            <?php } ?>
            <input type="file" id="<?php echo $content->exists?$content->id:'new'; ?>-mp3" name="mp3" class="form-control" accept="audio/mpeg3"<?php if(!$content->exists){ echo ' required'; } ?>>
        </div>
        <div class="form-group">
            <label for="<?php echo $content->exists?$content->id:'new'; ?>-ogg">OGG</label>
            <?php if($content->exists && $content->getFileUri('ogg')){ ?>
                <audio src="<?php echo $content->getFileUri('ogg'); ?>" controls="controls"></audio>
            <?php } ?>
            <input type="file" id="<?php echo $content->exists?$content->id:'new'; ?>-ogg" name="ogg" class="form-control" accept="audio/ogg">
        </div>
        <div class="form-group">
            <label for="<?php echo $content->exists?$content->id:'new'; ?>-wav">WAV</label>
            <?php if($content->exists && $content->getFileUri('wav')){ ?>
                <audio src="<?php echo $content->getFileUri('wav'); ?>" controls="controls"></audio>
            <?php } ?>
            <input type="file" id="<?php echo $content->exists?$content->id:'new'; ?>-wav" name="wav" class="form-control" accept="audio/wav">
        </div>
    </div>
    <div class="col-sm-12 col-md-7 col-lg-8">
        <div class="form-group">
            <label for="content-<?php echo $content->exists?$content->id:'new'; ?>-title">Title</label>
            <input type="text" id="content-<?php echo $content->exists?$content->id:'new'; ?>-title" name="title" class="form-control" required value="<?php echo $content->title; ?>">
        </div>
        <div class="form-group">
            <label for="content-<?php echo $content->exists?$content->id:'new'; ?>-transcription">Transcription</label>
            <textarea id="content-<?php echo $content->exists?$content->id:'new'; ?>-transcription" name="transcription" class="form-control" rows="3" required ><?php echo $content->transcription; ?></textarea>
        </div>
    </div>
</div>
