<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues;

use SpecialPage;

class SpecialAutomatedValues extends SpecialPage {

	public function __construct() {
		parent::__construct( 'AutomatedValues' );
	}

	public function execute( $subPage ): void {
		parent::execute( $subPage );

		$title = \Title::newFromText( 'MediaWiki:AutomatedValues' );

		if ( $title instanceof \Title ) {
			$this->getOutput()->redirect( $title->getFullURL() );
		}
	}

	public function getGroupName(): string {
		return 'wikibase';
	}

	public function getDescription(): string {
		return $this->msg( 'special-automated-values' )->escaped();
	}

}
