<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Tests\Unit;

use Compat;
use DataValues\NumberValue;
use DataValues\StringValue;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\AutomatedValues\Domain\Template;
use ProfessionalWiki\AutomatedValues\Domain\TemplateSegment;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\SnakList;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;

/**
 * @covers \ProfessionalWiki\AutomatedValues\Domain\Template
 * @covers \ProfessionalWiki\AutomatedValues\Domain\TemplateSegment
 */
class TemplateTest extends TestCase {

	public function testEmptySpecificationResultsInEmptyString(): void {
		$this->assertSame(
			'',
			( new Template() )->buildValue( new StatementList() )
		);

		$this->assertSame(
			'',
			( new Template() )->buildValue( new StatementList(
				new Statement( new PropertyValueSnak( Compat::newPId( 'P1' ), new StringValue( '111' ) ) ),
				new Statement( new PropertyValueSnak( Compat::newPId( 'P2' ), new StringValue( '222' ) ) ),
			) )
		);
	}

	public function testMainSnakValueHappyPath(): void {
		$template = new Template(
			new TemplateSegment( '$', Compat::newPId( 'P2' ), null ),
			new TemplateSegment( ', $', Compat::newPId( 'P1' ), null ),
		);

		$statements = new StatementList(
			new Statement( new PropertyValueSnak( Compat::newPId( 'P1' ), new StringValue( '111' ) ) ),
			new Statement( new PropertyValueSnak( Compat::newPId( 'P2' ), new StringValue( '222' ) ) ),
		);

		$this->assertSame(
			'222, 111',
			$template->buildValue( $statements )
		);
	}

	public function testPropertiesThatAreNotFoundAreOmitted(): void {
		$template = new Template(
			new TemplateSegment( '$', Compat::newPId( 'P2' ), null ),
			new TemplateSegment( ', $', Compat::newPId( 'P1' ), null ),
			new TemplateSegment( '$', Compat::newPId( 'P3' ), null ),
			new TemplateSegment( '$', Compat::newPId( 'P5' ), null ),
		);

		$statements = new StatementList(
			new Statement( new PropertyValueSnak( Compat::newPId( 'P3' ), new StringValue( '333' ) ) ),
			new Statement( new PropertyValueSnak( Compat::newPId( 'P4' ), new StringValue( '444' ) ) ),
		);

		$this->assertSame(
			'333',
			$template->buildValue( $statements )
		);
	}

	public function testNonStringValuesAreOmitted(): void {
		$template = new Template(
			new TemplateSegment( 'p1: $ ', Compat::newPId( 'P1' ), null ),
			new TemplateSegment( 'p2: $ ', Compat::newPId( 'P2' ), null ),
			new TemplateSegment( 'p3: $ ', Compat::newPId( 'P3' ), null ),
			new TemplateSegment( 'p4: $ ', Compat::newPId( 'P4' ), null ),
		);

		$statements = new StatementList(
			new Statement( new PropertyValueSnak( Compat::newPId( 'P1' ), new StringValue( '111' ) ) ),
			new Statement( new PropertyValueSnak( Compat::newPId( 'P2' ), new NumberValue( 222 ) ) ),
			new Statement( new PropertyValueSnak( Compat::newPId( 'P3' ), new EntityIdValue( Compat::newPId( 'P3' ) ) ) ),
			new Statement( new PropertyValueSnak( Compat::newPId( 'P4' ), new StringValue( '444' ) ) ),
		);

		$this->assertSame(
			'p1: 111 p4: 444 ',
			$template->buildValue( $statements )
		);
	}

	public function testSpecWithQualifiersHappyPath(): void {
		$template = new Template(
			new TemplateSegment( 'p1.p5: $ ', Compat::newPId( 'P1' ), Compat::newPId( 'P5' ) ),
			new TemplateSegment( 'p1: $ ', Compat::newPId( 'P1' ), null ),
			new TemplateSegment( 'p1.p6: $ ', Compat::newPId( 'P1' ), Compat::newPId( 'P6' ) ),
			new TemplateSegment( 'p2: $ ', Compat::newPId( 'P2' ), null ),
		);

		$statements = new StatementList(
			new Statement(
				new PropertyValueSnak( Compat::newPId( 'P1' ), new StringValue( '111' ) ),
				new SnakList( [
					new PropertyValueSnak( Compat::newPId( 'P4' ), new StringValue( '444' ) ),
					new PropertyValueSnak( Compat::newPId( 'P5' ), new StringValue( '555' ) ),
					new PropertyValueSnak( Compat::newPId( 'P6' ), new StringValue( '666' ) ),
				] )
			),
			new Statement( new PropertyValueSnak( Compat::newPId( 'P2' ), new StringValue( '222' ) ) ),
		);

		$this->assertSame(
			'p1.p5: 555 p1: 111 p1.p6: 666 p2: 222 ',
			$template->buildValue( $statements )
		);
	}

	public function testMissingAndNonStringQualifiersAreOmitted(): void {
		$template = new Template(
			new TemplateSegment( 'p1.p5: $ ', Compat::newPId( 'P1' ), Compat::newPId( 'P5' ) ),
			new TemplateSegment( 'p1.p6: $ ', Compat::newPId( 'P1' ), Compat::newPId( 'P6' ) ),
			new TemplateSegment( 'p1.p7: $ ', Compat::newPId( 'P1' ), Compat::newPId( 'P7' ) ),
			new TemplateSegment( 'p1.p8: $ ', Compat::newPId( 'P1' ), Compat::newPId( 'P8' ) ),
		);

		$statements = new StatementList(
			new Statement(
				new PropertyValueSnak( Compat::newPId( 'P1' ), new StringValue( '111' ) ),
				new SnakList( [
					new PropertyValueSnak( Compat::newPId( 'P4' ), new StringValue( '444' ) ),
					new PropertyValueSnak( Compat::newPId( 'P6' ), new StringValue( '666' ) ),
					new PropertyValueSnak( Compat::newPId( 'P7' ), new NumberValue( 777 ) ),
					new PropertyValueSnak( Compat::newPId( 'P8' ), new StringValue( '888' ) ),
				] )
			),
			new Statement( new PropertyValueSnak( Compat::newPId( 'P2' ), new StringValue( '222' ) ) ),
		);

		$this->assertSame(
			'p1.p6: 666 p1.p8: 888 ',
			$template->buildValue( $statements )
		);
	}

	public function testBuildsMultipleValues(): void {
		$template = new Template(
			new TemplateSegment( 'p1.p5: $ ', Compat::newPId( 'P1' ), Compat::newPId( 'P5' ) ),
			new TemplateSegment( 'p1: $ ', Compat::newPId( 'P1' ), null ),
			new TemplateSegment( 'p1.p7: $ ', Compat::newPId( 'P1' ), Compat::newPId( 'P7' ) ),
		);

		$statements = new StatementList(
			new Statement(
				new PropertyValueSnak( Compat::newPId( 'P1' ), new StringValue( 'First' ) ),
				new SnakList( [
					new PropertyValueSnak( Compat::newPId( 'P5' ), new StringValue( '555' ) ),
				] )
			),
			new Statement(
				new PropertyValueSnak( Compat::newPId( 'P1' ), new StringValue( 'Second' ) ),
				new SnakList( [
					new PropertyValueSnak( Compat::newPId( 'P7' ), new StringValue( '777' ) ),
				] )
			)
		);

		$this->assertSame(
			[
				'p1.p5: 555 p1: First ',
				'p1: Second p1.p7: 777 '
			],
			$template->buildValues( $statements )
		);
	}

	public function testBuildsSingleValueWhenMultipleStatementPropertiesAreUsed(): void {
		$template = new Template(
			new TemplateSegment( 'p1.p5: $ ', Compat::newPId( 'P1' ), Compat::newPId( 'P5' ) ),
			new TemplateSegment( 'p1: $ ', Compat::newPId( 'P1' ), null ),
			new TemplateSegment( 'p2.p7: $ ', Compat::newPId( 'P2' ), Compat::newPId( 'P7' ) ),
		);

		$statements = new StatementList(
			new Statement(
				new PropertyValueSnak( Compat::newPId( 'P1' ), new StringValue( 'First' ) ),
				new SnakList( [
					new PropertyValueSnak( Compat::newPId( 'P5' ), new StringValue( '555' ) ),
				] )
			),
			new Statement(
				new PropertyValueSnak( Compat::newPId( 'P2' ), new StringValue( 'Second' ) ),
				new SnakList( [
					new PropertyValueSnak( Compat::newPId( 'P7' ), new StringValue( '777' ) ),
				] )
			)
		);

		$this->assertSame(
			[
				'p1.p5: 555 p1: First p2.p7: 777 ',
			],
			$template->buildValues( $statements )
		);
	}

	public function testSinglePropertySupportsMultipleValues(): void {
		$spec = new Template(
			new TemplateSegment( '', Compat::newPId( 'P1' ), null )
		);

		$this->assertTrue( $spec->supportsMultipleValues() );
	}

	public function testMultiPropertyDoesNotSupportMultipleValues(): void {
		$spec = new Template(
			new TemplateSegment( '', Compat::newPId( 'P1' ), null ),
			new TemplateSegment( '', Compat::newPId( 'P2' ), null ),
		);

		$this->assertFalse( $spec->supportsMultipleValues() );
	}

	public function testPropertyWithQualifiersSupportsMultipleValues(): void {
		$spec = new Template(
			new TemplateSegment( '', Compat::newPId( 'P1' ), Compat::newPId( 'P5' ) ),
			new TemplateSegment( '', Compat::newPId( 'P1' ), null ),
			new TemplateSegment( '', Compat::newPId( 'P1' ), Compat::newPId( 'P6' ) ),
		);

		$this->assertTrue( $spec->supportsMultipleValues() );
	}

	public function testPropertyWithOtherQualifiersDoesNotSupportMultipleValues(): void {
		$spec = new Template(
			new TemplateSegment( '', Compat::newPId( 'P1' ), Compat::newPId( 'P5' ) ),
			new TemplateSegment( '', Compat::newPId( 'P1' ), null ),
			new TemplateSegment( '', Compat::newPId( 'P2' ), Compat::newPId( 'P6' ) ),
		);

		$this->assertFalse( $spec->supportsMultipleValues() );
	}

}
