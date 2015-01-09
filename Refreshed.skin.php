<?php

// inherit main code from SkinTemplate, set the CSS and template filter
class SkinRefreshed extends SkinTemplate {
	public $skinname = 'refreshed', $stylename = 'refreshed',
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
		// @see https:// github.com/Brickimedia/brickimedia/issues/224
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
				'name' => 'viewport',
				'content' => 'width=device-width, initial-scale=1.0'
			) )
		); // preventing iOS from zooming out when the sidebar is opened

		// Add JavaScript via ResourceLoader
		$out->addModules( 'skins.refreshed.js' );
	}

	function setupSkinUserCss( OutputPage $out ) {
		global $wgStylePath;

		parent::setupSkinUserCss( $out );

		// Add CSS via ResourceLoader
		$out->addModuleStyles( array(
			'mediawiki.skinning.interface',
			'mediawiki.skinning.content.externallinks',
			'skins.refreshed'
		) );

		// Internet Explorer fixes
		$out->addStyle( $wgStylePath . '/Refreshed/refreshed/ie8.css', 'screen', 'IE 8' );
	}
}

class RefreshedTemplate extends BaseTemplate {

	public function execute() {
		global $wgStylePath, $wgRefreshedHeader;

		$user = $this->getSkin()->getUser();

		// Title processing
		$titleBase = $this->getSkin()->getTitle();
		$title = $titleBase->getSubjectPage();
		$titleNamespace = $titleBase->getNamespace();
		$titleText = $title->getPrefixedText();
		$titleURL = $title->getLinkURL();

		$refreshedImagePath = "$wgStylePath/Refreshed/refreshed/images";

		// Output the <html> tag and whatnot
		$this->html( 'headelement' );
		?>
		<a id="fade-overlay"></a>
		<div id="header">
			<div id="header-inner">
				<div id="site-info">
					<?php
					if ( $wgRefreshedHeader['dropdown'] ) { // if there is a site dropdown (so there are multiple wikis)
						?>
						<div id="site-info-main" class="multiple-wikis">
						<a class="main header-button" href="<?php echo $wgRefreshedHeader['url'] ?>"><?php echo $wgRefreshedHeader['img'] ?></a>
						<a href="javascript:;" class="header-button arrow-link"><img class="arrow" src="<?php echo $refreshedImagePath ?>/arrow-highres.png" alt="" width="15" height="8" /></a>
						</div>
						<ul class="header-menu" style="display:none;">
							<?php
							foreach ( $wgRefreshedHeader['dropdown'] as $url => $img ) {
								?>
								<li class="header-dropdown-item">
									<a href="<?php echo $url ?>"><?php echo $img ?></a>
								</li>
								<?php
							}
							?>
						</ul>
					<?php
					} else {
						?>
						<div id="site-info-main">
						<a class="main header-button" href="<?php echo $wgRefreshedHeader['url'] ?>"><?php $wgRefreshedHeader['img'] ?></a>
						</div>
					<?php
					}
					?>
					<div id="site-info-mobile">
						<a class="main header-button" href="<?php echo $wgRefreshedHeader['url'] ?>"><?php echo $wgRefreshedHeader['mobileimg'] ?></a>
					</div>
				</div>
				<div class="search">
					<div id="search-closer" class="header-button"></div>
					<form action="<?php $this->text( 'wgScript' ) ?>" method="get">
						<input type="hidden" name="title" value="<?php $this->text( 'searchtitle' ) ?>"/>
						<?php echo $this->makeSearchInput( array( 'id' => 'searchInput' ) ); ?>
					</form>
				</div>
				<div id="search-shower" class="header-button"></div>

				<?php
				// test if Echo is installed
				if ( class_exists( 'EchoHooks' ) ) {
					?>
					<div id="echo"></div>
					<?php
				}
				?>

				<div id="user-info">
					<a class="header-button" href="javascript:;">
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
							?>
							<img class="arrow" src="<?php $refreshedImagePath ?>/arrow-highres.png" alt="" width="15" height="8" />
							<?php echo $avatarImage ?>
							<span class="username"><?php $user->getName() ?></span>
						<?php
						} else {
							?>
							<img class="avatar avatar-none" src="<?php echo $refreshedImagePath ?>/avatar-none.png" alt="" width="30" height="30" />
							<img class="arrow" src="<?php echo $refreshedImagePath ?>/arrow-highres.png" alt="" width="15" height="8" />
							<?php echo $avatarImage ?>
							<span class="username username-avatar-none"><?php echo $user->getName() ?></span>
						<?php
						}
						?>
					</a>
					<ul class="header-menu" style="display:none;">
						<?php
						// generate user tools (and notifications item in user tools if needed)
						$personalToolsCount = 0;
						foreach ( $this->getPersonalTools() as $key => $tool ) {
							$tool['class'] = 'header-dropdown-item'; // add the "header-dropdown-item" class to each li element
							echo $this->makeListItem( $key, $tool );
							if ( class_exists( 'EchoHooks' ) && $this->data['loggedin'] && $personalToolsCount == 2 ) { // if Echo is installed, user is logged in, and the first two tools have been generated (user and user talk)...
								?>
								<li id="pt-notifications-personaltools" class="header-dropdown-item">
									<?php
									echo Linker::link(
										SpecialPage::getTitleFor( 'Notifications' ),
										wfMessage( 'notifications' )->plain(),
										Linker::tooltipAndAccesskeyAttribs( 'pt-notifications' )
									)
									?>
								</li>
							<?php
							}
							$personalToolsCount++;
						}
						?>
					</ul>
				</div>
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
							<li class="page-item<?php echo ( $hasChildren ? ' page-item-has-children' : '' ) ?> header-button">
								<div class="clickable-region">
									<a class="nav<?php echo $counter ?>-link" href="javascript:;"><?php echo $menuNodes[$level0]['text'] ?></a>
									<img class="arrow" src="<?php echo $refreshedImagePath ?>/arrow-highres.png" width="14" />
								</div>
								<?php
								if ( $hasChildren ) {
									?>
									<ul class="children" style="display:none;">
										<?php
										foreach ( $menuNodes[$level0]['children'] as $level1 ) {
											if ( gettype( $menuNodes[$level1]['href'] ) == "string" ) { // if the link href is a string (and so it's not a link to a wiki page--wiki links are Title objects)
												?>
												<li class="header-dropdown-item">
													<a href="<?php echo $menuNodes[$level1]['href'] ?>"><?php echo $menuNodes[$level1]['text'] ?></a>
												</li>
												<?php
											} else if ( get_class( $menuNodes[$level1]['href'] ) == "Title" ) { // if the link href is a title (and so it's a link to a wiki page)
											?>
												<li class="header-dropdown-item">
													<?php
													echo Linker::link(
														$menuNodes[$level1]['href'],
														$menuNodes[$level1]['text']
													);
													?>
												</li>
												<?php
											} // hopefully it'll only be a string or Title, but don't print it if it's not
										}
										?>
									</ul>
									<?php
									$counter++;
								}
								?>
							</li>
							<?php
						}
					}
					?>
				</ul>
			</div>
		</div>
		<div id="full-wrapper">
			<div id="sidebar-wrapper">
				<div id="sidebar-shower" class="header-button"></div>
				<div id="sidebar-logo">
					<a class="main" href="<?php echo $wgRefreshedHeader['url'] ?>"><?php echo $wgRefreshedHeader['img'] ?></a>
				</div>
				<div id="sidebar">
					<?php
					unset( $this->data['sidebar']['SEARCH'] );
					unset( $this->data['sidebar']['TOOLBOX'] );
					unset( $this->data['sidebar']['LANGUAGES'] );

					foreach ( $this->data['sidebar'] as $main => $sub ) {
						?>
						<div class="main"><?php echo htmlspecialchars( $main ) ?></div>
						<ul>
							<?php
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
							?>
						</ul>
						<?php
					}

					if ( $this->data['language_urls'] ) {
						?>
						<div class="main"><?php $this->getMsg( 'otherlanguages' )->text() ?></div>
							<ul id="languages">
								<?php
								foreach ( $this->data['language_urls'] as $key => $link ) {
									echo $this->makeListItem( $key, $link, array( 'link-class' => 'sub', 'link-fallback' => 'span' ) );
								}
								?>
							</ul>
						</div>
						<?php
					}
					?>
				</div>
			</div>
			<div id="content" class="mw-body">
				<?php
				if ( $this->data['sitenotice'] ) {
					?>
					<div id="site-notice">
						<?php $this->html( 'sitenotice' ) ?>
					</div>
				<?php
				}
				?>
				<div id="new-talk">
					<?php $this->html( 'newtalk' ) ?>
				</div>
				<div id="firstHeading">
					<h1 class="scroll-shadow"><?php $this->html( 'title' ) ?></h1>
					<?php
					if ( method_exists( $this, 'getIndicators' ) ) {
						echo $this->getIndicators();
					}
					?>
					<div class="standard-toolbox static-toolbox">
						<?php
						$lastLinkOutsideOfStandardToolboxDropdownHasBeenGenerated = false;
						$amountOfToolsGenerated = 0;

						$toolbox = $this->getToolbox();

						// if there are actions like "edit," etc.
						// (not counting generic toolbox tools like "upload file")
						// in addition to non-page-specific ones like "page" (so a "more..." link is needed)
						if ( sizeof( $this->data['content_actions'] ) > 1 ) {
							foreach ( $this->data['content_actions'] as $key => $action ) {
								if ( !$lastLinkOutsideOfStandardToolboxDropdownHasBeenGenerated ) { // this runs until all the actions outside the dropdown have been generated (generates actions outside dropdown)
									echo $this->makeLink( $key, $action );
									$amountOfToolsGenerated++;
									if (
										sizeof( $this->data['content_actions'] ) == $amountOfToolsGenerated ||
										$key == 'history' || $key == 'addsection' ||
										$key == 'protect' || $key == 'unprotect'
									)
									{
										// if this is the last action or it is the
										// history, new section, or protect/unprotect action
										// (whichever comes first)
										$lastLinkOutsideOfStandardToolboxDropdownHasBeenGenerated = true;
										?>
										<div class="toolbox-container">
											<a href="javascript:;" id="toolbox-link"><?php echo $this->getMsg( 'moredotdotdot' )->text() ?></a>
											<div class="standard-toolbox-dropdown" style="display:none;">
												<div class="dropdown-triangle"></div>
												<ul>
									<?php
									}
								} else { // generates actions inside dropdown
									echo $this->makeListItem( $key, $action, array( 'text-wrapper' => array( 'tag' => 'span' ) ) );
								}
							}
							foreach ( $toolbox as $tool => $toolData ) { // generates toolbox tools inside dropdown (e.g. "upload file")
								echo $this->makeListItem( $tool, $toolData, array( 'text-wrapper' => array( 'tag' => 'span' ) ) );
							}
						} else { // if there aren't actions like edit, etc. (so a "tools" link is needed instead of a "more..." link)
							foreach ( $this->data['content_actions'] as $key => $action ) { // generates first link (i.e. "page" button on the mainspace, "special page" on Special namespace, etc.); the foreach loop should once run once since there should only be one link
								echo $this->makeLink( $key, $action );
							}
						?>
							<div class="toolbox-container">
								<a href="javascript:;" id="toolbox-link"><?php echo $this->getMsg( 'toolbox' )->text() ?></a>
								<div class="standard-toolbox-dropdown" style="display:none;">
									<div class="dropdown-triangle"></div>
									<ul>
										<?php
										foreach ( $toolbox as $tool => $toolData ) { // generates toolbox tools inside dropdown (e.g. "upload file")
											echo $this->makeListItem( $tool, $toolData, array( 'text-wrapper' => array( 'tag' => 'span' ) ) );
										}
						}
						wfRunHooks( 'SkinTemplateToolboxEnd', array( &$this, true ) );
						?>
									</ul>
							</div>
						</div>
					</div>
					<div id="main-title-messages">
						<div id="siteSub"><?php $this->msg( 'tagline' ) ?></div>
						<div id="contentSub"<?php $this->html( 'userlangattributes' ) ?>><?php $this->html( 'subtitle' ) ?></div>
						<div id="contentSub2"><?php $this->html( 'undelete' ) ?></div>
					</div>
					<?php
					if ( MWNamespace::isTalk( $titleNamespace ) ) { // if talk namespace
						echo Linker::link(
							$title,
							wfMessage( 'backlinksubtitle', $title->getPrefixedText() )->escaped(),
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

				// determining how many tools need to be generated
				$totalSmallToolsToGenerate = 0;
				$listOfToolsToGenerate = array(
					'ca-talk',
					'ca-viewsource',
					'ca-edit',
					'ca-history',
					'ca-delete',
					'ca-move',
					'ca-protect',
					'ca-unprotect',
					'ca-watch',
					'ca-unwatch'
				);

				foreach ( $this->data['content_actions'] as $action ) {
					if ( in_array( $action['id'], $listOfToolsToGenerate ) ) { // if the icon in question is one of the listed ones
						$totalSmallToolsToGenerate++;
					}
				}
				if ( MWNamespace::isTalk( $titleNamespace ) ) { // if talk namespace
					$totalSmallToolsToGenerate--; // remove a tool (the talk page tool) if the user is on a talk page
				}

				if ( $totalSmallToolsToGenerate > 0 && !$isEditing ) { // if there's more than zero tools to be generated and the user isn't editing a page
					?>
					<div id="small-toolbox-wrapper">
						<div id="small-toolbox">
							<?php
							$smallToolboxToolCount = 1;
							$amountOfSmallToolsToSkipInFront = 1; // skip the "page" (or equivalent) link

							if ( MWNamespace::isTalk( $titleNamespace ) ) { // if talk namespace
								$amountOfSmallToolsToSkipInFront = 2; // skip the "page" (or equivalent) and "talk" links
							}

							foreach ( $this->data['content_actions'] as $action ) {
								if ( $smallToolboxToolCount > $amountOfSmallToolsToSkipInFront ) { // if we're not supposed to skip this tool (e.g. if we're supposed to skip the first 2 tools and we're at the 3rd tool, then the boolean is true)
									// @todo Maybe write a custom makeLink()-like function for generating this code?
									if ( in_array( $action['id'], $listOfToolsToGenerate ) ) { // if the icon being rendered is one of the listed ones (if we're supposed to generate this tool)
										?>
										<a href="<?php echo htmlspecialchars( $action['href'] ) ?>" class="small-icon" id="icon-<?php echo $action['id'] ?>"></a>
										<?php
									}
									$smallToolboxToolCount++; // increment the count regardless of whether or not the tool was generated
								} else { // if we're supposed to skip this tool
									$smallToolboxToolCount++; // increment the count regardless of whether or not the tool was generated
								}
							}
							?>
						</div>
						<?php
						if ( $totalSmallToolsToGenerate > 2 ) {
							?>
							<a href="javascript:;" class="small-icon" id="icon-more"></a>
							<?php
						}
						?>
					</div>
					<?php
				}
				?>
				<div id="bodyContent">
					<?php $this->html( 'bodytext' ) ?>
				</div>
				<?php
				$this->html( 'catlinks' );
				if ( $this->data['dataAfterContent'] ) {
					$this->html( 'dataAfterContent' );
				}
				?>
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
					?>
					&ensp;
					<?php
					$this->html( $link );
					?>
					&ensp;
					<?php
					$noSkip = true;
				}
				?>
				<br />
				<?php
			}

			$footerIcons = $this->getFooterIcons( 'icononly' );
			if ( count( $footerIcons ) > 0 ) {
				$noSkip = false;
				foreach ( $footerIcons as $blockName => $footerIcons ) {
					foreach ( $footerIcons as $icon ) {
						?>
						&ensp;
						<?php
						echo $this->getSkin()->makeFooterIcon( $icon );
						?>
						&ensp;
						<?php
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

		// trim [ and ] from line to have just http:// en.wikipedia.org instead of [http:// en.wikipedia.org] for external links
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
					$href = $title->fixSpecialName();
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
