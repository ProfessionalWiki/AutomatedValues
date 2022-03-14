<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Domain;

use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Term\TermList;

class LabelSpecList {

	/**
	 * @var LabelSpec[]
	 */
	private array $labelSpecs;

	public function __construct( LabelSpec ...$labelSpecs ) {
		$this->labelSpecs = $labelSpecs;
	}

	public function applyTo( TermList $labels, StatementList $statements ): void {
		foreach ( $this->labelSpecs as $labelSpec ) {
			$labelSpec->applyTo( $labels, $statements );
		}
	}

}
