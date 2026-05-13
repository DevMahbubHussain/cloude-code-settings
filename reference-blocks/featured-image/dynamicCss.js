import {
	borderCss,
	objectToCssString,
	spacingGenerate,
	wrapInMediaQuery,
	getGlobalBreakpoint,
} from '../../shared/helpFn';
import { cssDataCheck } from '../../shared/cssUtils';

/**
 * Generate dynamic CSS for featured image wrapper.
 * @param {Object} attributes block attributes.
 */
const dynamicCss = (attributes) => {
	if (!attributes) {
		return '';
	}

	const {
		uniqueId,
		hideOnDesktop = false,
		hideOnTablet = false,
		hideOnMobile = false,
		wrapperPadding,
		wrapperPaddingUnit,
		wrapperMargin,
		wrapperMarginUnit,
		wrapperBorder,
		wrapperBorderRadius,
		wrapperBorderRadiusUnit,
		wrapperBoxShadow,
	} = attributes || {};

	if (!uniqueId) {
		return '';
	}

	// Wrapper selector
	const wrapper = `.spssp-unique-id-${uniqueId}.spssp-featured-image-wrapper`;

	// --- Base Styles ---
	const baseStyles = [
		{
			class: wrapper,
			styles: {
				...borderCss(wrapperBorder),
				'border-radius': spacingGenerate(wrapperBorderRadius, wrapperBorderRadiusUnit),
			},
		},
		{
			class: `${wrapper}:hover`,
			styles: {
				'border-color': wrapperBorder?.hoverColor,
			},
		},
	];

	// Box shadow
	if (wrapperBoxShadow?.enable) {
		const { horizontal, vertical, blur, spread, color, opacity, inset } = wrapperBoxShadow;
		const shadowOpacity = opacity / 100;
		const shadowColor = color ? `${color}${Math.round(shadowOpacity * 255).toString(16).padStart(2, '0')}` : 'transparent';

		baseStyles.push({
			class: wrapper,
			styles: {
				'box-shadow': `${inset ? 'inset ' : ''}${horizontal}px ${vertical}px ${blur}px ${spread}px ${shadowColor}`,
			},
		});
	}

	// --- Device-specific CSS generation ---
	const responsiveCss = (deviceType) => {
		const hideOnDevice = {
			Desktop: hideOnDesktop,
			Tablet: hideOnTablet,
			Mobile: hideOnMobile,
		};

		return [
			{
				class: wrapper,
				styles: {
					padding: spacingGenerate(wrapperPadding, wrapperPaddingUnit, deviceType),
					margin: spacingGenerate(wrapperMargin, wrapperMarginUnit, deviceType),
				},
			},
			{
				class: `.spssp-unique-id-${uniqueId}`,
				styles: { display: hideOnDevice[deviceType] ? 'none' : 'block' },
			},
		];
	};

	// --- Build CSS for each device ---
	const desktopCss = [...baseStyles, ...responsiveCss('Desktop')];
	const desktopCssString = objectToCssString(desktopCss);

	const tabletBreakpoint = getGlobalBreakpoint().tablet;
	const tabletCss = responsiveCss('Tablet');
	const tabletCssString = wrapInMediaQuery(
		objectToCssString(tabletCss),
		`only screen and (max-width: ${tabletBreakpoint}px)`
	);

	const mobileBreakpoint = getGlobalBreakpoint().mobile;
	const mobileCss = responsiveCss('Mobile');
	const mobileCssString = wrapInMediaQuery(
		objectToCssString(mobileCss),
		`only screen and (max-width: ${mobileBreakpoint}px)`
	);

	return `${desktopCssString} ${tabletCssString} ${mobileCssString}`.trim();
};

export default dynamicCss;
