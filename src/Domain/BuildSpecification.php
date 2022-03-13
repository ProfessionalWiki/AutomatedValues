<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Domain;

class BuildSpecification {

	/**
	 * @var Segment[]
	 */
	public /* readonly */ array $segments;

	public function __construct( Segment ...$segments ) {
		$this->segments = $segments;
	}

	public function supportsMultipleValues(): bool {
		return count( array_unique(
				array_map(
					fn( Segment $s ) => $s->statementPropertyId,
					$this->segments
				)
			) ) === 1;
	}

}
