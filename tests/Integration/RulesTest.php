<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Tests\Integration;

use DataValues\NumberValue;
use DataValues\StringValue;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\AutomatedValues\Domain\BuildSpecBasedAliasesRule;
use ProfessionalWiki\AutomatedValues\Domain\BuildSpecBasedLabelRule;
use ProfessionalWiki\AutomatedValues\Domain\BuildSpecification;
use ProfessionalWiki\AutomatedValues\Domain\EntityCriteria;
use ProfessionalWiki\AutomatedValues\Domain\NullAliasesRule;
use ProfessionalWiki\AutomatedValues\Domain\Rule;
use ProfessionalWiki\AutomatedValues\Domain\Rules;
use ProfessionalWiki\AutomatedValues\Domain\Segment;
use ProfessionalWiki\AutomatedValues\Domain\StatementEqualityCriterion;
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
 * @covers \ProfessionalWiki\AutomatedValues\Rules
 * @covers \ProfessionalWiki\AutomatedValues\Rule
 * @covers \ProfessionalWiki\AutomatedValues\EntityCriteria
 * @covers \ProfessionalWiki\AutomatedValues\BuildSpecBasedLabelRule
 * @covers \ProfessionalWiki\AutomatedValues\BuildSpecBasedAliasesRule
 * @covers \ProfessionalWiki\AutomatedValues\NullAliasesRule
 * @covers \ProfessionalWiki\AutomatedValues\NullLabelRule
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
					new StatementEqualityCriterion( new PropertyId( 'P1' ), new StringValue( 'expected' ) ),
					new StatementEqualityCriterion( new PropertyId( 'P2' ), new NumberValue( 42 ) ),
				),
				new BuildSpecBasedLabelRule(
					[ 'en', 'de' ],
					new BuildSpecification( new Segment( '$', new PropertyId( 'P1' ), null ) )
				),
				new NullAliasesRule()
			)
		);

		$item = new Item( null, null, null, new StatementList(
			new Statement( new PropertyValueSnak( new PropertyId( 'P1' ), new StringValue( 'expected' ) ) ),
			new Statement( new PropertyValueSnak( new PropertyId( 'P2' ), new NumberValue( 42 ) ) ),
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
					new StatementEqualityCriterion( new PropertyId( 'P1' ), new StringValue( 'expected' ) ),
					new StatementEqualityCriterion( new PropertyId( 'P2' ), new NumberValue( 404 ) ),
				),
				new BuildSpecBasedLabelRule(
					[ 'en', 'de' ],
					new BuildSpecification( new Segment( '$', new PropertyId( 'P1' ), null ) )
				),
				new NullAliasesRule()
			)
		);

		$item = new Item( null, null, null, new StatementList(
			new Statement( new PropertyValueSnak( new PropertyId( 'P1' ), new StringValue( 'expected' ) ) ),
			new Statement( new PropertyValueSnak( new PropertyId( 'P2' ), new NumberValue( 42 ) ) ),
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
					new StatementEqualityCriterion( new PropertyId( 'P3' ), new StringValue( 'matches' ) ),
				),
				new BuildSpecBasedLabelRule(
					[ 'en' ],
					new BuildSpecification( new Segment( '$', new PropertyId( 'P1' ), null ) )
				),
				new BuildSpecBasedAliasesRule(
					[ 'de', 'nl' ],
					new BuildSpecification( new Segment( '$', new PropertyId( 'P1' ), null ) )
				)
			),
			new Rule( // Expected to override the label modification from the previous rule
				new EntityCriteria(
					new StatementEqualityCriterion( new PropertyId( 'P2' ), new NumberValue( 42 ) ),
				),
				new BuildSpecBasedLabelRule(
					[ 'en' ],
					new BuildSpecification( new Segment( '$', new PropertyId( 'P1' ), null ) )
				),
				new NullAliasesRule()
			),
			new Rule( // Expected to not override since the criterion does not match
				new EntityCriteria(
					new StatementEqualityCriterion( new PropertyId( 'P1' ), new StringValue( '404' ) ),
				),
				new BuildSpecBasedLabelRule(
					[ 'fr' ],
					new BuildSpecification( new Segment( '$', new PropertyId( 'P1' ), null ) )
				),
				new NullAliasesRule()
			)
		);

		$item = new Item( null, null, null, new StatementList(
			new Statement( new PropertyValueSnak( new PropertyId( 'P1' ), new StringValue( 'expected' ) ) ),
			new Statement( new PropertyValueSnak( new PropertyId( 'P2' ), new NumberValue( 42 ) ) ),
			new Statement( new PropertyValueSnak( new PropertyId( 'P3' ), new StringValue( 'matches' ) ) ),
			new Statement( new PropertyValueSnak( new PropertyId( 'P1' ), new StringValue( 'expected alias' ) ) ),
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

}
