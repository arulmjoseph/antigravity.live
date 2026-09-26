import { Block, FieldType, createBlock, defaultOptions, isLayoutBlock, isScalarPrefillField, uid } from '../types/block';
import type { HiddenField } from '../types/hidden-field';
import {
	defaultFileExtensions,
	normalizeFileExtensions,
	type FileExtensionOption,
} from './fileExtensions';

const ALLOWED: FieldType[] = [
	'text',
	'email',
	'textarea',
	'checkbox',
	'phone',
	'number',
	'select',
	'radio',
	'url',
	'title',
	'paragraph',
	'file',
];

export type LegacyField = {
	id: string;
	type: FieldType | 'hidden';
	label: string;
	placeholder?: string;
	required?: boolean;
	content?: string;
	options?: string[];
	min?: number;
	max?: number;
	resizable?: boolean;
	minLength?: number;
	maxLength?: number;
	showLabel?: boolean;
	urlPrefill?: boolean;
	prefillKey?: string;
	defaultValue?: string;
	allowedExtensions?: string[];
	maxSizeMb?: number;
};

type RawField = LegacyField;

export type ParseFieldsSchemaOptions = {
	fileExtensionOptions?: FileExtensionOption[];
};

function mapRowToBlock( row: RawField, fileExtensionOptions: FileExtensionOption[] ): Block | null {
	if ( row.type === 'hidden' || ! ALLOWED.includes( row.type as FieldType ) ) {
		return null;
	}

	if ( isLayoutBlock( row.type as FieldType ) ) {
		const block: Block = {
			id: row.id || uid(),
			type: row.type as FieldType,
			label: row.type === 'title' ? row.label || '' : '',
			placeholder: '',
			required: false,
		};

		if ( row.type === 'paragraph' ) {
			block.content = row.content || '';
		}

		return block;
	}

	const block: Block = {
		id: row.id || uid(),
		type: row.type as FieldType,
		label: row.label || '',
		placeholder: row.placeholder || '',
		required: !! row.required,
		showLabel: row.showLabel !== false,
	};

	if ( row.type === 'select' || row.type === 'radio' ) {
		block.options =
			Array.isArray( row.options ) && row.options.length >= 2
				? row.options.map( ( o ) => String( o ) )
				: defaultOptions();
	}

	if ( row.type === 'checkbox' && Array.isArray( row.options ) && row.options.length >= 1 ) {
		block.options = row.options.map( ( o ) => String( o ) );
	}

	if ( row.type === 'number' ) {
		if ( typeof row.min === 'number' ) {
			block.min = row.min;
		}
		if ( typeof row.max === 'number' ) {
			block.max = row.max;
		}
	}

	if ( row.type === 'textarea' ) {
		block.resizable = row.resizable !== false;
		if ( typeof row.maxLength === 'number' && row.maxLength > 0 ) {
			block.maxLength = row.maxLength;
		}
	}

	if ( row.type === 'text' ) {
		if ( typeof row.minLength === 'number' && row.minLength >= 0 ) {
			block.minLength = row.minLength;
		}
		if ( typeof row.maxLength === 'number' && row.maxLength > 0 ) {
			block.maxLength = row.maxLength;
		}
	}

	if ( row.prefillKey ) {
		block.prefillKey = row.prefillKey;
	}
	if ( isScalarPrefillField( row.type as FieldType ) ) {
		block.urlPrefill = row.urlPrefill === true || !! row.prefillKey;
	}

	if ( row.type === 'file' ) {
		block.allowedExtensions = normalizeFileExtensions( row.allowedExtensions, fileExtensionOptions );
		block.maxSizeMb =
			typeof row.maxSizeMb === 'number' && row.maxSizeMb > 0 ? row.maxSizeMb : 5;
	}

	return block;
}

function mapRowToHiddenField( row: RawField ): HiddenField | null {
	if ( row.type !== 'hidden' || typeof row.id !== 'string' ) {
		return null;
	}

	const field: HiddenField = {
		id: row.id || uid(),
		label: row.label || '',
	};

	if ( row.prefillKey ) {
		field.prefillKey = row.prefillKey;
	}
	if ( row.defaultValue !== undefined && row.defaultValue !== '' ) {
		field.defaultValue = row.defaultValue;
	} else if ( row.defaultValue === '' ) {
		field.defaultValue = '';
	}

	return field;
}

/** Split saved fields JSON into canvas blocks and sidebar hidden fields. */
export function parseFieldsSchema(
	fields: unknown,
	options: ParseFieldsSchemaOptions = {}
): { blocks: Block[]; hiddenFields: HiddenField[] } {
	const fileExtensionOptions = options.fileExtensionOptions ?? [];

	if ( ! Array.isArray( fields ) ) {
		return { blocks: [], hiddenFields: [] };
	}

	const blocks: Block[] = [];
	const hiddenFields: HiddenField[] = [];

	for ( const row of fields ) {
		if ( ! row || typeof row !== 'object' ) {
			continue;
		}

		const raw = row as RawField;
		if ( raw.type === 'hidden' ) {
			const hidden = mapRowToHiddenField( raw );
			if ( hidden ) {
				hiddenFields.push( hidden );
			}
			continue;
		}

		const block = mapRowToBlock( raw, fileExtensionOptions );
		if ( block ) {
			if ( block.type === 'file' && block.allowedExtensions?.length === 0 ) {
				block.allowedExtensions = defaultFileExtensions( fileExtensionOptions );
			}
			blocks.push( block );
		}
	}

	return { blocks, hiddenFields };
}

/** Hydrate canvas blocks from saved form fields JSON. */
export function fromFieldsSchema( fields: unknown ): Block[] {
	return parseFieldsSchema( fields ).blocks;
}

/** Serialize blocks and hidden fields to legacy fields JSON for PHP sanitize_fields_schema(). */
export function toFieldsSchema( blocks: Block[], hiddenFields: HiddenField[] = [] ): LegacyField[] {
	const visible = blocks.map( ( block ) => {
		if ( isLayoutBlock( block.type ) ) {
			const field: LegacyField = {
				id: block.id,
				type: block.type,
				label: block.type === 'title' ? block.label : '',
				placeholder: '',
				required: false,
			};

			if ( block.type === 'paragraph' ) {
				field.content = block.content ?? '';
			}

			return field;
		}

		const field: LegacyField = {
			id: block.id,
			type: block.type,
			label: block.label,
			placeholder: block.placeholder,
			required: block.required,
		};

		if ( block.type === 'select' || block.type === 'radio' ) {
			const opts = ( block.options ?? [] ).map( ( o ) => o.trim() ).filter( Boolean );
			field.options = opts.length >= 2 ? opts : defaultOptions();
		}

		if ( block.type === 'checkbox' && block.options && block.options.length > 0 ) {
			const opts = block.options.map( ( o ) => o.trim() ).filter( Boolean );
			if ( opts.length >= 1 ) {
				field.options = opts;
			}
		}

		if ( block.type === 'number' ) {
			if ( block.min !== undefined ) {
				field.min = block.min;
			}
			if ( block.max !== undefined ) {
				field.max = block.max;
			}
		}

		if ( block.type === 'textarea' ) {
			if ( block.resizable === false ) {
				field.resizable = false;
			}
			if ( typeof block.maxLength === 'number' && block.maxLength > 0 ) {
				field.maxLength = block.maxLength;
			}
		}

		if ( block.type === 'text' ) {
			if ( typeof block.minLength === 'number' && block.minLength >= 0 ) {
				field.minLength = block.minLength;
			}
			if ( typeof block.maxLength === 'number' && block.maxLength > 0 ) {
				field.maxLength = block.maxLength;
			}
		}

		if ( block.type !== 'checkbox' && block.showLabel === false ) {
			field.showLabel = false;
		}

		if ( isScalarPrefillField( block.type ) && block.urlPrefill ) {
			field.urlPrefill = true;
			if ( block.prefillKey?.trim() ) {
				field.prefillKey = block.prefillKey.trim();
			}
		}

		if ( block.type === 'file' ) {
			field.allowedExtensions = ( block.allowedExtensions ?? [] ).filter( ( ext ) => ext !== '' );
			field.maxSizeMb = block.maxSizeMb ?? 5;
		}

		return field;
	} );

	const hidden = hiddenFields
		.map( ( field ) => ( {
			id: field.id,
			type: 'hidden' as const,
			label: field.label.trim(),
			placeholder: '',
			required: false,
			prefillKey: field.prefillKey?.trim() || undefined,
			defaultValue: field.defaultValue?.trim() || undefined,
		} ) )
		.filter( ( field ) => field.label !== '' );

	return [ ...visible, ...hidden ];
}

/** Default blocks for a brand-new form. */
export function defaultBlocks(): Block[] {
	return [
		createBlock( 'text', 'Name' ),
		createBlock( 'email', 'Email' ),
		createBlock( 'textarea', 'Message' ),
	];
}
