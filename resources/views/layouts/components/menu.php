<nav class="navbar navbar-default">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
				<span class="sr-only">Toggle Navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			
		</div>

		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			<ul class="nav navbar-nav">
				<li class="<?php echo $linkHelper->isActive('/');?>"><a href="<?php echo url('/'); ?>">Home</a></li>
				<?php if (Auth::check()) { ?>
				<li class="<?php echo $linkHelper->isActive('poi');?>"><a href="<?php echo action('POIController@index'); ?>">Manage POIs</a></li>
				<li class="<?php echo $linkHelper->isActive('page');?>"><a href="<?php echo action('PageController@index'); ?>">Manage Pages</a></li>
				<li class="<?php echo $linkHelper->isActive('location');?>"><a href="<?php echo action('LocationController@index'); ?>">Manage Custom Locations</a></li>
				<?php } ?>
			</ul>
			<?php if (Auth::check()) { ?>
			<ul class="nav navbar-nav navbar-right">
				<p class="navbar-text"><?php $last = $revisionHelper::getLastSynced('D j M Y H:i:s'); if($last){ echo 'Last synced: ' . $last;} ?></p>
				<?php if(Config::has('app.staging_url')) { ?>
					<li>
						<button type="button" onclick="window.open('<?php echo Config::get('app.staging_url'); ?>')" class="btn btn-warning navbar-btn">Preview <i class="fa fa-external-link"></i></button>
					</li>
				<?php } ?>
				<li>
					<form id="sync_form" method="GET" action="<?php echo action('ZoneController@syncLive'); ?>">
						<button id="sync_btn" type="submit" class="btn btn-info navbar-btn">Publish to Live <i class="fa fa-check"></i></button>
					</form>
				</li>
			</ul>
			<?php } ?>
		</div>
	</div>
</nav>