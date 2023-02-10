<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Tests\Unit;

use Compat;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\AutomatedValues\Domain\Template;
use ProfessionalWiki\AutomatedValues\Domain\TemplatedAliasesSpec;
use ProfessionalWiki\AutomatedValues\Domain\TemplateSegment;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Term\AliasGroup;
use Wikibase\DataModel\Term\AliasGroupList;

/**
 * @covers \ProfessionalWiki\AutomatedValues\Domain\TemplatedAliasesSpec
 */
class TemplatedAliasesSpecTest extends TestCase {

	public function testEmptyValuesCauseLabelRemoval(): void {
		$spec = new TemplatedAliasesSpec(
			[ 'en', 'de' ],
			new Template( new TemplateSegment( '$', Compat::newPId( 'P1' ), null ) )
		);

		$aliasGroupList = new AliasGroupList( [
			new AliasGroup( 'en', [ 'foo', 'bar' ] ),
			new AliasGroup( 'nl', [ 'baz', 'bah' ] ),
			new AliasGroup( 'de', [ 'pew' ] ),
		] );

		$spec->applyTo( $aliasGroupList, new StatementList() );

		$this->assertEquals(
			new AliasGroupList( [
				new AliasGroup( 'nl', [ 'baz', 'bah' ] ),
			] ),
			$aliasGroupList
		);
	}

}
