<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 David Slayback <dave@webempoweredchurch.org>
*  (c) 2009 Jeff Segars <jeff@webempoweredchurch.org>
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
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	 See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Menu for advanced frontend editing.
 * This class is responsible for building the HTML of the items on top of the FE editing
 * but does not worry about the overall (rights etc)
 * 
 * This class delivers four main functions
 *   => init() sets up the paths and templates
 *   => addToolbar() and addItem() to add sections to the menu, and items to the sections
 *   => build() which renders the sections and items added previously 
 *
 * @author	David Slayback <dave@webempoweredchurch.org>
 * @author	Jeff Segars <jeff@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage feeditadvanced
 */

class tx_feeditadvanced_menu {

	/**
	 * local copy of cObject to perform various template operations
	 * @var		tslib_content
	 */
	protected $cObj = NULL;
	/**
	 * the ID of the current page (references pages::uid)
	 * @var 	int
	 */
	protected $pid = 0;
	
	/**
	 * the name of the current user (FE takes precedence over BE)
	 * @todo	why is this needed?
	 * @var 	string
	 */
	protected $username = '';
	
	/**
	 * the path to the images
	 * @var 	string
	 */
	protected $imagePath = '';

	/**
	 * the array with the TSconfig
	 * @var 	array
	 */
	protected $modTSconfig = '';

	/**
	 * HTML marker template string for the edit panel
	 * @var		string
	 */
	protected $template = '';


	/**
	 * flag whether the menu is opened
	 * @var		boolean
	 */
	protected $menuOpen = false;


	/**
	 * prefix for all CSS-classes outputted through this file
	 * @var		string
	 */
	protected $cssPrefix = 'feEditAdvanced';


	/**
	 * holds all the sections of the menu
	 * @var		array
	 */
	protected $sections = array();
	
	
	/**
	 * holds all the sections of the menu, and in each section the items for the section
	 * @var		array
	 */
	protected $itemList = array();


	/**
	 * note: don't know when this is needed currently
	 *
	 */
	protected $userList = false;
        

	/**
	 * Initializes the menu.
	 *
	 * @return	void
	 * @todo	Any reason this isn't a constructor?
	 */
	public function init() {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->pid  = intval($GLOBALS['TSFE']->id);
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($this->pid, 'FeEdit');

			// TODO: do we need this?
		//$this->getUserListing();

			// check if the menu is opened
		if (!isset($GLOBALS['BE_USER']->uc['TSFE_adminConfig']['menuOpen'])
			|| ($GLOBALS['BE_USER']->uc['TSFE_adminConfig']['menuOpen'] == true)) {
			$this->menuOpen = true;
		}

		$this->username = ($GLOBALS['TSFE']->fe_user->user['username'] ? $GLOBALS['TSFE']->fe_user->user['username'] : $GLOBALS['BE_USER']->user['username']);

			// setting the base path for the icons
		$this->imagePath = $this->modTSconfig['properties']['skin.']['imagePath'];
		$this->imagePath = ($this->imagePath ? $this->imagePath : t3lib_extMgm::siteRelPath('feeditadvanced') . 'res/icons/');

			// loading template
		$templateFile = $this->modTSconfig['properties']['skin.']['templateFile'];
		$templateFile = ($templateFile ? $templateFile : t3lib_extMgm::siteRelPath('feeditadvanced') . 'res/template/feedit.tmpl');
		$templateFile = $GLOBALS['TSFE']->tmpl->getFileName($templateFile);
		$templateFile = $GLOBALS['TSFE']->tmpl->fileContent($templateFile);
		$this->template = t3lib_parsehtml::getSubpart($templateFile, '###MENU_' . ($this->menuOpen ? 'OPENED' : 'CLOSED' ) . '###');
	}

        /*function insertButtons()
        {
            $content = '';
            $content .= "<div onclick=\"toggleItem('.feEditAdvanced-leftMenu');\" class=\"extend_feEditAdvanced-editButton toggleMenuButton\"></div>";
            return $content;
        }
        
        function leftMenu()
        {
            $content = '';
            $content .= "<div class=\"feEditAdvanced-leftMenu\"></div>";
            return $content;
        }*/
        
        function insertMenu()
        {
            global $feeditadvanced_userSettings;
           
            $userSettingsArray = array();
            $userSettingsArray = json_decode($feeditadvanced_userSettings,TRUE);

            $hidden = $GLOBALS['TSFE']->page['hidden'];
            $nav_hide = $GLOBALS['TSFE']->page['nav_hide'];
            $content = '';
            $content .= $extraCSS;
            $content .= '
            <ul class="extend_feeditadvanced_top_menu">
                
                <li id="extend_feeditadvanced_top_menu_page" class="top_menu_item" onmouseenter="toggleItem(\'.feEditAdvanced-pageItemsMenu\',\'me\');return false;" onmouseleave="toggleItem(\'.feEditAdvanced-pageItemsMenu\');return false;" >
                    <a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:pageTooltip').'">
                        <span class="icon-white-edit"></span>
                        <span>'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:page').'</span>
                    </a>
                    <div class="feEditAdvanced-pageItemsMenu top_menu_div">
                        <ul>
                            <li><a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:pageEditPageTooltip').'" href="#" onclick="editPage();return false;">'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:pageEditPage').'</a></li>
                            <li><a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:pageMovePageTooltip').'" href="#" onclick="movePage(\'\');return false;">'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:pageMovePage').'</a></li>
                            <li><a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:pageCopyPageTooltip').'" href="#" onclick="movePage(\'copy\');return false;">'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:pageCopyPage').'</a></li>
                            <li><a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:pageDeletePageTooltip').'" href="#" onclick="deletePage();return false;">'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:pageDeletePage').'</a></li>';
                            if($hidden) {
                                $content .= '<li id="hideShowPage"><a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:pageShowPageTooltip').'" href="#" onclick="showPage();return false;">'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:pageShowPage').'</a></li>';
                            } else {
                                $content .= '<li id="hideShowPage"><a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:pageHidePageTooltip').'" href="#" onclick="hidePage();return false;">'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:pageHidePage').'</a></li>';
                            }
                            if($nav_hide) {
                                $content .= '<li id="hideShowPageInMenu"><a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:pageShowPageInMenuTooltip').'" href="#" onclick="showPageInMenu();return false;">'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:pageShowPageInMenu').'</a></li>';
                            } else {
                                $content .= '<li id="hideShowPageInMenu"><a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:pageHidePageInMenuTooltip').'" href="#" onclick="hidePageInMenu();return false;">'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:pageHidePageInMenu').'</a></li>';
                            }
                        $content .= '</ul>
                    </div>
                </li>

		<li id="extend_feeditadvanced_top_menu_new" class="top_menu_item" onmouseenter="toggleItem(\'.feEditAdvanced-newItemsMenu\',\'me\');return false;" onmouseleave="toggleItem(\'.feEditAdvanced-newItemsMenu\');return false;">
                    <a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:newTooltip').'">
                        <span class="icon-white-plus"></span>
                        <span>'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:new').'</span>
                    </a>
                    <div class="feEditAdvanced-newItemsMenu top_menu_div">
                        <ul>
                            <li><a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:newPageTooltip').'" href="#" onclick="newPage();return false;">'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:newPage').'</a></li>
                            <li><a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:newContentTooltip').'" href="#" onclick="newContent();return false;">'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:newContent').'</a></li>
                            <li id="extend_feeditadvanced_create_news"><a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:newNewsTooltip').'" href="#" onclick="createNews();return false;">'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:newNews').'</a></li>
                            <li><a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:newInsertRightColumnTooltip').'" href="#" onclick="addRightColumn();return false;">'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:newInsertRightColumn').'</a></li>
                        </ul>
                    </div>
                </li>
                
                <li id="extend_feeditadvanced_top_menu_tool" class="top_menu_item" onmouseenter="toggleItem(\'.feEditAdvanced-toolItemsMenu\',\'me\');return false;" onmouseleave="toggleItem(\'.feEditAdvanced-toolItemsMenu\');return false;">
                    <a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:toolTooltip').'">
                        <span class="icon-white-cog"></span>
                        <span>'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:tool').'</span>
                    </a>
                    <div class="feEditAdvanced-toolItemsMenu top_menu_div">
                        <ul>
                            <li><a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:toolFileManagerTooltip').'" href="#" onclick="fileManager();return false;">'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:toolFileManager').'</a></li>
                            <li><a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:toolMailFormManagerTooltip').'" href="#" onclick="mailFormManager();return false;">'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:toolMailFormManager').'</a></li>
                       </ul>
                    </div>
                </li>                

                <li id="extend_feeditadvanced_top_menu_display" class="top_menu_item" onmouseenter="toggleItem(\'.feEditAdvanced-displayItemsMenu\',\'me\');return false;" onmouseleave="toggleItem(\'.feEditAdvanced-displayItemsMenu\');return false;">
                    <a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:displayTooltip').'">
                        <span class="icon-white-eye-open"></span>
                        <span>'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:display').'</span>
                    </a>
                    <div class="feEditAdvanced-displayItemsMenu top_menu_div">
                        <ul>
                            <li><a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:displayHiddenContentTooltip').'" href="#" onclick="toggleHiddenObject(\'feEditAdvanced-hiddenElement\',\'hiddenElement\');return false;"><span>'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:displayHiddenContent').'</span><span id="hiddenElement" style="display:' . str_replace('block','inline-block',$userSettingsArray['hiddenElement']) . '" class="icon-white-ok"></span></a></li>
                            <li><a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:displayHiddenInMenuTooltip').'" href="#" onclick="toggleHiddenObject(\'feEditAdvanced-hiddenInMenu\',\'hiddenInMenu\');return false;"><span>'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:displayHiddenInMenu').'</span><span id="hiddenInMenu" style="display:' . str_replace('block','inline-block',$userSettingsArray['hiddenInMenu']) . '" class="icon-white-ok"></span></a></li>
                            <li><a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:displayHiddenPageTooltip').'" href="#" onclick="toggleHiddenObject(\'feEditAdvanced-hiddenPage\',\'hiddenPage\');return false;"><span>'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:displayHiddenPage').'</span><span id="hiddenPage" style="display:' . str_replace('block','inline-block',$userSettingsArray['hiddenPage']) . '" class="icon-white-ok"></a></li>
                        </ul>
                    </div>
                </li>
                
                <li id="extend_feeditadvanced_top_menu_help" class="top_menu_item">
                    <a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:helpTooltip').'" onclick="loadHelp();return false;">
                        <span class="icon-white-question-sign"></span>
                        <span>'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:help').'</span>
                    </a>
                </li>';
                        
                $hiddenStyle = ' style="display:none;';
                if ($_COOKIE['extend_feeditadvanced_copycutitem']) {
                    $hiddenStyle ='';
                }
                $content .= '<li id="extend_feeditadvanced_top_menu_paste" class="top_menu_item"'.$hiddenStyle.'>
                    <a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:pasteTooltip').'" onclick="pasteAction(\'\');return false;">
                    <span class="icon-white-download-alt"></span>
                    <span>'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:paste').'</span>
                    </a>
                </li>';
                
                $content .= '<li id="extend_feeditadvanced_top_menu_message" class="top_menu_item">
                    <div id="msg-div"></div>
                </li>
                
                <li id="extend_feeditadvanced_top_logo"><a href="http://typo3.org"><div class="extend_feeditadvanced_top_logo"></div></a></li>
                
                <li id="extend_feeditadvanced_top_menu_user" class="top_menu_item" onmouseenter="toggleItem(\'.feEditAdvanced-userItemsMenu\',\'me\');return false;" onmouseleave="toggleItem(\'.feEditAdvanced-userItemsMenu\');return false;">
                    <a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:userTooltip').'">
                        <span class="icon-white-user"></span>
                        <span>' . $GLOBALS['BE_USER']->user['username'] . '</span>
                    </a>
                    <div class="feEditAdvanced-userItemsMenu top_menu_div">
                        <ul>
                            <li><a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:userSettingsTooltip').'" href="#" onclick="userSettings();return false;">'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:userSettings').'</a></li>
                            <li><a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:userBackendTooltip').'" href="/typo3/">'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:userBackend').'</a></li>
                            <li><a title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:userLogoutTooltip').'" href="#" onclick="feeditadvanced_logout(\'' . t3lib_div::getIndpEnv('TYPO3_REQUEST_URL') . '\');return false;">'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:userLogout').'</a></li>
                        </ul>
                    </div>
                </li>
                
                
            </ul>';
            return $content;
        }
        
                        /*<li id="extend_feeditadvanced_top_menu_hiddencontent" class="top_menu_item">
                    <div title="'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:showhiddenTooltip').'">
			<input id="feEditAdvanced-showHiddenContent-input" type="checkbox" value="1">
			<span>'.$GLOBALS['LANG']->sL('LLL:EXT:extend_feeditadvanced/locallang.xml:showhidden').'</span>
                    </div>
                </li>*/

	/**
	 * This actually renders the top menu (depending on the state whether it's opened or not)
	 * and takes care of the templating and HTML
	 * 
	 * called from tx_feeditadvanced_adminpanel->buildMenu()
	 *
	 * @return	string the ready to go HTML
	 */
	public function build() {
		$this->init();

		$markers = array(
			'EXTPATH'   => t3lib_extMgm::siteRelPath('feeditadvanced'),
			'CSSPREFIX' => $this->cssPrefix
		);

			// if the menu is not open, then just show "Activate editing" box
		if (!$this->menuOpen) {
				$markers['OPEN_EDIT_MODE'] = $this->getLL('statusActivateEditing');
				$markers['OPEN_EDIT_MODE_TOOLTIP'] = $this->getLL('openTooltip');
		} else {
				// otherwise, the menu is open

				// @todo Temporary code to draw and "Edit Page" button.
				// @todo does not work by now
			$data = $GLOBALS['TSFE']->page;
			$this->cObj->start($data, 'pages');
			$conf = array(
				'allow' => 'edit,new,delete,hide',
				'template' => 'EXT:feeditadvanced/res/template/page_buttons.tmpl'
			);
			$markers['PAGE_EDIT_PANEL'] = $this->insertMenu();
                        //$markers['PAGE_EDIT_PANEL'] = $this->cObj->editPanel('', $conf);

				// show all sections and accompanying items that are in the first row
			$sectionParts  = t3lib_parsehtml::getSubpart($this->template, '###SECTIONS_FIRST_ROW###');
			$templateSection    = t3lib_parsehtml::getSubpart($sectionParts, '###SECTION###');
			$templateSingleItem = t3lib_parsehtml::getSubpart($sectionParts, '###SINGLE_ITEM###');
			$templateSeparator  = t3lib_parsehtml::getSubpart($sectionParts, '###SEPARATOR###');

			$subparts = array(
				'SECTIONS_FIRST_ROW'  => '',
				'SECTIONS_SECOND_ROW' => '',
				'USERLISTING' => '',
			);
			
			// loop through each section and render the section and the items
			foreach ($this->sections as $section) {
				$items = $this->itemList[$section['name']];
				if (!count($items)) {
					continue;
				}
				$sectionMarkers = array(
					'CSSID'     => $section['id'],
					'INLINECSS' => $section['inlineCSS'],
					'ITEMS'     => ''
				);
				foreach ($items as $item) {
					$itemMarkers = array(
						'SEPARATOR' => ($section['useSeparator'] ? $templateSeparator : ''),
						'NAME'      => $item
					);
					$sectionMarkers['ITEMS'] .= t3lib_parsehtml::substituteMarkerArray($templateSingleItem, $itemMarkers, '###ITEM_|###');
				}
				if ($section['isInFirstRow']) {
					$subparts['SECTIONS_FIRST_ROW'] .= t3lib_parsehtml::substituteMarkerArray($templateSection, $sectionMarkers, '###SECTION_|###');
				} else {
					$subparts['SECTIONS_SECOND_ROW'] .= t3lib_parsehtml::substituteMarkerArray($templateSection, $sectionMarkers, '###SECTION_|###');
				}
			}

				// add section = showing users online
			if ($this->userList) {
				$userMarkers = array('USER_LIST' => $this->userList, 'USER_LABEL' => $this->getLL('usersOnPage'));
				$subparts['USERLISTING'] = t3lib_parsehtml::getSubpart($this->template, '###USERLISTING###');
				$subparts['USERLISTING'] = t3lib_parsehtml::substituteMarkerArray($subparts['USERLISTING'], $userMarkers, '###|###');
			}

			// replace each subpart
			foreach ($subparts as $subpartKey => $subpartContent) {
				$this->template = $this->cObj->substituteSubpart($this->template, '###' . $subpartKey . '###', $subpartContent);
			}
		}

		$content = t3lib_parsehtml::substituteMarkerArray($this->template, $markers, '###|###');

			// hook to add additional menu features, including a sidebar
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['EXT:feeditadvanced/view/class.tx_feeditadvanced_menu.php']['build'])) {
			$_params = array(
				'menuOut' => &$content,	// deprecated, should use "content" now
				'content' => &$content,
				'isMenuOpen' => $menuOpen,
				'pObj' => &$this
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['EXT:feeditadvanced/view/class.tx_feeditadvanced_menu.php']['build'] as $_funcRef) {
				$content = t3lib_div::callUserFunction($_funcRef, $_params, $this);
			}
		}
		return $content;
	}


	/***
	  * API functions to add sections to the toolbar and items to the sections
	  * 
	  * these two functions add the content that later buildMenu() renders
	  */
	/**
	 * adds an item to the toolbar on top by taking all the need components and build the HTML element
	 * 
	 * is usually called by feeditadvanced_adminpanel
	 * 
	 * @param	$name	name of the section, later used in the addItem() function to put the item to right spot
	 * @param	$id	the ID of the HTML element used 
	 * @param	$useSeparator	whether to use the template with the separator
	 * @param	$inlineCSS		whether to add inline CSS to the element
	 * @param	$isInFirstRow	whether this section should be put in the first row or in second row
	 * @return	void
	 */
	public function addToolbar($name, $id = 0, $useSeparator = false, $inlineCSS = '', $isInFirstRow = false) {
		$this->sections[] = array(
			'name'         => $name,
			'id'           => $this->cssPrefix . '-' . ($id ? $id : lcfirst($name)),
			'useSeparator' => $useSeparator,
			'inlineCSS'    => $inlineCSS,
			'isInFirstRow' => $isInFirstRow
		);
	}


	/**
	 * adds an item to the toolbar on top by taking all the need components and build the HTML element
	 * 
	 * is usually called by feeditadvanced_adminpanel
	 * 
	 * @param	$section	the section the item is placed in
	 * @param	$name	the name of the item
	 * @param	$action	the action the item is called (used as an ID for the HTML)
	 * @param	$image	the image associated with the item
	 * @param	$title	the value of the title attribute for the anchor tag, optional
	 * @param	$onClick	additional Javascript (note: needs the onclick="" as well in the parameter)
	 * @param	$btnClass	the additional class for the whole button
	 * @param	$labelClass	the additional class for the label (is inside a <span> tag)
	 * @param	$hrefParams	the additional parameters added to the href="" attribute of the link, not used but sent to the server when adding this element to the page.
	 * @param	$rel	The rel attribute.
	 * @return	void
	 */
	public function addItem($section, $name, $action, $image, $title = '', $onClick = '', $btnClass = '', $labelClass = '', $hrefParams = '', $rel = '') {

		$ATagParams = array();
		$ATagParams[] = 'href="' . (strlen($hrefParams) ? $hrefParams : '#') . '"';
		
		if (strlen($action)) {
			$ATagParams[] = 'id="' . $action . '"';
		}
		$ATagParams[] = 'class="' . $this->cssPrefix . '-button' . (strlen($btnClass) ? ' ' . $btnClass : '') . '"';
		if (strlen($title)) {
			$ATagParams[] = 'title="' . $title . '"';
		}
		if (strlen($onClick)) {
			$ATagParams[] = $onClick;
		}
		if (strlen($rel)) {
			$ATagParams[] = 'rel="' . $rel . '"';
		}
		if (strlen($image)) {
			$imageTag = '<img src="' . $this->imagePath . $image . '" class="' . $this->cssPrefix . '-buttonImage" alt="" />';
		}
		$label = '<span class="' . $this->cssPrefix . '-buttonText' . (strlen($labelClass) ? ' ' . $labelClass : '') . '">' . $name . '</span>';

		$this->itemList[$section][] = '<a ' . implode(' ', $ATagParams) . '>' . $imageTag . $label . '</a>';
	}


	/**
	 * returns a label from the Locallang TSFE file, based on the key
	 * this is mainly a shortcut version to not write the LL file with it all the time
	 * @
	 * @return	string	the localized label
	 */
	protected function getLL($key) {
		return $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_tsfe.xml:' . $key, true);
	}


	/**
	 * returns a list of all users editing something currently
	 * 
	 * @note don't know when and how we need this, also, this method needs cleanup, badly!
	 *
	 * @return	void	all the info is stored in $this->userList
	 */
	protected function getUserListing() {
		$records = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'locks.*, user.realName',
			'sys_lockedrecords AS locks LEFT JOIN be_users AS user ON locks.userid=user.uid',
			'locks.userid!='.intval($GLOBALS['BE_USER']->user['uid']).'
			AND locks.tstamp > '.($GLOBALS['EXEC_TIME']-2*3600) .' 
			AND ( (locks.record_pid='.intval($this->pid) .' AND	 locks.record_table!=\'pages\') OR
			(locks.record_uid='.intval($this->pid) .' AND  locks.record_table=\'pages\') )'
			);
		$oldUser = 0;
		$user = 0;
		$userList = array();
		$openedRecords = array();
		if (is_array($records)) {
			foreach($records AS $lockedRecord) {
				$user = $lockedRecord['userid'];
				
				if($user != $oldUser) {
					$userList[$user] = ($lockedRecord['realName'] != '' ? $lockedRecord['realName'] : $lockedRecord['username']);
					$openedRecords[$user] = array('page' => 99999999999, 'content' => 99999999999, 'data' =>99999999999);		
				}
				switch ($lockedRecord['record_table']) {
					case 'pages':
						if( $lockedRecord['tstamp'] < $openedRecords[$user]['page'] ) {
							$openedRecords[$user]['page'] = $lockedRecord['tstamp'];
						}
						break;
					case 'tt_content':
						if( $lockedRecord['tstamp'] < $openedRecords[$user]['content'] ) {
							$openedRecords[$user]['content'] = $lockedRecord['tstamp'];
						}
					default:
						if( $lockedRecord['tstamp'] < $openedRecords[$user]['data'] ) {
							$openedRecords[$user]['data'] = $lockedRecord['tstamp'];
						}
						break;
				}
				$oldUser = $user;	
			}
		}
		$renderedListing = array();
		foreach($userList AS $userID => $userName) {
			if ($openedRecords[$userID]['page'] < 99999999999) {
				$time = $openedRecords[$userID]['page'];
				$openedRecords[$userID]['page'] = 'Page-Information (since ';
				$openedRecords[$userID]['page'] .= t3lib_BEfunc::calcAge($GLOBALS['EXEC_TIME']-$time, $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.minutesHoursDaysYears'));
				$openedRecords[$userID]['page'] .= ')';
			} else {
				unset($openedRecords[$userID]['page']);
			}
			if ($openedRecords[$userID]['content'] < 99999999999) {
				$time = $openedRecords[$userID]['content'];
				$openedRecords[$userID]['content'] = 'Contents (since ';
				$openedRecords[$userID]['content'] .= t3lib_BEfunc::calcAge($GLOBALS['EXEC_TIME']-$time, $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.minutesHoursDaysYears'));
				$openedRecords[$userID]['content'] .= ')';
			} else {
				unset($openedRecords[$userID]['content']);
			}
			if ($openedRecords[$userID]['data'] < 99999999999) {
				$time = $openedRecords[$userID]['data'];
				$openedRecords[$userID]['data'] = 'Data (since ';
				$openedRecords[$userID]['data'] .= t3lib_BEfunc::calcAge($GLOBALS['EXEC_TIME']-$time, $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.minutesHoursDaysYears'));
				$openedRecords[$userID]['data'] .= ')';
			} else {
				unset($openedRecords[$userID]['data']);
			}
			$message = $userName. ' currently editing: '. implode(', ',$openedRecords[$userID]);
		
			$renderedListing[$userID] = '<span title="'. $message . '">';
			$renderedListing[$userID] .= $userName;
			$renderedListing[$userID] .= '</span>';
		}
		
		$this->userList = implode(', ',$renderedListing);
	}
       
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feeditadvanced/view/class.tx_feeditadvanced_menu.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feeditadvanced/view/class.tx_feeditadvanced_menu.php']);
}

?>