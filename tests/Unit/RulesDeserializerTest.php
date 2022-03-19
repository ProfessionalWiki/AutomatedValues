<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Tests\Unit;

use DataValues\StringValue;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\AutomatedValues\DataAccess\RulesDeserializer;
use ProfessionalWiki\AutomatedValues\DataAccess\RulesJsonValidator;
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
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers \ProfessionalWiki\AutomatedValues\DataAccess\RulesDeserializer
 */
class RulesDeserializerTest extends TestCase {

	private const DEFAULT_LANGUAGE_CODES = [ 'nl', 'fr', 'de' ];

	public function testInvalidRulesResultInEmptyList(): void {
		$this->assertEquals(
			new Rules(),
			$this->newRulesDeserializer()->deserialize( '' )
		);
	}

	private function newRulesDeserializer(): RulesDeserializer {
		return new RulesDeserializer(
			RulesJsonValidator::newInstance(),
			self::DEFAULT_LANGUAGE_CODES
		);
	}

	public function testExampleRules(): void {
		$rules = $this->newRulesDeserializer()->deserialize( file_get_contents( __DIR__ . '/../../example.json' ) );

		$expectedRules = new Rules(
			new Rule(
				new EntityCriteria(
					new StatementEqualityCriterion(
						new PropertyId( 'P1' ),
						new StringValue( 'Q1' )
					)
				),
				new LabelSpecList(
					new TemplatedLabelSpec(
						[ 'en' ],
						new Template(
							new TemplateSegment( '$', new PropertyId( 'P2' ), null ),
						)
					)
				),
				new AliasesSpecList()
			),

			new Rule(
				new EntityCriteria(
					new StatementEqualityCriterion(
						new PropertyId( 'P1' ),
						new StringValue( 'Q2' )
					)
				),
				new LabelSpecList(
					new TemplatedLabelSpec(
						self::DEFAULT_LANGUAGE_CODES,
						new Template(
							new TemplateSegment( '$ ', new PropertyId( 'P5' ), null ),
							new TemplateSegment( '$', new PropertyId( 'P6' ), null ),
						)
					)
				),
				new AliasesSpecList(
					new TemplatedAliasesSpec(
						[ 'en' ],
						new Template(
							new TemplateSegment( 'The Great ', new PropertyId( 'P7' ), new PropertyId( 'P8' ) ),
							new TemplateSegment( '$', new PropertyId( 'P7' ), null ),
							new TemplateSegment( ' $', new PropertyId( 'P7' ), new PropertyId( 'P9' ) ),
						)
					),
					new TemplatedAliasesSpec(
						[ 'de' ],
						new Template(
							new TemplateSegment( 'Der große ', new PropertyId( 'P7' ), new PropertyId( 'P8' ) ),
							new TemplateSegment( '$', new PropertyId( 'P7' ), null ),
							new TemplateSegment( ' $', new PropertyId( 'P7' ), new PropertyId( 'P9' ) ),
						)
					)
				)
			),

			new Rule(
				new EntityCriteria(
				),
				new LabelSpecList(
					new TemplatedLabelSpec(
						[ 'en' ],
						new Template(
							new TemplateSegment( '$', new PropertyId( 'P42' ), null ),
						)
					)
				),
				new AliasesSpecList()
			),
		);

		$this->assertEquals(
			$expectedRules,
			$rules
		);
	}

}
