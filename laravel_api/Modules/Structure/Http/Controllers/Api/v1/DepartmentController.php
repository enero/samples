<?php

namespace Modules\Structure\Http\Controllers\Api\v1;

use Modules\Structure\Services\DepartmentService;
use Modules\Structure\Services\OrganizationService;
use Modules\Structure\Transformers\DepartmentTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Класс контроллера отдела.
 *
 * @property DepartmentService $service
 * @property DepartmentTransformer $transformer
 *
 * @package Modules\Structure\Http\Controllers\Api\v1
 */
class DepartmentController extends ApiCrudController
{
	/**
	 * @param DepartmentService $service
	 * @param DepartmentTransformer $transformer
	 */
	public function __construct(DepartmentService $service, DepartmentTransformer $transformer)
	{
		parent::__construct();

		$this->service     = $service;
		$this->transformer = $transformer;
	}

	/**
	 * Получить список отделов.
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function index(Request $request)
	{
		$options = $this->getOptionsIfCorrect($request);

		$rootDepartmentWithChildren = $this->service->getAll($options);
		$data = $this->transformItem($rootDepartmentWithChildren, $this->transformer);

		return $this->respondOk($data);
	}

	/**
	 * Получить отдел.
	 *
	 * @param int $id
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function show($id, Request $request)
	{
		$options = $this->getOptionsIfCorrect($request);

		$departmentWithChildren = $this->service->getOne($id, $options);
		$data = $this->transformItem($departmentWithChildren, $this->transformer);

		return $this->respondOk($data);
	}

	/**
	 * Обновить отдел.
	 *
	 * @param int $id
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function update($id, Request $request)
	{
		if (!$this->user->access(OrganizationService::ACCESS_EDIT_FULL) && !$this->user->access(OrganizationService::ACCESS_EDIT_OWN_DEPARTMENT)) {
			return $this->respondWithError('Нет доступа', Response::HTTP_FORBIDDEN);
		}

		$options = $this->getOptionsIfCorrect($request);

		$model = $this->service->update($id, $options);

		return $this->respondOk($model->toArray());
	}

	/**
	 * Обновить структуру дерева отделов.
	 *
	 * @param int $id
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function updateTree($id, Request $request)
	{
		if (!$this->user->access(OrganizationService::ACCESS_EDIT_FULL) && !$this->user->access(OrganizationService::ACCESS_EDIT_OWN_DEPARTMENT)) {
			return $this->respondWithError('Нет доступа', Response::HTTP_FORBIDDEN);
		}

		$options = $this->getOptionsIfCorrect($request);

		$departmentsTree = $this->service->updateTree($id, $options);
		$data = $this->transformItem($departmentsTree, $this->transformer);

		return $this->respondOk($data);
	}

	/**
	 * Проверить уникальность кода отдела.
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function checkCode(Request $request)
	{
		$options = $this->getOptionsIfCorrect($request);

		$isUnique = $this->service->isUniqueCode($options);

		return $this->respondOk(['is_unique_code' => $isUnique]);
	}
}
