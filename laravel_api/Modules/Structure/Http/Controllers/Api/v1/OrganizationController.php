<?php

namespace Modules\Structure\Http\Controllers\Api\v1;

use Modules\Structure\Services\OrganizationService;
use Modules\Structure\Transformers\OrganizationTransformer;

/**
 * Класс контроллера организаций.
 *
 * @package Modules\Structure\Http\Controllers\Api\v1
 *
 * @property OrganizationService $service
 * @property OrganizationTransformer $transformer
 */
class OrganizationController extends ApiCrudController
{
	/**
	 * @param OrganizationService $service
	 * @param OrganizationTransformer $transformer
	 */
	public function __construct(OrganizationService $service, OrganizationTransformer $transformer)
	{
		parent::__construct();

		$this->service     = $service;
		$this->transformer = $transformer;
	}
}
