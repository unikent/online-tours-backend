<div class="container">
    <div class="row">
        <div class="col-md-12 ">
            <p><a href="<?php echo action('PageController@create'); ?>" class="btn btn-success">Add new page</a></p>
            <div class="panel panel-default poi-panel">
                <div class="panel-heading">Manage Pages</div>

                <div class="panel-body">
                    <table class="table table-striped">
                    <?php
                    if(count($pages)<1){
                    ?>
                        <tr>
                            <td colspan="2" style="text-align: center">You do not have any Pages.</td>
                        </tr>
                    <?php
                    }else{
                    ?>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Slug</th>
                        </tr>
                    </thead>
                    <?php
                    foreach($pages as $page){
                    ?>
                        <tr>
                            <td><a href="<?php echo action('PageController@edit',[$page->id]); ?>"><?php echo $page->title;?></a></td>
                            <td><em><?php echo $page->slug;?></em></td>
                        </tr>
                    <?php
                    }}
                    ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
