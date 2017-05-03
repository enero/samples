<?php

namespace Modules\Structure\Http\Controllers\Api\v1;

use Modules\Structure\Services\UserService;
use Modules\Structure\Transformers\UserTransformer;

/**
 * Класс контроллера сотрудников.
 *
 * @package Modules\Structure\Http\Controllers\Api\v1
 *
 * @property UserService $service
 * @property UserTransformer $transformer
 */
class UserController extends SimpleCheckRightsController
{
	/**
	 * @inheritdoc
	 */
	protected $castsBoolean = [
		'is_disabled',
	];

	/**
	 * @param UserService $service
	 * @param UserTransformer $transformer
	 */
	public function __construct(UserService $service, UserTransformer $transformer)
	{
		parent::__construct();

		$this->service     = $service;
		$this->transformer = $transformer;
	}
}
