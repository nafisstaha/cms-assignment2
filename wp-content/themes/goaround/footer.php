<?php
/**
 * The template for displaying the footer
 *
 */

?>
	<footer class="row">
		
		<div class="col-sm-12 col-md-3 col-lg-3"><?php dynamic_sidebar( 'footer_area_one' ); ?></div>
		<div class="col-sm-12 col-md-3 col-lg-3"><?php dynamic_sidebar( 'footer_area_two' ); ?></div>
		<div class="col-sm-12 col-md-3 col-lg-3"><?php dynamic_sidebar( 'footer_area_three' ); ?></div>
		<div class="col-sm-12 col-md-3 col-lg-3"><?php dynamic_sidebar( 'footer_area_four' ); ?></div>
		
	</footer>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
