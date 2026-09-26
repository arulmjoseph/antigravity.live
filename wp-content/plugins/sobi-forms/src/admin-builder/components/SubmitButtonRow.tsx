import { useRef, useState } from 'react';
import { Settings } from 'lucide-react';
import type { Strings } from '../lib/i18n';
import { BuilderIcon } from './BuilderIcon';
import { SubmitButtonContextMenu } from './SubmitButtonContextMenu';

type Props = {
	strings: Strings;
	value: string;
	defaultText: string;
	onChange: ( value: string ) => void;
};

export function SubmitButtonRow( { strings, value, defaultText, onChange }: Props ) {
	const [ hovered, setHovered ] = useState( false );
	const [ menuOpen, setMenuOpen ] = useState( false );
	const [ menuAnchor, setMenuAnchor ] = useState<DOMRect | null>( null );
	const settingsRef = useRef<HTMLButtonElement>( null );

	const showHandles = hovered || menuOpen;
	const displayText = value.trim() || defaultText;

	const openMenu = () => {
		if ( settingsRef.current ) {
			setMenuAnchor( settingsRef.current.getBoundingClientRect() );
		}
		setMenuOpen( true );
	};

	const closeMenu = () => {
		setMenuOpen( false );
		setMenuAnchor( null );
	};

	return (
		<div
			className="sobiforms-block-row sobiforms-submit-button-row group relative flex items-start gap-3"
			onMouseEnter={ () => setHovered( true ) }
			onMouseLeave={ () => setHovered( false ) }
		>
			<input type="hidden" name="sobiforms_submit_button_text" value={ value } />

			<div
				className={ `sobiforms-block-row__handles sobiforms-submit-button-row__handles${
					showHandles ? ' is-visible' : ''
				}` }
				aria-hidden={ ! showHandles }
			>
				<button
					ref={ settingsRef }
					type="button"
					onClick={ () => ( menuOpen ? closeMenu() : openMenu() ) }
					className={ `sobiforms-block-row__handle sobiforms-handle-tooltip${
						menuOpen ? ' is-active' : ''
					}` }
					data-tooltip={ strings.ctaSettings }
					aria-label={ strings.ctaSettings }
					aria-expanded={ menuOpen }
				>
					<BuilderIcon icon={ Settings } />
				</button>
			</div>

			<div className="sobiforms-block-row__content relative min-w-0 flex-1 rounded-lg px-2 py-2 transition hover:bg-neutral-100">
				<p className="sobiforms-actions sobiforms-submit-button-row__actions">
					<button type="button" className="sobiforms-submit" disabled tabIndex={ -1 }>
						{ displayText }
					</button>
				</p>
			</div>

			<SubmitButtonContextMenu
				open={ menuOpen }
				anchor={ menuAnchor }
				anchorEl={ settingsRef.current }
				value={ value }
				defaultText={ defaultText }
				strings={ strings }
				onClose={ closeMenu }
				onChange={ onChange }
			/>
		</div>
	);
}
