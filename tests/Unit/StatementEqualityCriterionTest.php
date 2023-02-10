<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Tests\Unit;

use Compat;
use DataValues\StringValue;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\AutomatedValues\Domain\StatementEqualityCriterion;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\StatementListProvidingEntity;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;
use Wikibase\DataModel\Snak\PropertySomeValueSnak;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;

/**
 * @covers \ProfessionalWiki\AutomatedValues\Domain\StatementEqualityCriterion
 */
class StatementEqualityCriterionTest extends TestCase {

	public function testNoStatements_doNotMatch(): void {
		$criterion = new StatementEqualityCriterion( Compat::newPId( 'P1' ), new StringValue( 'foo' ) );
		$input = $this->newEntityWithStatements();

		$this->assertFalse( $criterion->matches( $input ) );
	}

	private function newEntityWithStatements( Statement ...$statements ): StatementListProvidingEntity {
		return new Item( null, null, null, new StatementList( ...$statements ) );
	}

	public function testMatchingStatement_matches(): void {
		$criterion = new StatementEqualityCriterion( Compat::newPId( 'P1' ), new StringValue( 'foo' ) );

		$input = $this->newEntityWithStatements(
			new Statement( new PropertyValueSnak( Compat::newPId( 'P1' ), new StringValue( 'wrong' ) ) ),
			new Statement( new PropertyValueSnak( Compat::newPId( 'P2' ), new StringValue( 'bar' ) ) ),
			new Statement( new PropertyValueSnak( Compat::newPId( 'P1' ), new StringValue( 'foo' ) ) ),
			new Statement( new PropertyValueSnak( Compat::newPId( 'P3' ), new StringValue( 'baz' ) ) ),
		);

		$this->assertTrue( $criterion->matches( $input ) );
	}

	public function testStatementWithWrongValue_doesNotMatch(): void {
		$criterion = new StatementEqualityCriterion( Compat::newPId( 'P1' ), new StringValue( 'foo' ) );

		$input = $this->newEntityWithStatements(
			new Statement( new PropertyValueSnak( Compat::newPId( 'P1' ), new StringValue( 'wrong' ) ) ),
		);

		$this->assertFalse( $criterion->matches( $input ) );
	}

	public function testStatementWithWrongPropertyId_doesNotMatch(): void {
		$criterion = new StatementEqualityCriterion( Compat::newPId( 'P404' ), new StringValue( 'foo' ) );

		$input = $this->newEntityWithStatements(
			new Statement( new PropertyValueSnak( Compat::newPId( 'P1' ), new StringValue( 'foo' ) ) ),
		);

		$this->assertFalse( $criterion->matches( $input ) );
	}

	public function testUsesBestStatements(): void {
		$criterion = new StatementEqualityCriterion( Compat::newPId( 'P1' ), new StringValue( 'foo' ) );

		$a = new Statement( new PropertyValueSnak( Compat::newPId( 'P1' ), new StringValue( 'wrong' ) ) );
		$a->setRank( Statement::RANK_PREFERRED );

		$b = new Statement( new PropertyValueSnak( Compat::newPId( 'P1' ), new StringValue( 'foo' ) ) );
		$b->setRank( Statement::RANK_NORMAL );

		$input = $this->newEntityWithStatements( $a, $b );

		$this->assertFalse( $criterion->matches( $input ) );
	}

	public function testEntityIdValueMatches(): void {
		$criterion = new StatementEqualityCriterion( Compat::newPId( 'P1' ), new StringValue( 'Q42' ) );

		$input = $this->newEntityWithStatements(
			new Statement( new PropertyValueSnak( Compat::newPId( 'P1' ), new EntityIdValue( new ItemId( 'Q42' ) ) ) ),
		);

		$this->assertTrue( $criterion->matches( $input ) );
	}

	public function testNonValueSnakDoesNotMatch(): void {
		$criterion = new StatementEqualityCriterion( Compat::newPId( 'P1' ), new StringValue( 'Q42' ) );

		$input = $this->newEntityWithStatements(
			new Statement( new PropertySomeValueSnak( Compat::newPId( 'P1' ) ) ),
			new Statement( new PropertyNoValueSnak( Compat::newPId( 'P1' ) ) ),
		);

		$this->assertFalse( $criterion->matches( $input ) );
	}

}
