<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @package Core
 * @link    http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Initialize the system
 */
define('TL_MODE', 'BE');
require_once '../../initialize.php';


/**
 * Class FilePicker
 *
 * Back end page picker.
 * @copyright  Leo Feyer 2005-2012
 * @author     Leo Feyer <http://www.contao.org>
 * @package    Core
 */
class FilePicker extends Backend
{

	/**
	 * Current Ajax object
	 * @var object
	 */
	protected $objAjax;
    protected $strTable;


	/**
	 * Initialize the controller
	 *
	 * 1. Import the user
	 * 2. Call the parent constructor
	 * 3. Authenticate the user
	 * 4. Load the language files
	 * DO NOT CHANGE THIS ORDER!
	 */
	public function __construct()
	{
		$this->import('BackendUser', 'User');
		parent::__construct();

		$this->User->authenticate();
		$this->loadLanguageFile('default');
	}


	/**
	 * Run the controller and parse the template
	 */
	public function run()
	{
		$this->Template = new BackendTemplate('be_tinyfiles');
		$this->Template->main = '';


        if ($this->Environment->isAjaxRequest)
        {
            //$this->strTable = $strTable;
            $this->objAjax = new Ajax($this->Input->post('action'));
			$this->objAjax->executePreActions();
            $objFileTree = new TinyFileTree();
            $folder = $this->Input->post('folder');
            $level  = $this->Input->post('level');
            echo $objFileTree->generateAjax($folder, '', $level);
            return;
        }

		//$objFileTree = new $GLOBALS['BE_FFL']['fileTree']();
        $objFileTree = new TinyFileTree();

		$this->Template->main = $objFileTree->generate();
		$this->Template->theme = $this->getTheme();
        $this->Template->base = $this->Environment->base;
		$this->Template->language = $GLOBALS['TL_LANGUAGE'];
		$this->Template->title = specialchars($GLOBALS['TL_LANG']['MSC']['filepicker']);
		$this->Template->headline = 'Dateiauswahl';
		$this->Template->charset = $GLOBALS['TL_CONFIG']['characterSet'];
		$this->Template->expandNode = $GLOBALS['TL_LANG']['MSC']['expandNode'];
		$this->Template->collapseNode = $GLOBALS['TL_LANG']['MSC']['collapseNode'];
		$this->Template->loadingData = $GLOBALS['TL_LANG']['MSC']['loadingData'];
		$this->Template->search = $GLOBALS['TL_LANG']['MSC']['search'];
//		$this->Template->action = ampersand(Environment::get('request'));
		$this->Template->value = $this->Session->get('file_selector_search');

		$this->Template->output();
	}
}


/**
 * Instantiate the controller
 */
$objFilePicker = new FilePicker();
$objFilePicker->run();
?>