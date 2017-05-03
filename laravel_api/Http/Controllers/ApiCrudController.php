<?php

namespace App\Http\Controllers;

use App\Support\RequestOptions;
use App\Support\RequestOptionParser;
use App\Exceptions\PreconditionFailedException;
use Illuminate\Http\Request;
use League\Fractal\TransformerAbstract;

/**
 * Абстрактный класс API-контроллера.
 */
abstract class ApiCrudController extends ApiController
{
	/**
	 * @var TransformerAbstract
	 */
	protected $transformer;

	/**
	 * Получить список записей.
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index(Request $request)
	{
		$options = $this->getOptionsIfCorrect($request);

		$collection = $this->service->getAll($options);
		$data       = $this->transformCollection($collection, $this->transformer);

		return $this->respondOk($data);
	}

	/**
	 * Получить запись для просмотра.
	 *
	 * @param int $id
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($id, Request $request)
	{
		$options = $this->getOptionsIfCorrect($request);

		$model = $this->service->getOne($id, $options);
		$data  = $this->transformItem($model, $this->transformer);

		return $this->respondOk($data);
	}

	/**
	 * Сохранить новую запись.
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store(Request $request)
	{
	    $options = $this->getOptionsIfCorrect($request);

		$model = $this->service->create($options);
		$data  = $this->transformItem($model, $this->transformer);

		return $this->respondCreated($data);
	}

	/**
	 * Обновить запись.
	 *
	 * @param int $id
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update($id, Request $request)
	{
		$options = $this->getOptionsIfCorrect($request);

		$model = $this->service->update($id, $options);
		$data  = $this->transformItem($model, $this->transformer);

		return $this->respondOk($data);
	}

	/**
	 * Удалить запись.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($id)
	{
		$model = $this->service->destroy($id);
		$data  = $this->transformItem($model, $this->transformer);

		return $this->respondOk($data);
	}

    /**
     * Получить опции.
     * В случае ошибок вернуть ответ ошибки.
     *
     * @param Request $request
     *
     * @return RequestOptions
     */
	protected function getOptionsIfCorrect(Request $request)
	{
	    /** @var RequestOptions $options */
			$options = (new RequestOptionParser($request))->get();

      $errors = $options->getErrors();
      if (!empty($errors)) {
				throw with(new PreconditionFailedException(''))->setErrors($errors);
			}

			return $options;
	}
}
