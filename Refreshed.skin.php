<?php

// inherit main code from SkinTemplate, set the CSS and template filter
class SkinRefreshed extends SkinTemplate {
	public $skinname = 'refreshed',
		$stylename = 'refreshed',
		$template = 'RefreshedTemplate',
		$useHeadElement = true,
		$headerNav = array();

	/**
	 * Initializes OutputPage and sets up skin-specific parameters
	 *
	 * @param OutputPage $out
	 */
	public function initPage( OutputPage $out ) {
		global $wgLocalStylePath;

		parent::initPage( $out );

		$out->addMeta( 'viewport', 'width=device-width' );

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
		// add jQuery Mobile touch events
		$out->addHeadItem( 'jquerymobiletouchevents',
			Html::element( 'script', array(
				'src' => htmlspecialchars( $wgLocalStylePath ) . "/Refreshed/refreshed/jquery.mobile.custom{$min}.js",
				'type' => 'text/javascript'
			) )
		);
		// prevent iOS from zooming out when the sidebar is opened
		$out->addHeadItem( 'viewportforios',
			Html::element( 'meta', array(
				'name' => 'viewport',
				'content' => 'width=device-width, initial-scale=1.0'
			) )
		);

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
		$out->addStyle( $wgStylePath . '/Refreshed/refreshed/iefontfix.css', 'screen', 'IE' );
		$out->addStyle( $wgStylePath . '/Refreshed/refreshed/wikifont/WikiFontLoader.css', 'screen and (-ms-high-contrast: active), (-ms-high-contrast: none)' ); /* IE 10+ */
	}
}

class RefreshedTemplate extends BaseTemplate {

	public function execute() {
		global $wgStylePath, $wgRefreshedHeader, $wgMemc;

		$skin = $this->getSkin();
		$user = $skin->getUser();

		// Title processing
		$titleBase = $skin->getTitle();
		$title = $titleBase->getSubjectPage();
		$titleNamespace = $titleBase->getNamespace();

		$refreshedImagePath = "$wgStylePath/Refreshed/refreshed/images";

		$key = wfMemcKey( 'refreshed', 'header' );
		$headerNav = $wgMemc->get( $key );
		if ( !$headerNav ) {
			$headerNav = array();
			$skin->addToSidebar( $headerNav, 'refreshed-navigation' );
			$wgMemc->set( $key, $headerNav , 60 * 60 * 24 ); // 24 hours
		}

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
							<a class="main header-button" href="<?php echo $wgRefreshedHeader['url'] ?>"><?php echo $wgRefreshedHeader['img'] ?></a><a class="header-button fade-trigger site-info-arrow"><span class="arrow wikiglyph wikiglyph-caret-down"></span></a>
							<ul class="header-menu fadable faded">
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
						</div>
						<?php
					} else {
						?>
						<div id="site-info-main">
							<a class="main header-button" href="<?php echo $wgRefreshedHeader['url'] ?>"><?php echo $wgRefreshedHeader['img'] ?></a>
						</div>
					<?php
					}
					?>
					<!--<div id="site-info-mobile"> COMMENTED OUT, DON'T WRITE CODE IF IT DOESN'T WORK
						<a class="main header-button" href="<?php //echo $wgRefreshedHeader['url'] ?>"><?php //echo $wgRefreshedHeader['mobileimg'] ?></a>
					</div>-->
				</div>
				<div class="search">
					<a id="search-shower" class="header-button fade-trigger fadable">
						<span class="wikiglyph wikiglyph-magnifying-glass"></span>
					</a>
					<a id="search-closer" class="header-button fade-trigger fadable faded">
						<span class="wikiglyph wikiglyph-x"></span>
					</a>
					<form class="search-form fadable faded" action="<?php $this->text( 'wgScript' ) ?>" method="get">
						<input type="hidden" name="title" value="<?php $this->text( 'searchtitle' ) ?>"/>
						<?php echo $this->makeSearchInput( array( 'id' => 'searchInput' ) ); ?>
					</form>
				</div>

				<?php
				// test if Echo is installed
				if ( class_exists( 'EchoHooks' ) ) {
					?>
					<div id="echo"></div>
					<?php
				}
				?>

				<div id="user-info">
					<a class="header-button fade-trigger">
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
							<span class="arrow wikiglyph wikiglyph-caret-down"></span>
							<?php echo $avatarImage ?>
							<span class="username"><?php echo $user->getName() ?></span>
						<?php
						} elseif ( $this->data['loggedin'] ) { // if no SocialProfile but user is logged in
							?>
							<span class="arrow wikiglyph wikiglyph-caret-down"></span>
							<span class="avatar-no-socialprofile wikiglyph wikiglyph-user-smile"></span>
							<span class="username username-nosocialprofile-registered"><?php echo $user->getName() ?></span>
						<?php
						} else { // if no SocialProfile but user is not logged in
							?>
							<span class="arrow wikiglyph wikiglyph-caret-down"></span>
							<span class="avatar-no-socialprofile wikiglyph wikiglyph-user-sleep"></span>
							<span class="username username-nosocialprofile-anon"><?php echo $this->getMsg( 'login' )->text() ?></span>
							<?php
						}
						?>
					</a>
					<ul class="header-menu fadable faded">
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
										$this->getMsg( 'notifications' )->plain(),
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
				<div id="header-categories">
					<?php
					if ( $headerNav ) {
						foreach ( $headerNav as $main => $sub ) {
							?>
							<div class="page-item<?php echo ( $sub ? ' page-item-has-children' : '' ) ?>">
								<a class="header-button fade-trigger">
									<span class="header-category-name"><?php echo htmlspecialchars( $main ) ?></span>
									<span class="arrow wikiglyph wikiglyph-caret-down"></span>
								</a>
								<ul class="header-category-menu fadable faded">
									<?php
										foreach ( $sub as $key => $item ) {
											$item['class'] = 'header-dropdown-item';
											echo $this->makeListItem(
												$key,
												$item
											);
										}
									?>
								</ul>
							</div>
						<?php
						}
					}
					?>
				</div>
			</div>
		</div>
		<div id="full-wrapper">
			<div id="sidebar-wrapper">
				<a class="sidebar-shower header-button"></a>
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
					<div id="main-title-messages">
						<div id="siteSub"><?php $this->msg( 'tagline' ) ?></div>
						<?php
						if ( $this->data['subtitle'] || $this->data['undelete'] ) {
							?>
							<div id="contentSub"<?php $this->html( 'userlangattributes' ) ?>><?php $this->html( 'subtitle' ) ?><?php $this->html( 'undelete' ) ?></div>
						<?php
						}
						?>
					</div>
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
											<a class="toolbox-link fade-trigger"><?php echo $this->getMsg( 'moredotdotdot' )->text() ?></a>
											<div class="standard-toolbox-dropdown fadable faded">
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
								<a class="toolbox-link fade-trigger"><?php echo $this->getMsg( 'toolbox' )->text() ?></a>
								<div class="standard-toolbox-dropdown fadable faded">
									<div class="dropdown-triangle"></div>
									<ul>
										<?php
										foreach ( $toolbox as $tool => $toolData ) { // generates toolbox tools inside dropdown (e.g. "upload file")
											echo $this->makeListItem( $tool, $toolData, array( 'text-wrapper' => array( 'tag' => 'span' ) ) );
										}
						}
						Hooks::run( 'SkinTemplateToolboxEnd', array( &$this, true ) );
						?>
									</ul>
							</div>
						</div>
					</div>
					<?php
					if ( MWNamespace::isTalk( $titleNamespace ) ) { // if talk namespace
						echo Linker::link(
							$title,
							$this->getMsg( 'backlinksubtitle', $title->getPrefixedText() )->escaped(),
							array( 'id' => 'back-to-subject' )
						);
					}
					?>
				</div>
				<?php
				reset( $this->data['content_actions'] );
				$pageTab = key( $this->data['content_actions'] );
				$isEditing = in_array(
					$skin->getRequest()->getText( 'action' ),
					array( 'edit', 'submit' )
				);

				// determining how many tools need to be generated
				$totalSmallToolsToGenerate = 0;
				$listOfToolsToGenerate = array(
					'wikiglyph wikiglyph-speech-bubbles' => 'ca-talk',
					'wikiglyph wikiglyph-pencil-lock-full' => 'ca-viewsource',
					'wikiglyph wikiglyph-pencil' => 'ca-edit',
					'wikiglyph wikiglyph-clock' => 'ca-history',
					'wikiglyph wikiglyph-trash' => 'ca-delete',
					'wikiglyph wikiglyph-move' => 'ca-move',
					'wikiglyph wikiglyph-lock' => 'ca-protect',
					'wikiglyph wikiglyph-unlock' => 'ca-unprotect',
					'wikiglyph wikiglyph-star' => 'ca-watch',
					'wikiglyph wikiglyph-unstar' => 'ca-unwatch'
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
						<div class="small-toolbox">
							<?php
							$smallToolBeingTested = 1;
							$amountOfSmallToolsToSkipInFront = 1; // skip the "page" (or equivalent) link
							$amountOfSmallToolsGenerated = 0;

							if ( MWNamespace::isTalk( $titleNamespace ) ) { // if talk namespace
								$amountOfSmallToolsToSkipInFront = 2; // skip the "page" (or equivalent) and "talk" links
							}
							foreach ( $this->data['content_actions'] as $action ) {
								if ( $smallToolBeingTested > $amountOfSmallToolsToSkipInFront ) { // if we're not supposed to skip this tool (e.g. if we're supposed to skip the first 2 tools and we're at the 3rd tool, then the boolean is true)
									// @todo Maybe write a custom makeLink()-like function for generating this code?
									if ( in_array( $action['id'], $listOfToolsToGenerate ) ) { // if the icon being rendered is one of the listed ones (if we're supposed to generate this tool)
										?><a href="<?php echo htmlspecialchars( $action['href'] ) ?>" title="<?php echo $action['text'] ?>" class="small-tool"><span class="<?php echo array_search( $action['id'], $listOfToolsToGenerate ) // key (wikiglyph) from $listOfToolsToGenerate ?>"></span></a><?php
										$amountOfSmallToolsGenerated++; // if a tool is indeed generated, increment this variable
									}
								}
								$smallToolBeingTested++; // increment this variable (amount of tools that have been tested) regardless of whether or not the tool was generated
							}
							?>
						</div><?php if ( $totalSmallToolsToGenerate > 3 ) { ?><div id="small-tool-more"><a title="<?php echo $this->getMsg( 'moredotdotdot' )->text() ?>" class="small-tool"><span class="wikiglyph wikiglyph-ellipsis"></span></a></div><?php } ?>
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
			Hooks::run( 'RefreshedFooter', array( &$footerExtra ) );
			echo $footerExtra;

			foreach ( $this->getFooterLinks() as $category => $links ) {
				foreach ( $links as $link ) {
					?>
					&ensp;
					<?php $this->html( $link ); ?>
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
				foreach ( $footerIcons as $blockName => $footerIcons ) {
					foreach ( $footerIcons as $icon ) {
						?>
						&ensp;
						<?php echo $skin->makeFooterIcon( $icon ); ?>
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
