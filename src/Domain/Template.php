<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Domain;

use Wikibase\DataModel\Statement\StatementList;

class Template {

	/**
	 * @var TemplateSegment[]
	 */
	public /* readonly */ array $segments;

	public function __construct( TemplateSegment ...$segments ) {
		$this->segments = $segments;
	}

	public function buildValue( StatementList $statements ): string {
		$buildValue = '';

		foreach ( $this->segments as $segment ) {
			$buildValue .= $segment->segmentToStrings( $statements )[0] ?? '';
		}

		return $buildValue;
	}

	/**
	 * @return string[]
	 */
	public function buildValues( StatementList $statements ): array {
		if ( !$this->supportsMultipleValues() ) {
			return [ $this->buildValue( $statements ) ];
		}

		return $this->buildMultipleValues( $statements );
	}

	public function supportsMultipleValues(): bool {
		return count( array_unique(
				array_map(
					fn( TemplateSegment $s ) => $s->statementPropertyId,
					$this->segments
				)
			) ) === 1;
	}

	/**
	 * @return string[]
	 */
	private function buildMultipleValues( StatementList $statements ): array {
		$values = [];

		foreach ( $statements->toArray() as $statement ) {
			$values[] = $this->buildValue( new StatementList( $statement ) );
		}

		return array_filter( $values, fn( string $s ) => $s !== '' );
	}

}
