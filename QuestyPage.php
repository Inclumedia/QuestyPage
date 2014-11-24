<?php
/**
 * InterwikiMap MediaWiki extension.
 *
 * This extension retrieves an interwiki map from a remote wiki and updates the local interwiki
 * map accordingly.
 *
 * Written by Leucosticte
 * https://www.mediawiki.org/wiki/User:Leucosticte
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Extensions
 */
if( !defined( 'MEDIAWIKI' ) ) {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	die( 1 );
}

$wgExtensionCredits['antispam'][] = array(
	'path' => __FILE__,
	'name' => 'QuestyPage',
	'author' => '[https://mediawiki.org/User:Leucosticte Leucosticte]',
	'url' => 'https://mediawiki.org/wiki/Extension:QuestyPage',
	'description' => 'Allows QuestyCaptcha to get its data from a wiki page',
	'version' => '1.0.1'
);

$wgHooks['QuestyCaptchaGetCaptcha'][] = 'questyCaptchaGetCaptcha';
$wgQuestyPage = 'Captcha:Questions';

function questyCaptchaGetCaptcha( &$question ) {
    global $wgQuestyPage;
    $attributes = array(
	'0' => 'question',
	'1' => 'answer'
    );
    $title = Title::newFromText( $wgQuestyPage );
    $articleId = $title->getArticleID();
    if ( $title->exists() ) {
	$article = Article::newFromID( $articleId );
	$page = $article->getPage();
	$content = $page->getRevision()->getContent( Revision::RAW );
	$contents = ContentHandler::getContentText( $content );
	if ( $contents ) {
	    $contentsArr = explode ( "\n", $contents );
	    foreach ( $contentsArr as $line ) {
		// Ignore lines that don't start with | or have only |
		if ( substr ( $line, 0, 1 ) == '|' && trim ( $line ) !=
		    '|' ) {
		    // Chop off that |
		    $line = substr ( $line, 1, strlen( $line ) - 1);
		    // Prefix divided from url by ||
		    $lineArr = explode ( '||', $line );
		    $value = array();
		    // Sometimes certain fields aren't specified. If
		    // it's a blank URL, leave it unset. Later, we'll
		    // see if the URL can be found elsewhere.
		    foreach ( $attributes as $key => $attribute ) {
			$value[$attribute] =
			    trim ( $lineArr[$key] );
		    }
		    $questions[] = $value;
		}
	    }
	    // pick a question, any question
	    $question = $questions[mt_rand( 0, count( $questions ) - 1 )];
	}
    }
    return true;
}