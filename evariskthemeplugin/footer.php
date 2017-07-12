<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
?>

		</div><!-- #main -->
	</div><!-- #page -->
	<footer id="colophon" role="contentinfo">

			<?php
				/* A sidebar in the footer? Yep. You can can customize
				 * your footer with three columns of widgets.
				 */
				get_sidebar( 'footer' );
			?>

			<div id="site-generator">
				<div id="site-generator-content">
					<p>Copyright</p>
					<nav>
						<ul>
							<li><a href="#">Mentions legales</a></li>
							<li><a href="#">Eoxia</a></li>
						</ul>
					</nav>
					<div style="clear:both;"></div>
				</div><!-- fin de site-generator-content -->
			</div>
	</footer><!-- #colophon -->


<?php wp_footer(); ?>

</body>
</html>