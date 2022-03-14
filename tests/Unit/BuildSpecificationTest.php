<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\AutomatedValues\Domain\Template;
use ProfessionalWiki\AutomatedValues\Domain\TemplateSegment;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers \ProfessionalWiki\AutomatedValues\BuildSpecification
 * @covers \ProfessionalWiki\AutomatedValues\Segment
 */
class BuildSpecificationTest extends TestCase {

	public function testSinglePropertySupportsMultipleValues(): void {
		$spec = new Template(
			new TemplateSegment( '', new PropertyId( 'P1' ), null )
		);

		$this->assertTrue( $spec->supportsMultipleValues() );
	}

	public function testMultiPropertyDoesNotSupportMultipleValues(): void {
		$spec = new Template(
			new TemplateSegment( '', new PropertyId( 'P1' ), null ),
			new TemplateSegment( '', new PropertyId( 'P2' ), null ),
		);

		$this->assertFalse( $spec->supportsMultipleValues() );
	}

	public function testPropertyWithQualifiersSupportsMultipleValues(): void {
		$spec = new Template(
			new TemplateSegment( '', new PropertyId( 'P1' ), new PropertyId( 'P5' ) ),
			new TemplateSegment( '', new PropertyId( 'P1' ), null ),
			new TemplateSegment( '', new PropertyId( 'P1' ), new PropertyId( 'P6' ) ),
		);

		$this->assertTrue( $spec->supportsMultipleValues() );
	}

	public function testPropertyWithOtherQualifiersDoesNotSupportMultipleValues(): void {
		$spec = new Template(
			new TemplateSegment( '', new PropertyId( 'P1' ), new PropertyId( 'P5' ) ),
			new TemplateSegment( '', new PropertyId( 'P1' ), null ),
			new TemplateSegment( '', new PropertyId( 'P2' ), new PropertyId( 'P6' ) ),
		);

		$this->assertFalse( $spec->supportsMultipleValues() );
	}

}
