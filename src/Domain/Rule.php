<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Domain;

use Wikibase\DataModel\Entity\StatementListProvidingEntity;
use Wikibase\DataModel\Term\AliasesProvider;
use Wikibase\DataModel\Term\LabelsProvider;

class Rule {

	private EntityCriteria $entityCriteria;
	private LabelRule $labelRule;
	private AliasesRule $aliasesRule;

	public function __construct(
		EntityCriteria $entityCriteria,
		LabelRule $labelRule,
		AliasesRule $aliasesRule
	) {
		$this->entityCriteria = $entityCriteria;
		$this->labelRule = $labelRule;
		$this->aliasesRule = $aliasesRule;
	}

	public function applyTo( StatementListProvidingEntity $entity ): void {
		if ( !$this->entityCriteria->match( $entity ) ) {
			return;
		}

		if ( $entity instanceof LabelsProvider ) {
			$this->labelRule->applyTo( $entity->getLabels(), $entity->getStatements() );
		}

		if ( $entity instanceof AliasesProvider ) {
			$this->aliasesRule->applyTo( $entity->getAliasGroups(), $entity->getStatements() );
		}
	}

}
