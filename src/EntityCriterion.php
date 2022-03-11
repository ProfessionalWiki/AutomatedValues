<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues;

use Wikibase\DataModel\Entity\StatementListProvidingEntity;

interface EntityCriterion {

	public function matches( StatementListProvidingEntity $entity ): bool;

}
