<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase {

    protected $baseUrl = 'http://localhost';
	public $auth_user = null;

	/**
	 * Creates the application.
	 *
	 * @return \Illuminate\Foundation\Application
	 */
	public function createApplication()
	{
        $app = require __DIR__.'/../bootstrap/app.php';
		$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
		return $app;
	}

    public function __call($method, $args)
    {
        if (in_array($method, ['get', 'post', 'put', 'patch', 'delete']))
        {
            return $this->call($method, $args[0]);
        }

        throw new BadMethodCallException;
    }

    public function action($method, $action, $wildcards = array(), $parameters = array(), $cookies = array(), $files = array(), $server = array(), $content = null){
    	if(in_array($method, array('POST', 'PUT', 'PATCH', 'DELETE'))){
    		if(!isset($parameters['_token'])){
    			$parameters['_token'] = csrf_token();
    		}
    	}
    	return parent::action($method, $action, $wildcards, $parameters, $cookies, $files, $server, $content);
    }

    public function htmlAction($method, $action, $wildcards = array(), $parameters = array(), $cookies = array(), $files = array(), $server = array(), $content = null){
    	$server = array_merge(array('HTTP_ACCEPT' => 'text/html'), $server);
    	return $this->action($method, $action, $wildcards, $parameters, $cookies, $files, $server, $content);
    }

    public function jsonAction($method, $action, $wildcards = array(), $parameters = array(), $cookies = array(), $files = array(), $server = array(), $content = null){
    	$server = array_merge(array('HTTP_ACCEPT' => 'application/json'), $server);
    	return $this->action($method, $action, $wildcards, $parameters, $cookies, $files, $server, $content);
    }

    public function ajaxAction($method, $action, $wildcards = array(), $parameters = array(), $cookies = array(), $files = array(), $server = array(), $content = null){
    	$server = array_merge(array('HTTP_X-Requested-With' => 'XMLHttpRequest'), $server);
    	return $this->action($method, $action, $wildcards, $parameters, $cookies, $files, $server, $content);
    }

	public function setUp()
	{
		parent::setUp();
		$this->resetEvents();
	}

	public function tearDown()
	{
	    Mockery::close();

        // If an auth_user has been set / persisted, get rid of it
        if($this->auth_user && $this->auth_user->exists){
            $this->auth_user->forceDelete();
        }

		parent::tearDown();
	}

	public static function setUpBeforeClass()
	{
        // Bootstrap an app instance
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        // Use it to nuke the 'users' table (clear out any stray users not caught by tearDown logic)
        $app->make('Illuminate\Database\DatabaseManager')->table('users')->truncate();
		parent::setUpBeforeClass();
	}

	public static function tearDownAfterClass()
    {
		parent::tearDownAfterClass();
	}

	// Manually reboot our models since laravel dowsnt do it for us after a flush
	private function resetEvents()
	{
		// Get all models in the Model directory
		$pathToModels = 'app/Models';
		$files = File::files($pathToModels);

		// Remove the directory name and the .php from the filename
		$files = str_replace($pathToModels.'/', '', $files);
		$files = str_replace('.php', '', $files);

		// Remove "BaseModel" as we dont want to boot that moodel
		if(($key = array_search('BaseModel', $files)) !== false) {
			unset($files[$key]);
		}


		// Reset each model event listeners.
		foreach ($files as $model) {
			$model = 'App\\Models\\'.$model;

            if( is_subclass_of($model,'Illuminate\Database\Eloquent\Model')) {
                // Flush any existing listeners.
                call_user_func(array($model, 'flushEventListeners'));

                // Reregister them.
                call_user_func(array($model, 'boot'));
            }
		}
	}


	public function setUnauthenticatedSession(){
		$this->app->make('Illuminate\Auth\AuthManager')->logout();
	}

	public function setAuthenticatedSession()
	{
		if($this->auth_user){
			$this->actingAs($this->auth_user);
		} else {
			$this->auth_user = factory('App\Models\User')->create();
			$this->setAuthenticatedSession();
		}
	}

	// Might come in handy in the future... e.g. for approval?
	public function setAdminSession()
	{
		$this->setAuthenticatedSession();
	}


	public function setAuthenticatedSessionWithPermissions(array $perms){
		// Set up user with permissions
		$this->auth_user = factory('App\Models\User')->create();
		$this->setAuthenticatedSession();
	}


    public static function search_array_for_object($array,$object,$field='id'){
        foreach($array as $obj) {
            if ($object->$field == $obj->$field) {
                return true;
            }
        }
        return false;
    }
}