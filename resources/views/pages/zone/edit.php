<div class="container">
	<div class="row">
		<div class="col-md-12 ">
			<?php echo view('pages.zone.components.form', ['zone' => $zone])->render(); ?>
		</div>
	</div>
</div>

<?php echo view('pages.zone.components.delete_modal')->render(); ?>