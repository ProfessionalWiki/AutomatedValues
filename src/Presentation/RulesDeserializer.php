<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Presentation;

use DataValues\StringValue;
use ProfessionalWiki\AutomatedValues\Domain\AliasesRule;
use ProfessionalWiki\AutomatedValues\Domain\BuildSpecBasedAliasesRule;
use ProfessionalWiki\AutomatedValues\Domain\BuildSpecBasedLabelRule;
use ProfessionalWiki\AutomatedValues\Domain\BuildSpecification;
use ProfessionalWiki\AutomatedValues\Domain\EntityCriteria;
use ProfessionalWiki\AutomatedValues\Domain\LabelRule;
use ProfessionalWiki\AutomatedValues\Domain\NullAliasesRule;
use ProfessionalWiki\AutomatedValues\Domain\NullLabelRule;
use ProfessionalWiki\AutomatedValues\Domain\Rule;
use ProfessionalWiki\AutomatedValues\Domain\Rules;
use ProfessionalWiki\AutomatedValues\Domain\Segment;
use ProfessionalWiki\AutomatedValues\Domain\StatementEqualityCriterion;
use Wikibase\DataModel\Entity\PropertyId;

class RulesDeserializer {

	private RuleValidator $validator;
	private array $defaultLanguageCodes;

	/**
	 * @param string[] $defaultLanguageCodes
	 */
	public function __construct( RuleValidator $validator, array $defaultLanguageCodes ) {
		$this->validator = $validator;
		$this->defaultLanguageCodes = $defaultLanguageCodes;
	}

	public function deserialize( string $rulesJson ): Rules {
		if ( $this->validator->validate( $rulesJson ) ) {
			$arrayRules = json_decode( $rulesJson, true );

			if ( is_array( $arrayRules ) ) {
				return $this->newRules( $arrayRules );
			}
		}

		// TODO: log warning

		return new Rules();
	}

	private function newRules( array $arrayRules ): Rules {
		$rules = [];

		foreach ( $arrayRules as $arrayRule ) {
			$rules[] = new Rule(
				$this->newEntityCriteria( $arrayRule ),
				$this->newLabelRule( $arrayRule ),
				$this->newAliasesRule( $arrayRule )
			);
		}

		return new Rules( ...$rules );
	}

	private function newEntityCriteria( array $arrayRule ): EntityCriteria {
		return new EntityCriteria(
			...array_map(
				fn( array $criterion ) => new StatementEqualityCriterion( new PropertyId( $criterion['statement'] ), new StringValue( $criterion['equalTo'] ) ),
				$arrayRule['when'] ?? []
			)
		);
	}

	private function newLabelRule( array $arrayRule ): LabelRule {
		if ( !array_key_exists( 'buildLabel', $arrayRule ) ) {
			return new NullLabelRule();
		}

		$labelSpec = $arrayRule['buildLabel'];
		$language = array_keys( $labelSpec )[0];

		return new BuildSpecBasedLabelRule(
			$language === '*' ? $this->defaultLanguageCodes : [ $language ], // TODO
			$this->newBuildSpec( $labelSpec[$language] )
		);
	}

	private function newBuildSpec( array $arraySpec ): BuildSpecification {
		$segments = [];

		foreach ( $arraySpec as $property => $template ) {
			$ids = explode( '.', $property );

			$segments[] = new Segment(
				$template,
				new PropertyId( $ids[0] ),
				array_key_exists( 1, $ids ) ? new PropertyId( $ids[1] ) : null
			);
		}

		return new BuildSpecification( ...$segments );
	}

	private function newAliasesRule( array $arrayRule ): AliasesRule {
		if ( !array_key_exists( 'buildAliases', $arrayRule ) ) {
			return new NullAliasesRule();
		}

		$labelSpec = $arrayRule['buildAliases'];
		$language = array_keys( $labelSpec )[0];

		return new BuildSpecBasedAliasesRule(
			$language === '*' ? $this->defaultLanguageCodes : [ $language ], // TODO
			$this->newBuildSpec( $labelSpec[$language] )
		);
	}

}
