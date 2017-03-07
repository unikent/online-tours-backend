<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Zone;
use App\Models\Tour;
use App\Models\Leaf;
use App\Models\Page;

class GenerateEndpoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:endpoints {connection=live}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate text file for load testing API endpoints.';

    /**
     * Path to file we'll be creating, relative to storage/app
     *
     * @var string
     */
    protected $file = 'siege.txt';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::setDefaultConnection($this->argument('connection'));

        $routes = app('router')->getRoutes()->getRoutes();

        $zones = array_flatten(Zone::take(3)->get(['leaf_id'])->toArray());
        $tours = array_flatten(Tour::take(10)->get(['id'])->toArray());
        $pois  = array_flatten(Leaf::orderByRaw("RAND()")->take(50)->get(['id'])->toArray());
        $pages = array_flatten(Page::take(5)->get(['id'])->toArray());

        $apiRoutes = [];

        foreach ($routes as $route) {
            if (false !== strpos($route->getPath(), 'api/')) {
                $apiRoutes[] = $route->getPath();
            }
        }

        foreach ($apiRoutes as $key => $route) {
            $slugs = ['zone', 'tour', 'poi', 'page'];
            $apiRoutes[$key] = $route = preg_replace('/{connection}/', $this->argument('connection'), $route);
            echo $route . "\r\n";
            foreach ($slugs as $slug) {
                if (strpos($route, $slug . '/')) {
                    foreach (${$slug . 's'} as $id) {
                        $apiRoutes[] = preg_replace('/{\w+}/', $id, $route);
                    }
                    unset($apiRoutes[$key]);
                    continue;
                }
            }
        }

        $this->writeSiegeFile($apiRoutes);
    }

    /**
     * Writes the routes to a siegefile
     *
     * @param array routes
     * @return void
     */
    protected function writeSiegeFile(array $routes)
    {
        if (\Storage::has($this->file)) {
            \Storage::delete($this->file);
        }

        $url = getenv('APP_URL');
        if (substr($url, -1) === '/') {
            $url = substr($url, 0, -1);
        }

        $write = ['API_URL=' . $url];

        foreach ($routes as &$route) {
            $route = '$(API_URL)/' . $route;
        }
        unset($route);

        $write = array_merge($write, $routes);
        \Storage::put($this->file, implode($write, "\n"));

        echo "\n" . 'Siegefile created successfully in ' . storage_path('app/' . $this->file) . "\n\n";
    }
}
