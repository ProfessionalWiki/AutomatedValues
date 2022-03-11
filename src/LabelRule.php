<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues;

use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Term\TermList;

interface LabelRule {

	public function applyTo( TermList $labels, StatementList $statements ): void;

}
