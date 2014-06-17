<?php
/**
 * @file
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die();
}

// inherit main code from SkinTemplate, set the CSS and template filter
class SkinRefreshed extends SkinTemplate {
	var $skinname = 'refreshed', $stylename = 'refreshed',
		$template = 'RefreshedTemplate', $useHeadElement = true;

	/**
	 * Initializes OutputPage and sets up skin-specific parameters
	 *
	 * @param OutputPage $out
	 */
	public function initPage( OutputPage $out ) {
		global $wgLocalStylePath;
	
		parent::initPage( $out );

		$min = $this->getRequest()->getFuzzyBool( 'debug' ) ? '.src' : '.min';
		// Add CSS @media support for older browsers (such as Internet Explorer
		// 8) that do not support it natively
		// @see https://github.com/Brickimedia/brickimedia/issues/224
		// @todo FIXME: add Respond into the resources directory
		// (skins/Refreshed/refreshed) and load it from there instead of from GitHub
		// Remember to use the global variable $wgLocalStylePath, just like how
		// Vector does!
		$out->addHeadItem( 'css3mediaquerypolyfill',
			'<!--[if lt IE 9]>' .
			Html::element( 'script', array(
				'src' => htmlspecialchars( $wgLocalStylePath ) . "/Refreshed/refreshed/respond{$min}.js",
				'type' => 'text/javascript'
			) ) . '<![endif]-->'
		);
    $out->addHeadItem( 'viewportforios',
      Html::element( 'meta', array(
        'name' => "viewport",
        'content' => "width=device-width, initial-scale=1.0"
      ) )
    ); // preventing iOS from zooming out when the sidebar is opened

		// Add JavaScript via ResourceLoader
		$out->addModules( 'skins.refreshed.js' );
	}

	function setupSkinUserCss( OutputPage $out ) {
		global $wgStylePath;

		parent::setupSkinUserCss( $out );

		// Add CSS via ResourceLoader
		$out->addModuleStyles( 'skins.refreshed' );

		// Internet Explorer fixes
		$out->addStyle( $wgStylePath . '/Refreshed/refreshed/ie8.css', 'screen', 'IE 8' );
	}
}

//$refreshedTOC = '';

class RefreshedTemplate extends BaseTemplate {

	public static function onOutputPageParserOutput( OutputPage &$out, ParserOutput $parseroutput ) {
		//global $refreshedTOC;
		//$refreshedTOC = $parseroutput->mSections;
		$refreshedTOC = $parseroutput->getTOCHTML();

		return true;
	}

	public function execute() {
		global $wgStylePath, $refreshedTOC, $wgRefreshedHeader;

		$user = $this->getSkin()->getUser();

		// new TOC processing
		$tocHTML = $refreshedTOC;
		/*if ( isset( $refreshedTOC ) ) {
			$i = 0;
			foreach ( $refreshedTOC as $tocpart ) {
				$class = "toclevel-{$tocpart['toclevel']}";
				$href = "#{$tocpart['anchor']}";
				$tocHTML .= "<a href='$href' data-to='$href' data-numid='$i' class='$class'>{$tocpart['line']}</a>";
				$i++;
			}
		}*/

		// Title processing
		$titleBase = $this->getSkin()->getTitle();
		$title = $titleBase->getSubjectPage();
		$titleNamespace = $titleBase->getNamespace();
		$titleText = $title->getPrefixedText();
		$titleURL = $title->getLinkURL();

		// Output the <html> tag and whatnot
		$this->html( 'headelement' );

		$refreshedImagePath = "$wgStylePath/Refreshed/refreshed/images";
?>
	<div id="header">
		<div id="headerinner">
			<div id="sidebarshower"></div>
			<div id="siteinfo">
				<?php
					if ( $wgRefreshedHeader['dropdown'] ) { // if there is a site dropdown (so there are multiple wikis)
						echo '<div id="siteinfo-main" class="multiplewikis">';
						echo '<a class="main" href="' . $wgRefreshedHeader['url'] . '">' . $wgRefreshedHeader['img'] . '</a>';
						echo '<a href="javascript:;" class="arrow-link"><img class="arrow" src="' . $refreshedImagePath . '/arrow-highres.png" alt="" width="15" height="8" /></a>';
						echo '</div>';
						echo '<div class="headermenu" style="display:none;">';
						foreach ( $wgRefreshedHeader['dropdown'] as $url => $img ) {
							echo '<a href="' . $url . '">' . $img . '</a>';
						}
						echo '</div>';
					} else {
						echo '<div id="siteinfo-main">';
						echo '<a class="main" href="' . $wgRefreshedHeader['url'] . '">' .  $wgRefreshedHeader['img'] . '</a>';
						echo '</div>';
					}
				?>
			</div>
			<div id="search">
				<form action="<?php $this->text( 'wgScript' ) ?>" method="get">
					<input type="hidden" name="title" value="<?php $this->text( 'searchtitle' ) ?>"/>
					<?php echo $this->makeSearchInput( array( 'id' => 'searchInput' ) ); ?>
				</form>
			</div>
		<?php
			// test if Echo is installed
			if ( class_exists( 'EchoHooks' ) ) {
				echo '<div id="echo"></div>';
			}
		?>
			<div id="searchshower"></div>
			<div id="userinfo">
				<a href="javascript:;">
					<?php
						$avatarImage = '';
						// Show the user's avatar image in the top left drop-down
						// menu, but only if SocialProfile is installed
						if ( class_exists( 'wAvatar' ) ) {
							$avatar = new wAvatar( $user->getId(), 'l' );
							$avatarImage = $avatar->getAvatarURL( array(
								'width' => 30,
								'class' => 'avatar'
							) );
							echo '<img class="arrow" src=' . $refreshedImagePath . '/arrow-highres.png" alt="" width="15" height="8" />' .
							$avatarImage .
							'<span>' . $user->getName() . '</span>';
						} else {
							echo '<img class="avatar avatar-none" src="' . $refreshedImagePath . '/avatar-none.png" alt="" width="30" height="30" height="8" />';
							echo '<img class="arrow" src="' . $refreshedImagePath . '/arrow-highres.png" alt="" width="15" height="8" />' .
							$avatarImage .
							'<span id="username-avatar-none">' . $user->getName() . '</span>';
						}
					?>
				</a>
				<ul class="headermenu" style="display:none;">
					<?php
						foreach ( $this->getPersonalTools() as $key => $tool ) {
							echo $this->makeListItem( $key, $tool );
						}
					?>
				</ul>
			</div>
		</div>
		<?php
		/*	echo "<ul id=\"header-categories\">
				<li>Category 1 <img src=\"$refreshedImagePath/arrow-highres.png\" width=\"14px\" /></li>
				<li>Category 2 <img src=\"$refreshedImagePath/arrow-highres.png\" width=\"14px\" /></li>
				<li>Category 3 <img src=\"$refreshedImagePath/arrow-highres.png\" width=\"14px\" /></li>
			</ul>";
		*/
		?>
		<ul id="header-categories">
			<?php
				$service = new RefreshedSkinNavigationService();
				$menuNodes = $service->parseMessage(
					'refreshed-navigation',
					array( 10, 10, 10, 10, 10, 10 ),
					60 * 60 * 3 // 3 hours
				);
				if ( is_array( $menuNodes ) && isset( $menuNodes[0] ) ) {
					$counter = 0;
					foreach ( $menuNodes[0]['children'] as $level0 ) {
						$hasChildren = isset( $menuNodes[$level0]['children'] );
			?>
			<li class="page_item<?php echo ( $hasChildren ? ' page_item_has_children' : '' ) ?>">
				<div class="clickableregion">
						<!--<a class="nav<?php echo $counter ?>_link" href="<?php echo $menuNodes[$level0]['href'] ?>">-->
						<a class="nav<?php echo $counter ?>_link" href="javascript:;"><?php echo $menuNodes[$level0]['text'] ?></a><img class="arrow" src="<?php echo $refreshedImagePath ?>/arrow-highres.png" width="14px" />
				</div>
							<?php if ( $hasChildren ) { ?>
							<ul class="children">
							<?php
									foreach ( $menuNodes[$level0]['children'] as $level1 ) {
							?>
							<li class="page_item">
								<a href="<?php echo $menuNodes[$level1]['href'] ?>"><?php echo $menuNodes[$level1]['text'] ?></a>
							</li>
			<?php
							}
							echo '</ul>';
							$counter++;
						}
						echo '</li>';
					}
				}
			?>
		</ul>
	</div>
	<div id="fullwrapper">
		<div id="sidebarwrapper">
			<div id="sidebar">
				<ul>
					<?php
						unset( $this->data['sidebar']['SEARCH'] );
						unset( $this->data['sidebar']['TOOLBOX'] );
						unset( $this->data['sidebar']['LANGUAGES'] );

						foreach ( $this->data['sidebar'] as $main => $sub ) {
							echo '<span class="main">' . htmlspecialchars( $main ) . '</span>';
							if ( is_array( $sub ) ) { // MW-generated stuff from the sidebar message
								foreach ( $sub as $key => $action ) {
									echo $this->makeListItem(
										$key,
										$action,
										array(
											'link-class' => 'sub',
											'link-fallback' => 'span'
										)
									);
								}
							} else {
								// allow raw HTML block to be defined by extensions (like NewsBox)
								echo $sub;
							}
						}

						if ( $this->data['language_urls'] ) {
							echo '<span class="main">' . $this->getMsg( 'otherlanguages' )->text() . '</span>';
							echo '<li><ul id="languages" style="display:none;">';
							foreach ( $this->data['language_urls'] as $key => $link ) {
								echo $this->makeListItem( $key, $link, array( 'link-class' => 'sub', 'link-fallback' => 'span' ) );
							}
						} ?>
				</ul>
			</div>
		</div>
		<div id="contentwrapper">
			<?php if ( $this->data['sitenotice'] ) { ?>
				<div id="site-notice">
					<?php $this->html( 'sitenotice' ); ?>
				</div>
			<?php } ?>
			<div id="newtalk"><?php $this->html( 'newtalk' ) ?></div>
			<div id="maintitle">
				<h1 class="scrollshadow"><?php $this->html( 'title' ) ?></h1>

                <div id="standardtoolbox">
					<?php
					$lastLinkOutsideOfStandardToolboxDropdownHasBeenGenerated = false;
					$moreToolsLinkHasBeenGenerated = false;
					$amountOfToolsGenerated = 0;

					$toolbox = $this->getToolbox();

        			if ( sizeof( $this->data['content_actions'] ) > 1 ) { //if there are tools like "edit," etc. in addition to non-page-specific ones like "special pages"
						foreach ( $this->data['content_actions'] as $key => $action ) {
							if ( !$lastLinkOutsideOfStandardToolboxDropdownHasBeenGenerated ) {
								echo $this->makeLink( $key, $action );
								$amountOfToolsGenerated++;
								if ( sizeof( $this->data['content_actions'] ) == $amountOfToolsGenerated || $key == "history" || $key == "addsection" || $key == "protect" || $key == "unprotect" ) { // if this is the last link or it is the history, new section, or protect/unprotect link (whichever comes first)
									$lastLinkOutsideOfStandardToolboxDropdownHasBeenGenerated = true;
									echo "<div id=\"toolboxcontainer\">
								<a href=\"javascript:;\" id=\"toolbox-link\">" . $this->getMsg( 'moredotdotdot' )->text() . "</a>
								<ul id=\"standardtoolboxdropdown\" style=\"display:none;\"><div class=\"dropdowntriangle\"></div>";
								}
							} else if ( !$moreToolsLinkHasBeenGenerated ) {
								echo $this->makeLink( $key, $action, array( 'text-wrapper' => array( 'tag' => 'span' ) ) );
								$moreToolsLinkHasBeenGenerated = true;
							} else {
								echo $this->makeListItem( $key, $action, array( 'text-wrapper' => array( 'tag' => 'span' ) ) );
							}
						}
						foreach ( $toolbox as $tool => $toolData ) {
							echo $this->makeListItem( $tool, $toolData, array( 'text-wrapper' => array( 'tag' => 'span' ) ) );
						}
                    } else {
						foreach ( $this->data['content_actions'] as $key => $action ) {
							if ( !$lastLinkOutsideOfStandardToolboxDropdownHasBeenGenerated ) {
								echo $this->makeLink( $key, $action );
								$lastLinkOutsideOfStandardToolboxDropdownHasBeenGenerated = true;
							}
						}
						echo '<div id="toolboxcontainer">
								<a href="javascript:;" id="toolbox-link">' . $this->getMsg( 'toolbox' )->text() . '</a>
								<ul id="standardtoolboxdropdown" style="display:none;"><div class="dropdowntriangle"></div>';
						foreach ( $toolbox as $tool => $toolData ) {
							echo $this->makeListItem( $tool, $toolData, array( 'text-wrapper' => array( 'tag' => 'span' ) ) );
						}
					}
          wfRunHooks( 'SkinTemplateToolboxEnd', array( &$this, true ) );
					echo '</ul>
					</div>
					</div>'; ?>

                <div id="maintitlemessages">
                    <div id="siteSub"><?php $this->msg( 'tagline' ) ?></div>
                    <div id="contentSub"<?php $this->html( 'userlangattributes' ) ?>><?php $this->html( 'subtitle' ) ?></div>
                    <div id="contentSub2"><?php $this->html( 'undelete' ) ?></div>
              </div>
				<?php
				if ( $titleNamespace % 2 == 1 && $titleNamespace > 0 ) { // if talk namespace: talk namespaces are odd positive integers
					echo Linker::link(
						$title,
						wfMessage( 'refreshed-back', $title->getPrefixedText() )->escaped(),
						array( 'id' => 'back-to-subject' )
					);
				}
				?>
			</div>
			<?php
			reset( $this->data['content_actions'] );
			$pageTab = key( $this->data['content_actions'] );
			$totalTools = count( $pageTab );
			$isEditing = in_array(
				$this->getSkin()->getRequest()->getText( 'action' ),
				array( 'edit', 'submit' )
			);
			$totalSmallToolsToBeRendered = 0;

			//determining how many tools need to be rendered
			foreach ( $this->data['content_actions'] as $action ) {
				if ( in_array( $action['id'], array ( 'ca-talk', 'ca-viewsource', 'ca-edit', 'ca-history', 'ca-delete', 'ca-move', 'ca-protect', 'ca-unprotect', 'ca-watch', 'ca-unwatch' ) ) ) { //if the icon in question is one of the listed ones
					$totalSmallToolsToBeRendered++;
				}
			}
			if ( $titleNamespace % 2 == 1 && $titleNamespace > 0 ) { // if talk namespace: talk namespaces are odd positive integers
				$totalSmallToolsToBeRendered--; // remove a tool (the talk page tool) if the user is on a talk page
			}


			if ( $totalSmallToolsToBeRendered > 0 && !$isEditing ) { // if there's more than zero tools to be rendered and the user isn't editing a page
				echo '<div id="smalltoolboxwrapper">';
				echo '<div id="smalltoolbox">';
				$smallToolboxToolCount = 1;
				$amountOfSmallToolsToSkipInFront = 1;
				$amountOfSmallToolsToSkipInMiddle = 0;

				if ( $titleNamespace % 2 == 1 && $titleNamespace > 0 ) { // if talk namespace: talk namespaces are odd positive integers
					$amountOfSmallToolsToSkipInFront = 2;
				}

					foreach ( $this->data['content_actions'] as $action ) {
						if ( $smallToolboxToolCount > $amountOfSmallToolsToSkipInFront ) {
							// @todo Maybe write a custom makeLink()-like function for generating this code?
							if ( in_array( $action['id'], array ('ca-talk', 'ca-viewsource', 'ca-edit', 'ca-history', 'ca-delete', 'ca-move', 'ca-protect', 'ca-unprotect', 'ca-watch', 'ca-unwatch' ) ) ) { //if the icon being rendered is one of the listed ones
								echo '<a href="' . htmlspecialchars( $action['href'] ) .
								'"><div class="small-icon" id="icon-' . $action['id'] . '"></div></a>';
  							$smallToolboxToolCount++;
							} else {
								$amountOfSmallToolsToSkipInMiddle++;
							}

						} else {
							$smallToolboxToolCount++;
						}
					}

				echo '</div>';
				if ( $totalSmallToolsToBeRendered > 2 ) {
					echo '<a href="javascript:;"><div class="small-icon" id="icon-more"></div></a>';
				}

				echo '</div>';
			} ?>
			<div id="content">
				<?php $this->html( 'bodytext' ); ?>
			</div>
			<!-- some strange stuff going on here... -->
			<?php $this->html( 'catlinks' ); ?>
			<?php if ( $this->data['dataAfterContent'] ) { $this->html( 'dataAfterContent' ); } ?>
			<br clear="all" />
		</div>
	</div>
	<div id="footer">
		<?php
			$footerExtra = '';
			wfRunHooks( 'RefreshedFooter', array( &$footerExtra ) );
			echo $footerExtra;

			foreach ( $this->getFooterLinks() as $category => $links ) {
				$noSkip = false;
				foreach ( $links as $link ) {
					echo '&ensp;';
					$this->html( $link );
					echo '&ensp;';
					$noSkip = true;
				}
				echo '<br />';
			}

			$footerIcons = $this->getFooterIcons( 'icononly' );
			if ( count( $footerIcons ) > 0 ) {
				$noSkip = false;
				foreach ( $footerIcons as $blockName => $footerIcons ) {
					foreach ( $footerIcons as $icon ) {
						echo '&ensp;';
						echo $this->getSkin()->makeFooterIcon( $icon );
						echo '&ensp;';
					}
				}
			}
		?>
	</div>
<?php
		$this->printTrail();
		echo Html::closeElement( 'body' );
		echo Html::closeElement( 'html' );
	}
}


/**
 * A fork of Oasis' NavigationService with some changes.
 * Namely the name was changed and "magic word" handling was removed from
 * parseMessage() and some (related) unused functions were also removed.
 */
class RefreshedSkinNavigationService {

	const version = '0.01';

	/**
	 * Parses a system message by exploding along newlines.
	 *
	 * @param $messageName String: name of the MediaWiki message to parse
	 * @param $maxChildrenAtLevel Array:
	 * @param $duration Integer: cache duration for memcached calls
	 * @param $forContent Boolean: is the message we're supposed to parse in
	 *								the wiki's content language (true) or not?
	 * @return Array
	 */
	public function parseMessage( $messageName, $maxChildrenAtLevel = array(), $duration, $forContent = false ) {
		wfProfileIn( __METHOD__ );

		global $wgLang, $wgContLang, $wgMemc;

		$this->forContent = $forContent;

		$useCache = $wgLang->getCode() == $wgContLang->getCode();

		if ( $useCache || $this->forContent ) {
			$cacheKey = wfMemcKey( $messageName, self::version );
			$nodes = $wgMemc->get( $cacheKey );
		}

		if ( empty( $nodes ) ) {
			if ( $this->forContent ) {
				$lines = explode( "\n", wfMessage( $messageName )->inContentLanguage()->text() );
			} else {
				$lines = explode( "\n", wfMessage( $messageName )->text() );
			}
			$nodes = $this->parseLines( $lines, $maxChildrenAtLevel );

			if ( $useCache || $this->forContent ) {
				$wgMemc->set( $cacheKey, $nodes, $duration );
			}
		}

		wfProfileOut( __METHOD__ );
		return $nodes;
	}

	/**
	 * Function used by parseMessage() above.
	 *
	 * @param $lines String: newline-separated lines from the supplied MW: msg
	 * @param $maxChildrenAtLevel Array:
	 * @return Array
	 */
	private function parseLines( $lines, $maxChildrenAtLevel = array() ) {
		wfProfileIn( __METHOD__ );

		$nodes = array();

		if ( is_array( $lines ) && count( $lines ) > 0 ) {
			$lastDepth = 0;
			$i = 0;
			$lastSkip = null;

			foreach ( $lines as $line ) {
				// we are interested only in lines that are not empty and start with asterisk
				if ( trim( $line ) != '' && $line{0} == '*' ) {
					$depth = strrpos( $line, '*' ) + 1;

					if ( $lastSkip !== null && $depth >= $lastSkip ) {
						continue;
					} else {
						$lastSkip = null;
					}

					if ( $depth == $lastDepth + 1 ) {
						$parentIndex = $i;
					} elseif ( $depth == $lastDepth ) {
						$parentIndex = $nodes[$i]['parentIndex'];
					} else {
						for ( $x = $i; $x >= 0; $x-- ) {
							if ( $x == 0 ) {
								$parentIndex = 0;
								break;
							}
							if ( $nodes[$x]['depth'] <= $depth - 1 ) {
								$parentIndex = $x;
								break;
							}
						}
					}

					if ( isset( $maxChildrenAtLevel[$depth - 1] ) ) {
						if ( isset( $nodes[$parentIndex]['children'] ) ) {
							if ( count( $nodes[$parentIndex]['children'] ) >= $maxChildrenAtLevel[$depth - 1] ) {
								$lastSkip = $depth;
								continue;
							}
						}
					}

					$node = $this->parseOneLine( $line );
					$node['parentIndex'] = $parentIndex;
					$node['depth'] = $depth;

					$nodes[$node['parentIndex']]['children'][] = $i + 1;
					$nodes[$i + 1] = $node;
					$lastDepth = $node['depth'];
					$i++;
				}
			}
		}

		wfProfileOut( __METHOD__ );
		return $nodes;
	}

	/**
	 * @param $line String: line to parse
	 * @return Array
	 */
	private function parseOneLine( $line ) {
		wfProfileIn( __METHOD__ );

		// trim spaces and asterisks from line and then split it to maximum two chunks
		$lineArr = explode( '|', trim( $line, '* ' ), 2 );

		// trim [ and ] from line to have just http://en.wikipedia.org instead of [http://en.wikipedia.org] for external links
		$lineArr[0] = trim( $lineArr[0], '[]' );

		if ( count( $lineArr ) == 2 && $lineArr[1] != '' ) {
			$link = trim( wfMessage( $lineArr[0] )->inContentLanguage()->text() );
			$desc = trim( $lineArr[1] );
		} else {
			$link = $desc = trim( $lineArr[0] );
		}

		$text = $this->forContent ? wfMessage( $desc )->inContentLanguage() : wfMessage( $desc );
		if ( $text->isDisabled() ) {
			$text = $desc;
		}

		if ( wfMessage( $lineArr[0] )->isDisabled() ) {
			$link = $lineArr[0];
		}

		if ( preg_match( '/^(?:' . wfUrlProtocols() . ')/', $link ) ) {
			$href = $link;
		} else {
			if ( empty( $link ) ) {
				$href = '#';
			} elseif ( $link{0} == '#' ) {
				$href = '#';
			} else {
				$title = Title::newFromText( $link );
				if ( is_object( $title ) ) {
					$href = $title->fixSpecialName()->getLocalURL();
				} else {
					$href = '#';
				}
			}
		}

		wfProfileOut( __METHOD__ );
		return array(
			'original' => $lineArr[0],
			'text' => $text,
			'href' => $href
		);
	}

}
