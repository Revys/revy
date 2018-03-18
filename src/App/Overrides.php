<?php
namespace Revys\Revy\App;

use Symfony\Component\Finder\SplFileInfo;

class Overrides
{
	protected $cache_directory = "overrides";
	protected $map_file = "autoload_map.php";
    protected $overrides_directory = 'overrides';

    protected $map = array();
	public $composer_map;

	private $cache_classes;
    private $forceIndex;

    public function register()
	{
		spl_autoload_register(array($this, 'loadClass'), true, true);

		$this->forceIndex = config('revy.config.force_overrides_index');
		$this->cache_directory = storage_path('revy') . DIRECTORY_SEPARATOR . $this->cache_directory;
		$this->cache_classes = $this->cache_directory . DIRECTORY_SEPARATOR . 'classes';
		$this->map_file = $this->cache_directory . DIRECTORY_SEPARATOR . $this->map_file;
        $this->overrides_directory = base_path() . DIRECTORY_SEPARATOR . $this->overrides_directory;

		if (! is_dir($this->overrides_directory))
			\File::makeDirectory($this->overrides_directory, 0755, true);

		if (! is_dir($this->cache_classes))
			\File::makeDirectory($this->cache_classes, 0755, true);

		if (! is_dir($this->cache_directory))
			\File::makeDirectory($this->cache_directory, 0755, true);

		if (! \File::exists($this->map_file) or $this->forceIndex)
			$this->indexOverrides();
		else
		    $this->map = include_once $this->map_file;
	}

	public function loadClass($class)
    {
        $mapped_file = $this->loadMappedFile($class);

        if ($mapped_file) {
            return $mapped_file;
        }

        return false;
    }

    protected function loadMappedFile($class)
    {
        if (isset($this->map[$class]) === false) {
            return false;
        }

        if (! isset($this->map[$class]))
			return false;

		$file = base_path() . $this->map[$class];

        if ($this->requireFile($file))
            return $file;

        return false;
    }

    protected function requireFile($file)
    {
        if (file_exists($file)) {
            require $file;
            return true;
        }

        return false;
    }

	public function indexOverrides()
    {
		$map = array();

		$directories = array(
			base_path('vendor/revys/revy/src/App'),
			base_path('vendor/revys/revy-admin/src/App')
		);

		\File::cleanDirectory($this->cache_classes);

		foreach ($directories as $directory) {
		    if (! \File::exists($directory))
		        continue;

			$files = \File::allFiles($directory);

			if (count($files)) {
				foreach ($files as $file) {
					if (strpos($file->getFileName(), 'Base.php') !== false) {
						$class = $this->getClassName($file);

						$dir = $this->findInOverrides($class);

						if ($dir !== false) {
							$map[$class] = $dir;
						} else {
                            try {
                                $fileName = $this->makeOverrideClass($class, false, true);
                            } catch (\Exception $e) {
                                \Log::error($e->getMessage());
                                break;
                            }

                            if ($fileName)
								$map[$class] = $fileName;
						}
					}
				}
			}
		}

		$content = '<?php return ' . var_export($map, true) . ';';

		file_put_contents($this->map_file, $content);

		return true;
	}

	public function getClassName(SplFileInfo $file)
	{
		$src = file_get_contents($file);

		if (preg_match('/^namespace\s+(.+?);\s*$/sm', $src, $m))
			$namespace = $m[1];

		return (isset($namespace) ? $namespace . '\\' : '') . str_replace('Base.php', '', $file->getFileName());
	}

	public function findInOverrides($class)
	{
		$file = $this->overrides_directory . DIRECTORY_SEPARATOR . $class . '.php';

		if (\File::exists($file))
			return str_replace(base_path(), '', $file);

		return false;
	}

	public function makeOverrideClass($class, $real = true, $force = true)
	{
		$parts = explode('\\', $class);

		$namespace = implode('\\', array_slice($parts, 0, -1));
		$className = end($parts);

		if ($real)
			$dir = $this->overrides_directory . DIRECTORY_SEPARATOR . $namespace;
		else
			$dir = $this->cache_directory . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $namespace;

		$file = $dir . DIRECTORY_SEPARATOR . $className . '.php';

		if (! $this->composer_map)
			$this->composer_map = require_once(base_path('vendor') . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR . 'autoload_psr4.php');

		$prefix = '';

		if (count($parts) > 2 and isset($this->composer_map[$parts[0] . '\\' . $parts[1] . '\\']))
			$prefix = $parts[0].'\\'.$parts[1] . '\\';
		else if (count($parts) > 1 and isset($this->composer_map[$parts[0] . '\\']))
			$prefix = $parts[0] . '\\';

		if ($prefix)
			$source = $this->composer_map[$prefix][0] . DIRECTORY_SEPARATOR . str_replace($prefix, '', $namespace) . DIRECTORY_SEPARATOR . $className . 'Base.php';
		else
			$source = base_path($namespace) . DIRECTORY_SEPARATOR . $className . 'Base.php';

		$source = str_replace('\\', DIRECTORY_SEPARATOR, $source);
        $dir = str_replace('\\', DIRECTORY_SEPARATOR, $dir);
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);

		if (\File::exists($source)) {
			if ((! \File::exists($file) or $force)) {
				$content =
				"<?php\n".
				"namespace {$namespace};\n\n".

//				"use {$namespace}\\{$className}Base;\n\n".

				"class {$className} extends {$className}Base\n".
				"{\n".
				"	//\n".
				"}";

				if (! \File::exists($dir))
					\File::makeDirectory($dir, 0755, true);

				if (file_put_contents($file, $content))
					return str_replace(base_path(), '', $file);
			}
		} else {
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new \Exception('Source file not found at: ' . $source);
		}

		return false;
	}

	public function addPrefix($class, $prefix = 'rac')
	{
		if ($prefix == 'r')
			$class = 'Revys\Revy\App\\' . $class;
		elseif ($prefix == 'ra')
			$class = 'Revys\RevyAdmin\App\\' . $class;
		elseif ($prefix == 'rc')
			$class = 'Revys\Revy\App\Http\Controllers\\' . $class . 'Controller';
		elseif ($prefix == 'rac')
			$class = 'Revys\RevyAdmin\App\Http\Controllers\\' . $class . 'Controller';

		return $class;
	}
}