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
		parent::initPage( $out );

		// Append CSS which includes IE only behavior fixes for hover support -
		// this is better than including this in a CSS file since it doesn't
		// wait for the CSS file to load before fetching the HTC file.
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
				'src' => "https://raw.github.com/scottjehl/Respond/master/dest/respond{$min}.js",
				'type' => 'text/javascript'
			) ) . '<![endif]-->'
		);

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
		<div id="siteinfo">
			<?php
				if ( $wgRefreshedHeader['dropdown'] ) { // if there is a site dropdown (so there are multiple wikis)
					echo "<div id='siteinfo-main' class='multiplewikis'>";
					echo "<a class='main' href='" . $wgRefreshedHeader['url'] . "'>" .  $wgRefreshedHeader['img'] . "</a>";
					echo "<a href='javascript:;' class='arrow-link'><img class='arrow' src='{$refreshedImagePath}/arrow-highres.png' alt='' width='15' height='8' /></a>";
					echo "</div>";
					echo "<div class='headermenu' style='display:none;'>";
					foreach ( $wgRefreshedHeader['dropdown'] as $url => $img ) {
						echo "<a href='$url'>{$img}</a>";
					}
					echo "</div>";
				} else {
					echo "<div id='siteinfo-main'>";
					echo "<a class='main' href='" . $wgRefreshedHeader['url'] . "'>" .  $wgRefreshedHeader['img'] . "</a>";
					echo "</div>";
				}
			?>
		</div>
        <div id="search">
			<form action="<?php $this->text( 'wgScript' ) ?>" method="get">
				<input type="hidden" name="title" value="<?php $this->text( 'searchtitle' ) ?>"/>
				<?php echo $this->makeSearchInput( array( 'id' => 'searchInput' ) ); ?>
			</form>
		</div>
        <div id="userinfo">
			<a href='javascript:;'>
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
						echo "<img class=\"arrow\" src=\"$refreshedImagePath/arrow-highres.png\" alt=\"\" width=\"15\" height=\"8\" />
						{$avatarImage}
						<span>{$user->getName()}</span>";
					} else {
						echo "<img class=\"avatar avatar-none\" src=\"$refreshedImagePath/avatar-none.png\" alt=\"\" width=\"30\" height=\"30\" height=\"8\" />";
						echo "<img class=\"arrow\" src=\"$refreshedImagePath/arrow-highres.png\" alt=\"\" width=\"15\" height=\"8\" />
						{$avatarImage}
						<span id=\"username-avatar-none\">{$user->getName()}</span>";
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
							echo "<li><ul id='languages' style='display:none;'>";
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
								<img class=\"arrow\" src=\"$refreshedImagePath/arrow-highres.png\" alt=\"\" width=\"11\" height=\"6\" />
								<img class=\"arrow no-show\" src=\"$refreshedImagePath/arrow-highres-hover.png\" alt=\"\" width=\"11\" height=\"6\" />
								<ul id=\"standardtoolboxdropdown\" style=\"display:none;\"><div class=\"dropdowntriangle\"></div>";
								}
							} else if ( !$moreToolsLinkHasBeenGenerated ) {
								echo $this->makeLink( $key, $action, array( 'text-wrapper' => array( 'tag' => 'span' ) ) );
								$moreToolsLinkHasBeenGenerated = true;
							} else {
								echo $this->makeListItem( $key, $action, array( 'text-wrapper' => array( 'tag' => 'span' ) ) );
							}
						}
						foreach( $toolbox as $tool => $toolData ) {
							echo $this->makeListItem( $tool, $toolData, array( 'text-wrapper' => array( 'tag' => 'span' ) ) );
						}
                    } else {
						foreach ( $this->data['content_actions'] as $key => $action ) {
							if ( !$lastLinkOutsideOfStandardToolboxDropdownHasBeenGenerated ) {
								echo $this->makeLink( $key, $action );
								$lastLinkOutsideOfStandardToolboxDropdownHasBeenGenerated = true;
							}
						}
						echo "<div id=\"toolboxcontainer\">
								<a href=\"javascript:;\" id=\"toolbox-link\">" . $this->getMsg( 'toolbox' )->text() . "</a>
								<img class=\"arrow\" src=\"$refreshedImagePath/arrow-highres.png\" alt=\"\" width=\"11\" height=\"6\" />
								<img class=\"arrow no-show\" src=\"$refreshedImagePath/arrow-highres-hover.png\" alt=\"\" width=\"11\" height=\"6\" />
								<ul id=\"standardtoolboxdropdown\" style=\"display:none;\"><div class=\"dropdowntriangle\"></div>";
						foreach( $toolbox as $tool => $toolData ) {
							echo $this->makeListItem( $tool, $toolData, array( 'text-wrapper' => array( 'tag' => 'span' ) ) );
						}
					}
					echo '</ul>
					</div>
					</div>'; ?>
                
                <div id="maintitlemessages">
                    <div id="siteSub"><?php $this->msg('tagline') ?></div>
                    <div id="contentSub"<?php $this->html( 'userlangattributes' ) ?>><?php $this->html( 'subtitle' ) ?></div>
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
			$totalActions = count( $pageTab );
			$isEditing = in_array(
				$this->getSkin()->getRequest()->getText( 'action' ),
				array( 'edit', 'submit' )
			);

			if ( $totalActions > 0 && !$isEditing ) { // if there's more than zero buttons and the user isn't editing a page
				echo '<div id="smalltoolboxwrapper">';
				echo '<div id="smalltoolbox">';
				$smallToolboxActionCount = 1;

				if ( $titleNamespace % 2 == 1 && $titleNamespace > 0 ) { // if talk namespace: talk namespaces are odd positive integers
					foreach ( $this->data['content_actions'] as $action ) {
						if ( $smallToolboxActionCount > 1 ) {
							// @todo Maybe write a custom makeLink()-like function for generating this code?
							echo '<a href="' . htmlspecialchars( $action['href'] ) .
								'"><div class="small-icon" id="icon-' . $action['id'] . '"></div></a>';
							$smallToolboxActionCount++;
						} else {
							$smallToolboxActionCount++;
						}
					}
				} else { // if not talk namespace
					foreach ( $this->data['content_actions'] as $action ) {
						echo '<a href="' . htmlspecialchars( $action['href'] ) .
							'"><div class="small-icon" id="icon-' . $action['id'] . '"></div></a>';
						$smallToolboxActionCount++;
					}
				}

				echo '</div>';
				if ( $smallToolboxActionCount > 2 ) {
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
		<div id="rightbar">
			<div class="shower"></div>
		</div>
	</div>
	<div id="footer">
		<?php
			$footerExtra = '';
			wfRunHooks( 'RefreshedFooter', array( &$footerExtra ) );
			echo $footerExtra;

			foreach ( $this->getFooterLinks() as $category => $links ) {
				$noskip = false;
				foreach ( $links as $link ) {
					echo '&ensp;';
					$this->html( $link );
					echo '&ensp;';
					$noskip = true;
				}
				echo '<br />';
			}

			$footerIcons = $this->getFooterIcons( 'icononly' );
			if ( count( $footerIcons ) > 0 ) {
				$noskip = false;
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
