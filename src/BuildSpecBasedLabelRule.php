<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues;

use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Term\TermList;

class BuildSpecBasedLabelRule implements LabelRule {

	/**
	 * @var string[]
	 */
	private array $languageCodes;

	private BuildSpecification $buildSpecification;

	/**
	 * @param string[] $languageCodes
	 * @param BuildSpecification $buildSpecification
	 */
	public function __construct( array $languageCodes, BuildSpecification $buildSpecification ) {
		$this->languageCodes = $languageCodes;
		$this->buildSpecification = $buildSpecification;
	}

	public function applyTo( TermList $labels, StatementList $statements ): void {
		$label = ( new ValueBuilder() )->buildValue( $this->buildSpecification, $statements );

		foreach ( $this->languageCodes as $languageCode ) {
			$labels->setTextForLanguage( $languageCode, $label );
		}
	}

}
