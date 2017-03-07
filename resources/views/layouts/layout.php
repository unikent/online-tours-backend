<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Kent Tours</title>

    <script>
        var APP_DATA = { "app_url": '<?php echo url('/'); ?>' };
        var APP_UTIL = {};
    </script>
    
	<?php 
		// Queue assets
		$style->enqueue('base', '/css/app.css');
		$script->enqueue('core-js', 'js/app.min.js');
        $script->enqueue('routes', 'js/routes.js');
	?>

	<?=$style;?>

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<!--[if IE ]>
<body class="ie-compat">
<![endif]-->
<!--[if !IE]>-->
<body>
<!--<![endif]-->
	<?php echo view('layouts.components.kentbar')->render(); ?>
	<?php echo view('layouts.components.menu')->render(); ?>

    <?php if(Session::has('alert') || $errors->count()>0){
        $alert = Session::get('alert');
        ?>
        <div class="container page_alerts">
            <div class="row">
                <div class="col-xs-12">
                    <?php if (Session::has('alert')){ ?>
                        <div class="alert alert alert-<?php echo $alert['type']; ?> alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <?php echo $alert['message']; ?>
                        </div>
                    <?php
                    }
                    if($errors->count()>0){
                        ?>
                        <div class="alert alert alert-danger alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            Your submission contained errors please correct them in order to proceed. Details can be found inline below.</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>

	<?php echo $content; ?>

    <?php echo view('layouts.components.sync_modal')->render(); ?>

	<!-- Scripts -->
	<?php echo $script; ?>


</body>
</html>
