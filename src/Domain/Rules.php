<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Domain;

use Wikibase\DataModel\Entity\StatementListProvidingEntity;

class Rules {

	/**
	 * @var Rule[]
	 */
	private array $rules;

	public function __construct( Rule ...$rules ) {
		$this->rules = $rules;
	}

	public function applyTo( StatementListProvidingEntity $entity ): void {
		foreach ( $this->rules as $rule ) {
			$rule->applyTo( $entity );
		}
	}

}
