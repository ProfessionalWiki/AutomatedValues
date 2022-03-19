<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\DataAccess;

use ProfessionalWiki\AutomatedValues\Domain\Rules;

class CombiningRulesLookup implements RulesLookup {

	private string $baseRules;
	private RulesDeserializer $deserializer;
	private WikiRulesLookup $wikiRulesLookup;
	private bool $enableWikiRules;

	public function __construct( string $baseRules, RulesDeserializer $deserializer, WikiRulesLookup $wikiRulesLookup, bool $enableWikiRules ) {
		$this->baseRules = $baseRules;
		$this->deserializer = $deserializer;
		$this->wikiRulesLookup = $wikiRulesLookup;
		$this->enableWikiRules = $enableWikiRules;
	}

	public function getRules(): Rules {
		$rules = $this->deserializer->deserialize( '{"rules":' . $this->baseRules . '}' );

		if ( !$this->enableWikiRules ) {
			return $rules;
		}

		return $rules->plus( $this->wikiRulesLookup->getRules() );
	}

}
