<?php

namespace App\Providers;

use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Response;

class ResponseMacroServiceProvider extends ServiceProvider
{
	/**
	 * Выполнить действия после регистрации всех поставщиков услуг.
	 */
	public function boot()
	{
		ResponseFactory::macro('success', function ($data, $status = null) {
			$status = $status ?? Response::HTTP_OK;

			$result = [];
			if (!isset($data['data'])) {
				$result['data'] = $data;
			} else {
				$result = $data;
			}

			return response()->json((['success' => true] + $result), $status);
		});

		ResponseFactory::macro('error', function ($errors, $status = null) {
			$status = $status ?? Response::HTTP_BAD_REQUEST;

			return response()->json([
				'success' => false,
				'errors'  => (is_array($errors) ? $errors : [[$errors]]),
			], $status);
		});
	}

	/**
	 * Зарегистрировать поставщика услуг.
	 */
	public function register()
	{
	}
}
