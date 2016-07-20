<?php
namespace \CaT\TableRelations\Tables;
use \CaT\TableRelations\Graphs as Graphs;

abstract class TableDependency implements Graphs\abstractTableDependency, Graphs\abstractEdge {
	public function dependingTables(abstractTable $from, abstractTable $to, Predicates\Predicate $predicate) {
		$this->from = $from;
		$this->to = $to;
	}

	public function from() {
		return $this->from->id();
	}

	public function to() {
		return $this->to->id();
	}

	public function dependanceCondition() {
		return $this->predicate;
	}
}