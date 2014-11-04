<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Jeff Segars <jeff@webempoweredchurch.org>
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * @author	Jeff Segars <jeff@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage feeditadvanced
 */

$feeditadvanced_userSettings;
        
class tx_feeditadvanced_frontendedit extends t3lib_frontendedit {

	/**
	 * Initializes frontend editing. Mainly relies on parent class but also
	 * does some basic page setup needed by feeditadvanced before content element
	 * rendering.
	 *
	 * @return void
	 */
	public function initConfigOptions() {
		$topAdminPanel = t3lib_div::makeInstance('tx_feeditadvanced_adminpanel');
		if ($topAdminPanel->isMenuOpen()) {
			$GLOBALS['BE_USER']->adminPanel->forcePreview();
		}
                
                global $feeditadvanced_userSettings;
                $userName = $GLOBALS['BE_USER']->user['username'];
                $userSettingsArray = array();
                if(isset($_COOKIE['extend_feeditadvanced_usersettings'])) {
                    $feeditadvanced_userSettings = $_COOKIE['extend_feeditadvanced_usersettings'];
                    //echo $feeditadvanced_userSettings;
                    $userSettingsArray = json_decode($feeditadvanced_userSettings,TRUE);
                } else {
                    $userSettings = $this->getUserSettings($userName);
                    if($userSettings) {
                        $feeditadvanced_userSettings = $userSettings;
                        $userSettingsArray = json_decode($feeditadvanced_userSettings,TRUE);
                        try {
                            setcookie("extend_feeditadvanced_usersettings", $feeditadvanced_userSettings, "0", "/");
                        }
                        //catch exception
                        catch(Exception $e) {
                            echo 'Message: ' .$e->getMessage();
                        }
                    } else {

                        $userSettingsArray["hiddenElement"] = "none";
                        $userSettingsArray["hiddenInMenu"] = "none";
                        $userSettingsArray["hiddenPage"] = "none";
                        $feeditadvanced_userSettings = json_encode($userSettingsArray);
                        try {
                            setcookie("extend_feeditadvanced_usersettings", $feeditadvanced_userSettings, "0", "/");
                        
                        }
                        //catch exception
                        catch(Exception $e) {
                            echo 'Message: ' .$e->getMessage();
                        }
                        
                    }
                }
        
                $cssContent = '<style type="text/css">
                .feEditAdvanced-hiddenPage {
                        display:'. $userSettingsArray['hiddenPage'] . ';
                        opacity: 0.5;
                        -ms-filter: "alpha(opacity=50)";
                    }

                    .feEditAdvanced-hiddenInMenu {
                        display:'. $userSettingsArray['hiddenInMenu'] . ';
                        opacity: 0.5;
                        -ms-filter: "alpha(opacity=50)";
                    }
                    </style>';
                $GLOBALS["TSFE"]->additionalHeaderData["feeditadvanced_extracss"] = $cssContent;

		parent::initConfigOptions();
	}
        
        function getUserSettings($username)
        {
            $userSettings;
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("tx_feeditadvanced_usersettings", "be_users", "username='". addslashes($username). "'") or die('108; '.mysql_error());
            $row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res);
            $userSettings = $row['tx_feeditadvanced_usersettings'];
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
            return $userSettings;
        }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feeditadvanced/controller/class.tx_feeditadvanced_frontendedit.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feeditadvanced/controller/class.tx_feeditadvanced_frontendedit.php']);
}
?>