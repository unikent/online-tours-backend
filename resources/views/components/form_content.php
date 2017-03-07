<?php
    $contentHelper::enqueueAssets();
?>
<script>
    if (typeof APP_DATA.dropzones === "undefined"){ APP_DATA.dropzones = {}; }
</script>
<?php
    $contentHelper::getContentList($owner);
?>
<div class="row">
    <div class="col-sm-6 col-sm-offset-3"><a href="#" id="add-content-btn" class="btn btn-primary btn-block">Add Content <i class="fa fa-plus"></i></a></div>
</div>
<div id="contentDeleteConfirm" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Delete / Detach</h2>
            </div>
            <div class="modal-body">
                <p>If you <strong class="text-warning" >DETACH</strong> this content it will simply remove it from this POI/Tour. The content will remain in the database, will remain attached to any other POI's and Tours where it is being used, and can be reused elsewhere.</p>
                <p>If you <strong class="text-danger">DELETE</strong> this content, it will remove it from the database. This content will be removed from all POI's and Tours where it is being used.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="kf-chevron-left"></i> Cancel</button>
                <button id="contentDetachConfirmButton" type="button" class="btn btn-warning">Detach <i class="fa fa-chain-broken "></i></button>
                <button id="contentDeleteConfirmButton" type="button" class="btn btn-danger">Delete <i class="fa fa-trash"></i></button>
            </div>
        </div>

    </div>
</div>