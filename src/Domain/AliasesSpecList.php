<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Domain;

use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Term\AliasGroupList;

class AliasesSpecList {

	/**
	 * @var AliasesSpec[]
	 */
	private array $aliasesSpecs;

	public function __construct( AliasesSpec ...$aliasesSpecs ) {
		$this->aliasesSpecs = $aliasesSpecs;
	}

	public function applyTo( AliasGroupList $aliasGroups, StatementList $statements ): void {
		foreach ( $this->aliasesSpecs as $aliasesSpec ) {
			$aliasesSpec->applyTo( $aliasGroups, $statements );
		}
	}

}
