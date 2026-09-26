type Props = {
	label: string;
	checked: boolean;
	onChange: ( checked: boolean ) => void;
};

export function ContextMenuToggle( { label, checked, onChange }: Props ) {
	return (
		<label className="sobiforms-field-context-menu__toggle-row">
			<span className="sobiforms-field-context-menu__toggle-label">{ label }</span>
			<span className="sobiforms-context-menu-switch">
				<input
					type="checkbox"
					className="sobiforms-context-menu-switch__input"
					checked={ checked }
					onChange={ ( e ) => onChange( e.target.checked ) }
				/>
				<span className="sobiforms-context-menu-switch__track" aria-hidden="true">
					<span className="sobiforms-context-menu-switch__thumb" />
				</span>
			</span>
		</label>
	);
}
