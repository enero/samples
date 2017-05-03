<?php

namespace App;

use File;
use KodiCMS\ModulesLoader\ModulesLoader as BaseModuleLoader;

class ModulesLoader extends BaseModuleLoader
{
	/**
	 * Загрузчик модулей
	 *
	 *
	 * $modulesList = [
	 * 		'modulename', // Namespace: Modules\modulename, Path appDir/Modules/modulename
	 * 		'ModuleName2' => [
	 * 			'path' => {relative path to module},
	 * 			'namespace' => '\\CustomNamespace\\ModuleName2\\'
	 * 		]
	 * ]
	 * Если $modulesList - null, будет произведён поиск модулей в директории app_path('Modules').
	 *
	 * @see https://github.com/KodiCMS/module-loader
	 * @param array|null $modulesList
	 */
	public function __construct(array $modulesList = null)
	{
		register_shutdown_function([$this, 'shutdownHandler']);

		if (is_null($modulesList)) {
			$modulesList = $this->getModulesList();
		}

		foreach ($modulesList as $moduleName => $modulePath) {
			$moduleNamespace = null;
			$moduleInfo = [];

			if (is_array($modulePath)) {
				$moduleInfo	  = $modulePath;
				$moduleNamespace = array_get($modulePath, 'namespace');
				$modulePath	  = array_get($modulePath, 'path');
			} elseif (is_numeric($moduleName)) {
				$moduleName = $modulePath;
				$modulePath = null;
			}

			if (is_null($modulePath)) {
				$modulePath = app_path('Modules' . DIRECTORY_SEPARATOR . $moduleName);
			}

			$this->addModule($moduleName, $modulePath, $moduleNamespace, null, $moduleInfo);
		}

		$this->addModule('App', app_path(), $this->getAppNamespace(), \KodiCMS\ModulesLoader\AppModuleContainer::class);
	}

	/**
	 * Получить хэш список модулей системы.
	 *
	 * @return array
	 */
	public function getModulesList()
	{
		// TODO - закэшировать
		return $this->findModules();
	}

	/**
	 * Найти доступные модули.
	 *
	 * @return array
	 */
	public function findModules()
	{
		$modulesList = [];

		foreach (File::directories(app_path('Modules')) as $directory) {
			if (File::exists($directory . DIRECTORY_SEPARATOR . 'Providers' . DIRECTORY_SEPARATOR . 'ModuleServiceProvider.php')) {
				$modulesList[] = File::basename($directory);
			}
		}

		return $modulesList;
	}
}
