<?php

namespace Modules\Structure\Models;

use App\Support\RequestOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use App\Models\Tis;
use App\Models\Core\User;
use Carbon\Carbon;
use Modules\Structure\Scopes\StaffUserFiredScope;

/**
 * Класс модели позиции штата.
 *
 * @package Modules\Structure\Models
 *
 * @property int $id
 * @property int $department_id идентификатор отдела
 * @property int $position_id идентификатор должности
 * @property int $user_id идентификатор сотрудника
 * @property bool $is_head флаг руководителя отдела
 * @property bool $is_main_job флаг основной работы
 * @property int $employment вид занятости
 * @property int $work_character код характера работы
 * @property string $schedule_days набор рабочих дней недели
 * @property Carbon $schedule_time_begin время начала рабочего дня
 * @property Carbon $schedule_time_end время окончания рабочего дня
 * @property bool $is_probation флаг испытательного срока
 * @property Carbon $probation_date_begin дата начала испытательного срока
 * @property Carbon $probation_date_end дата окончания испытательного срока
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Department $department
 * @property Position $position
 * @property User $user
 *
 * @method static Staff getModel()
 * @method static Staff applyOptions()
 * @method static Staff findOrFail()
 * @method static QueryBuilder|Builder byDepartment(Department $department)
 * @method static QueryBuilder|Builder withFired()
 */
class Staff extends Tis
{
	protected $table = 'structure_staff';

	protected $fillable = [
		'position_id',
		'user_id',
		'is_head',
		'is_main_job',
		'work_character',
		'schedule_days',
		'schedule_time_begin',
		'schedule_time_end',
		'is_probation',
		'probation_date_begin',
		'probation_date_end',
		'department_id',
	];

	protected $casts = [
		'is_head'      => 'boolean',
		'is_main_job'  => 'boolean',
		'is_probation' => 'boolean',
	];

	/**
	 * @inheritdoc
	 */
	protected static function boot()
	{
		parent::boot();

		static::addGlobalScope(new StaffUserFiredScope());
	}

	/**
	 * Получить отдел, к которому относится позиция в штате.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\belongsTo
	 */
	public function department()
	{
		return $this->belongsTo(Department::class);
	}

	/**
	 * Получить должность, которая определена для позиции в штате.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\belongsTo
	 */
	public function position()
	{
		return $this->belongsTo(Position::class);
	}

	/**
	 * Получить сотрудника, назначенного на должность.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\belongsTo
	 */
	public function user()
	{
		return $this->belongsTo(User::class);
	}

	/**
	 * Применить условие для выборки по отделу.
	 *
	 * @param Builder|QueryBuilder $query
	 * @param Department $department
	 * @return Builder|QueryBuilder
	 */
	public function scopeByDepartment($query, Department $department)
	{
		return $query->where('department_id', $department->getKey());
	}

	/**
	 * Получить позиции штата по отделу.
	 *
	 * @param RequestOptions|null $options
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public static function getByDepartment(RequestOptions $options = null)
	{
		$query = self::applyOptions($options);

		/** @var Department $department */
		$department = Department::findOrFail($options->getData()['department_id']);

		return $query->byDepartment($department)->get();
	}
}
