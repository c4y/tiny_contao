<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Leo Feyer 2005-2012
 * @author     Leo Feyer <http://www.contao.org>
 * @package    Backend
 * @license    LGPL
 * @filesource
 */


/**
 * Class FileTree
 *
 * Provide methods to handle input field "file tree".
 * @copyright  Leo Feyer 2005-2012
 * @author     Leo Feyer <http://www.contao.org>
 * @package    Controller
 */
class TinyFileTree extends Widget
{

	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = true;

	/**
	 * Template
	 * @var string
	 */
	//protected $strTemplate = 'be_widget';
    protected $strTemplate = 'be_tiny_filepicker';

	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$this->import('BackendUser', 'User');

		$tree = '';
		$path = $GLOBALS['TL_CONFIG']['uploadPath'];

		if (!is_array($this->varValue))
		{
			$this->varValue = array($this->varValue);
		}

		// Start from root
		if ($this->User->isAdmin)
		{
			$tree = $this->renderFiletree(TL_ROOT . '/' . $path, 0);
		}

		// Set filemounts
		else
		{
			foreach ($this->eliminateNestedPaths($this->User->filemounts) as $node)
			{
				$tree .= $this->renderFiletree(TL_ROOT . '/' . $node, 0, true);
			}
		}

		$strReset = '';
		$strReset = "\n" . '    <li class="tl_folder"><div class="tl_left">&nbsp;</div> <div class="tl_right"><label for="reset_' . $this->strId . '" class="tl_change_selected">' . $GLOBALS['TL_LANG']['MSC']['resetSelected'] . '</label> <input type="radio" name="' . $this->strName . '" id="reset_' . $this->strName . '" class="tl_tree_radio" value="" onfocus="Backend.getScrollOffset()"></div><div style="clear:both"></div></li>';

		return '<ul class="tl_listing tree_view filetree'.(strlen($this->strClass) ? ' ' . $this->strClass : '').'" id="tinyfiletree">
    <li class="tl_folder_top"><div class="tl_left">'.$this->generateImage('filemounts.gif').' '.(strlen($GLOBALS['TL_LANG']['MSC']['filetree']) ? $GLOBALS['TL_LANG']['MSC']['filetree'] : 'Files directory').'</div> <div class="tl_right"><label for="ctrl_'.$this->strId.'" class="tl_change_selected">'.$GLOBALS['TL_LANG']['MSC']['changeSelected'].'</label> <input type="checkbox" name="'.$this->strName.'_save" id="ctrl_'.$this->strId.'" class="tl_tree_checkbox" value="1" onclick="Backend.showTreeBody(this,\''.$this->strId.'_parent\')"></div><div style="clear:both"></div></li><li class="parent" id="'.$this->strId.'_parent"><ul>'.$tree.$strReset.'
  </ul></li></ul>';

	}


	/**
	 * Generate a particular subpart of the file tree and return it as HTML string
	 * @param string
	 * @param string
	 * @param integer
	 * @param boolean
	 * @return string
	 */
	public function generateAjax($folder, $strField, $level, $mount=false)
	{
		if (!$this->Environment->isAjaxRequest)
		{
			return '';
		}

		return $this->renderFiletree(TL_ROOT.'/'.$folder, ($level * 20), $mount);
	}


	/**
	 * Recursively render the file tree
	 * @param string
	 * @param integer
	 * @param boolean
	 * @return string
	 */
	protected function renderFiletree($path, $intMargin, $mount=false)
	{
		// Invalid path
		if (!is_dir($path))
		{
			return '';
		}

		// Make sure that $this->varValue is an array (see #3369)
		if (!is_array($this->varValue))
		{
			$this->varValue = array($this->varValue);
		}

		static $session;
		$session = $this->Session->getData();

		$flag = substr($this->strField, 0, 2);
		$node = 'tree_' . $this->strTable . '_' . $this->strField;
		$xtnode = 'tree_' . $this->strTable . '_' . $this->strName;

		// Get session data and toggle nodes
		if ($this->Input->get($flag.'tg'))
		{
			$session[$node][$this->Input->get($flag.'tg')] = (isset($session[$node][$this->Input->get($flag.'tg')]) && $session[$node][$this->Input->get($flag.'tg')] == 1) ? 0 : 1;
			$this->Session->setData($session);

			$this->redirect(preg_replace('/(&(amp;)?|\?)'.$flag.'tg=[^& ]*/i', '', $this->Environment->request));
		}

		$return = '';
		$intSpacing = 20;
		$files = array();
		$folders = array();
		$level = ($intMargin / $intSpacing + 1);

		// Mount folder
		if ($mount)
		{
			$folders = array($path);
		}

		// Scan directory and sort the result
		else
		{
			foreach (scan($path) as $v)
			{
				if (!is_dir($path.'/'.$v) && $v != '.DS_Store')
				{
					$files[] = $path.'/'.$v;
					continue;
				}

				if (substr($v, 0, 1) != '.')
				{
					$folders[] = $path.'/'.$v;
				}
			}
		}

		natcasesort($folders);
		$folders = array_values($folders);

		natcasesort($files);
		$files = array_values($files);

		$folderClass = 'tl_folder';

		// Process folders
		for ($f=0; $f<count($folders); $f++)
		{
			$countFiles = 0;
			$return .= "\n    " . '<li class="'.$folderClass.'" onmouseover="Theme.hoverDiv(this, 1)" onmouseout="Theme.hoverDiv(this, 0)"><div class="tl_left" style="padding-left:'.$intMargin.'px">';

			// Check whether there are subfolders or files
			foreach (scan($folders[$f]) as $v)
			{
                // Abfrage ob files durchsucht ausgewählt werden dürfen gegen true ersetzt
				if (is_dir($folders[$f].'/'.$v) || true)
				{
					$countFiles++;
				}
			}

			$tid = md5($folders[$f]);
			$folderAttribute = 'style="margin-left:20px"';
			$session[$node][$tid] = is_numeric($session[$node][$tid]) ? $session[$node][$tid] : 0;
			$currentFolder = str_replace(TL_ROOT.'/', '', $folders[$f]);
			$blnIsOpen = ($session[$node][$tid] == 1 || count(preg_grep('/^' . preg_quote($currentFolder, '/') . '\//', $this->varValue)) > 0);

			// Add a toggle button if there are childs
			if ($countFiles > 0)
			{
				$folderAttribute = '';
				$img = $blnIsOpen ? 'folMinus.gif' : 'folPlus.gif';
				$alt = $blnIsOpen ? $GLOBALS['TL_LANG']['MSC']['collapseNode'] : $GLOBALS['TL_LANG']['MSC']['expandNode'];
				$return .= '<a href="'.$this->addToUrl($flag.'tg='.$tid).'" title="'.specialchars($alt).'" onclick="Backend.getScrollOffset();return AjaxRequest.toggleFiletree(this,\''.$xtnode.'_'.$tid.'\',\''.$currentFolder.'\',\''.$this->strField.'\',\''.$this->strName.'\','.$level.')">'.$this->generateImage($img, '', 'style="margin-right:2px"').'</a>';
			}

			$folderImg = ($blnIsOpen && $countFiles > 0) ? 'folderO.gif' : 'folderC.gif';
			$folderLabel = '<strong>'.specialchars(basename($currentFolder)).'</strong>';

			// Prevent folder selection
			$return .= $this->generateImage($folderImg, '', $folderAttribute).' <label>'.$folderLabel.'</label></div> <div class="tl_right">&nbsp;';
			$return .= '</div><div style="clear:both"></div></li>';

			// Call the next node
			if ($countFiles > 0 && $blnIsOpen)
			{
				$return .= '<li class="parent" id="'.$xtnode.'_'.$tid.'"><ul class="level_'.$level.'">';
				$return .= $this->renderFiletree($folders[$f], ($intMargin + $intSpacing));
				$return .= '</ul></li>';
			}
		}

		// Process files
		if (true)
		{
			$allowedExtensions = null;

            // todo: GLOBALS ersetzen gegen eigene Settings
			//$allowedExtensions = trimsplit(',', 'jpg,jpeg,png,pdf,txt');

			for ($h=0; $h<count($files); $h++)
			{
				$thumbnail = '';
				$popupWidth = 600;
				$popupHeight = 260;

				$currentFile = str_replace(TL_ROOT . '/', '', $files[$h]);
				$currentEncoded = $this->urlEncode($currentFile);

				$objFile = new File($currentFile);

				// Check file extension
				if (is_array($allowedExtensions) && !in_array($objFile->extension, $allowedExtensions))
				{
					continue;
				}

				$return .= "\n    " . '<li class="tl_file" onmouseover="Theme.hoverDiv(this, 1)" onmouseout="Theme.hoverDiv(this, 0)"><div class="tl_left" style="padding-left:'.($intMargin + $intSpacing).'px">';

				// Generate thumbnail
				if ($objFile->isGdImage && $objFile->height > 0)
				{
					$popupWidth = ($objFile->width > 600) ? ($objFile->width + 61) : 661;
					$popupHeight = ($objFile->height + 305);
					$thumbnail .= ' <span class="tl_gray">('.$objFile->width.'x'.$objFile->height.')</span>';

					if ($GLOBALS['TL_CONFIG']['thumbnails'] && $objFile->height <= $GLOBALS['TL_CONFIG']['gdMaxImgHeight'] && $objFile->width <= $GLOBALS['TL_CONFIG']['gdMaxImgWidth'])
					{
						$_height = ($objFile->height < 70) ? $objFile->height : 70;
						$_width = (($objFile->width * $_height / $objFile->height) > 400) ? 90 : '';

						$thumbnail .= '<br><img src="' . TL_FILES_URL . $this->getImage($currentEncoded, $_width, $_height) . '" alt="" style="margin:0px 0px 2px 23px">';
					}
				}

				$return .= '<a href="contao/popup.php?src='.base64_encode($currentEncoded).'" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['view']).'" data-lightbox="details '.$popupWidth.' '.$popupHeight.'">' . $this->generateImage($objFile->icon, $objFile->mime).'</a> <label for="'.$this->strName.'_'.md5($currentFile).'">'.utf8_convert_encoding(specialchars(basename($currentFile)), $GLOBALS['TL_CONFIG']['characterSet']).'</label>'.$thumbnail.'</div> <div class="tl_right">';

				// Add checkbox or radio button
                // todo: GLOBALS ersetzen (Auswahl checkbox/radio entfernt)

				$return .= '<input type="radio" name="tinyfiles" id="'.'tiny'.'_'.md5($currentFile).'" class="tl_tree_radio" value="'.specialchars($currentFile).'" onfocus="Backend.getScrollOffset()"'.$this->optionChecked($currentFile, $this->varValue).'>';

				$return .= '</div><div style="clear:both"></div></li>';
			}
		}

		return $return;
	}
}

?>