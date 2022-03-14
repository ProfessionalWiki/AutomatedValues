<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Domain;

use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Term\TermList;

class NullLabelSpec implements LabelSpec {

	public function applyTo( TermList $labels, StatementList $statements ): void {
	}

}
