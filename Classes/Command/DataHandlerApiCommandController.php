<?php
namespace Etobi\CoreAPI\Command;

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
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * DataHandler API Command Controller
 *
 * @package TYPO3
 * @subpackage tx_coreapi
 */
class DataHandlerApiCommandController extends CommandController {


	/**
	 * @var \Etobi\CoreAPI\Service\DataHandlerApiService
	 * @inject
	 */
	protected $dataHandlerApiService;

	/**
	 * Process TCE command map
	 *
	 * Leave the argument 'actions' empty or use "help" to see the available ones
	 *
	 * @param string $actions List of actions which will be executed
	 * @param boolean $dry If set, a dry run will be done
	 * @return void
	 */
	public function commandCommand($actions = '', $dry = FALSE) {

		if ($actions === 'help' || strlen($actions) === 0) {
			$this->outputLine();
			$this->outputLine('The help command displays help for a given command:');
			$this->outputLine();
			$this->outputLine('./typo3/cli_dispatch.phpsh extbase datahandlerapi:command <tablename>/<uid>/<command>/<value>');
			$this->outputLine('./typo3/cli_dispatch.phpsh extbase datahandlerapi:command pages/225/copy/18');
			$this->outputLine('./typo3/cli_dispatch.phpsh extbase datahandlerapi:command \'{"pages":{"234":{"copy":123}}}\'');
			$this->outputLine();
			$this->outputLine('Link: http://docs.typo3.org/typo3cms/CoreApiReference/singlehtml/Index.html#tce-database-basics');
			$this->outputLine();
			$this->outputLine();
			$this->quit();
		}

		try {
			$actionArray = $this->transformActionString($actions);
		} catch (\InvalidArgumentException $e) {
			$this->outputLine('Action string transformation error.');
			$this->outputLine($e->getMessage());
			$this->quit();
		}

		try {
			$result = $this->dataHandlerApiService->coreEngineCommand($actionArray, $dry);
		} catch (\Exception $e) {
			$this->outputLine('Oups....');
			$this->outputLine($e->getMessage());
			$this->quit();
		}

		if (!$this->dataHandlerApiService->console == array()) {
			foreach ($this->dataHandlerApiService->console as $console) {
				$this->outputLine('DataHandler::console' . $console);
			}

		}

	}

	/**
	 * Process TCE data map
	 *
	 * Leave the argument 'actions' empty or use "help" to see the available ones
	 *
	 * @param string $actions List of actions which will be executed
	 * @param boolean $dry If set, a dry run will be done
	 * @return void
	 */
	public function dataCommand($actions = '', $dry = FALSE) {

		if ($actions === 'help' || strlen($actions) === 0) {
			$this->outputLine();
			$this->outputLine('The help command displays help for a given command:');
			$this->outputLine();
			$this->outputLine('Link: http://docs.typo3.org/typo3cms/CoreApiReference/singlehtml/Index.html#tce-database-basics');
			$this->outputLine();
			$this->quit();
		}

		/** @var $service \Etobi\CoreAPI\Service\DataHandlerApiService */
		$service = $this->objectManager->get('\Etobi\CoreAPI\Service\DataHandlerApiService');

		try {
			$result = $service->coreEngineData($actions, $dry);
		} catch (\Exception $e) {
			$this->outputLine('Oups....');
			$this->outputLine($e->getMessage());
			$this->quit();
		}

	}

	/**
	 * @param string $actionString
	 * @return array
	 * @throws InvalidArgumentException
	 */
	protected function transformActionString($actionString) {
		if (preg_match('(.*/.*/.*/.*)', $actionString)) {
			$parts = explode('/', $actionString);
			if (count($parts) !== 4) {
				throw new \InvalidArgumentException('Short hand action must have 4 parts.');
			}
			$actionArray[$parts[0]][$parts[1]][$parts[2]] = $parts[3];
		} else {
			$actionArray = json_decode($actionString, TRUE);
			$jsonError = json_last_error();
			if ($jsonError > 0) {
				switch ($jsonError) {
					case JSON_ERROR_DEPTH:
						throw new \InvalidArgumentException('Maximum stack depth exceeded.');
						break;
					case JSON_ERROR_STATE_MISMATCH:
						throw new \InvalidArgumentException('Underflow or the modes mismatch.');
						break;
					case JSON_ERROR_CTRL_CHAR:
						throw new \InvalidArgumentException('Unexpected control character found.');
						break;
					case JSON_ERROR_SYNTAX:
						throw new \InvalidArgumentException('Syntax error, malformed JSON.');
						break;
					case JSON_ERROR_UTF8:
						throw new \InvalidArgumentException('Malformed UTF-8 characters, possibly incorrectly encoded.');
						break;
					default:
						throw new \InvalidArgumentException('Unknown error.');
						break;
				}
			}
		}

		return $actionArray;
	}

}