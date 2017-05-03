<?php

namespace Modules\Structure\Http\Controllers\Structures;

use App\Http\Controllers\Controller;
use Modules\Structure\Services\DepartmentService;
use Modules\Structure\Services\OrganizationService;
use Modules\Structure\Transformers\DepartmentTransformer;

class StructuresController extends Controller
{
	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function index()
	{
		$accessViewFull = $this->user->access(OrganizationService::ACCESS_VIEW_FULL);
		if (!$accessViewFull) {
			return redirect()->to('/');
		}

		$defaultDepartmentWithChildren = app(DepartmentService::class)->getDefaultDepartment($this->user);
		$defaultDepartmentData = ($defaultDepartmentWithChildren ? (new DepartmentTransformer())->transform($defaultDepartmentWithChildren) : null);

		return view('structures.index', [
			'access' => [
				'view_full'           => $accessViewFull,
				'edit_full'           => $this->user->access(OrganizationService::ACCESS_EDIT_FULL),
				'edit_own_department' => $this->user->access(OrganizationService::ACCESS_EDIT_OWN_DEPARTMENT),
				'administrator'       => $this->user->access('tis.structure.administrator'),
			],
			'defaultDepartment' => $defaultDepartmentData,
		]);
	}
}
