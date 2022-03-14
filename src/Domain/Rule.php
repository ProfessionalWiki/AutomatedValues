<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Domain;

use Wikibase\DataModel\Entity\StatementListProvidingEntity;
use Wikibase\DataModel\Term\AliasesProvider;
use Wikibase\DataModel\Term\LabelsProvider;

class Rule {

	private EntityCriteria $entityCriteria;
	private LabelSpec $labelSpec;
	private AliasesSpec $aliasesSpec;

	public function __construct(
		EntityCriteria $entityCriteria,
		LabelSpec $labelSpec,
		AliasesSpec $aliasesSpec
	) {
		$this->entityCriteria = $entityCriteria;
		$this->labelSpec = $labelSpec;
		$this->aliasesSpec = $aliasesSpec;
	}

	public function applyTo( StatementListProvidingEntity $entity ): void {
		if ( !$this->entityCriteria->match( $entity ) ) {
			return;
		}

		if ( $entity instanceof LabelsProvider ) {
			$this->labelSpec->applyTo( $entity->getLabels(), $entity->getStatements() );
		}

		if ( $entity instanceof AliasesProvider ) {
			$this->aliasesSpec->applyTo( $entity->getAliasGroups(), $entity->getStatements() );
		}
	}

}
