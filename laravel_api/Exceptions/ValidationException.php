<?php

namespace App\Exceptions;

use Illuminate\Validation\Validator;

/**
 * Класс ValidationException
 * 
 * @package App\Exceptions
 */
class ValidationException extends \Illuminate\Validation\ValidationException
{
	/**
	 * @var array
	 */
	protected $messages = [];

	/**
	 * @var array
	 */
	protected $rules = [];

	/**
	 * @return Validator
	 */
	public function getValidator()
	{
		return $this->validator;
	}

	/**
	 * @return array
	 */
	public function getFailedRules()
	{
		return $this->validator->failed();
	}

	/**
	 * @return array
	 */
	public function getErrorMessages()
	{
		return $this->validator->errors()->getMessages();
	}
}
