<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Tests\MediaWiki;

use DataValues\StringValue;
use MediaWikiIntegrationTestCase;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers \ProfessionalWiki\AutomatedValues\Hooks
 * @group Database
 */
class ConfigSaveTest extends MediaWikiIntegrationTestCase {

	public function testRulesAreAppliedOnEdit(): void {
		$this->createConfigPage(
			'
[
	{
		"buildLabel": {
			"en": {
				"P2": "$ $"
			}
		}
	}
]
			'
		);

		$property = new Property( new PropertyId( 'P2' ), null, 'string' );
		$property->getStatements()->addNewStatement( new PropertyValueSnak( new PropertyId( 'P2' ), new StringValue( 'main' ) ) );

		$this->assertSame(
			'main main',
			$this->privateSaveAndLoadProperty( $property )->getLabels()->getByLanguage( 'en' )->getText()
		);
	}

	private function createConfigPage( string $config ) {
		$this->insertPage(
			'MediaWiki:AutomatedValues',
			$config
		);
	}

	private function privateSaveAndLoadProperty( Property $property ): Property {
		$this->saveProperty( $property );

		return WikibaseRepo::getDefaultInstance()->getPropertyLookup()->getPropertyForId( $property->getId() );
	}

	private function saveProperty( Property $property ) {
		$this->insertPage(
			'Property:' . $property->getId()->serialize(),
			json_encode( WikibaseRepo::getDefaultInstance()->getCompactEntitySerializer()->serialize( $property ) )
		);
	}

	public function testLanguageDefaults(): void {
		$this->setMwGlobals( 'wgAutomatedValuesDefaultLanguages', [ 'en', 'nl' ] );

		$this->createConfigPage(
			'
[
	{
		"buildLabel": {
			"*": {
				"P2": "$"
			}
		}
	}
]
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
