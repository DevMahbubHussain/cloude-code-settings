/**
 * Featured Image Block Editor Component
 *
 * Wrapper block for product-image with additional styling options
 */

import { __ } from '@wordpress/i18n';
import { useEffect, useMemo } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import {
	InnerBlocks,
	useBlockProps,
	useInnerBlocksProps,
	InspectorControls,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import { Placeholder, Spinner } from '@wordpress/components';
import clsx from 'clsx';
import useUniqueId from '../../utils/useUniqueId.js';
import { useProduct } from '../../shared/useProduct.js';
import dynamicCss from './dynamicCss.js';
import FeaturedImageInspectorControls from './inspectorControls.jsx';

const TEMPLATE = [
	[
		'sp-smart-store/product-image',
		{
			showSaleBadge: false,
		},
	],
];

export default function Edit({ attributes, setAttributes, context, clientId }) {
	const {
		uniqueId,
		productId,
		wrapperPadding,
		wrapperPaddingUnit,
		wrapperMargin,
		wrapperMarginUnit,
		wrapperBorder,
		wrapperBorderRadius,
		wrapperBorderRadiusUnit,
		wrapperBoxShadow,
	} = attributes;

	// Generate unique ID
	useUniqueId(uniqueId, setAttributes, 'spssp-fi-');

	// Get inner blocks for empty state handling
	const { innerBlocks, wasBlockJustInserted } = useSelect(
		(select) => {
			return {
				innerBlocks: select(blockEditorStore).getBlocks(clientId) || [],
				wasBlockJustInserted: select(blockEditorStore).wasBlockJustInserted?.(clientId) || false,
			};
		},
		[clientId]
	);

	// Get replaceInnerBlocks for template lock protection
	const { replaceInnerBlocks } = useDispatch(blockEditorStore);

	// Template lock bypass protection - ensure only product-image is inside
	useEffect(() => {
		if (innerBlocks.length === 0) {
			// Empty, add default template
			replaceInnerBlocks(clientId, TEMPLATE, false);
		} else if (innerBlocks.length > 1 || innerBlocks[0]?.name !== 'sp-smart-store/product-image') {
			// Invalid state, reset to valid
			replaceInnerBlocks(clientId, TEMPLATE, false);
		}
	}, [innerBlocks, clientId, replaceInnerBlocks]);

	// Get postId from context or attributes
	const postId = context?.postId || attributes?.productId;

	// Fetch product data
	const { product, isResolving } = useProduct(postId);

	// Debug: Always log to confirm product ID retrieval
	useEffect(() => {
		console.log('FeaturedImage Block Debug:', {
			blockName: 'featured-image',
			clientId,
			contextPostId: context?.postId,
			attributeProductId: attributes?.productId,
			finalPostId: postId,
			productFound: !!product,
			productName: product?.name || 'N/A',
			productId: product?.id || 'N/A',
			isResolving,
			innerBlocksCount: innerBlocks.length,
			wasBlockJustInserted,
		});
	}, [postId, product, isResolving, context, attributes, clientId, innerBlocks, wasBlockJustInserted]);

	// Generate dynamic CSS
	const blockStyling = useMemo(() => {
		if (!uniqueId) return '';
		return dynamicCss({
			...attributes,
			uniqueId,
		});
	}, [attributes, uniqueId]);

	// Block props
	const blockProps = useBlockProps({
		className: clsx(
			'spssp-featured-image-wrapper',
			{
				'spssp-featured-image-empty': !innerBlocks.length,
				'spssp-featured-image-loading': isResolving,
			},
			uniqueId ? `spssp-unique-id-${uniqueId}` : ''
		),
	});

	// Inner blocks props
	const innerBlockProps = useInnerBlocksProps(
		{
			className: 'spssp-featured-image-inner',
		},
		{
			template: wasBlockJustInserted ? TEMPLATE : undefined,
			allowedBlocks: ['sp-smart-store/product-image'],
			templateLock: 'all',
		}
	);

	// Empty state
	if (!innerBlocks.length) {
		return (
			<div {...blockProps}>
				<style>{blockStyling}</style>
				<Placeholder
					icon={__('featured image', 'woo-product-slider-pro')}
					label={__('Featured Image', 'woo-product-slider-pro')}
					instructions={__('Loading product image...', 'woo-product-slider-pro')}
				>
					<Spinner />
				</Placeholder>
			</div>
		);
	}

	return (
		<>
			<InspectorControls>
				<FeaturedImageInspectorControls attributes={attributes} setAttributes={setAttributes} />
			</InspectorControls>
			<div {...blockProps}>
				<style>{blockStyling}</style>
				<InnerBlocks {...innerBlockProps} />
				{isResolving && (
					<div className="spssp-featured-image-loading-overlay">
						<Spinner />
					</div>
				)}
			</div>
		</>
	);
}
