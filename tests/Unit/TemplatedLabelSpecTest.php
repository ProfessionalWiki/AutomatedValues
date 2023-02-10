<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Tests\Unit;

use Compat;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\AutomatedValues\Domain\Template;
use ProfessionalWiki\AutomatedValues\Domain\TemplatedLabelSpec;
use ProfessionalWiki\AutomatedValues\Domain\TemplateSegment;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;

/**
 * @covers \ProfessionalWiki\AutomatedValues\Domain\TemplatedLabelSpec
 */
class TemplatedLabelSpecTest extends TestCase {

	public function testEmptyValuesCauseLabelRemoval(): void {
		$spec = new TemplatedLabelSpec(
			[ 'en', 'de' ],
			new Template( new TemplateSegment( '$', Compat::newPId( 'P1' ), null ) )
		);

		$labels = new TermList();
		$labels->setTextForLanguage( 'en', 'foo' );
		$labels->setTextForLanguage( 'nl', 'bar' );
		$labels->setTextForLanguage( 'de', 'baz' );

		$spec->applyTo( $labels, new StatementList() );

		$this->assertEquals(
			new TermList( [ new Term( 'nl', 'bar' ) ] ),
			$labels
		);
	}

}
