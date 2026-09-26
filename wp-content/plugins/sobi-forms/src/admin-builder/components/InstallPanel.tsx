import { useState } from 'react';
import type { Strings } from '../lib/i18n';

type Props = {
	strings: Strings;
	formId: number;
	slug: string;
	variant?: 'tab' | 'overlay';
};

function copyText( text: string ): Promise<void> | void {
	if ( navigator.clipboard && window.isSecureContext ) {
		return navigator.clipboard.writeText( text );
	}
	const textarea = document.createElement( 'textarea' );
	textarea.value = text;
	textarea.setAttribute( 'readonly', '' );
	textarea.style.position = 'fixed';
	textarea.style.left = '-9999px';
	document.body.appendChild( textarea );
	textarea.select();
	try {
		document.execCommand( 'copy' );
	} finally {
		document.body.removeChild( textarea );
	}
}

export function InstallPanel( { strings, formId, slug, variant = 'tab' }: Props ) {
	const panelClass =
		variant === 'overlay'
			? 'sobiforms-install-panel sobiforms-install-panel--overlay'
			: 'sobiforms-builder-tab-panel max-w-2xl space-y-6 py-4';
	const [ copied, setCopied ] = useState( false );

	if ( ! formId ) {
		return (
			<div className={ panelClass }>
				<p className="text-sm text-neutral-600">{ strings.installSaveFirst }</p>
			</div>
		);
	}

	const shortcodeId = `[sobiforms id="${ formId }"]`;
	const shortcodeSlug = slug ? `[sobiforms slug="${ slug }"]` : '';

	const handleCopy = async () => {
		try {
			await copyText( shortcodeId );
			setCopied( true );
			window.setTimeout( () => setCopied( false ), 2000 );
		} catch {
			// ignore
		}
	};

	return (
		<div className={ panelClass }>
			<section>
				<h3 className="mb-2 text-sm font-semibold text-neutral-900">{ strings.installShortcode }</h3>
				<div className="flex flex-wrap items-center gap-2">
					<code className="rounded bg-neutral-100 px-2 py-1 text-sm">{ shortcodeId }</code>
					<button type="button" className="button button-small" onClick={ handleCopy }>
						{ copied ? strings.copied : strings.copyShortcode }
					</button>
				</div>
				{ shortcodeSlug && (
					<p className="mt-2 text-xs text-neutral-500">
						{ strings.shortcodeBySlug }
						<code className="ml-1 rounded bg-neutral-100 px-1.5 py-0.5 text-xs">{ shortcodeSlug }</code>
					</p>
				) }
			</section>

			<section>
				<h3 className="mb-2 text-sm font-semibold text-neutral-900">{ strings.installBlock }</h3>
				<ol className="list-decimal space-y-2 pl-5 text-sm text-neutral-700">
					<li>{ strings.installBlockStep1 }</li>
					<li>{ strings.installBlockStep2 }</li>
					<li>{ strings.installBlockStep3 }</li>
				</ol>
			</section>

			<section>
				<h3 className="mb-2 text-sm font-semibold text-neutral-900">{ strings.installAnywhere }</h3>
				<p className="text-sm text-neutral-600">{ strings.installAnywhereDesc }</p>
			</section>
		</div>
	);
}
