<?php
/**
 * Featured Image CSS Generator
 *
 * @package ShapedPlugin\WooProductSliderPro\Blocks\Builder\FeaturedImage
 */

namespace ShapedPlugin\WooProductSliderPro\Blocks\Builder\FeaturedImage;

use ShapedPlugin\WooProductSliderPro\Blocks\Utils\CSS\Css_Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * FeaturedImageCssGenerator class
 */
class FeaturedImageCssGenerator {

	/**
	 * Generate CSS for Featured Image wrapper
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $unique_id Unique ID for CSS scoping.
	 * @param array  $context Context array.
	 * @return string Generated CSS.
	 */
	public static function generate( $attributes, $unique_id, $context = array() ) {
		if ( empty( $unique_id ) ) {
			return '';
		}

		$wrapper = '.spssp-unique-id-' . esc_attr( $unique_id ) . '.spssp-featured-image-wrapper';

		$desktop_css = array_merge(
			self::get_base_styles( $attributes, $wrapper ),
			self::get_responsive_styles( $attributes, $wrapper, 'Desktop' )
		);

		$css_object = array(
			'desktop_css' => $desktop_css,
			'tablet_css'  => self::get_responsive_styles( $attributes, $wrapper, 'Tablet' ),
			'mobile_css'  => self::get_responsive_styles( $attributes, $wrapper, 'Mobile' ),
		);

		return Css_Helpers::filter_responsive_dynamic_css( $css_object );
	}

	/**
	 * Get base (non-device) styles.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $wrapper   Wrapper selector.
	 * @return array
	 */
	private static function get_base_styles( $attributes, $wrapper ) {
		$wrapper_border        = $attributes['wrapperBorder'] ?? array();
		$wrapper_border_radius = $attributes['wrapperBorderRadius'] ?? array();
		$wrapper_box_shadow    = $attributes['wrapperBoxShadow'] ?? array();

		$styles = array(
			array(
				'selector' => $wrapper,
				'styles'   => array_merge(
					array(
						'border-radius' => Css_Helpers::get_spacing_value( $wrapper_border_radius ),
					),
					Css_Helpers::get_border_css( $wrapper_border, array() )
				),
			),
			array(
				'selector' => "{$wrapper}:hover",
				'styles'   => array(
					'border-color' => $wrapper_border['hoverColor'] ?? '',
				),
			),
		);

		// Box shadow.
		if ( ! empty( $wrapper_box_shadow['enable'] ) ) {
			$horizontal = $wrapper_box_shadow['horizontal'] ?? 0;
			$vertical   = $wrapper_box_shadow['vertical'] ?? 0;
			$blur       = $wrapper_box_shadow['blur'] ?? 0;
			$spread     = $wrapper_box_shadow['spread'] ?? 0;
			$color      = $wrapper_box_shadow['color'] ?? '#000000';
			$opacity    = $wrapper_box_shadow['opacity'] ?? 0;
			$inset      = $wrapper_box_shadow['inset'] ?? false;

			$shadow_opacity = $opacity / 100;
			$hex_color      = ltrim( $color, '#' );
			$shadow_color   = '#' . $hex_color . dechex( round( $shadow_opacity * 255 ) );

			$shadow_value = ( $inset ? 'inset ' : '' ) .
				$horizontal . 'px ' .
				$vertical . 'px ' .
				$blur . 'px ' .
				$spread . 'px ' .
				$shadow_color;

			$styles[] = array(
				'selector' => $wrapper,
				'styles'   => array(
					'box-shadow' => $shadow_value,
				),
			);
		}

		return $styles;
	}

	/**
	 * Get responsive styles for a device.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $wrapper   Wrapper selector.
	 * @param string $device    Device name.
	 * @return array
	 */
	private static function get_responsive_styles( $attributes, $wrapper, $device ) {
		$wrapper_padding = $attributes['wrapperPadding'] ?? array();
		$wrapper_margin  = $attributes['wrapperMargin'] ?? array();

		$hide_on_device = array(
			'Desktop' => $attributes['hideOnDesktop'] ?? false,
			'Tablet'  => $attributes['hideOnTablet'] ?? false,
			'Mobile'  => $attributes['hideOnMobile'] ?? false,
		);

		return array(
			array(
				'selector' => $wrapper,
				'styles'   => array(
					'padding' => Css_Helpers::get_spacing_value( $wrapper_padding, $device ),
					'margin'  => Css_Helpers::get_spacing_value( $wrapper_margin, $device ),
				),
			),
			array(
				'selector' => '.spssp-unique-id-' . esc_attr( $attributes['uniqueId'] ?? '' ),
				'styles'   => array(
					'display' => $hide_on_device[ $device ] ? 'none' : 'block',
				),
			),
		);
	}
}
