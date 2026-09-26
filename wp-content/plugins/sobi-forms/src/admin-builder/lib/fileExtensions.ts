export type FileExtensionGroup = 'application' | 'image' | 'text';

export type FileExtensionOption = {
	ext: string;
	label: string;
	group: FileExtensionGroup;
};

export const FILE_EXTENSION_GROUP_ORDER: FileExtensionGroup[] = [
	'application',
	'image',
	'text',
];

export function defaultFileExtensions( options: FileExtensionOption[] ): string[] {
	return options
		.filter( ( option ) => option.group === 'application' )
		.map( ( option ) => option.ext );
}

export function normalizeFileExtensions(
	raw: unknown,
	options: FileExtensionOption[]
): string[] {
	const allowed = new Set( options.map( ( option ) => option.ext ) );
	const extensions: string[] = [];

	if ( Array.isArray( raw ) ) {
		for ( const ext of raw ) {
			if ( typeof ext === 'string' && allowed.has( ext ) && ! extensions.includes( ext ) ) {
				extensions.push( ext );
			}
		}
	}

	return extensions.length > 0 ? extensions : defaultFileExtensions( options );
}
