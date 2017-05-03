<?php

namespace Modules\Structure\Services;

use App\Contracts\BaseCrudServiceContract;
use App\Exceptions\ValidationException;
use Modules\Structure\Models\Grade;
use App\Support\RequestOptions;

/**
 * Класс GradeService.
 *
 * @package Modules\Structure\Services
 */
class GradeService implements BaseCrudServiceContract
{
    /**
     * Получить правила валидации.
     * 
     * @param Grade $grade
     * @return array
     */
    public static function getRules(Grade $grade = null)
    {
        $grade = $grade ?: new Grade();
        $gradeScheme = $grade->getTableWithSchema();
        $uniqueNameRule = '|unique:' . $gradeScheme;
        if ($grade->exists()) {
            $uniqueNameRule .= ',id,' . $grade->getKey();
        }

        return [
            'name' => 'required|string|max:255' . $uniqueNameRule,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAll(RequestOptions $options)
    {
        return Grade::applyOptions($options)->get();
    }

    /**
     * @inheritdoc
     */
    public function getOne($id, RequestOptions $options = null)
    {
        return Grade::applyOptions($options)->findOrFail($id);
    }

    /**
     * Создать грейд.
     *
     * @param RequestOptions $options
     * @return Grade
     * @throws ValidationException
     */
    public function create(RequestOptions $options)
    {
        Grade::validate($options->getData(), static::getRules());

        return Grade::create($options->getData());
    }

    /**
     * Обновить грейд.
     *
     * @param int $id
     * @param RequestOptions $options
     * @return Grade
     * @throws ValidationException
     */
    public function update($id, RequestOptions $options)
    {
        $grade = Grade::findOrFail($id);

        Grade::validate($options->getData(), static::getRules($grade));

        $grade->update($options->getData());

        return Grade::applyOptions($options)->findOrFail($id);
    }

    /**
     * Удалить грейд.
     *
     * @param int $id
     * @return Grade
     */
    public function destroy($id)
    {
        $grade = Grade::findOrFail($id);

        $grade->delete();

        return $grade;
    }
}
