<?php

return [

    /*
     * The destination path for the javascript file.
     */
    'path' => 'public/js',

    /*
     * The destination filename for the javascript file.
     */
    'filename' => 'routes',

    /*
     * The namespace for the helper functions. By default this will bind them to
     * `window.laroute`.
     */
    'namespace' => 'onlinetours',

    /*
     * Generate absolute URLs
     *
     * Set the Application URL in config/app.php
     */
    'absolute' => true,

    /*
     * The Filter Methode
     *
     * 'all' => All routes except "'laroute' => false"
     * 'only' => Only "'laroute' => true" routes
     * 'force' => All routes, ignored "laroute" route parameter
     */
    'filter' => 'all',

    /*
     * Controller Namespace
     *
     * Set here your controller namespace (see RouteServiceProvider -> $namespace) for cleaner action calls
     * e.g. 'App\Http\Controllers'
     */
    'action_namespace' => 'App\Http\Controllers',

    /*
     * The path to the template `laroute.js` file. This is the file that contains
     * the ported helper Laravel url/route functions and the route data to go
     * with them.
     */
    'template' => 'vendor/lord/laroute/src/templates/laroute.js',

];
