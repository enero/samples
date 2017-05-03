<?php

namespace App\Http\Controllers;

use ArrayAccess;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use League\Fractal\Manager as FractalManager;
use League\Fractal\TransformerAbstract as FractalTransformerAbstract;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\Collection as FractalCollection;
use App\Serializers\TisDataArraySerializer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

/**
 * Абстрактный класс API-контроллера.
 */
abstract class ApiController extends Controller
{
	/**
	 * @var FractalManager
	 */
	protected $manager;

	/**
	 * @var string свойство, в котором будут отдаваться данные
	 */
	protected $resourceKey = 'data';

	/**
	 * @var Object объект сервиса
	 */
	protected $service;

	/**
	 * Парсинг includes и задать выходной формат для сериализации.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->manager = new FractalManager();

		if (request()->has('includes')) {
			$this->manager->parseIncludes(request()->input('includes'));
		}

		$this->manager->setSerializer(new TisDataArraySerializer());
	}

	/**
	 * Получить успешный ответ.
	 *
	 * @param ArrayAccess $data
	 * @param int $code
	 * @return JsonResponse
	 */
	protected function respond($data, $code)
	{
		return response()->success($data, $code);
	}

	/**
	 * Получить ответ с ошибкой.
	 *
	 * @param array $errors
	 * @param int $code
	 * @return JsonResponse
	 */
	protected function respondWithError($errors, $code)
	{
		return response()->error($errors, $code);
	}

	/**
	 * Получить ответ успешного чтения.
	 *
	 * @param ArrayAccess $data
	 * @return JsonResponse
	 */
	protected function respondOk($data)
	{
		return $this->respond($data, Response::HTTP_OK);
	}

	/**
	 * Получить ответ успешного создания.
	 *
	 * @param array $data
	 * @return JsonResponse
	 */
	protected function respondCreated($data)
	{
		return $this->respond($data, Response::HTTP_CREATED);
	}

	/**
	 * Получить ответ с ошибками условий запроса.
	 *
	 * @param array $errors
	 * @return JsonResponse
	 */
	protected function respondPreconditionFailed($errors)
	{
		return $this->respondWithError($errors, Response::HTTP_PRECONDITION_FAILED);
	}

	/**
	 * Преобразовать данные одной сущности.
	 *
	 * @param Model $data
	 * @param FractalTransformerAbstract $transformer
	 * @return array
	 */
	protected function transformItem($data, $transformer)
	{
		$resource = new FractalItem($data, $transformer, $this->resourceKey);

		return $this->manager->createData($resource)->toArray();
	}

	/**
	 * Преобразовать данные коллекции сущностей.
	 *
	 * @param Collection $data
	 * @param FractalTransformerAbstract $transformer
	 * @param LengthAwarePaginator $paginator
	 * @return array
	 */
	protected function transformCollection($data, $transformer, $paginator = null)
	{
		$resource = new FractalCollection($data, $transformer, $this->resourceKey);

		if ($paginator && request()->has('offset')) {
			$resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
		}

		return $this->manager->createData($resource)->toArray();
	}
}
