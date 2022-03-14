<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Tests\Unit;

use DataValues\NumberValue;
use DataValues\StringValue;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\AutomatedValues\Domain\TemplateSegments;
use ProfessionalWiki\AutomatedValues\Domain\Segment;
use ProfessionalWiki\AutomatedValues\Domain\ValueBuilder;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\SnakList;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;

/**
 * @covers \ProfessionalWiki\AutomatedValues\ValueBuilder
 */
class ValueBuilderTest extends TestCase {

	public function testEmptySpecificationResultsInEmptyString(): void {
		$this->assertSame(
			'',
			( new ValueBuilder() )->buildValue( new TemplateSegments(), new StatementList() )
		);

		$this->assertSame(
			'',
			( new ValueBuilder() )->buildValue( new TemplateSegments(), new StatementList(
				new Statement( new PropertyValueSnak( new PropertyId( 'P1' ), new StringValue( '111' ) ) ),
				new Statement( new PropertyValueSnak( new PropertyId( 'P2' ), new StringValue( '222' ) ) ),
			) )
		);
	}

	public function testMainSnakValueHappyPath(): void {
		$spec = new TemplateSegments(
			new Segment( '$', new PropertyId( 'P2' ), null ),
			new Segment( ', $', new PropertyId( 'P1' ), null ),
		);

		$statements = new StatementList(
			new Statement( new PropertyValueSnak( new PropertyId( 'P1' ), new StringValue( '111' ) ) ),
			new Statement( new PropertyValueSnak( new PropertyId( 'P2' ), new StringValue( '222' ) ) ),
		);

		$this->assertSame(
			'222, 111',
			( new ValueBuilder() )->buildValue( $spec, $statements )
		);
	}

	public function testPropertiesThatAreNotFoundAreOmitted(): void {
		$spec = new TemplateSegments(
			new Segment( '$', new PropertyId( 'P2' ), null ),
			new Segment( ', $', new PropertyId( 'P1' ), null ),
			new Segment( '$', new PropertyId( 'P3' ), null ),
			new Segment( '$', new PropertyId( 'P5' ), null ),
		);

		$statements = new StatementList(
			new Statement( new PropertyValueSnak( new PropertyId( 'P3' ), new StringValue( '333' ) ) ),
			new Statement( new PropertyValueSnak( new PropertyId( 'P4' ), new StringValue( '444' ) ) ),
		);

		$this->assertSame(
			'333',
			( new ValueBuilder() )->buildValue( $spec, $statements )
		);
	}

	public function testNonStringValuesAreOmitted(): void {
		$spec = new TemplateSegments(
			new Segment( 'p1: $ ', new PropertyId( 'P1' ), null ),
			new Segment( 'p2: $ ', new PropertyId( 'P2' ), null ),
			new Segment( 'p3: $ ', new PropertyId( 'P3' ), null ),
			new Segment( 'p4: $ ', new PropertyId( 'P4' ), null ),
		);

		$statements = new StatementList(
			new Statement( new PropertyValueSnak( new PropertyId( 'P1' ), new StringValue( '111' ) ) ),
			new Statement( new PropertyValueSnak( new PropertyId( 'P2' ), new NumberValue( 222 ) ) ),
			new Statement( new PropertyValueSnak( new PropertyId( 'P3' ), new EntityIdValue( new PropertyId( 'P3' ) ) ) ),
			new Statement( new PropertyValueSnak( new PropertyId( 'P4' ), new StringValue( '444' ) ) ),
		);

		$this->assertSame(
			'p1: 111 p4: 444 ',
			( new ValueBuilder() )->buildValue( $spec, $statements )
		);
	}

	public function testSpecWithQualifiersHappyPath(): void {
		$spec = new TemplateSegments(
			new Segment( 'p1.p5: $ ', new PropertyId( 'P1' ), new PropertyId( 'P5' ) ),
			new Segment( 'p1: $ ', new PropertyId( 'P1' ), null ),
			new Segment( 'p1.p6: $ ', new PropertyId( 'P1' ), new PropertyId( 'P6' ) ),
			new Segment( 'p2: $ ', new PropertyId( 'P2' ), null ),
		);

		$statements = new StatementList(
			new Statement(
				new PropertyValueSnak( new PropertyId( 'P1' ), new StringValue( '111' ) ),
				new SnakList( [
					new PropertyValueSnak( new PropertyId( 'P4' ), new StringValue( '444' ) ),
					new PropertyValueSnak( new PropertyId( 'P5' ), new StringValue( '555' ) ),
					new PropertyValueSnak( new PropertyId( 'P6' ), new StringValue( '666' ) ),
				] )
			),
			new Statement( new PropertyValueSnak( new PropertyId( 'P2' ), new StringValue( '222' ) ) ),
		);

		$this->assertSame(
			'p1.p5: 555 p1: 111 p1.p6: 666 p2: 222 ',
			( new ValueBuilder() )->buildValue( $spec, $statements )
		);
	}

	public function testMissingAndNonStringQualifiersAreOmitted(): void {
		$spec = new TemplateSegments(
			new Segment( 'p1.p5: $ ', new PropertyId( 'P1' ), new PropertyId( 'P5' ) ),
			new Segment( 'p1.p6: $ ', new PropertyId( 'P1' ), new PropertyId( 'P6' ) ),
			new Segment( 'p1.p7: $ ', new PropertyId( 'P1' ), new PropertyId( 'P7' ) ),
			new Segment( 'p1.p8: $ ', new PropertyId( 'P1' ), new PropertyId( 'P8' ) ),
		);

		$statements = new StatementList(
			new Statement(
				new PropertyValueSnak( new PropertyId( 'P1' ), new StringValue( '111' ) ),
				new SnakList( [
					new PropertyValueSnak( new PropertyId( 'P4' ), new StringValue( '444' ) ),
					new PropertyValueSnak( new PropertyId( 'P6' ), new StringValue( '666' ) ),
					new PropertyValueSnak( new PropertyId( 'P7' ), new NumberValue( 777 ) ),
					new PropertyValueSnak( new PropertyId( 'P8' ), new StringValue( '888' ) ),
				] )
			),
			new Statement( new PropertyValueSnak( new PropertyId( 'P2' ), new StringValue( '222' ) ) ),
		);

		$this->assertSame(
			'p1.p6: 666 p1.p8: 888 ',
			( new ValueBuilder() )->buildValue( $spec, $statements )
		);
	}

	public function testBuildsMultipleValues(): void {
		$spec = new TemplateSegments(
			new Segment( 'p1.p5: $ ', new PropertyId( 'P1' ), new PropertyId( 'P5' ) ),
			new Segment( 'p1: $ ', new PropertyId( 'P1' ), null ),
			new Segment( 'p1.p7: $ ', new PropertyId( 'P1' ), new PropertyId( 'P7' ) ),
		);

		$statements = new StatementList(
			new Statement(
				new PropertyValueSnak( new PropertyId( 'P1' ), new StringValue( 'First' ) ),
				new SnakList( [
					new PropertyValueSnak( new PropertyId( 'P5' ), new StringValue( '555' ) ),
				] )
			),
			new Statement(
				new PropertyValueSnak( new PropertyId( 'P1' ), new StringValue( 'Second' ) ),
				new SnakList( [
					new PropertyValueSnak( new PropertyId( 'P7' ), new StringValue( '777' ) ),
				] )
			)
		);

		$this->assertSame(
			[
				'p1.p5: 555 p1: First ',
				'p1: Second p1.p7: 777 '
			],
			( new ValueBuilder() )->buildValues( $spec, $statements )
		);
	}

	public function testBuildsSingleValueWhenMultipleStatementPropertiesAreUsed(): void {
		$spec = new TemplateSegments(
			new Segment( 'p1.p5: $ ', new PropertyId( 'P1' ), new PropertyId( 'P5' ) ),
			new Segment( 'p1: $ ', new PropertyId( 'P1' ), null ),
			new Segment( 'p2.p7: $ ', new PropertyId( 'P2' ), new PropertyId( 'P7' ) ),
		);

		$statements = new StatementList(
			new Statement(
				new PropertyValueSnak( new PropertyId( 'P1' ), new StringValue( 'First' ) ),
				new SnakList( [
					new PropertyValueSnak( new PropertyId( 'P5' ), new StringValue( '555' ) ),
				] )
			),
			new Statement(
				new PropertyValueSnak( new PropertyId( 'P2' ), new StringValue( 'Second' ) ),
				new SnakList( [
					new PropertyValueSnak( new PropertyId( 'P7' ), new StringValue( '777' ) ),
				] )
			)
		);

		$this->assertSame(
			[
				'p1.p5: 555 p1: First p2.p7: 777 ',
			],
			( new ValueBuilder() )->buildValues( $spec, $statements )
		);
	}

}
