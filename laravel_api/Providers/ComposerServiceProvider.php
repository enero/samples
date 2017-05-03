<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Core\Models\Menu\MainMenu;
use App\Modules\Core\Models\Menu\MainMenuCategory;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Класс поставщика услуг для отрисовки представлений.
 *
 * @package App\Providers
 */
class ComposerServiceProvider extends ServiceProvider
{
	/**
	 * Загрузка сервисов приложения.
	 *
	 * @return void
	 */
	public function boot()
	{
		// Привязка данных к представлению при отрисовке шаблона
		view()->composer('partials.main_nav', function ($view) {
			$relations = [
				'menus' => function (Relation $query) {
					/** @var \Illuminate\Database\Eloquent\Builder|MainMenu $query */
					return $query->isActive();
				},
			];

			/** @var \Illuminate\View\View $view */
			$view->with([
				'full_menu' => MainMenuCategory::with($relations)->orderBy('position')->get(),
			]);
		});
	}

	/**
	 * Регистрация сервисов приложения.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}
}
