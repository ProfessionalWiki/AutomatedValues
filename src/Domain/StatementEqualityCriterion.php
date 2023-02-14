<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Domain;

use DataValues\DataValue;
use DataValues\StringValue;
use ProfessionalWiki\AutomatedValues\Compat;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\StatementListProvidingEntity;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\Snak;

class StatementEqualityCriterion implements EntityCriterion {

	private PropertyId $propertyId;
	private DataValue $expectedValue;

	public function __construct( PropertyId $propertyId, DataValue $expectedValue ) {
		$this->propertyId = $propertyId;
		$this->expectedValue = $expectedValue;
	}

	public function matches( StatementListProvidingEntity $entity ): bool {
		foreach ( $entity->getStatements()->getByPropertyId( $this->propertyId )->getBestStatements()->toArray() as $statement ) {
			if ( $this->snakMatches( $statement->getMainSnak() ) ) {
				return true;
			}
		}

		return false;
	}

	private function snakMatches( Snak $snak ): bool {
		if ( $snak instanceof PropertyValueSnak ) {
			return $this->dataValueMatches( $snak->getDataValue() );
		}

		return false;
	}

	private function dataValueMatches( DataValue $dataValue ): bool {
		if ( $dataValue instanceof EntityIdValue && $this->expectedValue instanceof StringValue ) {
			return $dataValue->getEntityId()->getSerialization() === $this->expectedValue->getValue();
		}
		return Compat::dataValueEquals( $dataValue, $this->expectedValue);
	}

}
