<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Domain;

use DataValues\DataValue;
use DataValues\StringValue;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\Snak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;

class TemplateSegment {

	private string $template;
	public /* readonly */ PropertyId $statementPropertyId;
	private ?PropertyId $qualifierPropertyId;

	/**
	 * @param string $template $ is replaced by the value
	 */
	public function __construct( string $template, PropertyId $statementProperty, ?PropertyId $qualifierProperty ) {
		$this->template = $template;
		$this->statementPropertyId = $statementProperty;
		$this->qualifierPropertyId = $qualifierProperty;
	}

	public function buildString( StatementList $statements ): ?string {
		foreach ( $this->getValuesForSegment( $statements ) as $dataValue ) {
			if ( $dataValue instanceof StringValue ) {
				return $this->replaceDollar( $dataValue->getValue() );
			}
		}

		return null;
	}

	private function replaceDollar( string $parameter ): string {
		return str_replace( '$', $parameter, $this->template );
	}

	/**
	 * @return DataValue[]
	 */
	private function getValuesForSegment( StatementList $statements ): array {
		$values = [];

		foreach ( $statements->getByPropertyId( $this->statementPropertyId )->getBestStatements()->toArray() as $statement ) {
			$values[] = $this->getValueFromSegment( $statement );
		}

		return array_filter( $values, fn( $v ) => $v !== null );
	}

	private function getValueFromSegment( Statement $statement ): ?DataValue {
		if ( $this->qualifierPropertyId === null ) {
			return $this->getMainSnakValue( $statement );
		}

		return $this->getQualifierValue( $statement );
	}

	private function getMainSnakValue( Statement $statement ): ?DataValue {
		$mainSnak = $statement->getMainSnak();

		if ( $mainSnak instanceof PropertyValueSnak ) {
			return $mainSnak->getDataValue();
		}

		return null;
	}

	private function getQualifierValue( Statement $statement ): ?DataValue {
		/**
		 * @var Snak $qualifier
		 */
		foreach ( $statement->getQualifiers() as $qualifier ) {
			if ( $qualifier instanceof PropertyValueSnak ) {
				if ( $qualifier->getPropertyId()->equals( $this->qualifierPropertyId ) ) {
					return $qualifier->getDataValue();
				}
			}
		}

		return null;
	}

}
