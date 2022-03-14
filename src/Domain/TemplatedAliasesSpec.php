<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Domain;

use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Term\AliasGroupList;

class TemplatedAliasesSpec implements AliasesSpec {

	/**
	 * @var string[]
	 */
	private array $languageCodes;

	private Template $buildSpecification;

	/**
	 * @param string[] $languageCodes
	 * @param Template $buildSpecification
	 */
	public function __construct( array $languageCodes, Template $buildSpecification ) {
		$this->languageCodes = $languageCodes;
		$this->buildSpecification = $buildSpecification;
	}

	public function applyTo( AliasGroupList $aliasGroups, StatementList $statements ): void {
		$aliases = ( new ValueBuilder() )->buildValues( $this->buildSpecification, $statements );

		foreach ( $this->languageCodes as $languageCode ) {
			$aliasGroups->setAliasesForLanguage( $languageCode, $aliases );
		}
	}

}
