<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestParticipantData.php';

/**
 * Class ilTestAccess
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 */
class ilTestAccess
{
    /**
     * @var ilAccessHandler
     */
    protected $access;
    
    /**
     * @var integer
     */
    protected $refId;
    
    /**
     * @var integer
     */
    protected $testId;
    
    /**
     * @param integer $refId
     * @param integer $testId
     */
    public function __construct($refId, $testId)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $this->setAccess($DIC->access());
        
        $this->setRefId($refId);
        $this->setTestId($testId);
    }
    
    /**
     * @return ilAccessHandler
     */
    public function getAccess() : ilAccessHandler
    {
        return $this->access;
    }
    
    /**
     * @param ilAccessHandler $access
     */
    public function setAccess($access)
    {
        $this->access = $access;
    }
    
    /**
     * @return int
     */
    public function getRefId() : int
    {
        return $this->refId;
    }
    
    /**
     * @param int $refId
     */
    public function setRefId($refId)
    {
        $this->refId = $refId;
    }
    
    /**
     * @return int
     */
    public function getTestId() : int
    {
        return $this->testId;
    }
    
    /**
     * @param int $testId
     */
    public function setTestId($testId)
    {
        $this->testId = $testId;
    }
    
    /**
     * @return bool
     */
    public function checkCorrectionsAccess() : bool
    {
        return $this->getAccess()->checkAccess('write', '', $this->getRefId());
    }
    
    /**
     * @return bool
     */
    public function checkScoreParticipantsAccess() : bool
    {
        if ($this->getAccess()->checkAccess('write', '', $this->getRefId())) {
            return true;
        }
        
        if ($this->getAccess()->checkPositionAccess(ilOrgUnitOperation::OP_SCORE_PARTICIPANTS, $this->getRefId())) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @return bool
     */
    public function checkManageParticipantsAccess() : bool
    {
        if ($this->getAccess()->checkAccess('tst_results', '', $this->getRefId())) {
            return true;
        }
        
        if ($this->getAccess()->checkPositionAccess(ilOrgUnitOperation::OP_MANAGE_PARTICIPANTS, $this->getRefId())) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @return bool
     */
    public function checkParticipantsResultsAccess() : bool
    {
        if ($this->getAccess()->checkAccess('tst_results', '', $this->getRefId())) {
            return true;
        }
        
        if ($this->getAccess()->checkPositionAccess(ilOrgUnitOperation::OP_ACCESS_RESULTS, $this->getRefId())) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @return bool
     */
    public function checkStatisticsAccess() : bool
    {
        if ($this->getAccess()->checkAccess('tst_statistics', '', $this->getRefId())) {
            return true;
        }
        
        return $this->checkParticipantsResultsAccess();
    }
    
    /**
     * @param callable $participantAccessFilter
     * @param integer $activeId
     * @return bool
     */
    protected function checkAccessForActiveId($accessFilter, $activeId) : bool
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $participantData = new ilTestParticipantData($DIC->database(), $DIC->language());
        $participantData->setActiveIdsFilter(array($activeId));
        $participantData->setParticipantAccessFilter($accessFilter);
        $participantData->load($this->getTestId());
        
        return in_array($activeId, $participantData->getActiveIds());
    }
    
    /**
     * @param integer $activeId
     * @return bool
     */
    public function checkResultsAccessForActiveId($activeId) : bool
    {
        $accessFilter = ilTestParticipantAccessFilter::getAccessResultsUserFilter($this->getRefId());
        return $this->checkAccessForActiveId($accessFilter, $activeId);
    }
    
    /**
     * @param integer $activeId
     * @return bool
     */
    public function checkScoreParticipantsAccessForActiveId($activeId) : bool
    {
        $accessFilter = ilTestParticipantAccessFilter::getScoreParticipantsUserFilter($this->getRefId());
        return $this->checkAccessForActiveId($accessFilter, $activeId);
    }
    
    /**
     * @param integer $activeId
     * @return bool
     */
    public function checkStatisticsAccessForActiveId($activeId) : bool
    {
        $accessFilter = ilTestParticipantAccessFilter::getAccessStatisticsUserFilter($this->getRefId());
        return $this->checkAccessForActiveId($accessFilter, $activeId);
    }
}
