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

class ValueBuilder {

	public function buildValue( Template $specification, StatementList $statements ): string {
		$buildValue = '';

		foreach ( $specification->segments as $segment ) {
			$buildValue .= $this->segmentToStrings( $segment, $statements )[0] ?? '';
		}

		return $buildValue;
	}

	/**
	 * @return string[]
	 */
	public function buildValues( Template $specification, StatementList $statements ): array {
		if ( !$specification->supportsMultipleValues() ) {
			return [ $this->buildValue( $specification, $statements ) ];
		}

		return $this->buildMultipleValues( $specification, $statements );
	}

	/**
	 * @return string[]
	 */
	private function buildMultipleValues( Template $specification, StatementList $statements ): array {
		$values = [];

		foreach ( $statements->toArray() as $statement ) {
			$values[] = $this->buildValue( $specification, new StatementList( $statement ) );
		}

		return array_filter( $values, fn( string $s ) => $s !== '' );
	}

	/**
	 * @return string[]
	 */
	private function segmentToStrings( TemplateSegment $segment, StatementList $statements ): array {
		$strings = [];

		foreach ( $this->getValuesForSegment( $segment, $statements ) as $dataValue ) {
			if ( $dataValue instanceof StringValue ) {
				$strings[] = $this->buildTemplate( $segment->template, $dataValue->getValue() );
			}
		}

		return $strings;
	}

	private function buildTemplate( string $template, string $parameter ): string {
		return str_replace( '$', $parameter, $template );
	}

	/**
	 * @return DataValue[]
	 */
	private function getValuesForSegment( TemplateSegment $segment, StatementList $statements ): array {
		$values = [];

		foreach ( $statements->getByPropertyId( $segment->statementPropertyId )->toArray() as $statement ) {
			$values[] = $this->getValueFromSegment( $segment, $statement );
		}

		return array_filter( $values, fn( $v ) => $v !== null );
	}

	private function getValueFromSegment( TemplateSegment $segment, Statement $statement ): ?DataValue {
		if ( $segment->qualifierPropertyId === null ) {
			return $this->getMainSnakValue( $statement );
		}

		return $this->getQualifierValue( $statement, $segment->qualifierPropertyId );
	}

	private function getMainSnakValue( Statement $statement ): ?DataValue {
		$mainSnak = $statement->getMainSnak();

		if ( $mainSnak instanceof PropertyValueSnak ) {
			return $mainSnak->getDataValue();
		}

		return null;
	}

	private function getQualifierValue( Statement $statement, PropertyId $propertyId ): ?DataValue {
		/**
		 * @var Snak $qualifier
		 */
		foreach ( $statement->getQualifiers() as $qualifier ) {
			if ( $qualifier instanceof PropertyValueSnak ) {
				if ( $qualifier->getPropertyId()->equals( $propertyId ) ) {
					return $qualifier->getDataValue();
				}
			}
		}

		return null;
	}

}
