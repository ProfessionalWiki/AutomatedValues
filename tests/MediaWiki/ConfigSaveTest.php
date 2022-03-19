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
 * @covers \ProfessionalWiki\AutomatedValues\DataAccess\WikiRulesLookup
 * @covers \ProfessionalWiki\AutomatedValues\DataAccess\PageContentFetcher
 * @group Database
 */
class ConfigSaveTest extends AutomatedValuesMwTestCase {

	public function testRulesAreAppliedOnEdit(): void {
		$this->createConfigPage(
			'
{
	"rules": [
		{
			"buildLabel": {
				"en": {
					"P2": "$ $"
				}
			}
		}
	]
}
			'
		);

		$property = new Property( new PropertyId( 'P2' ), null, 'string' );
		$property->getStatements()->addNewStatement( new PropertyValueSnak( new PropertyId( 'P2' ), new StringValue( 'main' ) ) );

		$this->assertSame(
			'main main',
			$this->privateSaveAndLoadProperty( $property )->getLabels()->getByLanguage( 'en' )->getText()
		);
	}

	public function testLanguageDefaults(): void {
		$this->setMwGlobals( 'wgAutomatedValuesDefaultLanguages', [ 'en', 'nl' ] );

		$this->createConfigPage(
			'
{
	"rules": [
		{
			"buildLabel": {
				"*": {
					"P2": "$"
				}
			}
		}
	]
}
			'
		);

		$property = new Property( new PropertyId( 'P2' ), null, 'string' );
		$property->getStatements()->addNewStatement( new PropertyValueSnak( new PropertyId( 'P2' ), new StringValue( 'expected' ) ) );

		$this->assertEquals(
			new TermList( [
				new Term( 'en', 'expected' ),
				new Term( 'nl', 'expected' ),
			] ),
			$this->privateSaveAndLoadProperty( $property )->getLabels()
		);
	}

}
