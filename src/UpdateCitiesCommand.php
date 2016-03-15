<?php namespace Znck\Cities;

use DB;
use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class UpdateCitiesCommand extends Command
{
    const QUERY_LIMIT = 100;
    const INSTALL_HISTORY = 'vendor/znck/cities/install.txt';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cities:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update/Install cities in database.';

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var FileLoader
     */
    protected $loader;

    /**
     * @var string
     */
    protected $cities;

    /**
     * @var string
     */
    protected $states;

    /**
     * @var string
     */
    protected $hash;

    /**
     * Create a new command instance.
     *
     * @param Filesystem $files
     * @param Application $app
     */
    public function __construct(Filesystem $files, Application $app)
    {
        parent::__construct();

        $this->files = $files;

        $this->path = dirname(__DIR__).'/data/en';

        $this->loader = new FileLoader($files, dirname(__DIR__).'/data');

        $config = $app->make('config');
        $this->cities = $config->get('cities');
        $this->states = $config->get('states');

        if ($this->files->exists(storage_path(self::INSTALL_HISTORY))) {
            $this->hash = $this->files->get(storage_path(self::INSTALL_HISTORY));
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $countries = $this->files->directories($this->path);

        $cities = [];

        foreach ($countries as $countryDirectory) {
            $states = $this->files->files($countryDirectory);
            $country = $this->last(explode(DIRECTORY_SEPARATOR, $countryDirectory));
            foreach ($states as $stateDirectory) {
                list($state, $_) = explode('.', $this->last(explode(DIRECTORY_SEPARATOR, $stateDirectory)));
                $data = $this->loader->load($country, $state, 'en');
                foreach ($data as $key => $name) {
                    $cities[] = [
                        'name'     => $name,
                        'code'     => "${country} ${state} ${key}",
                        'state_id' => "${country} ${state}",
                    ];
                }
            }
        }

        $cities = Collection::make($cities);
        $hash = md5($cities->toJson());

        if ($hash === $this->hash) {
            return false;
        }

        $cityCodes = $cities->pluck('code');

        $stateCodes = $cities->pluck('state_id')->unique();
        $stateIds = Collection::make(DB::table($this->states)->whereIn('code', $stateCodes)->pluck('id', 'code'));

        $cities->map(
            function ($item) use ($stateIds) {
                $item['state_id'] = $stateIds->get($item['state_id']);

                return $item;
            }
        );


        $existingCityIDs = Collection::make(DB::table($this->cities)->whereIn('code', $cityCodes)->pluck('id', 'code'));
        $cities->map(
            function ($item) use ($existingCityIDs) {
                if ($existingCityIDs->has($item['code'])) {
                    $item['id'] = $existingCityIDs->get($item['code']);
                }

                return $item;
            }
        );

        $cities = $cities->groupBy(
            function ($item) {
                return array_has($item, 'id') ? 'update' : 'create';
            }
        );

        DB::transaction(
            function () use ($cities, $hash) {
                $create = Collection::make($cities->get('create'));
                $update = Collection::make($cities->get('update'));

                foreach ($create->chunk(static::QUERY_LIMIT) as $entries) {
                    DB::table($this->cities)->insert($entries);
                }

                foreach ($update->chunk(static::QUERY_LIMIT) as $entries) {
                    DB::table($this->cities)->update($entries);
                }
                $this->files->put(storage_path(static::INSTALL_HISTORY), $hash);
            }
        );
    }

    private function last(array $data)
    {
        if (empty($data)) {
            throw new \Exception("$data should not be empty.");
        }

        return $data[count($data) - 1];
    }
}