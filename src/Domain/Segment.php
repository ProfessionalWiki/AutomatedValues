<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Domain;

use Wikibase\DataModel\Entity\PropertyId;

class Segment {

	public /* readonly */ string $template;
	public /* readonly */ PropertyId $statementPropertyId;
	public /* readonly */ ?PropertyId $qualifierPropertyId;

	/**
	 * @param string $template $ is replaced by the value
	 */
	public function __construct( string $template, PropertyId $statementProperty, ?PropertyId $qualifierProperty ) {
		$this->template = $template;
		$this->statementPropertyId = $statementProperty;
		$this->qualifierPropertyId = $qualifierProperty;
	}

}
