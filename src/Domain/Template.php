<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Domain;

class Template {

	/**
	 * @var TemplateSegment[]
	 */
	public /* readonly */ array $segments;

	public function __construct( TemplateSegment ...$segments ) {
		$this->segments = $segments;
	}

	public function supportsMultipleValues(): bool {
		return count( array_unique(
				array_map(
					fn( TemplateSegment $s ) => $s->statementPropertyId,
					$this->segments
				)
			) ) === 1;
	}

}
