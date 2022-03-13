<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Domain;

use Wikibase\DataModel\Entity\StatementListProvidingEntity;

class EntityCriteria {

	/**
	 * @var EntityCriterion[]
	 */
	private array $criteria;

	public function __construct( EntityCriterion ...$criteria ) {
		$this->criteria = $criteria;
	}

	public function match( StatementListProvidingEntity $entity ): bool {
		foreach ( $this->criteria as $criterion ) {
			if ( !$criterion->matches( $entity ) ) {
				return false;
			}
		}

		return true;
	}

}
