<?php
/*
Plugin Name: Hestia Pro Blog Extension
Plugin URI: http://www.hardeepasrani.com/
Description: A lightweight plugin to add more features to blog section of Hestia Pro.
Author: Hardeep Asrani
Author URI: http://www.hardeepasrani.com/
Version: 1.0
*/

/**
 * Blog section for the homepage.
 *
 * @package Hestia
 * @since Hestia 1.0
 */
if ( ! function_exists( 'hestia_blog' ) ) :
	/**
	 * Blog section content.
	 *
	 * @since Hestia 1.0
	 * @modified 1.1.34
	 */
	function hestia_blog( $is_shortcode = false ) {
		$hide_section = get_theme_mod( 'hestia_blog_hide', false );
		if ( ! $is_shortcode && (bool) $hide_section === true ) {
			return;
		}
		if ( current_user_can( 'edit_theme_options' ) ) {
			/* translators: 1 - link to customizer setting. 2 - 'customizer' */
			$hestia_blog_subtitle = get_theme_mod( 'hestia_blog_subtitle', sprintf( __( 'Change this subtitle in %s.','hestia-pro' ), sprintf( '<a href="%1$s" class="default-link">%2$s</a>', esc_url( admin_url( 'customize.php?autofocus&#91;control&#93;=hestia_blog_subtitle' ) ), __( 'customizer','hestia-pro' ) ) ) );
		} else {
			$hestia_blog_subtitle = get_theme_mod( 'hestia_blog_subtitle' );
		}
		$hestia_blog_title = get_theme_mod( 'hestia_blog_title', __( 'Blog', 'hestia-pro' ) );
		if ( $is_shortcode ) {
			$hestia_blog_title = '';
			$hestia_blog_subtitle = '';
		}
		$hestia_blog_items = get_theme_mod( 'hestia_blog_items', 3 );
		$hestia_blog_cat = get_theme_mod( 'hestia_blog_cat', 0 );
		$class_to_add = 'container';
		if ( $is_shortcode ) {
			$class_to_add = '';
		}
		?>
		<section class="blogs hestia-blogs" id="blog" data-sorder="hestia_blog">
			<div class="<?php echo esc_attr( $class_to_add ); ?>">
				<div class="row">
					<div class="col-md-8 col-md-offset-2 text-center">
					<?php if ( ! empty( $hestia_blog_title ) || is_customize_preview() ) : ?>
						<h2 class="hestia-title"><?php echo esc_html( $hestia_blog_title ); ?></h2>
					<?php endif; ?>
					<?php if ( ! empty( $hestia_blog_subtitle ) || is_customize_preview() ) : ?>
						<h5 class="description"><?php echo wp_kses_post( $hestia_blog_subtitle ); ?></h5>
					<?php endif; ?>
					</div>
				</div>
				<?php hestia_blog_content_extended( $hestia_blog_items, $hestia_blog_cat ); ?>
			</div>
		</section>
		<?php
	}
endif;
/**
 * Get content for blog section.
 *
 * @since 1.1.31
 * @access public
 * @param string $hestia_blog_items Number of items.
 * @param bool   $is_callback Flag to check if it's callback or not.
 */
function hestia_blog_content_extended( $hestia_blog_items, $hestia_blog_cat, $is_callback = false ) {
	if ( ! $is_callback ) { ?>
		<div class="hestia-blog-content">
		<?php
	}
	$args = array(
		'ignore_sticky_posts' => true,
		);
		$args['posts_per_page'] = ! empty( $hestia_blog_items ) ? absint( $hestia_blog_items ) : 3;
		$args['cat'] = ! empty( $hestia_blog_cat ) ? absint( $hestia_blog_cat ) : 0;
		$loop = new WP_Query( $args );
		$allowed_html = array(
			'br' => array(),
			'em' => array(),
			'strong' => array(),
			'i' => array(
			        'class' => array(),
			),
			'span' => array(),
		);
	if ( $loop->have_posts() ) :
		$i = 1;
		echo '<div class="row">';
		while ( $loop->have_posts() ) :
			$loop->the_post(); ?>
			<article class="col-md-4 hestia-blog-item">
				<div class="card card-plain card-blog">
					<?php if ( has_post_thumbnail() ) : ?>
					<div class="card-image">
						<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
							<?php the_post_thumbnail( 'hestia-blog' ); ?>
						</a>
					</div>
				<?php endif; ?>
					<div class="content">
						<h6 class="category"><?php hestia_category(); ?></h6>
						<h4 class="card-title">
							<a class="blog-item-title-link" href="<?php echo esc_url( get_permalink() ); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark">
								<?php echo wp_kses( force_balance_tags( get_the_title() ), $allowed_html ); ?>
							</a>
						</h4>
						<p class="card-description"><?php echo wp_kses_post( get_the_excerpt() ); ?></p>
					</div>
				</div>
			</article>
			<?php
			if ( $i % 3 == 0 ) {
				echo '</div><!-- /.row -->';
				echo '<div class="row">';
			}
			$i++;
		endwhile;
		echo '</div>';
	endif;
	if ( ! $is_callback ) { ?>
				</div>
				<?php
	}
}

/**
 * Custom control to pull categories.
 */
function hestia_add_customizer_custom_category_controls( $wp_customize ) {
	class Hestia_Category_Dropdown_Control extends WP_Customize_Control {
		private $cats = false;

		public function __construct($manager, $id, $args = array(), $options = array()) {
			$this->cats = get_categories($options);

			parent::__construct( $manager, $id, $args );
		}

		/**
		 * Render the content of the category dropdown
		 *
		 * @return HTML
		 */
		public function render_content() {
				if(!empty($this->cats)) {
					?>
						<label>
						  <span class="customize-category-select-control"><?php echo esc_html( $this->label ); ?></span>
						  <select <?php $this->link(); ?>>
							<option value="0"><?php echo _e('All Posts', 'hestia-pro'); ?></option>
							   <?php
									foreach ( $this->cats as $cat ) {
										printf('<option value="%s" %s>%s</option>', $cat->term_id, selected($this->value(), $cat->term_id, false), $cat->name);
									}
							   ?>
						  </select>
						</label>
					<?php
				}
			}
	}
}
add_action( 'customize_register', 'hestia_add_customizer_custom_category_controls' );

/**
 * Hook controls for Pricing section to Customizer.
 *
 * @since Hestia 1.0
 */
function hestia_blog_customize_register_extend( $wp_customize ) {

	$wp_customize->add_setting( 'hestia_blog_cat', array(
		'default' => 0,
		'capability' => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field'
	));
	$wp_customize->add_control(new Hestia_Category_Dropdown_Control($wp_customize, 'hestia_blog_cat', array(
		'label' => __('Display Posts From:', '-pro'),
		'section' => 'hestia_blog',
		'priority' => 20,
		'settings' => 'hestia_blog_cat'
	)));

}

add_action( 'customize_register', 'hestia_blog_customize_register_extend' );