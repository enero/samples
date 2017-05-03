<?php

namespace Modules\Structure\Models;

use App\Models\Tis;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Carbon\Carbon;

/**
 * Класс модели организации.
 *
 * @package Modules\Structure\Models
 *
 * @property int    $id
 * @property string $short_name      краткое название организации
 * @property string $full_name       полное название организации
 * @property bool   $is_legal_entity флаг юридического лица
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Department[] $departments связь с отделами
 *
 * @method static Organization getModel()
 */
class Organization extends Tis
{
	protected $table = 'structure_organizations';

	protected $hidden = [
		'departmentsTree',
	];

	protected $fillable = [
		'short_name',
		'full_name',
		'is_legal_entity',
	];

	protected $casts = [
		'is_legal_entity' => 'boolean',
	];

	/**
	 * Получить отделы организации.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function departments()
	{
		return $this->hasMany(Department::class);
	}

	/**
	 * Получить корневой отдел организации.
	 *
	 * @return Builder|QueryBuilder
	 */
	public function departmentsRoot()
	{
		/** @var Builder|QueryBuilder $query */
		$query = $this->hasOne(Department::class);
		$query->whereNull('parent_id');

		return $query;
	}

	//TODO Метод пока нужен, т.к. с фронта приходит параметр includes=departmentsTree, но он отдает корень, а не дерево.
	/**
	 * Получить корневой отдел организации.
	 *
	 * @return Builder
	 */
	public function departmentsTree()
	{
		/** @var Builder|QueryBuilder $query */
		$query = $this->hasOne(Department::class);
		$query->whereNull('parent_id');

		return $query;
	}
}
