<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Presentation;

use DataValues\StringValue;
use ProfessionalWiki\AutomatedValues\Domain\AliasesSpec;
use ProfessionalWiki\AutomatedValues\Domain\TemplatedAliasesSpec;
use ProfessionalWiki\AutomatedValues\Domain\TemplatedLabelSpec;
use ProfessionalWiki\AutomatedValues\Domain\Template;
use ProfessionalWiki\AutomatedValues\Domain\EntityCriteria;
use ProfessionalWiki\AutomatedValues\Domain\LabelSpec;
use ProfessionalWiki\AutomatedValues\Domain\NullAliasesSpec;
use ProfessionalWiki\AutomatedValues\Domain\NullLabelSpec;
use ProfessionalWiki\AutomatedValues\Domain\Rule;
use ProfessionalWiki\AutomatedValues\Domain\Rules;
use ProfessionalWiki\AutomatedValues\Domain\TemplateSegment;
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

	private function newLabelRule( array $arrayRule ): LabelSpec {
		if ( !array_key_exists( 'buildLabel', $arrayRule ) ) {
			return new NullLabelSpec();
		}

		$labelSpec = $arrayRule['buildLabel'];
		$language = array_keys( $labelSpec )[0];

		return new TemplatedLabelSpec(
			$language === '*' ? $this->defaultLanguageCodes : [ $language ], // TODO
			$this->newTemplateSegments( $labelSpec[$language] )
		);
	}

	private function newTemplateSegments( array $arraySpec ): Template {
		$segments = [];

		foreach ( $arraySpec as $property => $template ) {
			$ids = explode( '.', $property );

			$segments[] = new TemplateSegment(
				$template,
				new PropertyId( $ids[0] ),
				array_key_exists( 1, $ids ) ? new PropertyId( $ids[1] ) : null
			);
		}

		return new Template( ...$segments );
	}

	private function newAliasesRule( array $arrayRule ): AliasesSpec {
		if ( !array_key_exists( 'buildAliases', $arrayRule ) ) {
			return new NullAliasesSpec();
		}

		$labelSpec = $arrayRule['buildAliases'];
		$language = array_keys( $labelSpec )[0];

		return new TemplatedAliasesSpec(
			$language === '*' ? $this->defaultLanguageCodes : [ $language ], // TODO
			$this->newTemplateSegments( $labelSpec[$language] )
		);
	}

}
