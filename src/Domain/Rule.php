<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Domain;

use Wikibase\DataModel\Entity\StatementListProvidingEntity;
use Wikibase\DataModel\Term\AliasesProvider;
use Wikibase\DataModel\Term\LabelsProvider;

class Rule {

	private EntityCriteria $entityCriteria;
	private LabelSpecList $labelSpecList;
	private AliasesSpecList $aliasesSpecList;

	public function __construct(
		EntityCriteria $entityCriteria,
		LabelSpecList $labelSpecList,
		AliasesSpecList $aliasesSpecList
	) {
		$this->entityCriteria = $entityCriteria;
		$this->labelSpecList = $labelSpecList;
		$this->aliasesSpecList = $aliasesSpecList;
	}

	public function applyTo( StatementListProvidingEntity $entity ): void {
		if ( !$this->entityCriteria->match( $entity ) ) {
			return;
		}

		if ( $entity instanceof LabelsProvider ) {
			$this->labelSpecList->applyTo( $entity->getLabels(), $entity->getStatements()->getBestStatements() );
		}

		if ( $entity instanceof AliasesProvider ) {
			$this->aliasesSpecList->applyTo( $entity->getAliasGroups(), $entity->getStatements()->getBestStatements() );
		}
	}

}
