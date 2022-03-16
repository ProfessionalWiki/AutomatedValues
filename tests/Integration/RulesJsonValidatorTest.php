<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Tests\Integration;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\AutomatedValues\DataAccess\RulesJsonValidator;
use ProfessionalWiki\AutomatedValues\Tests\TestRules;

/**
 * @covers \ProfessionalWiki\AutomatedValues\DataAccess\RulesJsonValidator
 */
class RulesJsonValidatorTest extends TestCase {

	/**
	 * @dataProvider \ProfessionalWiki\AutomatedValues\Tests\TestRules::invalidJsonProvider
	 */
	public function testInvalidJson( string $json ): void {
		$this->assertFalse( RulesJsonValidator::newInstance()->validate( $json ) );
	}

	public function invalidJsonProvider(): iterable {
		yield from TestRules::invalidJsonProvider();
	}

	/**
	 * @dataProvider \ProfessionalWiki\AutomatedValues\Tests\TestRules::validJsonProvider()
	 */
	public function testValidJson( string $json ): void {
		$this->assertTrue( RulesJsonValidator::newInstance()->validate( $json ) );
	}

}
