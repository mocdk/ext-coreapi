<?php
namespace Etobi\CoreAPI\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Jon Klixbüll Langeland <jon@klixbull.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * DataHandler API service
 *
 * @package TYPO3
 * @subpackage tx_coreapi
 */
class DataHandlerApiService {

	public $console = array();

	/**
	 * @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected $adminUser;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
	 */
	protected $objectManager;

	/**
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
	 *
	 * @return void
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Initialize the object.
	 *
	 * @return void
	 */
	public function initializeObject() {
		// Create a fake admin user
		$this->adminUser = $this->objectManager->get('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication');
		$this->adminUser->user['uid'] = $GLOBALS['BE_USER']->user['uid'];
		$this->adminUser->user['username'] = '_CLI_lowlevel';
		$this->adminUser->user['admin'] = 1;
		$this->adminUser->workspace = 0;
	}

	/**
	 * Process TCE command map
	 *
	 * @param array $cmd The command array
	 * @param boolean $dry dry run
	 * @return integer
	 * @throws InvalidArgumentException
	 */
	public function coreEngineCommand($cmd, $dry = FALSE) {

		if (!$dry) {

			/** @var $dataHandler \TYPO3\CMS\Core\DataHandling\DataHandler */
			$dataHandler = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
			$dataHandler->stripslashes_values = 0;

			// Boolean. If this is set, then a page is deleted by deleting the whole branch under it (user must have deletepermissions to it all). If not set, then the page is deleted ONLY if it has no branch
			$dataHandler->deleteTree = TRUE;

			$dataHandler->copyTree = 99;

			$dataHandler->start(array(), $cmd, $this->adminUser);
			$dataHandler->process_cmdmap();

			if (!$dataHandler->errorLog == array()) {
				$this->console = array_merge($this->console, $dataHandler->errorLog);
			}

		}

		return TRUE;
	}

	/**
	 * Process TCE data map
	 *
	 * @param array $data The Data string
	 * @param boolean $dry dry run
	 * @return integer
	 * @throws InvalidArgumentException
	 */
	public function coreEngineData($data, $dry = FALSE) {

		if (!$dry) {
			/** @var $dataHandler \TYPO3\CMS\Core\DataHandling\DataHandler */
			$dataHandler = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
			$dataHandler->stripslashes_values = 0;

			$dataHandler->start(array(), $data, $this->adminUser);
			$dataHandler->process_datamap();

			if (!$dataHandler->errorLog == array()) {
				$this->console = array_merge($this->console, $dataHandler->errorLog);
			}
		}

		return TRUE;
	}
}