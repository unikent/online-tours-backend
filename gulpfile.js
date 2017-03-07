// Quiet the elixir notifications
process.env.DISABLE_NOTIFIER = true;

var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
	mix.less('app.less');
	//mix.copy('resources/assets/vendor/bootstrap/fonts','public/fonts');
	mix.copy('resources/assets/vendor/kent-font/public/fonts','public/fonts');
    mix.copy('resources/assets/vendor/font-awesome/fonts','public/fonts');
	mix.copy('resources/assets/vendor/jstree-bootstrap-theme/dist/themes/proton/30px.png','public/css/30px.png');
	mix.copy('resources/assets/vendor/jstree-bootstrap-theme/dist/themes/proton/32px.png','public/css/32px.png');
	mix.copy('resources/assets/vendor/jstree-bootstrap-theme/dist/themes/proton/throbber.gif','public/css/throbber.gif');

	mix.copy('resources/assets/vendor/select2/select2.png','public/css/select2.png');
	mix.copy('resources/assets/vendor/select2/select2.png','public/css/select2.png');
	mix.copy('resources/assets/vendor/select2/select2-spinner.gif','public/css/select2-spinner.gif');

    mix.copy('resources/assets/vendor/medium-editor/dist/css/medium-editor.min.css','public/css/medium-editor.min.css');
    mix.copy('resources/assets/vendor/medium-editor/dist/css/themes/default.min.css','public/css/default.min.css');

	mix.scripts(
		[
			"../vendor/jquery/dist/jquery.js",
            "../vendor/jquery-ui/jquery-ui.js",
            "../vendor/jquery-cookie/jquery.cookie.js",
            "../vendor/dirtyFields-jQuery-Plugin/jquery.dirtyFields.js",
			"../vendor/bootstrap/dist/js/bootstrap.js",
			"../vendor/jstree-bootstrap-theme/dist/jstree.js",
			"../vendor/select2/select2.js",
			"app.js",
			"tree.js"
		],
		'public/js/app.min.js'
	);

	mix.scripts(
		[
			"poi.js"
		],
		'public/js/poi.min.js'
	);

	mix.scripts(
		[
			"location.js"
		],
		'public/js/location.min.js'
	);

    mix.scripts(
        [
            "../vendor/dropzone/dist/dropzone.js",
            "../vendor/medium-editor/dist/js/medium-editor.js",
            "content.js"
        ],
        'public/js/content.min.js'
    );

    mix.scripts(
		[
			"map.js"
		],
		'public/js/map.min.js'
	);

	mix.scripts(
		[
			"tour.js"
		],
		'public/js/tour.min.js'
	);

	mix.scripts(
		[
			"zone.js"
		],
		'public/js/zone.min.js'
	);

    mix.scripts(
        [
            "page.js"
        ],
        'public/js/page.min.js'
    );

});
