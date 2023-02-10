<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Tests\Integration;

use Compat;
use DataValues\DataValue;
use DataValues\NumberValue;
use DataValues\StringValue;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\AutomatedValues\Domain\AliasesSpecList;
use ProfessionalWiki\AutomatedValues\Domain\EntityCriteria;
use ProfessionalWiki\AutomatedValues\Domain\LabelSpecList;
use ProfessionalWiki\AutomatedValues\Domain\Rule;
use ProfessionalWiki\AutomatedValues\Domain\Rules;
use ProfessionalWiki\AutomatedValues\Domain\StatementEqualityCriterion;
use ProfessionalWiki\AutomatedValues\Domain\Template;
use ProfessionalWiki\AutomatedValues\Domain\TemplatedAliasesSpec;
use ProfessionalWiki\AutomatedValues\Domain\TemplatedLabelSpec;
use ProfessionalWiki\AutomatedValues\Domain\TemplateSegment;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Term\AliasGroup;
use Wikibase\DataModel\Term\AliasGroupList;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;

/**
 * @covers \ProfessionalWiki\AutomatedValues\Domain\Rules
 * @covers \ProfessionalWiki\AutomatedValues\Domain\Rule
 * @covers \ProfessionalWiki\AutomatedValues\Domain\EntityCriteria
 * @covers \ProfessionalWiki\AutomatedValues\Domain\TemplatedLabelSpec
 * @covers \ProfessionalWiki\AutomatedValues\Domain\TemplatedAliasesSpec
 * @covers \ProfessionalWiki\AutomatedValues\Domain\LabelSpecList
 * @covers \ProfessionalWiki\AutomatedValues\Domain\AliasesSpecList
 */
class RulesTest extends TestCase {

	public function testEmptyRulesCausesNoModification(): void {
		$rules = new Rules();

		$item = new Item( null, null, null, new StatementList() );

		$rules->applyTo( $item );

		$this->assertEquals(
			new Item( null, null, null, new StatementList() ),
			$item
		);
	}

	public function testWhenAllCriteriaMatch_modificationsAreApplied(): void {
		$rules = new Rules(
			new Rule(
				new EntityCriteria(
					new StatementEqualityCriterion( Compat::newPId( 'P1' ), new StringValue( 'expected' ) ),
					new StatementEqualityCriterion( Compat::newPId( 'P2' ), new NumberValue( 42 ) ),
				),
				new LabelSpecList(
					new TemplatedLabelSpec(
						[ 'en', 'de' ],
						new Template( new TemplateSegment( '$', Compat::newPId( 'P1' ), null ) )
					)
				),
				new AliasesSpecList()
			)
		);

		$item = new Item( null, null, null, new StatementList(
			new Statement( new PropertyValueSnak( Compat::newPId( 'P1' ), new StringValue( 'expected' ) ) ),
			new Statement( new PropertyValueSnak( Compat::newPId( 'P2' ), new NumberValue( 42 ) ) ),
		) );

		$item->setLabel( 'en', 'toBeChanged' );
		$item->setLabel( 'nl', 'unchanged' );

		$rules->applyTo( $item );

		$this->assertEquals(
			new TermList( [
				new Term( 'en', 'expected' ),
				new Term( 'nl', 'unchanged' ),
				new Term( 'de', 'expected' )
			] ),
			$item->getLabels()
		);
	}

	public function testWhenOneCriteriaDoesNotMatch_modificationsAreNotMade(): void {
		$rules = new Rules(
			new Rule(
				new EntityCriteria(
					new StatementEqualityCriterion( Compat::newPId( 'P1' ), new StringValue( 'expected' ) ),
					new StatementEqualityCriterion( Compat::newPId( 'P2' ), new NumberValue( 404 ) ),
				),
				new LabelSpecList(
					new TemplatedLabelSpec(
						[ 'en', 'de' ],
						new Template( new TemplateSegment( '$', Compat::newPId( 'P1' ), null ) )
					)
				),
				new AliasesSpecList()
			)
		);

		$item = new Item( null, null, null, new StatementList(
			new Statement( new PropertyValueSnak( Compat::newPId( 'P1' ), new StringValue( 'expected' ) ) ),
			new Statement( new PropertyValueSnak( Compat::newPId( 'P2' ), new NumberValue( 42 ) ) ),
		) );

		$item->setLabel( 'en', 'unchanged1' );
		$item->setLabel( 'nl', 'unchanged2' );

		$rules->applyTo( $item );

		$this->assertEquals(
			new TermList( [
				new Term( 'en', 'unchanged1' ),
				new Term( 'nl', 'unchanged2' ),
			] ),
			$item->getLabels()
		);
	}

	public function testMultipleRulesAreApplied(): void {
		$rules = new Rules(
			new Rule(
				new EntityCriteria(
					new StatementEqualityCriterion( Compat::newPId( 'P3' ), new StringValue( 'matches' ) ),
				),
				new LabelSpecList(
					new TemplatedLabelSpec(
						[ 'en' ],
						new Template( new TemplateSegment( '$', Compat::newPId( 'P1' ), null ) )
					)
				),
				new AliasesSpecList(
					new TemplatedAliasesSpec(
						[ 'de', 'nl' ],
						new Template( new TemplateSegment( '$', Compat::newPId( 'P1' ), null ) )
					)
				)
			),
			new Rule( // Expected to override the label modification from the previous rule
				new EntityCriteria(
					new StatementEqualityCriterion( Compat::newPId( 'P2' ), new NumberValue( 42 ) ),
				),
				new LabelSpecList(
					new TemplatedLabelSpec(
						[ 'en' ],
						new Template( new TemplateSegment( '$', Compat::newPId( 'P1' ), null ) )
					)
				),
				new AliasesSpecList()
			),
			new Rule( // Expected to not override since the criterion does not match
				new EntityCriteria(
					new StatementEqualityCriterion( Compat::newPId( 'P1' ), new StringValue( '404' ) ),
				),
				new LabelSpecList(
					new TemplatedLabelSpec(
						[ 'fr' ],
						new Template( new TemplateSegment( '$', Compat::newPId( 'P1' ), null ) )
					)
				),
				new AliasesSpecList()
			)
		);

		$item = new Item( null, null, null, new StatementList(
			new Statement( new PropertyValueSnak( Compat::newPId( 'P1' ), new StringValue( 'expected' ) ) ),
			new Statement( new PropertyValueSnak( Compat::newPId( 'P2' ), new NumberValue( 42 ) ) ),
			new Statement( new PropertyValueSnak( Compat::newPId( 'P3' ), new StringValue( 'matches' ) ) ),
			new Statement( new PropertyValueSnak( Compat::newPId( 'P1' ), new StringValue( 'expected alias' ) ) ),
		) );

		$item->setLabel( 'en', 'toBeChanged' );
		$item->setLabel( 'fr', 'unchanged' );

		$rules->applyTo( $item );

		$this->assertEquals(
			new TermList( [
				new Term( 'fr', 'unchanged' ),
				new Term( 'en', 'expected' ),
			] ),
			$item->getLabels()
		);

		$this->assertEquals(
			new AliasGroupList( [
				new AliasGroup( 'de', [ 'expected', 'expected alias' ] ),
				new AliasGroup( 'nl', [ 'expected', 'expected alias' ] ),
			] ),
			$item->getAliasGroups()
		);
	}

	public function testMultipleLabelSpecs(): void {
		$rules = new Rules(
			new Rule(
				new EntityCriteria(),
				new LabelSpecList(
					new TemplatedLabelSpec(
						[ 'en', 'de' ],
						new Template( new TemplateSegment( '$', Compat::newPId( 'P1' ), null ) )
					),
					new TemplatedLabelSpec(
						[ 'en', 'nl' ],
						new Template( new TemplateSegment( '$', Compat::newPId( 'P2' ), null ) )
					)
				),
				new AliasesSpecList()
			)
		);

		$item = new Item( null, null, null, new StatementList(
			new Statement( new PropertyValueSnak( Compat::newPId( 'P1' ), new StringValue( '111' ) ) ),
			new Statement( new PropertyValueSnak( Compat::newPId( 'P2' ), new StringValue( '222' ) ) ),
		) );

		$rules->applyTo( $item );

		$this->assertEquals(
			new TermList( [
				new Term( 'en', '222' ), // Overridden by the second LabelSpec
				new Term( 'de', '111' ),
				new Term( 'nl', '222' )
			] ),
			$item->getLabels()
		);
	}

	public function testOnlyBestStatementsAreUsedToBuildValues(): void {
		$rules = new Rules(
			new Rule(
				new EntityCriteria(
				),
				new LabelSpecList(
					new TemplatedLabelSpec(
						[ 'en' ],
						new Template( new TemplateSegment( '$', Compat::newPId( 'P1' ), null ) )
					)
				),
				new AliasesSpecList(
					new TemplatedAliasesSpec(
						[ 'en' ],
						new Template( new TemplateSegment( '$', Compat::newPId( 'P1' ), null ) )
					)
				)
			)
		);

		$item = new Item( null, null, null, new StatementList(
			$this->newStatement( 'P1', new StringValue( 'deprecated 1' ), Statement::RANK_DEPRECATED ),
			$this->newStatement( 'P1', new StringValue( 'normal 1' ), Statement::RANK_NORMAL ),
			$this->newStatement( 'P1', new StringValue( 'deprecated 2' ), Statement::RANK_DEPRECATED ),
			$this->newStatement( 'P1', new StringValue( 'normal 2' ), Statement::RANK_NORMAL ),
			$this->newStatement( 'P2', new StringValue( 'preferred but wrong statement' ), Statement::RANK_PREFERRED ),
		) );

		$rules->applyTo( $item );

		$this->assertEquals(
			new TermList( [
				new Term( 'en', 'normal 1' ),
			] ),
			$item->getLabels()
		);

		$this->assertEquals(
			new AliasGroupList( [
				new AliasGroup( 'en', [ 'normal 1', 'normal 2' ] ),
			] ),
			$item->getAliasGroups()
		);
	}

	private function newStatement( string $pId, DataValue $value, int $rank ): Statement {
		$statement = new Statement( new PropertyValueSnak( Compat::newPId( $pId ), $value ) );
		$statement->setRank( $rank );
		return $statement;
	}

}
