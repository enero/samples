<?php

namespace App\Providers;

use App\ModulesLoader;
use KodiCMS\ModulesLoader\ModulesFileSystem;
use KodiCMS\ModulesLoader\Providers\ModuleServiceProvider as BaseModuleServiceProvider;
use Validator;
use App\Exceptions\ConstraintException;
use Modules\Structure\Models\Department;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
	/**
	 * Выполнить действия после регистрации всех поставщиков услуг.
	 */
	public function boot()
	{
		Validator::extend('greaterOrEqualThenAssignedUsersCount', function ($attribute, $value, $parameters, $validator) {
			$model = Department::findOrFail($validator->getData()['id']);
			if ($value < $model->assigned_users_count) {
				throw new ConstraintException('Максимальное количество позиций в штате не может быть меньше количества назначенных сотрудников');
			}

			return true;
		});
	}

	/**
	 * @inheritdoc
	 */
	public function register()
	{
		$this->app->singleton('modules.loader', function () {
			return new ModulesLoader();
		});

		$this->app->singleton('modules.filesystem', function ($app) {
			return new ModulesFileSystem($app['modules.loader'], $app['files']);
		});

		$this->registerAliases();
		$this->registerProviders();
		$this->registerConsoleCommands();
	}
}
