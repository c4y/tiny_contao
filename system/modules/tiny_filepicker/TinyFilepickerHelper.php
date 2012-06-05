<?php if(!defined('TL_ROOT')) {die('You cannot access this file directly!');
}

/**
 * @copyright 4ward.media 2012 <http://www.4wardmedia.de>
 * @author Christoph Wiechert <wio@psitrax.de>
 */
 
class TinyFilepickerHelper extends System
{

	public function TinyMCE_Customzier_getFilebrowser($arrBrowsers)
	{
		$arrBrowsers['tinyFilepicker'] = array
		(
			'label'	=> 'Tiny Filebrowser',
			'javascript' => '
    fileBrowserURL = "'.TL_PATH.'/system/modules/tiny_filepicker/tinyfiles.php";
    tinyMCE.activeEditor.windowManager.open({
            title: "Contao File Browser",
            url: fileBrowserURL,
            width: 750,
            height: 400,
            inline: 0,
            maximizable: 1,
            close_previous: 0
        },
        {
            window : win,
            input : field_name
        }
    );'
		);

		return $arrBrowsers;
	}
}