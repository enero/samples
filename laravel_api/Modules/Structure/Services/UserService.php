<?php

namespace Modules\Structure\Services;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\BaseCrudServiceContract;
use App\Support\RequestOptions;

/**
 * Класс сервиса сотрудников.
 *
 * @package Modules\Structure\Services
 */
class UserService implements BaseCrudServiceContract
{
	/**
	 * Получить список сотрудников.
	 *
	 * @param RequestOptions $options
	 * @return User[]|Collection
	 */
	public function getAll(RequestOptions $options)
	{
		//TODO Проверка прав

		return User::applyOptions($options)->isFired()->get();
	}

	/**
	 * Получить сотрудника.
	 *
	 * @param int $id
	 * @param RequestOptions $options
	 * @return User
	 */
	public function getOne($id, RequestOptions $options = null)
	{
		//TODO Проверка прав

		return User::applyOptions($options)->findOrFail($id);
	}
}
