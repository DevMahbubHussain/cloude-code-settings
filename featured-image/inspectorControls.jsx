/**
 * Featured Image Inspector Controls
 */

import { __ } from '@wordpress/i18n';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { InspectorControl } from '@woo-product-slider-pro/components';
import GeneralTab from './tabs/generalTab.jsx';
import { AdvanceTab } from '../../showcase/shared/tabs/index.js';

const Inspector = ({ attributes, setAttributes }) => {
	const { hideOnDesktop, hideOnTablet, hideOnMobile, productId } = attributes;

	return (
		<>
			<PanelBody title={__('Wrapper Settings', 'woo-product-slider-pro')} initialOpen={true}>
				<GeneralTab attributes={attributes} setAttributes={setAttributes} />
			</PanelBody>

			<PanelBody title={__('Advanced', 'woo-product-slider-pro')} initialOpen={false}>
				<AdvanceTab
					attributes={attributes}
					setAttributes={setAttributes}
					showPreloader={false}
				/>
			</PanelBody>
		</>
	);
};

const FeaturedImageInspectorControls = ({ attributes, setAttributes }) => {
	return (
		<InspectorControl Inspector={Inspector} attributes={attributes} setAttributes={setAttributes} />
	);
};

export default FeaturedImageInspectorControls;
