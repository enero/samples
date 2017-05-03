<?php

namespace Modules\Structure\Scopes;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Scope;

/**
 * Глобальное условие для Staff.
 * Исключает уволенных сотрудников.
 *
 * @package Modules\Structure\Scopes
 */
class StaffUserFiredScope implements Scope
{
	/**
	 * Добавить глобальное условие.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $builder
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 * @return void
	 */
	public function apply(Builder $builder, Model $model)
	{
		$sql = $this->onlyNotFiredUserSql();
		$builder->whereRaw(\DB::raw('user_id NOT IN ( ' . $sql . ')'));
	}

	/**
	 * Расширить queryBuilder методами.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $builder
	 * @return void
	 */
	public function extend(Builder $builder)
	{
		$builder->macro('withFired', function (Builder $builder) {
			return $builder->withoutGlobalScope($this);
		});
	}

	/**
	 * Возвращает SQL-строку, содержащую условия выборки только не уволенных сотрудников.
	 *
	 * @return string
	 */
	protected function onlyNotFiredUserSql()
	{
		$user = new User();
		$tbl = $user->getTable();
		$sql = User::formatBuilder(User::isFired(true)->select('id'));

		// добавляем название базы данных в секцию from
		return str_replace('`' . $tbl . '`', '`' . $user->getConnectionName() . '`.`' . $tbl . '`', $sql);
	}
}

