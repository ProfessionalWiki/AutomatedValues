<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Domain;

use DataValues\DataValue;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\StatementListProvidingEntity;
use Wikibase\DataModel\Snak\PropertyValueSnak;

class StatementEqualityCriterion implements EntityCriterion {

	private PropertyId $propertyId;
	private DataValue $expectedValue;

	public function __construct( PropertyId $propertyId, DataValue $expectedValue ) {
		$this->propertyId = $propertyId;
		$this->expectedValue = $expectedValue;
	}

	public function matches( StatementListProvidingEntity $entity ): bool {
		// TODO: also support detecting matching EntityIDs

		$expectedSnak = new PropertyValueSnak( $this->propertyId, $this->expectedValue );

		foreach ( $entity->getStatements()->getBestStatements()->toArray() as $statement ) {
			if ( $statement->getMainSnak()->equals( $expectedSnak ) ) {
				return true;
			}
		}

		return false;
	}

}
