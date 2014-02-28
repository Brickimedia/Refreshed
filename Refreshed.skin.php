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
		parent::setupSkinUserCss( $out );

		// Add CSS via ResourceLoader
		$out->addModuleStyles( 'skins.refreshed' );
	}
}

$refreshedTOC = '';

class RefreshedTemplate extends BaseTemplate {

	public static function onOutputPageParserOutput( OutputPage &$out, ParserOutput $parseroutput ) {
		global $refreshedTOC;
		$refreshedTOC = $parseroutput->mSections;

		return true;
	}

	public function execute() {
		global $wgStylePath, $refreshedTOC;

		$user = $this->getSkin()->getUser();

		// new TOC processing
		$tocHTML = '';
		if ( isset( $refreshedTOC ) ) {
			foreach ( $refreshedTOC as $tocpart ) {
				$class = "toclevel-{$tocpart['toclevel']}";
				$tocHTML .= "<a href=\"#{$tocpart['anchor']}\" class=\"$class\">{$tocpart['line']}</a>";
			}
		}

		// Title processing
		$titleBase = $this->getSkin()->getTitle();
		$title = $titleBase->getSubjectPage();
		$titleNamespace = $titleBase->getNamespace();
		$titleText = $title->getPrefixedText();
		$titleURL = $title->getLinkURL();

		if ( $title->inNamespace( 0 ) ) {
			$titleText = wfMessage( 'refreshed-article', $titleText )->text();
		}
		$titleText = str_replace( '/', '&#8203;/&#8203;', $titleText );
		$titleText = str_replace( ':', '&#8203;:&#8203;', $titleText );

		// Output the <html> tag and whatnot
		$this->html( 'headelement' );

		$refreshedImagePath = "$wgStylePath/Refreshed/refreshed/images";
?>

	<div id="header">
		<?php
		$logos = array(
			'meta' => "<img width=\"144\" height=\"30\" src=\"$refreshedImagePath/brickimedia.svg\" alt=\"\" />",
			'en' => "<img width=\"138\" height=\"30\" src=\"$refreshedImagePath/brickipedia.svg\" alt=\"\" />",
			'customs' => "<img width=\"100\" height=\"30\" src=\"$refreshedImagePath/customs.svg\" alt=\"\" />",
			'stories' => "<img width=\"144\" height=\"30\" src=\"$refreshedImagePath/stories.svg\" alt=\"\" />",
			'cuusoo' => "<img width=\"144\" height=\"36\" src=\"$refreshedImagePath/cuusoo.svg\" alt=\"\" />",
		);

		$groups = $user->getGroups();

		if ( in_array( 'sysop', $groups ) ) {
			$logos['admin'] = "<img width=\"81\" height=\"22\" src=\"$refreshedImagePath/admin.svg\" alt=\"\" />";
		}
		if ( in_array( 'sysadmin', $groups ) ) {
			$logos['dev'] = "<img width=\"169\" height=\"26\" src=\"$refreshedImagePath/dev.png\" alt=\"\" />";
		}

		global $bmProject;
		?>
		<div id="siteinfo">
			<a href='javascript:;'>
				<?php
					if ( isset( $logos[$bmProject] ) ) {
						echo $logos[$bmProject];
						unset( $logos[$bmProject] );
					}
					echo "<img class=\"arrow\" src=\"$refreshedImagePath/arrow-highres.png\" alt=\"\" width=\"15\" height=\"8\" />";
				?>
			</a>
			<div class="headermenu" style="display:none;">
				<?php
					foreach ( $logos as $site => $logo ) {
						echo "<a href=\"http://$site.brickimedia.org\">{$logo}</a>";
					}
				?>
			</div>
		</div>
	</div>
	<div id="fullwrapper">
		<div id="leftbar">
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
			<div id="leftbar-main">
				<ul id="leftbar-top">
					<?php
						reset( $this->data['content_actions'] );
						$pageTab = key( $this->data['content_actions'] );

						$this->data['content_actions'][$pageTab]['text'] = $titleText;

						$title = $this->data['content_actions'][$pageTab];
						echo '<li><a class="' . $title['class'] . '" ' .
							'id="' . $title['id'] . '" ' .
							'href="' . htmlspecialchars( $title['href'] ) . '">' .
							$title['text'] . '</a></li>'; // no htmlspecialchars for <wbr>s
						//echo $this->makeListItem( 'title', $titleLink );
						unset( $this->data['content_actions'][$pageTab] );

						foreach ( $this->data['content_actions'] as $key => $action ) {
							echo $this->makeListItem( $key, $action );
						}

						echo "<li><a href=\"javascript:;\" id=\"toolbox-link\">
							<img class=\"arrow\" src=\"$refreshedImagePath/arrow-highres.png\" alt=\"\" width=\"11\" height=\"6\" />
							<img class=\"arrow no-show\" src=\"$refreshedImagePath/arrow-highres-hover.png\" alt=\"\" width=\"11\" height=\"6\" />
							{$this->getMsg( 'toolbox' )->text()}</a></li>";
					?>
					<li><ul id="toolbox" style="display:none;">
						<?php
							$toolbox = $this->getToolbox();
							foreach( $toolbox as $tool => $toolData ) {
								echo $this->makeListItem( $tool, $toolData );
							}
						?>
					</ul></li>
					<?php
						if ( $this->data['language_urls'] ) {
							echo "<li><a href=\"javascript:;\" id=\"languages-link\">
								<img class=\"arrow\" src=\"$refreshedImagePath/arrow-highres.png\" alt=\"\" width=\"11\" height=\"6\" />
								<img class=\"arrow no-show\" src=\"$refreshedImagePath/arrow-highres-hover.png\" alt=\"\" width=\"11\" height=\"6\" />
								{$this->getMsg( 'otherlanguages' )->text()}</a></li>";
							echo "<li><ul id='languages' style='display:none;'>";
							foreach ( $this->data['language_urls'] as $key => $link ) {
								echo $this->makeListItem( $key, $link );
							}
							echo "</ul></li>";
						}
					?>
				</ul>
				<div id="leftbar-bottom">
					<div id="refreshed-toc">
						<div id="toc-box"></div>
						<!-- <div> -->
							<?php echo $tocHTML; ?>
						<!-- </div> -->
					</div>
				</div>
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
				<?php
				$title = $titleBase->getSubjectPage(); // reassigning it because it's changed in #leftbar-top
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
				$actionCount = 1;

				if ( $titleNamespace % 2 == 1 && $titleNamespace > 0 ) { // if talk namespace: talk namespaces are odd positive integers
					foreach ( $this->data['content_actions'] as $action ) {
						if ( $actionCount > 1 ) {
							// @todo Maybe write a custom makeLink()-like function for generating this code?
							echo '<a href="' . htmlspecialchars( $action['href'] ) .
								'"><div class="small-icon" id="icon-' . $action['id'] . '"></div></a>';
							$actionCount++;
						} else {
							$actionCount++;
						}
					}
				} else { // if not talk namespace
					foreach ( $this->data['content_actions'] as $action ) {
						echo '<a href="' . htmlspecialchars( $action['href'] ) .
							'"><div class="small-icon" id="icon-' . $action['id'] . '"></div></a>';
						$actionCount++;
					}
				}

				echo '</div>';
				if ( $actionCount > 2 ) {
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
			<div id="search">
				<form action="<?php $this->text( 'wgScript' ) ?>" method="get">
					<input type="hidden" name="title" value="<?php $this->text( 'searchtitle' ) ?>"/>
					<?php echo $this->makeSearchInput( array( 'id' => 'searchInput' ) ); ?>
				</form>
			</div>
			<div id="rightbar-main">
				<ul id="rightbar-top">
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
						} ?>
				</ul>
			</div>
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
