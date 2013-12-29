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

	function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );

		// Add CSS & JS via ResourceLoader
		$out->addModules( 'skins.refreshed' );
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
		$titleText = str_replace( '/', '<wbr>/<wbr>', $titleText );
		$titleText = str_replace( ':', '<wbr>:<wbr>', $titleText );

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
			'stories' => "<img width=\"144\" height=\"30\" src=\"$refreshedImagePath/stories.png\" alt=\"\" />",
			'cuusoo' => "<img width=\"144\" height=\"36\" src=\"$refreshedImagePath/cuusoo.png\" alt=\"\" />",
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
			<div class="shower">
				<?php echo "<img class=\"arrow\" src=\"$refreshedImagePath/mobile-expand-edit.png\" alt=\"\" width=\"48\" height=\"48\" />"; ?>
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
						}
						echo "<img class=\"arrow\" src=\"$refreshedImagePath/arrow-highres.png\" alt=\"\" width=\"15\" height=\"8\" />
							{$avatarImage}
							<span>{$user->getName()}</span>";
					?>
				</a>
				<div class="headermenu" style="display:none;">
					<?php
						foreach ( $this->getPersonalTools() as $key => $tool ) {
							foreach ( $tool['links'] as $linkKey => $link ) {
								echo $this->makeLink( $linkKey, $link );
							}
						}
					?>
				</div>
			</div>
			<div id="leftbar-main">
				<div id="leftbar-top">
					<?php
						reset( $this->data['content_actions'] );
						$pageTab = key( $this->data['content_actions'] );

						$this->data['content_actions'][$pageTab]['text'] = $titleText;

						$title = $this->data['content_actions'][$pageTab];
						echo '<a class="' . $title['class'] . '" ' .
							'id="' . $title['id'] . '" ' .
							'href="' . htmlspecialchars( $title['href'] ) . '">' .
							$title['text'] . '</a>'; // no htmlspecialchars for <wbr>s
						unset( $this->data['content_actions'][$pageTab] );

						foreach ( $this->data['content_actions'] as $key => $action ) {
							echo $this->makeLink( $key, $action );
						}
						echo "<a href=\"javascript:;\" id=\"toolbox-link\">
							<img class=\"arrow\" src=\"$refreshedImagePath/arrow-highres.png\" alt=\"\" width=\"11\" height=\"6\" />
							<img class=\"arrow no-show\" src=\"$refreshedImagePath/arrow-highres-hover.png\" alt=\"\" width=\"11\" height=\"6\" />
							{$this->getMsg( 'toolbox' )->text()}</a>";
					?>
					<div id="toolbox" style="display:none;">
						<?php
							$toolbox = $this->getToolbox();
							foreach( $toolbox as $tool => $toolData ) {
								echo $this->makeLink( $tool, $toolData );
							}
						?>
					</div>
				</div>
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
				<h1>
					<?php $this->html( 'title' ) ?>
					<div class="mobile-overlay"></div>
				</h1>
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

				echo '<div class="mobile-overlay"></div>';
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
			<div class="shower">
				<?php echo "<img class=\"arrow\" src=\"$refreshedImagePath/mobile-expand.png\" alt=\"\" width=\"48\" height=\"48\" />"; ?>
			</div>
			<div id="search">
				<form action="<?php $this->text( 'wgScript' ) ?>" method="get">
					<input type="hidden" name="title" value="<?php $this->text( 'searchtitle' ) ?>"/>
					<?php echo $this->makeSearchInput( array( 'id' => 'searchInput' ) ); ?>
				</form>
			</div>
			<div id="rightbar-main">
				<div id="rightbar-top">
					<?php
						unset( $this->data['sidebar']['SEARCH'] );
						unset( $this->data['sidebar']['TOOLBOX'] );
						unset( $this->data['sidebar']['LANGUAGES'] );

						foreach ( $this->data['sidebar'] as $main => $sub ) {
							echo '<span class="main">' . htmlspecialchars( $main ) . '</span>';
							if ( is_array( $sub ) ) { // MW-generated stuff from the sidebar message
								foreach ( $sub as $key => $action ) {
									echo $this->makeLink( $key, $action, array( 'link-class' => 'sub' ) );
								}
							} else {
								// allow raw HTML block to be defined by extensions (like NewsBox)
								echo $sub;
							}
						} ?>
				</div>
			</div>
		</div>
	</div>
	<div id="footer">
		<?php
			// @todo FIXME:
			// 1) Make this more configurable (right now it's horribly site-specific)
			// 2) Consider renaming the hook to something like RefreshedInFooter, RefreshedFooter, etc.
			// 3) Move the ad code somewhere else, hard-coding it in is nasty
			$showAdvert = false;
			wfRunHooks( 'RefreshedAdvert', array( &$showAdvert ) );
			if ( $showAdvert ):
		?>
		<div id="advert">
			<p><?php echo wfMessage( 'refreshed-advert' )->plain(); ?></p>
			<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
			<!-- Refreshed ad -->
			<ins class="adsbygoogle"
				style="display:inline-block;width:728px;height:90px"
				data-ad-client="ca-pub-9543775174763951"
				data-ad-slot="7733872730"></ins>
			<script>
				(adsbygoogle = window.adsbygoogle || []).push({});
			</script>
		</div>
		<?php
			endif;

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
