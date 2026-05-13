<?php
/**
 * Featured Image Block
 *
 * @package ShapedPlugin\WooProductSliderPro\Blocks\Builder\FeaturedImage
 */

namespace ShapedPlugin\WooProductSliderPro\Blocks\Builder\FeaturedImage;

use ShapedPlugin\WooProductSliderPro\Blocks\AbstractBlock;

defined( 'ABSPATH' ) || exit;

/**
 * FeaturedImage block class
 */
class FeaturedImage extends AbstractBlock {

	/**
	 * Block name
	 *
	 * @var string
	 */
	protected $block_name = 'featured-image';

	/**
	 * API version
	 *
	 * @var string
	 */
	protected $api_version = '3';

	/**
	 * Register script and style assets for the block type before it is registered.
	 *
	 * This registers the scripts; it does not enqueue them.
	 */
	protected function register_block_type_assets() {
		return null;
	}

	/**
	 * Get context usage
	 *
	 * @return array
	 */
	protected function get_block_type_uses_context() {
		return array( 'query', 'queryId', 'postId', 'imageId', 'uniqueId' );
	}

	/**
	 * Get block-specific attributes.
	 * Extends parent method to add FeaturedImage specific attributes.
	 *
	 * @return array Block-specific attributes array.
	 */
	protected function get_block_specific_attributes() {
		return FeaturedImageAttributes::get();
	}

	/**
	 * Get the block's attributes.
	 *
	 * @param array $attributes Block attributes. Default empty array.
	 * @return array  Block attributes merged with defaults.
	 */
	protected function parse_attributes( $attributes ) {
		$defaults = array(
			'uniqueId'           => '',
			'productId'          => 0,
			'wrapperPadding'     => array(
				'device' => array(
					'Desktop' => array( 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0 ),
					'Tablet'  => array( 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0 ),
					'Mobile'  => array( 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0 ),
				),
				'unit'   => array(
					'Desktop' => 'px',
					'Tablet'  => 'px',
					'Mobile'  => 'px',
				),
			),
			'wrapperMargin'      => array(
				'device' => array(
					'Desktop' => array( 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0 ),
					'Tablet'  => array( 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0 ),
					'Mobile'  => array( 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0 ),
				),
				'unit'   => array(
					'Desktop' => 'px',
					'Tablet'  => 'px',
					'Mobile'  => 'px',
				),
			),
			'wrapperBorder'      => array(
				'style'      => 'none',
				'color'      => '',
				'hoverColor' => '',
			),
			'wrapperBorderRadius' => array(
				'device' => array(
					'Desktop' => array( 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0 ),
					'Tablet'  => array( 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0 ),
					'Mobile'  => array( 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0 ),
				),
				'unit'   => array(
					'Desktop' => 'px',
					'Tablet'  => 'px',
					'Mobile'  => 'px',
				),
			),
			'wrapperBoxShadow'   => array(
				'enable'    => false,
				'horizontal' => 0,
				'vertical'   => 0,
				'blur'       => 0,
				'spread'     => 0,
				'color'      => '#000000',
				'opacity'    => 0,
				'inset'      => false,
			),
			'hideOnDesktop'      => false,
			'hideOnTablet'       => false,
			'hideOnMobile'       => false,
		);

		return wp_parse_args( $attributes, $defaults );
	}

	/**
	 * Include and render the block
	 *
	 * @param array     $attributes Block attributes. Default empty array.
	 * @param string    $content    Block content. Default empty string.
	 * @param \WP_Block $block      Block instance.
	 * @return string Rendered block type output.
	 */
	protected function render( $attributes, $content, $block ) {
		// Edge case: WooCommerce not installed.
		if ( ! function_exists( 'wc_get_product' ) ) {
			return '';
		}

		// Edge case: Admin only render for preview.
		if ( is_admin() ) {
			return $this->render_admin( $attributes, $content, $block );
		}

		// Get uniqueId.
		$parent_unique_id = $block->context['uniqueId'] ?? '';
		$own_unique_id    = $attributes['uniqueId'] ?? '';
		$unique_id        = ! empty( $parent_unique_id ) ? $parent_unique_id : $own_unique_id;

		// Generate uniqueId if missing.
		if ( empty( $unique_id ) ) {
			$unique_id = 'spssp-fi-' . uniqid();
		}

		// Get postId from context or attributes.
		$post_id = $block->context['postId'] ?? $attributes['productId'] ?? null;

		// Edge case: No product context.
		if ( ! $post_id ) {
			return '';
		}

		// Edge case: Product not found.
		$product = wc_get_product( $post_id );
		if ( ! $product ) {
			return '';
		}

		// Edge case: Empty inner blocks content.
		if ( empty( $content ) ) {
			return '';
		}

		// Generate dynamic CSS.
		$css_generator = new FeaturedImageCssGenerator();
		$dynamic_css   = $css_generator->generate( $attributes, $unique_id );

		// Render block.
		ob_start();
		include __DIR__ . '/render.php';
		$html = ob_get_clean();

		if ( ! empty( $dynamic_css ) ) {
			$dynamic_css = '<style type="text/css">' . $dynamic_css . '</style>';
		}
		return $dynamic_css . $html;
	}

	/**
	 * Render for admin preview
	 *
	 * @param array     $attributes Block attributes.
	 * @param string    $content    Block content.
	 * @param \WP_Block $block      Block instance.
	 * @return string Rendered output.
	 */
	private function render_admin( $attributes, $content, $block ) {
		// Return placeholder for admin.
		$unique_id = $attributes['uniqueId'] ?? 'preview';
		return '<div class="spssp-featured-image-wrapper spssp-unique-id-' . esc_attr( $unique_id ) . '">' . $content . '</div>';
	}
}
