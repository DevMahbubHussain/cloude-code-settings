<?php
/**
 * Featured Image Block Attributes
 *
 * @package ShapedPlugin\WooProductSliderPro\Blocks\Builder\FeaturedImage
 */

namespace ShapedPlugin\WooProductSliderPro\Blocks\Builder\FeaturedImage;

use ShapedPlugin\WooProductSliderPro\Blocks\CommonAttributes;
use ShapedPlugin\WooProductSliderPro\Blocks\AttributeGenerators;

/**
 * Featured Image Attributes Class.
 */
class FeaturedImageAttributes {

	/**
	 * Get attributes.
	 *
	 * @return array
	 */
	public static function get() {
		return array_merge(
			array(
				// General Settings.
				'uniqueId'  => AttributeGenerators::string(),
				'productId' => AttributeGenerators::number( 0 ),

				// Visibility.
				'hideOnDesktop' => AttributeGenerators::boolean( false ),
				'hideOnTablet'  => AttributeGenerators::boolean( false ),
				'hideOnMobile'  => AttributeGenerators::boolean( false ),

				// Wrapper Spacing.
				'wrapperPadding'      => AttributeGenerators::responsive_spacing( 0, 0, 0, 0, 'px', false ),
				'wrapperPaddingUnit'  => array(
					'type'    => 'string',
					'default' => 'px',
				),
				'wrapperMargin'       => AttributeGenerators::responsive_spacing( 0, 0, 0, 0, 'px', false ),
				'wrapperMarginUnit'   => array(
					'type'    => 'string',
					'default' => 'px',
				),

				// Wrapper Border.
				'wrapperBorder' => AttributeGenerators::border( 'none', '', '' ),

				// Wrapper Border Radius.
				'wrapperBorderRadius'       => AttributeGenerators::responsive_spacing( 0, 0, 0, 0, 'px', false ),
				'wrapperBorderRadiusUnit'   => array(
					'type'    => 'string',
					'default' => 'px',
				),

				// Wrapper Box Shadow.
				'wrapperBoxShadow' => array(
					'type'    => 'object',
					'default' => array(
						'enable'    => false,
						'horizontal' => 0,
						'vertical'   => 0,
						'blur'       => 0,
						'spread'     => 0,
						'color'      => '#000000',
						'opacity'    => 0,
						'inset'      => false,
					),
				),

				// Advanced.
				'customCSSClass'  => array(
					'type'               => 'string',
					'default'            => '',
					'__experimentalRole' => 'content',
				),
				'customCSSID'     => array(
					'type'               => 'string',
					'default'            => '',
					'__experimentalRole' => 'content',
				),
			)
		);
	}
}
