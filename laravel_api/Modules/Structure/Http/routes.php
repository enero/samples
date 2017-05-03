<?php
Route::group(['middleware' => 'web'], function () {
	Route::group(['prefix' => 'structures', 'namespace' => 'Structures'], function () {
		Route::get('/', 'StructuresController@index')->name('front.structure.index');
		Route::get('/list', 'StructuresController@index')->name('front.structure.list');
		Route::get('/positions', 'StructuresController@index')->name('front.structure.positions');
		Route::get('/settings/{tab?}', 'StructuresController@index')->name('front.structure.settings');
		Route::get('/edit/{structure}', 'StructuresController@index')->name('front.structure.edit');
		Route::any('{all?}', 'StructuresController@index')->where('all', '.+');
	});

	Route::group(['namespace' => 'Api\v1', 'prefix' => 'api/v1/'], function() {
		Route::group(['prefix' => 'structure'], function () {
			Route::resource('organizations', 'OrganizationController', [
				'only' => ['index', 'show', 'store', 'update', 'destroy']
			]);
			Route::resource('positions', 'PositionController', [
				'only' => ['index', 'show', 'store', 'update', 'destroy']
			]);
			Route::resource('employment_types', 'EmploymentTypeController', [
				'only' => ['index', 'show', 'store', 'update', 'destroy']
			]);
			Route::put('departments/{id}/tree', 'DepartmentController@updateTree')->name('structure.tree');
			Route::get('departments/unique', 'DepartmentController@checkCode')->name('api.v1.structure.departments.unique');;
			Route::resource('departments', 'DepartmentController', [
				'only' => ['index', 'show', 'store', 'update', 'destroy']
			]);

			Route::resource('staff', 'StaffController', [
				'only' => ['store', 'index', 'show', 'update', 'destroy']
			]);

			Route::post('staff/{id}', 'StaffController@store');

			Route::resource('user_legal_persons', 'UserLegalPersonController', [
				'only' => ['index', 'show', 'update', 'destroy', 'store']
			]);

			Route::get('staff_can_set_is_main_job', 'StaffController@canSetIsMainJob')->name('api.v1.structure.staff.can_set_is_main_job');

			Route::resource('users', 'UserController', [
				'only' => ['index', 'show']
			]);
			Route::resource('grades', 'GradeController', [
				'only' => ['index', 'show', 'store', 'update', 'destroy']
			]);
		});
	});
});
