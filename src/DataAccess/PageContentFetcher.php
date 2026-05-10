<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\DataAccess;

use MediaWiki\Content\Content;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Title\MalformedTitleException;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleParser;

class PageContentFetcher {

	private TitleParser $titleParser;
	private RevisionLookup $revisionLookup;

	public function __construct( TitleParser $titleParser, RevisionLookup $revisionLookup ) {
		$this->titleParser = $titleParser;
		$this->revisionLookup = $revisionLookup;
	}

	public function getPageContent( string $pageTitle ): ?Content {
		try {
			$linkTarget = $this->titleParser->parseTitle( $pageTitle );
		} catch ( MalformedTitleException $e ) {
			return null;
		}

		$revision = $this->revisionLookup->getRevisionByTitle( Title::newFromLinkTarget( $linkTarget ) );

		if ( $revision === null ) {
			return null;
		}

		// $revision->getRevisionRecord()->getContent( 'main' );
		return $revision->getContent( 'main' );
	}

}
