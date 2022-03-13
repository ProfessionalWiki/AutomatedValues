<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Domain;

use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Term\AliasGroupList;

interface AliasesRule {

	public function applyTo( AliasGroupList $aliasGroups, StatementList $statements ): void;

}
