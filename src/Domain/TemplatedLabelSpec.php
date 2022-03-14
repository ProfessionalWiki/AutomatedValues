<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Domain;

use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Term\TermList;

class TemplatedLabelSpec implements LabelSpec {

	/**
	 * @var string[]
	 */
	private array $languageCodes;

	private Template $template;

	/**
	 * @param string[] $languageCodes
	 * @param Template $template
	 */
	public function __construct( array $languageCodes, Template $template ) {
		$this->languageCodes = $languageCodes;
		$this->template = $template;
	}

	public function applyTo( TermList $labels, StatementList $statements ): void {
		$label = $this->template->buildValue( $statements );

		foreach ( $this->languageCodes as $languageCode ) {
			$labels->setTextForLanguage( $languageCode, $label );
		}
	}

}
