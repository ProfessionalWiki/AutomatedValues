<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues;

use DataValues\DataValue;
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Entity\PropertyId;

class Compat {
	public static function newPId( string $id ): PropertyId {
		if ( class_exists( NumericPropertyId::class ) ) {
			return new NumericPropertyId( $id );
		}
		return new PropertyId( $id );
	}
	/**
	 * @param DataValue $a
	 * @param mixed $b
	 *
	 * @return bool
	 */
	public static function dataValueEquals( $a, $b ): bool {
		if ( $a === $b ) {
			return true;
		}
		return $b instanceof DataValue && $a->serialize() === $b->serialize();
	}
}
