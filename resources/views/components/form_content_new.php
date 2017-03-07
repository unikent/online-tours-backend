<div id="choose-content-type" class="row open">
    <div class="col-sm-6 col-sm-offset-3">
        <div class="form-group">
            <label for="new-content-type">Select Content Type</label>
            <select id="new-content-type" class="form-control">
                <?php
                foreach($contentHelper::getTypes() as $type){
                    echo '<option value="' .$type . '">'. ucfirst($type) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <button id="choose-content-type-next" class="btn btn-primary pull-right">Next <i class="kf-chevron-right"></i></button>
            </div>
        </div>
    </div>
</div>
<div id="new-content-form" class="row" style="display: none">
    <div class="col-sm-12">
        <ul class="nav nav-tabs">
            <li role="presentation" class="active"><a href="#editContent" data-toggle="tab">Create new <span class="typeName"></span></a></li>
            <li role="presentation"><a href="#existingContent" data-toggle="tab">Use existing <span class="typeName"></span></a></li>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="editContent">
                <div class="col-sm-12">

                </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="existingContent">
                <div class="col-sm-12">
                    <input type="hidden" class="big-select2 content-search" placeholder="Search Content" value=""> <a id="add-existing-content" class="btn btn-primary">Add <i class="kf-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>