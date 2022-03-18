<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\DataAccess;

use ProfessionalWiki\AutomatedValues\Domain\Rules;

interface RulesLookup {

	public function getRules(): Rules;

}
