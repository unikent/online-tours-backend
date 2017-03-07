<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Kent Tours</title>

	<?php 
		// Queue assets
		$style->enqueue("base", "/css/app.css");
	?>

	<?php echo $style; ?>

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body>
	<?php echo view('layouts.components.kentbar')->render(); ?>

    <?php if(Session::has('alert')): ?>
    	<?php $alert = Session::get('alert'); ?>

	    <div class="container messages">
	        <div class="row">
	            <div class="message alert message--alert alert-<?php echo $alert['type']; ?> alert-dismissible" role="alert">
	                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	                <?php echo $alert['message']; ?>
	            </div>
	        </div>
	    </div>
    <?php endif; ?>

	<div class="container messages">
	    <div class="row">
	        <div class="col-xs-12">
	            <p class="alert alert-danger">
	                <?php echo $message; ?>
	            </p>
	        </div>
	    </div>
	</div>

	<!-- Scripts -->
	<?php echo $script; ?>

</body>
</html>
