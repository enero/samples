<?

namespace App\Contracts;

/**
 * Модули, реализующие этот интерфейс, будут учитываться в sync-module-rights.php
 * Наследование модулей не поддерживается.
 * Символ точки '.' в grants будет заменён на название модуля.
 *
 * @package App\Contracts
 */
interface ModuleProvideAccessRights
{
	/**
	 * Получить список прав модуля для дальнейшего создания их в системе прав.
	 *
	 * Пример:
	 * return [
	 *     'rightName' => [
	 *         'title'    => 'Example rule',
	 *         'grants'   => ['.'],
	 *         'selector' => null,
	 *     ],
	 * ];
	 *
	 * @return array
	 */
	public function getAccessRights() : array;

	/**
	 * Получить базовое название модуля для системы прав.
	 *
	 * @return string
	 */
	public function getModuleRightName() : string;
}