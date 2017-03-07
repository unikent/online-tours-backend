<div class="container">
	<div class="row">
		<div class="col-md-12 ">
			<div class="panel panel-default poi-panel">
				<div class="panel-heading">Custom Locations</div>
				<div class="panel-body">
					<nav class="pull-right">
						<a href="<?php echo action('LocationController@create'); ?>" class="btn btn-primary">Create</a>
					</nav>

					<br /><br />

					<table class="table table-striped">
						<tr>
							<th>Name</th>
							<th></th>
						</tr>

						<?php if(count($locations)): ?>
							<?php foreach($locations as $location): ?>
								<tr>
									<td><a href="<?php echo action('LocationController@edit', $location->id); ?>"><?php echo $location->name; ?></a></td>
									<td>
										<a href="<?php echo action('LocationController@edit', $location->id); ?>">Edit</a>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else: ?>
							<tr>
								<td colspan="2">Currently, there are no custom locations.</td>
							</tr>
						<?php endif; ?>
					</table>

				</div>
			</div>
		</div>
	</div>
</div>