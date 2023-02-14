<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues;

use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Entity\PropertyId;

class Compat {
	public static function newPId( string $id ): PropertyId {
		// if ( class_exists( NumericPropertyId::class ) ) {
			return new NumericPropertyId( $id );
		// }
		// return new PropertyId( $id );
	}
}
