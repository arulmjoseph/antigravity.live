/** Display close datetime in the header status badge. */
export function formatCloseAtBadge( closeAt: string ): string {
	if ( ! closeAt.trim() ) {
		return '';
	}
	const d = new Date( closeAt );
	if ( Number.isNaN( d.getTime() ) ) {
		return closeAt;
	}
	return d.toLocaleString( undefined, {
		month: 'short',
		day: 'numeric',
		year: 'numeric',
		hour: 'numeric',
		minute: '2-digit',
	} );
}
