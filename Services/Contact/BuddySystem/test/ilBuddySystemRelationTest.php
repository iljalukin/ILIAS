<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemRelationTest
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilBuddySystemRelationTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var ilBuddyList
	 */
	protected $buddylist;

	/**
	 *
	 */
	public function setUp()
	{
	}

	/**
	 *
	 */
	public function testPriorStateIsEmptyAfterInstanceWasCreated()
	{
		$state_mock = $this->getMock('ilBuddySystemRelationState');
		$relation   = new ilBuddySystemRelation($state_mock);
		$this->assertNull($relation->getPriorState());
	}

	/**
	 *
	 */
	public function testPriorStateCanBeRetrievedAfterSubsequentTransitions()
	{
		$state_mock         = $this->getMock('ilBuddySystemRelationState');
		$further_state_mock = $this->getMock('ilBuddySystemRelationState');
		$finish_state_mock  = $this->getMock('ilBuddySystemRelationState');
		$state_mock->expects($this->any())->method('link');

		$relation = new ilBuddySystemRelation($state_mock);
		$relation->setState($further_state_mock);
		$this->assertEquals($state_mock, $relation->getPriorState());
		$relation->setState($finish_state_mock);
		$this->assertEquals($state_mock, $relation->getPriorState());
	}

	/**
	 *
	 */
	public function testValuesCanBeFetchedByGettersWhenSetBySetters()
	{
		$state_mock = $this->getMock('ilBuddySystemRelationState');
		$relation = new ilBuddySystemRelation($state_mock);

		$relation->setUserId(1);
		$this->assertEquals(1, $relation->getUserId());

		$relation->setBuddyUserId(2);
		$this->assertEquals(2, $relation->getBuddyUserId());

		$ts = time();
		$relation->setTimestamp($ts);
		$this->assertEquals($ts, $relation->getTimestamp());
	}
}