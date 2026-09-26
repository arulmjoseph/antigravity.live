import { LAYOUT_ITEMS, SLASH_ITEMS, SlashItem } from '../types/block';
import type { Strings } from './i18n';

export type ResolvedSlashItem = SlashItem & { label: string; description: string };

function resolveItems( items: SlashItem[], strings: Strings ): ResolvedSlashItem[] {
	return items.map( ( item ) => ( {
		...item,
		label: strings[ item.labelKey as keyof Strings ] as string,
		description: strings[ item.descriptionKey as keyof Strings ] as string,
	} ) );
}

export function resolveLayoutItems( strings: Strings ): ResolvedSlashItem[] {
	return resolveItems( LAYOUT_ITEMS, strings );
}

export function resolveSlashItems( strings: Strings ): ResolvedSlashItem[] {
	return resolveItems( SLASH_ITEMS, strings );
}

export function filterSlashItems( items: ResolvedSlashItem[], query: string ): ResolvedSlashItem[] {
	const q = query.trim().toLowerCase();
	if ( ! q ) {
		return items;
	}
	return items.filter(
		( item ) =>
			item.label.toLowerCase().includes( q ) ||
			item.description.toLowerCase().includes( q ) ||
			item.keywords?.some( ( k ) => k.includes( q ) )
	);
}
