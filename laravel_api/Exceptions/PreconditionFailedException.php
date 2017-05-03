<?php

namespace App\Exceptions;

/**
 * Класс исключения, связанного с ограничением целостности данных.
 *
 * @package App\Exceptions
 */
class PreconditionFailedException extends \Exception
{
	/**
	 * @var array
	 */
	public $errors;

	/**
	 * Установить ошибки
	 *
	 * @param array $errors
	 * @return static
	 */
	public function setErrors($errors)
	{
		$this->errors = $errors;

		return $this;
	}
}
