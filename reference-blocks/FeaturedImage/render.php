<?php
/**
 * Featured Image Block Render Template
 *
 * @package ShapedPlugin\WooProductSliderPro\Blocks\Builder\FeaturedImage
 */

defined( 'ABSPATH' ) || exit;

$wrapper_classes = array(
	'spssp-featured-image-wrapper',
	'spssp-unique-id-' . esc_attr( $unique_id ),
);

$custom_css_class = $attributes['customCSSClass'] ?? '';
$custom_css_id    = $attributes['customCSSID'] ?? '';

if ( ! empty( $custom_css_class ) ) {
	$wrapper_classes[] = esc_attr( $custom_css_class );
}

$wrapper_id = ! empty( $custom_css_id ) ? 'id="' . esc_attr( $custom_css_id ) . '"' : '';

?>
<div <?php echo $wrapper_id; ?> class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
	<?php echo $content; ?>
</div>
