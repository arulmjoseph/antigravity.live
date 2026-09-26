import { useEffect, useMemo, useRef, useState } from 'react';
import { Check } from 'lucide-react';
import type { Strings } from '../lib/i18n';
import {
	FILE_EXTENSION_GROUP_ORDER,
	type FileExtensionGroup,
	type FileExtensionOption,
} from '../lib/fileExtensions';

type Props = {
	options: FileExtensionOption[];
	selected: string[];
	strings: Strings;
	onChange: ( extensions: string[] ) => void;
};

const GROUP_LABEL_KEYS: Record< FileExtensionGroup, keyof Strings > = {
	application: 'fileExtensionGroupApplication',
	image: 'fileExtensionGroupImage',
	text: 'fileExtensionGroupText',
};

const GROUP_ALL_LABEL_KEYS: Record< FileExtensionGroup, keyof Strings > = {
	application: 'fileExtensionGroupAllApplication',
	image: 'fileExtensionGroupAllImage',
	text: 'fileExtensionGroupAllText',
};

export function FileExtensionsPicker( { options, selected, strings, onChange }: Props ) {
	const [ open, setOpen ] = useState( false );
	const rootRef = useRef<HTMLDivElement>( null );

	const selectedSet = useMemo( () => new Set( selected ), [ selected ] );

	const grouped = useMemo( () => {
		const map: Record< FileExtensionGroup, FileExtensionOption[] > = {
			application: [],
			image: [],
			text: [],
		};
		for ( const option of options ) {
			if ( map[ option.group ] ) {
				map[ option.group ].push( option );
			}
		}
		return map;
	}, [ options ] );

	const summary = useMemo( () => {
		if ( selected.length === 0 ) {
			return strings.fileExtensionsNone;
		}
		const labels = options
			.filter( ( option ) => selectedSet.has( option.ext ) )
			.map( ( option ) => option.label );
		if ( labels.length <= 3 ) {
			return labels.join( ', ' );
		}
		return strings.fileExtensionsSummary.replace( '%d', String( labels.length ) );
	}, [ options, selected.length, selectedSet, strings ] );

	useEffect( () => {
		if ( ! open ) {
			return;
		}
		const handlePointerDown = ( event: MouseEvent ) => {
			if ( ! rootRef.current?.contains( event.target as Node ) ) {
				setOpen( false );
			}
		};
		document.addEventListener( 'mousedown', handlePointerDown );
		return () => document.removeEventListener( 'mousedown', handlePointerDown );
	}, [ open ] );

	const applySelection = ( next: Set< string > ) => {
		if ( next.size < 1 ) {
			return;
		}
		onChange( Array.from( next ) );
	};

	const toggleExtension = ( ext: string ) => {
		const next = new Set( selected );
		if ( next.has( ext ) ) {
			if ( next.size <= 1 ) {
				return;
			}
			next.delete( ext );
		} else {
			next.add( ext );
		}
		applySelection( next );
	};

	const toggleGroup = ( group: FileExtensionGroup, groupExts: string[] ) => {
		const next = new Set( selected );
		const allSelected = groupExts.every( ( ext ) => next.has( ext ) );

		if ( allSelected ) {
			for ( const ext of groupExts ) {
				if ( next.size <= 1 ) {
					break;
				}
				next.delete( ext );
			}
		} else {
			for ( const ext of groupExts ) {
				next.add( ext );
			}
		}

		applySelection( next );
	};

	const renderGroup = ( group: FileExtensionGroup, items: FileExtensionOption[] ) => {
		if ( items.length === 0 ) {
			return null;
		}

		const groupExts = items.map( ( item ) => item.ext );
		const allSelected = groupExts.every( ( ext ) => selectedSet.has( ext ) );
		const someSelected = groupExts.some( ( ext ) => selectedSet.has( ext ) );

		return (
			<div key={ group } className="sobiforms-file-ext-picker__group">
				<div className="sobiforms-file-ext-picker__group-label">
					{ strings[ GROUP_LABEL_KEYS[ group ] ] }
				</div>
				<button
					type="button"
					className="sobiforms-file-ext-picker__row"
					onClick={ () => toggleGroup( group, groupExts ) }
				>
					<span className="sobiforms-file-ext-picker__row-label">
						{ strings[ GROUP_ALL_LABEL_KEYS[ group ] ] }
					</span>
					<span className="sobiforms-file-ext-picker__row-check" aria-hidden="true">
						{ allSelected ? <Check size={ 16 } strokeWidth={ 2.25 } /> : null }
						{ ! allSelected && someSelected ? (
							<span className="sobiforms-file-ext-picker__row-partial">−</span>
						) : null }
					</span>
				</button>
				{ items.map( ( option ) => (
					<button
						key={ option.ext }
						type="button"
						className="sobiforms-file-ext-picker__row"
						onClick={ () => toggleExtension( option.ext ) }
					>
						<span className="sobiforms-file-ext-picker__row-label">.{ option.ext }</span>
						<span className="sobiforms-file-ext-picker__row-check" aria-hidden="true">
							{ selectedSet.has( option.ext ) ? (
								<Check size={ 16 } strokeWidth={ 2.25 } />
							) : null }
						</span>
					</button>
				) ) }
			</div>
		);
	};

	return (
		<div
			ref={ rootRef }
			className={ `sobiforms-file-ext-picker${ open ? ' is-open' : '' }` }
		>
			<span className="sobiforms-file-ext-picker__heading">{ strings.fileAllowedExtensions }</span>
			<button
				type="button"
				className="sobiforms-file-ext-picker__trigger"
				onClick={ () => setOpen( ( value ) => ! value ) }
				aria-expanded={ open }
			>
				{ summary }
			</button>
			{ open && (
				<div className="sobiforms-file-ext-picker__panel">
					<div className="sobiforms-file-ext-picker__list">
						{ FILE_EXTENSION_GROUP_ORDER.map( ( group ) =>
							renderGroup( group, grouped[ group ] )
						) }
					</div>
				</div>
			) }
		</div>
	);
}
