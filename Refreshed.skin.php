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
		global $wgStylePath, $wgContentNamespaces, $refreshedTOC, $namespaceNames, $wgUser;

		// new TOC processing
		$tocHTML = '';
		if ( isset( $refreshedTOC ) ) {
			foreach ( $refreshedTOC as $tocpart ) {
				$class = "toclevel-{$tocpart['toclevel']}";
				$tocHTML .= "<a href=\"#{$tocpart['anchor']}\" class=\"$class\">{$tocpart['line']}</a>";
			}
		}

		// Title processing
		$title = $this->getSkin()->getTitle()->getSubjectPage();
		$titleText = $title->getPrefixedText();

		if ( $title->inNamespace( 0 ) ) {
			$titleText = wfMessage( 'refreshed-article', $titleText )->text();
		}
		$titleText = str_replace( '/', '<wbr>/<wbr>', $titleText );
		$titleText = str_replace( ':', '<wbr>:<wbr>', $titleText );

		// suppress warnings to prevent notices about missing indexes in $this->data
		wfSuppressWarnings();

		// Output the <html> tag and whatnot
		$this->html( 'headelement' );

		$refreshedImagePath = "$wgStylePath/Refreshed/refreshed/images";
?>

	<div id="header">
		<?php
		$logos = array(
			'meta' => "<img width=\"144\" height=\"30\" src=\"$refreshedImagePath/brickimedia.png\" alt=\"\" />",
			'en' => "<img width=\"138\" height=\"30\" src=\"$refreshedImagePath/brickipedia.png\" alt=\"\" />",
			'customs' => "<img width=\"100\" height=\"30\" src=\"$refreshedImagePath/customs.png\" alt=\"\" />",
			'stories' => "<img width=\"144\" height=\"30\" src=\"$refreshedImagePath/stories.png\" alt=\"\" />",
			'cuusoo' => "<img width=\"144\" height=\"36\" src=\"$refreshedImagePath/cuusoo.png\" alt=\"\" />",
			'admin' => "<img width=\"81\" height=\"22\" src=\"$refreshedImagePath/admin.png\" alt=\"\" />",
			'dev' => "<img width=\"169\" height=\"26\" src=\"$refreshedImagePath/dev.png\" alt=\"\" />"
		);

		$groups = $wgUser->getGroups();
		//$globalGroups = efGURGetGroups();

		if ( in_array( 'sysop', $groups ) ) {
			$logos['admin'] = "<img width=\"81\" height=\"22\" src=\"$refreshedImagePath/admin.png\" alt=\"\" />";
		}
		if ( in_array( 'sysadmin', $groups ) ) {
			$logos['dev'] = "<img width=\"169\" height=\"26\" src=\"$refreshedImagePath/dev.png\" alt=\"\" />";
		}

		global $bmProject;
		?>
		<div id="siteinfo">
			<a href='javascript:;'>
				<?php
					echo $logos[$bmProject];
					unset( $logos[$bmProject] );
					echo "<img class=\"arrow\" src=\"$refreshedImagePath/arrow.png\" alt=\"\" width=\"15\" height=\"8\" />";
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
				<?php echo "<img class=\"arrow\" src=\"$refreshedImagePath/mobile-expand-edit.png\" alt=\"\" width=\"48\" height=\"58\" />"; ?>
			</div>
			<div id="userinfo">
				<a href='javascript:;'>
					<?php
						global $wgUser, $wgArticlePath;
						$id = $wgUser->getId();
						if ( is_file( '/var/www/wiki/images/avatars/' . $id . '_m.png' ) ) {
							$avatar = '/images/avatars/' . $id . '_m.png';
						} elseif ( is_file( '/var/www/wiki/images/avatars/' . $id . '_m.jpg' ) ) {
							$avatar = '/images/avatars/' . $id . '_m.jpg';
						} elseif ( is_file( '/var/www/wiki/images/avatars/' . $id . '_m.gif' ) ) {
							$avatar = '/images/avatars/' . $id . '_m.gif';
						} else {
							$avatar = '/images/avatars/default_m.gif';
						}
						echo "<img class=\"arrow\" src=\"$refreshedImagePath/arrow.png\" alt=\"\" /><img alt=\"\" class=\"avatar\" src=\"http://meta.brickimedia.org" . $avatar . "\" width=\"30\" /><span>{$wgUser->getName()}</span>";
					?>
				</a>
				<div class="headermenu" style="display:none;">
					<?php
						foreach ( $this->getPersonalTools() as $key => $tool ) {
							foreach ( $tool['links'] as $linkKey => $link ) {
								echo $this->makeLink( $linkKey, $link, $options );
							}
						}
					?>
				</div>
				<!--<img alt="" class="avatar" />-->
			</div>
			<div id="leftbar-main">
				<div id="leftbar-top">
					<div id="pagelinks">
						<?php
						reset( $this->data['content_actions'] );
						$pageTab = key( $this->data['content_actions'] );

						$this->data['content_actions'][$pageTab]['text'] = $titleText;

						foreach ( $this->data['content_actions'] as $action ) {
					 		echo '<a class="' . $action['class'] . '" ' .
					 			'id="' . $action['id'] . '" ' .
					 			'href="' . htmlspecialchars( $action['href'] ) . '">' .
					 			$action['text'] . '</a>'; // no htmlspecialchars
						} ?>
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
					<h1 class="title-overlay">&nbsp;</h1>
				</h1>
			</div>
			<div id="content">
				<?php $this->html( 'bodytext' ); ?>
			</div>
			<div id="cats">
				<?php $this->html( 'catlinks' ); ?>
			</div>
			<?php if ( $this->data['dataAfterContent'] ) { $this->html( 'dataAfterContent' ); } ?>
			<br clear="all" />
		</div>
		<div id="rightbar">
			<div class="shower">
				<?php echo "<img class=\"arrow\" alt=\"\" src=\"$refreshedImagePath/mobile-expand.png\" width=\"48\" height=\"48\" />"; ?>
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
								foreach ( $sub as $action ) {
									echo '<a class="sub" id="' . $action['id'] . '" ' .
										'href="' . htmlspecialchars( $action['href'] ) . '">' .
										htmlspecialchars( $action['text'] ) . '</a>';
								}
							} else {
								// allow raw HTML block to be defined by extensions (like NewsBox)
								echo $sub;
							}
						} ?>
				</div>
				<div id="rightbar-bottom">
					<div id="sitelinks">
						<?php /*foreach ( $this->data['sidebar']['bottom'] as $action ) {
					 		echo "<a id='" . $action['id'] . "' " .
					 			"href='" . htmlspecialchars( $action['href'] ) . "'>" .
					 			htmlspecialchars( $action['text'] ) . "</a>";
						}*/ ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="footer">
		<?php
		foreach ( $this->getFooterLinks() as $category => $links ) {
			$this->html( $category );
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
