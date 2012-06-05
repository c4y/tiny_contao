<?php if(!defined('TL_ROOT')) {die('You cannot access this file directly!');
}

/**
 * @copyright 4ward.media 2012 <http://www.4wardmedia.de>
 * @author Christoph Wiechert <wio@psitrax.de>
 */

$GLOBALS['TL_HOOKS']['TinyMCE_Customizer_Filebrowser'][] = array('TinyFilepickerHelper','TinyMCE_Customzier_getFilebrowser');