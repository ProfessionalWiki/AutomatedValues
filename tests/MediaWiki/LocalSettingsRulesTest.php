<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Tests\MediaWiki;

use DataValues\StringValue;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;

/**
 * @covers \ProfessionalWiki\AutomatedValues\Hooks
 * @covers \ProfessionalWiki\AutomatedValues\AutomatedValuesFactory
 * @covers \ProfessionalWiki\AutomatedValues\Domain\Rules
 * @covers \ProfessionalWiki\AutomatedValues\DataAccess\CombiningRulesLookup
 * @group Database
 */
class LocalSettingsRulesTest extends AutomatedValuesMwTestCase {

	public function testLoadsOnlyLocalSettingsRules(): void {
		$this->setMwGlobals( 'wgAutomatedValuesEnableDefiningRulesInWiki', false );

		$this->createConfigPage(
			'
[
	{
		"buildLabel": {
			"de": {
				"P2": "$"
			}
		}
	}
]
			'
		);

		$this->setMwGlobals(
			'wgAutomatedValuesRules',
			'
[
	{
		"buildLabel": {
			"en": {
				"P3": "$"
			}
		}
	}
]
			'
		);

		$property = new Property( new PropertyId( 'P3' ), null, 'string' );
		$property->getStatements()->addNewStatement( new PropertyValueSnak( new PropertyId( 'P2' ), new StringValue( 'not expected' ) ) );
		$property->getStatements()->addNewStatement( new PropertyValueSnak( new PropertyId( 'P3' ), new StringValue( 'expected' ) ) );

		$this->assertEquals(
			new TermList( [
				new Term( 'en', 'expected' ),
			] ),
			$this->privateSaveAndLoadProperty( $property )->getLabels()
		);
	}

	public function testLoadsBothLocalAndWikiRules(): void {
		$this->setMwGlobals( 'wgAutomatedValuesEnableDefiningRulesInWiki', true );

		$this->createConfigPage(
			'
[
	{
		"buildLabel": {
			"de": {
				"P2": "$"
			}
		}
	}
]
			'
		);

		$this->setMwGlobals(
			'wgAutomatedValuesRules',
			'
[
	{
		"buildLabel": {
			"en": {
				"P3": "$"
			}
		}
	}
]
			'
		);

		$property = new Property( new PropertyId( 'P3' ), null, 'string' );
		$property->getStatements()->addNewStatement( new PropertyValueSnak( new PropertyId( 'P2' ), new StringValue( 'also expected' ) ) );
		$property->getStatements()->addNewStatement( new PropertyValueSnak( new PropertyId( 'P3' ), new StringValue( 'expected' ) ) );

		$this->assertEquals(
			new TermList( [
				new Term( 'en', 'expected' ),
				new Term( 'de', 'also expected' ),
			] ),
			$this->privateSaveAndLoadProperty( $property )->getLabels()
		);
	}

}
