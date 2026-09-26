import { useState, type ReactNode } from 'react';
import { ChevronDown } from 'lucide-react';
import { BuilderIcon } from './BuilderIcon';

type HintStatus = 'paused' | 'scheduled' | 'limit';

type Props = {
	title: string;
	hint?: string;
	hintStatus?: HintStatus;
	defaultOpen?: boolean;
	children: ReactNode;
};

export function CollapsibleSection( {
	title,
	hint,
	hintStatus,
	defaultOpen = false,
	children,
}: Props ) {
	const [ open, setOpen ] = useState( defaultOpen );

	return (
		<section className="sobiforms-settings-section">
			<button
				type="button"
				className="sobiforms-settings-section__trigger"
				onClick={ () => setOpen( ( v ) => ! v ) }
				aria-expanded={ open }
			>
				<span className="sobiforms-settings-section__trigger-text">
					<span className="sobiforms-settings-section__title">{ title }</span>
					{ hint && (
						<span
							className={
								'sobiforms-settings-section__hint' +
								( hintStatus ? ` is-status-${ hintStatus }` : '' )
							}
						>
							{ hint }
						</span>
					) }
				</span>
				<span
					className={ `sobiforms-settings-section__chevron${ open ? ' is-open' : '' }` }
					aria-hidden="true"
				>
					<BuilderIcon icon={ ChevronDown } />
				</span>
			</button>
			<div
				className={
					'sobiforms-settings-section__body' + ( open ? '' : ' is-collapsed' )
				}
			>
				{ children }
			</div>
		</section>
	);
}
