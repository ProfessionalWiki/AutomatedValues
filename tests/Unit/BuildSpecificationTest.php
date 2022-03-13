<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\AutomatedValues\Domain\BuildSpecification;
use ProfessionalWiki\AutomatedValues\Domain\Segment;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers \ProfessionalWiki\AutomatedValues\BuildSpecification
 * @covers \ProfessionalWiki\AutomatedValues\Segment
 */
class BuildSpecificationTest extends TestCase {

	public function testSinglePropertySupportsMultipleValues(): void {
		$spec = new BuildSpecification(
			new Segment( '', new PropertyId( 'P1' ), null )
		);

		$this->assertTrue( $spec->supportsMultipleValues() );
	}

	public function testMultiPropertyDoesNotSupportMultipleValues(): void {
		$spec = new BuildSpecification(
			new Segment( '', new PropertyId( 'P1' ), null ),
			new Segment( '', new PropertyId( 'P2' ), null ),
		);

		$this->assertFalse( $spec->supportsMultipleValues() );
	}

	public function testPropertyWithQualifiersSupportsMultipleValues(): void {
		$spec = new BuildSpecification(
			new Segment( '', new PropertyId( 'P1' ), new PropertyId( 'P5' ) ),
			new Segment( '', new PropertyId( 'P1' ), null ),
			new Segment( '', new PropertyId( 'P1' ), new PropertyId( 'P6' ) ),
		);

		$this->assertTrue( $spec->supportsMultipleValues() );
	}

	public function testPropertyWithOtherQualifiersDoesNotSupportMultipleValues(): void {
		$spec = new BuildSpecification(
			new Segment( '', new PropertyId( 'P1' ), new PropertyId( 'P5' ) ),
			new Segment( '', new PropertyId( 'P1' ), null ),
			new Segment( '', new PropertyId( 'P2' ), new PropertyId( 'P6' ) ),
		);

		$this->assertFalse( $spec->supportsMultipleValues() );
	}

}
