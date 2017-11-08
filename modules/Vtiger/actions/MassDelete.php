<?php

/**
 * Mass records delete action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_MassDelete_Action extends Vtiger_Mass_Action
{

	/**
	 * {@inheritDoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		$userPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPriviligesModel->hasModuleActionPermission($request->getModule(), 'MassDelete')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordIds = self::getRecordsListFromRequest($request);
		$skipped = [];
		foreach ($recordIds as $recordId) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			if (!$recordModel->privilegeToDelete()) {
				$skipped[] = $recordModel->getName();
				continue;
			}
			$recordModel->delete();
			unset($recordModel);
		}
		$text = \App\Language::translate('LBL_CHANGES_SAVED');
		$type = 'success';
		if ($skipped) {
			$type = 'info';
			$text .= PHP_EOL . \App\Language::translate('LBL_OMITTED_RECORDS');
			foreach ($skipped as $name) {
				$text .= PHP_EOL . $name;
			}
		}
		$response = new Vtiger_Response();
		$response->setResult(['notify' => ['text' => $text, 'type' => $type]]);
		$response->emit();
	}
}
