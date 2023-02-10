<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\DataAccess;

use Compat;
use DataValues\StringValue;
use ProfessionalWiki\AutomatedValues\Domain\AliasesSpecList;
use ProfessionalWiki\AutomatedValues\Domain\EntityCriteria;
use ProfessionalWiki\AutomatedValues\Domain\LabelSpecList;
use ProfessionalWiki\AutomatedValues\Domain\Rule;
use ProfessionalWiki\AutomatedValues\Domain\Rules;
use ProfessionalWiki\AutomatedValues\Domain\StatementEqualityCriterion;
use ProfessionalWiki\AutomatedValues\Domain\Template;
use ProfessionalWiki\AutomatedValues\Domain\TemplatedAliasesSpec;
use ProfessionalWiki\AutomatedValues\Domain\TemplatedLabelSpec;
use ProfessionalWiki\AutomatedValues\Domain\TemplateSegment;

class RulesDeserializer {

	private RulesJsonValidator $validator;
	private array $defaultLanguageCodes;

	/**
	 * @param string[] $defaultLanguageCodes
	 */
	public function __construct( RulesJsonValidator $validator, array $defaultLanguageCodes ) {
		$this->validator = $validator;
		$this->defaultLanguageCodes = $defaultLanguageCodes;
	}

	public function deserialize( string $rulesJson ): Rules {
		if ( $this->validator->validate( $rulesJson ) ) {
			$array = json_decode( $rulesJson, true );

			if ( is_array( $array ) && array_key_exists( 'rules', $array ) ) {
				return $this->newRules( $array['rules'] );
			}
		}

		// TODO: log warning or throw and log higher up

		return new Rules();
	}

	private function newRules( array $arrayRules ): Rules {
		$rules = [];

		foreach ( $arrayRules as $arrayRule ) {
			$rules[] = new Rule(
				$this->newEntityCriteria( $arrayRule ),
				$this->newLabelSpecList( $arrayRule ),
				$this->newAliasesSpecList( $arrayRule )
			);
		}

		return new Rules( ...$rules );
	}

	private function newEntityCriteria( array $arrayRule ): EntityCriteria {
		return new EntityCriteria(
			...array_map(
				fn( array $criterion ) => new StatementEqualityCriterion( Compat::newPId( $criterion['statement'] ), new StringValue( $criterion['equalTo'] ) ),
				$arrayRule['when'] ?? []
			)
		);
	}

	private function newLabelSpecList( array $arrayRule ): LabelSpecList {
		$specs = [];

		foreach ( $arrayRule['buildLabel'] ?? [] as $language => $arrayTemplate ) {
			$specs[] = new TemplatedLabelSpec(
				$language === '*' ? $this->defaultLanguageCodes : [ $language ],
				$this->newTemplate( $arrayTemplate )
			);
		}

		return new LabelSpecList( ...$specs );
	}

	private function newTemplate( array $arraySpec ): Template {
		$segments = [];

		foreach ( $arraySpec as $property => $template ) {
			$ids = explode( '.', $property );

			$segments[] = new TemplateSegment(
				$template,
				Compat::newPId( $ids[0] ),
				array_key_exists( 1, $ids ) ? Compat::newPId( $ids[1] ) : null
			);
		}

		return new Template( ...$segments );
	}

	private function newAliasesSpecList( array $arrayRule ): AliasesSpecList {
		$specs = [];

		foreach ( $arrayRule['buildAliases'] ?? [] as $language => $arrayTemplate ) {
			$specs[] = new TemplatedAliasesSpec(
				$language === '*' ? $this->defaultLanguageCodes : [ $language ],
				$this->newTemplate( $arrayTemplate )
			);
		}

		return new AliasesSpecList( ...$specs );
	}

}
