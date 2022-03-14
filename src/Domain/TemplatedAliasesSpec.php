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

	private Template $template;

	/**
	 * @param string[] $languageCodes
	 * @param Template $template
	 */
	public function __construct( array $languageCodes, Template $template ) {
		$this->languageCodes = $languageCodes;
		$this->template = $template;
	}

	public function applyTo( AliasGroupList $aliasGroups, StatementList $statements ): void {
		$aliases = $this->template->buildValues( $statements );

		foreach ( $this->languageCodes as $languageCode ) {
			$aliasGroups->setAliasesForLanguage( $languageCode, $aliases );
		}
	}

}
