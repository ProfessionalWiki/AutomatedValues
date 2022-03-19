<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues;

use EditPage;
use MediaWiki\Revision\RenderedRevision;
use MediaWiki\Revision\RevisionAccessException;
use ProfessionalWiki\AutomatedValues\DataAccess\RulesJsonValidator;
use Title;
use Wikibase\DataModel\Entity\StatementListProvidingEntity;
use Wikibase\Repo\Content\EntityContent;

class Hooks {

	public static function onMultiContentSave( RenderedRevision $renderedRevision ): void {
		try {
			$content = $renderedRevision->getRevision()->getSlot( 'main' )->getContent();
		}
		catch ( RevisionAccessException $ex ) {
		}

		if ( isset( $content ) && $content instanceof EntityContent ) {
			try {
				$entity = $content->getEntity();
			}
			catch ( \Exception $ex ) {
			}

			if ( isset( $entity ) && $entity instanceof StatementListProvidingEntity ) {
				AutomatedValuesFactory::getInstance()->getRulesLookup()->getRules()->applyTo( $entity );
			}
		}
	}

	public static function onContentHandlerDefaultModelFor( Title $title, ?string &$model ): void {
		if ( AutomatedValuesFactory::getInstance()->isConfigTitle( $title ) ) {
			$model = 'json'; // CONTENT_MODEL_JSON (string to make Psalm happy)
		}
	}

	public static function onEditFilter( EditPage $editPage, string $text, string $section, string &$error ): void {
		if ( AutomatedValuesFactory::getInstance()->isConfigTitle( $editPage->getTitle() )
			&& !RulesJsonValidator::newInstance()->validate( $text ) ) {

			// Would be nice to show a more specific error message, but at the moment RulesJsonValidator does not support this.
			$error = \Html::errorBox( wfMessage( 'automated-values-config-invalid' )->escaped() );
		}
	}

}
