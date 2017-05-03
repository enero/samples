<?php

namespace Modules\Structure\Services;

use Illuminate\Database\Eloquent\Collection;
use App\Support\RequestOptions;
use Modules\Structure\Models\EmploymentType;
use App\Contracts\BaseCrudServiceContract;

/**
 * Класс сервиса типов занятости.
 *
 * @package Modules\Structure\Services
 */
class EmploymentService implements BaseCrudServiceContract
{
    /**
     * Получить список типов занятостей.
     *
     * @param RequestOptions $options
     * @return Collection|EmploymentType[]
     */
    public function getAll(RequestOptions $options)
    {
        return EmploymentType::applyOptions($options)->get();
    }

    /**
     * Получить тип занятости.
     *
     * @param int $id
     * @param RequestOptions $options
     * @return EmploymentType
     */
    public function getOne($id, RequestOptions $options = null)
    {
        return EmploymentType::applyOptions($options)->findOrFail($id);
    }

    /**
     * Создать тип занятости.
     *
     * @param RequestOptions $options
     * @return EmploymentType
     * @throws \App\Exceptions\ValidationException
     */
    public function create(RequestOptions $options = null)
    {
        EmploymentType::validate($options->getData(), ['name' => 'required|min:2|max:255']);

        return EmploymentType::create($options->getData());
    }

    /**
     * Обновить тип занятости.
     *
     * @param int $id
     * @param RequestOptions $options
     * @return EmploymentType
     * @throws \App\Exceptions\ValidationException
     */
    public function update($id, RequestOptions $options)
    {
        EmploymentType::validate($options->getData(), ['name' => 'required|min:2|max:255']);

        /** @var EmploymentType $type */
        $type = EmploymentType::findOrFail($id);
        $type->update($options->getData());

        return EmploymentType::applyOptions($options)->findOrFail($id);
    }

    /**
     * Удалить тип занятости.
     *
     * @param int $id
     * @return EmploymentType
     */
    public function destroy($id)
    {
        $type = EmploymentType::findOrFail($id);
        $type->delete();

        return $type;
    }
}
