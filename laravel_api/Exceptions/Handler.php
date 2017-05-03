<?php

namespace App\Exceptions;

use Illuminate\Http\Response;
use Exception;
use App\Exceptions\ValidationException as AppValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		AuthorizationException::class,
		HttpException::class,
		ModelNotFoundException::class,
		ValidationException::class,
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception  $e
	 * @return void
	 */
	public function report(Exception $e)
	{
		parent::report($e);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $e
	 * @return Response
	 */
	public function render($request, Exception $e)
	{
		if ($request->ajax() || $request->wantsJson()) {

			if ($e instanceof ModelNotFoundException) {
				return response()->error($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
			} elseif ($e instanceof QueryException || $e instanceof ConstraintException) {
				return response()->error($e->getMessage(), Response::HTTP_BAD_REQUEST);
			} elseif ($e instanceof AppValidationException) {
				return response()->error($e->getErrorMessages(), Response::HTTP_PRECONDITION_FAILED);
			} elseif ($e instanceof PreconditionFailedException) {
				return response()->error($e->errors, Response::HTTP_PRECONDITION_FAILED);
			}

			$withDebug = '';
			if (env('APP_DEBUG')) {
				$withDebug = ' ' . $e->getFile() . ':' . $e->getLine();
			}

			return response()->error(
				$e->getMessage() . $withDebug,
				(method_exists($e, 'getStatusCode') ? $e->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR)
			);
		}

		return parent::render($request, $e);
	}
}
