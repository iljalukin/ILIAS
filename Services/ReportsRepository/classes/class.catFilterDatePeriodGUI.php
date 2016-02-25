<?php
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/Form/classes/class.ilDateDurationInputGUI.php");
require_once("Services/Form/classes/class.ilHiddenInputGUI.php");

class catFilterDatePeriodGUI {
	protected $parent;
	protected $filter;
	protected $path;
	protected $options;

	public function __construct($parent, $filter, $path, array $post_values) {
		$this->parent = $parent;
		$this->filter = $filter;
		$this->path = $path;
		$this->options = array("0"=>"Ja","1"=>"Nein");
	}

	public function executeCommand() {

	}

	public function getHTML() {
		$form = new ilPropertyFormGUI();
		$form->setTitle("Title");
		$form->addCommandButton("saveFilter", $this->lng->txt("continue"));
		$form->setFormAction($this->ctrl->getFormAction($this->parent));

		$duration = new ilDateDurationInputGUI($this->filter->label(), $this->path);
		$duration->setInfo($this->filter->description());
		$duration->setShowDate(true);
		$duration->setShowTime(false);
		$form->addItem($duration);

		$post_values = new ilHiddenInputGUI("post_values");
		$post_values->setValue(serialize($post_values));
		$form->addItem($post_values);

		return $form->getHTML();
	}
}