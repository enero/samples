<?php

namespace Modules\Structure\Providers;

use App\Contracts\ModuleProvideAccessRights;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider implements ModuleProvideAccessRights
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
	}

	/**
	 * @inheritdoc
	 */
	public function getModuleRightName() : string
	{
		return 'structure';
	}

	/**
	 * @inheritdoc
	 */
	public function getAccessRights() : array
	{
		return [
			'administrator' => [
				'title'  => 'Администратор',
				'grants' => ['.'],
			],
			'edit_full' => [
				'title'  => 'Полное редактирование',
				'grants' => ['.'],
			],
			'edit_own_department' => [
				'title'  => 'Редактирование собственного отдела',
				'grants' => ['.'],
			],
			'view_full' => [
				'title'  => 'Полный просмотр',
				'grants' => ['.'],
			],
		];
	}
}

