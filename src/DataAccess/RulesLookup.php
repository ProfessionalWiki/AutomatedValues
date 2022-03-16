<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\DataAccess;

use ProfessionalWiki\AutomatedValues\Domain\Rules;

class RulesLookup {

	private const CONFIG_PAGE_TITLE = 'MediaWiki:AutomatedValues';
//	private const CONFIG_PAGE_NS = 8; // NS_MEDIAWIKI

	private PageContentFetcher $contentFetcher;
	private RulesDeserializer $deserializer;

	public function __construct( PageContentFetcher $contentFetcher, RulesDeserializer $deserializer ) {
		$this->contentFetcher = $contentFetcher;
		$this->deserializer = $deserializer;
	}

	public function getRules(): Rules {
		$content = $this->contentFetcher->getPageContent( self::CONFIG_PAGE_TITLE );

		if ( $content instanceof \JsonContent ) {
			return $this->rulesFromJsonContent( $content );
		}

		return new Rules();
	}

	private function rulesFromJsonContent( \JsonContent $content ): Rules {
		return $this->deserializer->deserialize( $content->getText() );
	}

}
