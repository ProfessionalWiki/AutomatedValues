<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Domain;

use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Term\TermList;

interface LabelSpec {

	public function applyTo( TermList $labels, StatementList $statements ): void;

}
