import { useEffect, useMemo, useRef } from 'react';
import { filterSlashItems, ResolvedSlashItem } from '../lib/filterSlashItems';
import type { Strings } from '../lib/i18n';

type Props = {
	items: ResolvedSlashItem[];
	query: string;
	selectedIndex: number;
	strings: Strings;
	onSelect: ( item: ResolvedSlashItem ) => void;
	onIndexChange: ( index: number ) => void;
};

export function SlashMenu( { items, query, selectedIndex, strings, onSelect, onIndexChange }: Props ) {
	const listRef = useRef<HTMLDivElement>( null );
	const filtered = useMemo( () => filterSlashItems( items, query ), [ items, query ] );

	useEffect( () => {
		const el = listRef.current?.querySelector( `[data-index="${ selectedIndex }"]` );
		el?.scrollIntoView( { block: 'nearest' } );
	}, [ selectedIndex ] );

	if ( filtered.length === 0 ) {
		return (
			<div className="absolute left-0 top-full z-50 mt-1 w-72 rounded-lg border border-neutral-200 bg-white p-3 text-sm text-neutral-500 shadow-lg">
				{ strings.noResults }
			</div>
		);
	}

	return (
		<div
			ref={ listRef }
			className="absolute left-0 top-full z-50 mt-1 max-h-64 w-72 overflow-y-auto rounded-lg border border-neutral-200 bg-white py-2 shadow-xl"
			role="listbox"
			aria-label={ strings.questions }
		>
			<div className="px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-neutral-400">
				{ strings.questions }
			</div>
			{ filtered.map( ( item, index ) => {
				const selected = index === selectedIndex;
				return (
					<button
						key={ item.type }
						type="button"
						data-index={ index }
						role="option"
						aria-selected={ selected }
						className={ `flex w-full flex-col items-start px-3 py-2 text-left transition ${
							selected ? 'bg-neutral-100' : 'hover:bg-neutral-50'
						}` }
						onMouseEnter={ () => onIndexChange( index ) }
						onMouseDown={ ( e ) => {
							e.preventDefault();
							onSelect( item );
						} }
					>
						<span className="text-sm font-medium text-neutral-900">{ item.label }</span>
						<span className="text-xs text-neutral-500">{ item.description }</span>
					</button>
				);
			} ) }
		</div>
	);
}
