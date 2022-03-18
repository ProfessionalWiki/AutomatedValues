<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Tests\MediaWiki;

use Serializers\Serializer;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Services\Lookup\LegacyAdapterPropertyLookup;
use Wikibase\DataModel\Services\Lookup\PropertyLookup;
use Wikibase\Repo\WikibaseRepo;

abstract class AutomatedValuesMwTestCase extends \MediaWikiIntegrationTestCase {

	protected function privateSaveAndLoadProperty( Property $property ): Property {
		$this->saveProperty( $property );

		return $this->getPropertyLookup()->getPropertyForId( $property->getId() );
	}

	private function saveProperty( Property $property ) {
		$this->insertPage(
			'Property:' . $property->getId()->serialize(),
			json_encode( $this->getPropertySerializer()->serialize( $property ) )
		);
	}

	private function getPropertyLookup(): PropertyLookup {
		if ( method_exists( WikibaseRepo::class, 'getDefaultInstance' ) ) {
			return WikibaseRepo::getDefaultInstance()->getPropertyLookup();
		}

		return new LegacyAdapterPropertyLookup( WikibaseRepo::getEntityLookup() );
	}

	private function getPropertySerializer(): Serializer {
		if ( method_exists( WikibaseRepo::class, 'getDefaultInstance' ) ) {
			return WikibaseRepo::getDefaultInstance()->getCompactEntitySerializer();
		}

		return WikibaseRepo::getCompactEntitySerializer();
	}

	protected function createConfigPage( string $config ) {
		$this->insertPage(
			'MediaWiki:AutomatedValues',
			$config
		);
	}

}
