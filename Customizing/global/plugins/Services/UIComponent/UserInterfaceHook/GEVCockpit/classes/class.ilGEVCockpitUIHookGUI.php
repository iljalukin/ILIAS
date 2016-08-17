<?php

/* Copyright (c) 2016, Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

require_once("./Services/UIComponent/classes/class.ilUIHookPluginGUI.php");

/**
 * Creates a submenu for the Cockpit of the GEV.
 */
class ilGEVCockpitUIHookGUI extends ilUIHookPluginGUI {
	/**
	 * @inheritdoc
	 */
	function getHTML($a_comp, $a_part, $a_par = array()) {
		if ( 	$a_part != "template_get"
			|| 	$a_par["tpl_id"] != "Services/MainMenu/tpl.main_menu.html"
			|| 	(!$this->isCockpit() && !$this->isSearch())
		   ) {
			return parent::getHTML($a_comp, $a_part, $a_par);
		}

		global $ilUser;
		$user_utils = gevUserUtils::getInstanceByObj($ilUser);

		if ($this->isCockpit()) { 
			$this->active = $this->getActiveItem();
			$this->items = array
				( "bookings"
					=> array("Buchungen", "ilias.php?baseClass=gevDesktopGUI&cmd=toMyCourses")
				, "edubio"
					=> array("Bildungsbiografie", $user_utils->getEduBioLink())
				, "profile"
					=> array("Profil", "ilias.php?baseClass=gevDesktopGUI&cmd=toMyProfile")
				, "tep"
					=> array("TEP", "ilias.php?baseClass=ilTEPGUI")
				, "trainer_ops"
					=> array("Trainingseinsätze", "ilias.php?baseClass=gevDesktopGUI&cmd=toMyTrainingsAp")
				, "training_admin"
					=> array("Trainingsverwaltung", "ilias.php?baseClass=gevDesktopGUI&cmd=toMyTrainingsAdmin")
				);
		}
		else if ($this->isSearch()) {
			$this->active = "search_all";
			$this->items = array
				( "search"
					=> array("Suche", "http://www.google.de")
				, "search_all"
					=> array("Alle", "http://www.google.de")
				, "search_onside"
					=> array("Präsenz", "http://www.google.de")
				, "search_webinar"
					=> array("Webinar", "http://www.google.de")
				, "search_wbt"
					=> array("Selbstlernkurs", "http://www.google.de")
				);
		}
		else {
			throw new \LogicException("Should not get here...");
		}

		$current_skin = ilStyleDefinition::getCurrentSkin();

		$this->addCss($current_skin);
		$html = $this->getSubMenuHTML($current_skin);

		return array
			( "mode" => ilUIHookPluginGUI::APPEND
			, "html" => $html
			);
	}

	protected function isCockpit() {
		global $ilUser;
		return
			( $_GET["baseClass"] == "gevDesktopGUI" 
				|| ($_GET["cmdClass"] == "ilobjreportedubiogui"
					&& $_GET["target_user_id"] == $ilUser->getId())
				|| $_GET["baseClass"] == "ilTEPGUI"
			)
			&& $_GET["cmdClass"] != "gevcoursesearchgui"
			&& $_GET["cmdClass"] != "iladminsearchgui"
			;
	}

	protected function isSearch() {
		return $_GET["baseClass"] == "gevDesktopGUI"
			&& $_GET["cmdClass"] == "gevcoursesearchgui"
			;
	}

	protected function getActiveItem() {
		if ($this->isCockpit()) {
			if ($_GET["cmdClass"] == "gevmycoursesgui") {
				return "bookings";
			}
			if ($_GET["cmdClass"] == "ilobjreportedubiogui") {
				return "edubio";
			}
			if ($_GET["cmdClass"] == "gevuserprofilegui") {
				return "profile";
			}
			if ($_GET["baseClass"] == "ilTEPGUI") {
				return "tep";
			}
			if ($_GET["cmdClass"] == "gevmytrainingsapgui") {
				return "trainer_ops";
			}
			if ($_GET["cmdClass"] == "gevmytrainingsadmingui") {
				return "training_admin";
			}
		}
		return null;
	}

	protected function getSubMenuHTML($current_skin) {
		assert('is_string($current_skin)');
		$tpl = $this->getTemplate($current_skin, true, true); 
		$count = 1;
		foreach ($this->items as $id => $data) {
			list($label, $link) = $data;
			if ($this->active == $id) {
				$tpl->touchBlock("active");
			}
			$tpl->setCurrentBlock("item");
			$tpl->setVariable("ID", $id);
			$tpl->setVariable("LABEL", $label);
			$tpl->setVariable("LINK", $link);
			$tpl->parseCurrentBlock();
			$count++;
		}
		return $tpl->get();
	}

	protected function addCss($current_skin) {
		assert('is_string($current_skin)');
		global $tpl;
		$loc = $this->getStyleSheetLocation($current_skin);
		$tpl->addCss($loc);
	}

	protected function getTemplate($current_skin, $remove_unknown_vars, $remove_empty_blocks) {
		assert('is_string($current_skin)');
		$skin_folder = $this->getSkinFolder($current_skin);
		$tpl_file = "tpl.submenu.html";
		$tpl_path = $skin_folder."/Plugins/GEVCockpit/$tpl_file";
		if (is_file($tpl_path)) {
			return new ilTemplate($tpl_path, $remove_unknown_vars, $remove_empty_blocks);
		}
		else {
			return $this->plugin_object->getTemplate("tpl.submenu.html", $remove_unknown_vars, $remove_empty_blocks);
		}
	}

	protected function getStyleSheetLocation($current_skin) {
		assert('is_string($current_skin)');
		$skin_folder = $this->getSkinFolder($current_skin);
		$css_file = "submenu.css";
		$css_path = $skin_folder."/Plugins/GEVCockpit/$css_file";
		if (is_file($css_path)) {
			return $css_path;
		}
		else {
			return $this->plugin_object->getStyleSheetLocation("submenu.css");
		}
	}

	protected function getSkinFolder($current_skin) {
		assert('is_string($current_skin)');
		return "./Customizing/global/skin/$current_skin";
	}
}
