import { uid } from './block';

export type HiddenField = {
	id: string;
	label: string;
	prefillKey?: string;
	defaultValue?: string;
};

export const MAX_HIDDEN_FIELDS = 20;

export function createHiddenField( label = '' ): HiddenField {
	return {
		id: uid(),
		label,
	};
}

export function isHiddenFieldConfigured( field: HiddenField ): boolean {
	if ( ! field.label.trim() ) {
		return false;
	}

	const hasUrlParam = Boolean( field.prefillKey?.trim() );
	const hasDefault = Boolean( field.defaultValue?.trim() );

	return hasUrlParam || hasDefault;
}
