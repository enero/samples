<?php

namespace Modules\Structure\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Models\Core\User;
use Modules\Structure\Models\Staff;
use Modules\Structure\Services\StaffService;
use Modules\Structure\Transformers\StaffTransformer;

/**
 * Класс контроллера позиций штата.
 *
 * @package Modules\Structure\Http\Controllers\Api\v1
 *
 * @property StaffService $service
 * @property StaffTransformer $transformer
 */
class StaffController extends SimpleCheckRightsController
{
	protected $castsNullable = [
		'user_id',
	];

	protected $castsBoolean = [
		'is_head',
		'is_main_job',
		'is_probation',
	];

	/**
	 * @param StaffService $service
	 * @param StaffTransformer $transformer
	 */
	public function __construct(StaffService $service, StaffTransformer $transformer)
	{
		parent::__construct();

		$this->service     = $service;
		$this->transformer = $transformer;
	}

	/**
	 * Проверить, можно ли указать "основное место работы" для сотрудника.
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function canSetIsMainJob(Request $request)
	{
		$user = User::findOrFail($request->get('user_id'));
		$staffId = $request->get('staff_id', null);
		$staff   = ($staffId ? Staff::findOrFail($staffId) : null);

		return $this->respondOk([
			'data' => [
				'can_set_is_main_job' => $this->service->canSetIsMainJob($user, $staff)
			]
		]);
	}
}
