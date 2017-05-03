<?php

namespace App\Serializers;

use League\Fractal\Serializer\DataArraySerializer;

/**
 * Класс TisDataArraySerializer - в отличие от родителя изменяет формат (не оборачивает вложенные данные в 'data').
 *
 * @package League\Fractal\Serializer
 */
class TisDataArraySerializer extends DataArraySerializer
{
	/**
	 * Сериализовать коллекцию сущностей.
	 *
	 * @param string $resourceKey
	 * @param array  $data
	 *
	 * @return array
	 */
	public function collection($resourceKey, array $data)
	{
		return ($resourceKey ? [$resourceKey => $data] : $data);
	}

	/**
	 * Сериализовать одну сущность.
	 *
	 * @param string $resourceKey
	 * @param array  $data
	 *
	 * @return array
	 */
	public function item($resourceKey, array $data)
	{
		return ($resourceKey ? [$resourceKey => $data] : $data);
	}
}
