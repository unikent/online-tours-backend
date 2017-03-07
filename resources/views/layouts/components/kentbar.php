<div class='kentbar navbar'>
	<div class="container-fluid">
	<a class="navbar-brand" href="#"><i class="kf-kent-horizontal"></i></a>
			<ul class="nav navbar-nav navbar-right">
				<?php if (Auth::check()): ?>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?=Auth::user()->name;?> <span class="caret"></span></a>
						<ul class="dropdown-menu" role="menu">
							<li><a href="<?php echo url("/auth/logout"); ?>">Logout</a></li>
						</ul>
					</li>
				<?php endif; ?>
			</ul>
	</div>
</div>