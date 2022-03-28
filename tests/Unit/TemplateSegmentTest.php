<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Tests\Unit;

use DataValues\DataValue;
use DataValues\StringValue;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\AutomatedValues\Domain\TemplateSegment;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;

/**
 * @covers \ProfessionalWiki\AutomatedValues\Domain\TemplateSegment
 */
class TemplateSegmentTest extends TestCase {

	public function testUsesFirstOfTheBestStatements(): void {
		$segment = new TemplateSegment( '$', new PropertyId( 'P1' ), null );

		$this->assertSame(
			'First preferred',
			$segment->buildString(
				new StatementList(
					$this->newStatement( 'P2', new StringValue( 'Wrong property' ), Statement::RANK_PREFERRED ),
					$this->newStatement( 'P1', new StringValue( 'Deprecated' ), Statement::RANK_DEPRECATED ),
					$this->newStatement( 'P1', new StringValue( 'First preferred' ), Statement::RANK_PREFERRED ),
					$this->newStatement( 'P1', new StringValue( 'Normal' ), Statement::RANK_NORMAL ),
					$this->newStatement( 'P1', new StringValue( 'Second preferred' ), Statement::RANK_PREFERRED ),
				)
			)
		);
	}

	private function newStatement( string $pId, DataValue $value, int $rank ): Statement {
		$statement = new Statement( new PropertyValueSnak( new PropertyId( $pId ), $value ) );
		$statement->setRank( $rank );
		return $statement;
	}

}
