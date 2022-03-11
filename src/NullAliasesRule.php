<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues;

use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Term\AliasGroupList;

class NullAliasesRule implements AliasesRule {

	public function applyTo( AliasGroupList $aliasGroups, StatementList $statements ): void {
	}

}
