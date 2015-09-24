<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
 * Metadata block
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_IsCalledBy ilObjectMetaDataBlockGUI: ilColumnGUI
 *
 * @ingroup ServicesObject
 */
class ilObjectMetaDataBlockGUI extends ilBlockGUI
{
	static $block_type = "advmd";
	
	protected $record; // [ilAdvancedMDRecord]
	protected $values; // [ilAdvancedMDValues]
	
	static protected $records = array(); // [array]
	
	/**
	* Constructor
	*/
	function __construct(ilAdvancedMDRecord $a_record)
	{		
		parent::ilBlockGUI();
						
		$this->record = $a_record;		
		
		$this->setTitle($this->record->getTitle());		
		$this->setBlockId("advmd_".$this->record->getRecordId());				
		$this->setEnableNumInfo(false);
		// $this->setAvailableDetailLevels(3);		
		$this->allow_moving = false;
	}

	/**
	* Get block type
	*
	* @return	string	Block type.
	*/
	static function getBlockType()
	{
		return self::$block_type;
	}

	/**
	* Is this a repository object
	*
	* @return	string	Block type.
	*/
	static function isRepositoryObject()
	{
		return false;
	}
	
	/**
	* Get Screen Mode for current command.
	*/
	static function getScreenMode()
	{
		return IL_SCREEN_SIDE;
	}
	
	public function setValues(ilAdvancedMDValues $a_values)
	{
		$this->values = $a_values;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");

		switch ($next_class)
		{
			default:
				return $this->$cmd();
		}
	}

	/**
	* Fill data section
	*/
	function fillDataSection()
	{		
		global $lng;
		
		$btpl = new ilTemplate("tpl.advmd_block.html", true, true, "Services/Object");		
		
		// see ilAdvancedMDRecordGUI::parseInfoPage()
		
		$old_dt = ilDatePresentation::useRelativeDates();		
		ilDatePresentation::setUseRelativeDates(false);
		
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
		include_once('Services/ADT/classes/class.ilADTFactory.php');	
		
		// this correctly binds group and definitions
		$this->values->read();

		$defs = $this->values->getDefinitions();									
		foreach($this->values->getADTGroup()->getElements() as $element_id => $element)				
		{																								
			$btpl->setCurrentBlock("item");
			$btpl->setVariable("CAPTION", $defs[$element_id]->getTitle());
			if($element->isNull())
			{	
				$value = "-";
			}
			else
			{
				$value = ilADTFactory::getInstance()->getPresentationBridgeForInstance($element);

				if($element instanceof ilADTLocation)
				{
					$value->setSize("100%", "200px");
				}

				$value = $value->getHTML();
			}
			$btpl->setVariable("VALUE", $value);
			$btpl->parseCurrentBlock();										
		}
					
		$this->setDataSection($btpl->get());		
		
		ilDatePresentation::setUseRelativeDates($old_dt);
		
		return;		
	}			
}

?>
