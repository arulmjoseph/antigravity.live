/**
 * Field types — aligned with Sobiforms_Form_Repository::ALLOWED_TYPES.
 */
export type FieldType =
	| 'text'
	| 'email'
	| 'textarea'
	| 'checkbox'
	| 'phone'
	| 'number'
	| 'select'
	| 'radio'
	| 'url'
	| 'title'
	| 'paragraph'
	| 'file';

export type Block = {
	id: string;
	type: FieldType;
	label: string;
	placeholder: string;
	required: boolean;
	/** Paragraph layout block body copy. */
	content?: string;
	options?: string[];
	min?: number;
	max?: number;
	/** Text — min character count when limit enabled. */
	minLength?: number;
	/** Textarea / text — max character count when limit enabled. */
	maxLength?: number;
	/** Textarea only — visitor can drag to resize (default true). */
	resizable?: boolean;
	/** Show label above the field on the front-end (default true). */
	showLabel?: boolean;
	/** Enable URL query parameter prefill for this field. */
	urlPrefill?: boolean;
	/** URL query parameter key for client-side prefill. */
	prefillKey?: string;
	/** File upload — allowed extension keys (e.g. pdf, docx). */
	allowedExtensions?: string[];
	/** File upload — max size in megabytes. */
	maxSizeMb?: number;
};

export const DEFAULT_TEXTAREA_MAX_LENGTH = 500;
export const MAX_TEXTAREA_MAX_LENGTH = 10000;
export const DEFAULT_TEXT_MAX_LENGTH = 255;
export const MAX_TEXT_MAX_LENGTH = 255;
export const MAX_FIELD_OPTIONS = 20;
export const DEFAULT_TITLE_TEXT = 'Section title';
export const DEFAULT_PARAGRAPH_TEXT = 'Add instructions or context for visitors.';

export const LAYOUT_TYPES: FieldType[] = [ 'title', 'paragraph' ];

export const PREFILL_SCALAR_TYPES: FieldType[] = [
	'text',
	'email',
	'phone',
	'url',
	'number',
	'textarea',
];

export function isLayoutBlock( type: FieldType ): boolean {
	return LAYOUT_TYPES.includes( type );
}

export function isScalarPrefillField( type: FieldType ): boolean {
	return PREFILL_SCALAR_TYPES.includes( type );
}

export type SlashItem = {
	type: FieldType;
	labelKey: string;
	descriptionKey: string;
	keywords?: string[];
};

export const DEFAULT_OPTIONS = [ 'Option 1', 'Option 2' ];

/** Picker entries (layout blocks). */
export const LAYOUT_ITEMS: SlashItem[] = [
	{ type: 'title', labelKey: 'title', descriptionKey: 'titleDesc', keywords: [ 'heading', 'title', 'section' ] },
	{ type: 'paragraph', labelKey: 'paragraph', descriptionKey: 'paragraphDesc', keywords: [ 'paragraph', 'text', 'instructions' ] },
];

/** Picker entries (questions). */
export const SLASH_ITEMS: SlashItem[] = [
	{ type: 'text', labelKey: 'shortText', descriptionKey: 'shortTextDesc', keywords: [ 'text', 'input', 'short' ] },
	{ type: 'textarea', labelKey: 'longText', descriptionKey: 'longTextDesc', keywords: [ 'textarea', 'long', 'message' ] },
	{ type: 'email', labelKey: 'email', descriptionKey: 'emailDesc', keywords: [ 'email', 'mail' ] },
	{ type: 'url', labelKey: 'link', descriptionKey: 'linkDesc', keywords: [ 'link', 'url', 'website' ] },
	{ type: 'phone', labelKey: 'phone', descriptionKey: 'phoneDesc', keywords: [ 'phone', 'tel', 'mobile' ] },
	{ type: 'number', labelKey: 'number', descriptionKey: 'numberDesc', keywords: [ 'number', 'numeric' ] },
	{ type: 'select', labelKey: 'select', descriptionKey: 'selectDesc', keywords: [ 'select', 'dropdown' ] },
	{ type: 'radio', labelKey: 'radio', descriptionKey: 'radioDesc', keywords: [ 'radio', 'choice' ] },
	{ type: 'checkbox', labelKey: 'checkbox', descriptionKey: 'checkboxDesc', keywords: [ 'checkbox', 'consent' ] },
	{ type: 'file', labelKey: 'fileUpload', descriptionKey: 'fileUploadDesc', keywords: [ 'file', 'upload', 'attachment', 'document' ] },
];

export function uid(): string {
	return 'field_' + Math.random().toString( 36 ).slice( 2, 10 );
}

export function defaultOptions(): string[] {
	return [ ...DEFAULT_OPTIONS ];
}

export function createBlock( type: FieldType, label = '' ): Block {
	const block: Block = {
		id: uid(),
		type,
		label: '',
		placeholder: '',
		required: false,
		showLabel: true,
	};

	if ( type === 'title' ) {
		block.label = label || DEFAULT_TITLE_TEXT;
		return block;
	}

	if ( type === 'paragraph' ) {
		block.content = label || DEFAULT_PARAGRAPH_TEXT;
		return block;
	}

	block.label = label;

	if ( type === 'select' || type === 'radio' || type === 'checkbox' ) {
		block.options = defaultOptions();
	}

	if ( type === 'textarea' ) {
		block.resizable = true;
	}

	if ( type === 'file' ) {
		block.allowedExtensions = [];
		block.maxSizeMb = 5;
	}

	return block;
}

/** Letter badge for choice tile previews (A, B, C…). */
export function choiceBadgeLetter( index: number ): string {
	return String.fromCharCode( 65 + ( index % 26 ) );
}

export function blockHasChoiceOptions( block: Block ): boolean {
	return (
		block.type === 'select' ||
		block.type === 'radio' ||
		( block.type === 'checkbox' && block.options && block.options.length > 0 )
	);
}

export function patchTypeChange( block: Block, type: FieldType ): Partial<Block> {
	if ( isLayoutBlock( type ) && isLayoutBlock( block.type ) ) {
		const patch: Partial<Block> = {
			type,
			required: false,
			placeholder: '',
			options: undefined,
			min: undefined,
			max: undefined,
			minLength: undefined,
			maxLength: undefined,
			resizable: undefined,
			showLabel: undefined,
		};

		if ( type === 'title' && block.type === 'paragraph' ) {
			patch.label = block.content?.trim() || DEFAULT_TITLE_TEXT;
			patch.content = undefined;
		} else if ( type === 'paragraph' && block.type === 'title' ) {
			patch.label = '';
			patch.content = block.label.trim() || DEFAULT_PARAGRAPH_TEXT;
		}

		return patch;
	}

	const patch: Partial<Block> = { type };

	const needsOptions =
		type === 'select' || type === 'radio' || type === 'checkbox';
	const minOptions = type === 'checkbox' ? 1 : 2;

	if ( needsOptions ) {
		const current = block.options ?? [];
		if ( current.length >= minOptions ) {
			patch.options = [ ...current ];
		} else {
			patch.options = defaultOptions();
		}
	}

	if ( type === 'textarea' && block.resizable === undefined ) {
		patch.resizable = true;
	}

	if ( type !== 'number' ) {
		patch.min = undefined;
		patch.max = undefined;
	}

	if ( type !== 'text' && type !== 'textarea' ) {
		patch.minLength = undefined;
		patch.maxLength = undefined;
	}

	if ( type !== 'textarea' ) {
		patch.resizable = undefined;
	}

	if ( type === 'paragraph' ) {
		patch.label = '';
		patch.content = block.label.trim() || DEFAULT_PARAGRAPH_TEXT;
	}

	if ( type === 'title' && block.type === 'paragraph' ) {
		patch.label = block.content?.trim() || DEFAULT_TITLE_TEXT;
		patch.content = undefined;
	}

	if ( ! isScalarPrefillField( type ) ) {
		patch.prefillKey = undefined;
		patch.urlPrefill = undefined;
	}

	if ( type === 'file' ) {
		patch.allowedExtensions = [];
		patch.maxSizeMb = 5;
	} else {
		patch.allowedExtensions = undefined;
		patch.maxSizeMb = undefined;
	}

	return patch;
}
