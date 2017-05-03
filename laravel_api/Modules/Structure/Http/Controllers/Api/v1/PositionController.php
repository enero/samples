<?php

namespace Modules\Structure\Http\Controllers\Api\v1;

use Modules\Structure\Services\PositionService;
use Modules\Structure\Transformers\PositionTransformer;

/**
 * Класс контроллера должностей.
 *
 * @package Modules\Structure\Http\Controllers\Api\v1
 *
 * @property PositionService $service
 * @property PositionTransformer $transformer
 */
class PositionController extends SimpleCheckRightsController
{
	/**
	 * @param PositionService $service
	 * @param PositionTransformer $transformer
	 */
	public function __construct(PositionService $service, PositionTransformer $transformer)
	{
		parent::__construct();

		$this->service     = $service;
		$this->transformer = $transformer;
	}
}
