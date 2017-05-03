<?php

namespace Modules\Structure\Models;

use App\Models\Node;
use App\Support\RequestOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use App\Exceptions\ConstraintException;
use Baum\Extensions\Eloquent\Collection as BaumCollection;

/**
 * Класс модели отдела.
 *
 * @package Modules\Structure\Models
 *
 * @property int $organization_id идентификатор организации
 * @property string $name название отдела
 * @property string $code код отдела
 * @property int $max_staff максимальное количество позиций в штате
 * @property-read int $assigned_users_count количество назначенных сотрудников
 * @property Organization $organization связь с организацией
 * @property Collection|Staff[] $staff связь с позициями штата
 *
 * @method static Department getModel()
 * @method static QueryBuilder|Builder byOrganization(Organization $organization)
 * @method static QueryBuilder|Builder rootByOrganization(Organization $organization)
 * @method static QueryBuilder|Builder root()
 * @method static QueryBuilder|Builder hrDepartment()
 * @method static QueryBuilder|Builder lawyerDepartment()
 * @method static Department|Collection findOrFail($id, $columns = [])
 */
class Department extends Node
{
	const ROOT_DEPARTMENT_NAME = 'Корневой раздел';

	/**
	 * @inheritdoc
	 */
	protected $table = 'structure_departments';

	/**
	 * @inheritdoc
	 */
	protected $fillable = [
		'name',
		'code',
		'parent_id',
		'max_staff',
		'organization_id',
		'order',
	];

	/**
	 * Поля, по которым определеляется область видимости вложенного множества.
	 * Это позволяет разделять и хранить несколько множеств в одной таблице.
	 *
	 * @var array
	 */
	protected $scoped = ['organization_id'];

	/**
	 * Применить условие для выборки корневого отдела организации.
	 *
	 * @param QueryBuilder|Builder $query
	 * @param Organization $organization
	 * @return QueryBuilder|Builder
	 */
	public function scopeRootByOrganization($query, Organization $organization)
	{
		/** @var QueryBuilder|Builder|static $query */
		return $query->byOrganization($organization)->whereNull('parent_id');
	}

	/**
	 * Применить условие для выборки по организации.
	 *
	 * @param QueryBuilder|Builder $query
	 * @param Organization $organization
	 * @return QueryBuilder|Builder
	 */
	public function scopeByOrganization($query, Organization $organization)
	{
		return $query->where('organization_id', $organization->getKey());
	}

	/**
	 * @TODO потом сделать нормально
	 *
	 * Применить условие для выборки HR отдела.
	 *
	 * @param QueryBuilder|Builder $query
	 * @return QueryBuilder|Builder
	 */
	public function scopeHrDepartment($query)
	{
		return $query->where('name', 'Отдел по подбору персонала');
	}

	/**
	 * @TODO потом сделать нормально
	 *
	 * Применить условие для выборки юридического отдела.
	 *
	 * @param QueryBuilder|Builder $query
	 * @return QueryBuilder|Builder
	 */
	public function scopeLawyerDepartment($query)
	{
		return $query->where('name', 'Юридический отдел');
	}

	/**
	 * Получить организацию, которой принадлежит отдел.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\belongsTo
	 */
	public function organization()
	{
		return $this->belongsTo(Organization::class);
	}

	/**
	 * Получить позиции штата отдела.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function staff()
	{
		return $this->hasMany(Staff::class);
	}

	/**
	 * Вычислить количество назначенных сотрудников с групировкой по отделам.
	 *
	 * @return QueryBuilder|Builder
	 */
	public function usersCount()
	{
		/** @var QueryBuilder|Builder $query */
		$query = $this->hasOne(Staff::class);
		$query->selectRaw('department_id, COUNT(*) AS number');
		$query->whereNotNull('user_id');
		$query->groupBy('department_id');

		return $query;
	}

	/**
	 * Получить количество назначенных сотрудников в отделе.
	 *
	 * @return int
	 */
	public function getAssignedUsersCountAttribute()
	{
		if (!array_key_exists('usersCount', $this->relations)) {
			$this->load('usersCount');
		}

		$related = $this->getRelation('usersCount');

		return ($related ? (int) $related->number : 0);
	}

	/**
	 * Создать корневой отдел организации.
	 *
	 * @param Organization $organization
	 * @return self
	 */
	public static function createRoot(Organization $organization)
	{
		$department = new self();

		$department->name = self::ROOT_DEPARTMENT_NAME;
		$department->organization()->associate($organization);
		$department->save();

		return $department;
	}

	/**
	 * Создать отдел.
	 *
	 * @param array $data
	 * @return self|Node
	 */
	public static function createNode(array $data = [])
	{
		/** @var Organization $organization */
		$organization = Organization::findOrFail($data['organization_id']);

		if (empty($data['parent_id'])) {
			$data['parent_id'] = self::getRootDepartment($organization)->id;
		}

		return parent::create($data);
	}

	/**
	 * Получить корневой отдел организации.
	 *
	 * @param Organization $organization
	 * @return static
	 */
	public static function getRootDepartment(Organization $organization)
	{
		/** @var Department $department */
		$department = self::rootByOrganization($organization)->first();

		return $department;
	}

	/**
	 * Получить отдел и его дочерние отделы.
	 *
	 * @param RequestOptions $resourceOptions
	 * @return self[]|Collection|BaumCollection
	 */
	public static function getDescendantsAndSelfDepartment(RequestOptions $resourceOptions)
	{
		/** @var Organization $organization */
		$organization = Organization::findOrFail($resourceOptions->getData()['organization_id']);

		/** @var Builder $descendantsAndSelfBuilder */
		$descendantsAndSelfBuilder = self::getRootDepartment($organization)->descendantsAndSelf();
		Department::applyOptions($resourceOptions, $descendantsAndSelfBuilder);

		return $descendantsAndSelfBuilder->get();
	}

	/**
	 * Обновить всё дерево.
	 *
	 * @param int $rootId
	 * @param array $data
	 * @return self
	 * @throws ConstraintException
	 * @throws \Throwable
	 */
	public static function updateTree($rootId, array $data = [])
	{
		/** @var self $rootDepartment */
		$rootDepartment = self::findOrFail($rootId);

		if (!$rootDepartment->isRoot()) {
			throw new ConstraintException('Обновляемый раздел не является корневым');
		}

		try {
			\DB::beginTransaction();

			if (empty($data)) {
				foreach ($rootDepartment->getImmediateDescendants() as $departmentFirstLevel) {
					// Удаляется сам узел и потомки
					$departmentFirstLevel->delete();
				}
			} else {
				// Хотя бы один отдел остается
				$rootDepartment->makeTree($data);
			}

			\DB::commit();
		} catch (\Throwable $e) {
			\DB::rollBack();
			throw $e;
		}

		return $rootDepartment;
	}
}

