<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues;

use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Term\AliasGroupList;

class BuildSpecBasedAliasesRule implements AliasesRule {

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

	public function applyTo( AliasGroupList $aliasGroups, StatementList $statements ): void {
		$aliases = ( new ValueBuilder() )->buildValues( $this->buildSpecification, $statements );

		foreach ( $this->languageCodes as $languageCode ) {
			$aliasGroups->setAliasesForLanguage( $languageCode, $aliases );
		}
	}

}
