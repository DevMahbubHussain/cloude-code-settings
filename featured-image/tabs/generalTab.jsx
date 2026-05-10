/**
 * General Tab for Featured Image Wrapper
 *
 * Controls for spacing, border, and box shadow
 */

import { __ } from '@wordpress/i18n';
import { Border, BoxShadow, Spacing } from '@woo-product-slider-pro/components';

const GeneralTab = ({ attributes, setAttributes }) => {
	const {
		wrapperPadding,
		wrapperPaddingUnit,
		wrapperMargin,
		wrapperMarginUnit,
		wrapperBorder,
		wrapperBorderRadius,
		wrapperBorderRadiusUnit,
		wrapperBoxShadow,
	} = attributes;

	return (
		<>
			<Spacing
				label={__('Padding', 'woo-product-slider-pro')}
				attributes={wrapperPadding}
				attributesKey="wrapperPadding"
				unit={wrapperPaddingUnit}
				unitKey="wrapperPaddingUnit"
				setAttributes={setAttributes}
				units={['px', '%', 'em', 'rem']}
			/>

			<Spacing
				label={__('Margin', 'woo-product-slider-pro')}
				attributes={wrapperMargin}
				attributesKey="wrapperMargin"
				unit={wrapperMarginUnit}
				unitKey="wrapperMarginUnit"
				setAttributes={setAttributes}
				units={['px', '%', 'em', 'rem']}
			/>

			<Border
				attributes={{
					border: wrapperBorder,
				}}
				setAttributes={setAttributes}
				attributesKey={{
					border: 'wrapperBorder',
				}}
			/>

			<Spacing
				label={__('Border Radius', 'woo-product-slider-pro')}
				attributes={wrapperBorderRadius}
				attributesKey="wrapperBorderRadius"
				unit={wrapperBorderRadiusUnit}
				unitKey="wrapperBorderRadiusUnit"
				setAttributes={setAttributes}
				units={['px', '%']}
			/>

			<BoxShadow
				attributes={wrapperBoxShadow}
				attributesKey="wrapperBoxShadow"
				setAttributes={setAttributes}
			/>
		</>
	);
};

export default GeneralTab;
