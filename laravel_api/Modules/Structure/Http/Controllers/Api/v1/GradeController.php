<?php

namespace Modules\Structure\Http\Controllers\Api\v1;

use App\Http\Controllers\ApiCrudController;
use Illuminate\Http\Request;
use Modules\Structure\Services\OrganizationService;
use Modules\Structure\Services\GradeService;
use Modules\Structure\Transformers\GradeTransformer;
use Symfony\Component\HttpFoundation\Response;

/**
 * Класс контроллера грейдов.
 *
 * @package Modules\Structure\Http\Controllers\Api\v1
 *
 * @property GradeService $service
 * @property GradeTransformer $transformer
 */
class GradeController extends ApiCrudController
{

    /**
     * @param GradeService $service
     * @param GradeTransformer $transformer
     */
    public function __construct(GradeService $service, GradeTransformer $transformer)
    {
        parent::__construct();

        $this->service     = $service;
        $this->transformer = $transformer;
    }

    /**
     * @inheritdoc
     */
    public function store(Request $request)
    {
        if (!$this->user->access(OrganizationService::ACCESS_EDIT_FULL) && !$this->user->access(OrganizationService::ACCESS_EDIT_OWN_DEPARTMENT)) {
            return $this->respondWithError('Нет доступа', Response::HTTP_FORBIDDEN);
        }

        return parent::store($request);
    }

    /**
     * @inheritdoc
     */
    public function destroy($id)
    {
        if (!$this->user->access(OrganizationService::ACCESS_EDIT_FULL) && !$this->user->access(OrganizationService::ACCESS_EDIT_OWN_DEPARTMENT)) {
            return $this->respondWithError('Нет доступа', Response::HTTP_FORBIDDEN);
        }

        return parent::destroy($id);
    }

    /**
     * @inheritdoc
     */
    public function update($id, Request $request)
    {
        if (!$this->user->access(OrganizationService::ACCESS_EDIT_FULL) && !$this->user->access(OrganizationService::ACCESS_EDIT_OWN_DEPARTMENT)) {
            return $this->respondWithError('Нет доступа', Response::HTTP_FORBIDDEN);
        }

        return parent::update($id, $request);
    }
}
