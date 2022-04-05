<?php
/**
 * The header.
 *
 * This file is what displays all of the head section
 *
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
	<!-- call in the custom CSS file that make -->
	<link rel="stylesheet" src="style-custom.css" type="text/css">
	<!-- call in any libraries that you might use -->
	<!-- call in any custom fonts (Google fonts) -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Ubuntu&display=swap" rel="stylesheet">
</head>

<body <?php body_class(); ?>>
<header class="container">
		<a href="<?php echo esc_url(home_url()); ?>">
			<img src="<?php echo esc_url(home_url('wp-content/uploads/2022/04/6802231_astronomy_earth_galaxy_planet_science_icon.png')) ?>" alt="logo" width="100" height="100">
		</a>
		<nav>
		<?php
			wp_nav_menu(array(
				'menu' => 'main',
				'theme_location' => '',
				'depth' => 2,
				'fallback_cb' => false,
				'menu_class' => 'class_menu',
				'container' => false,
				'items_wrap' => '%3$s' 
			));
		?>
	</nav>
</header>


