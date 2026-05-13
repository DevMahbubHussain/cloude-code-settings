/**
 * Featured Image Block Registration
 *
 * Wrapper block for product-image with additional styling options
 */

import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import edit from './edit.jsx';
import { FeaturedImageIcon } from './icons.jsx';

registerBlockType(metadata.name, {
	...metadata,
	apiVersion: 3,
	icon: <FeaturedImageIcon />,
	edit,
	save: () => null,
});
