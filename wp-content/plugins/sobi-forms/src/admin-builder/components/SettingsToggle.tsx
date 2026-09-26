type Props = {
	label: string;
	hint?: string;
	name?: string;
	checked: boolean;
	disabled?: boolean;
	title?: string;
	onChange: ( checked: boolean ) => void;
};

export function SettingsToggle( {
	label,
	hint,
	name,
	checked,
	disabled = false,
	title,
	onChange,
}: Props ) {
	return (
		<label
			className={ `sobiforms-settings-toggle${ disabled ? ' is-disabled' : '' }` }
			title={ title }
		>
			<span className="sobiforms-settings-toggle__text">
				<span className="sobiforms-settings-toggle__label">{ label }</span>
				{ hint && <span className="sobiforms-settings-toggle__hint">{ hint }</span> }
			</span>
			{ name && (
				<input
					type="hidden"
					name={ name }
					value={ disabled ? ( checked ? '1' : '0' ) : '0' }
				/>
			) }
			<span className="sobiforms-context-menu-switch">
				<input
					type="checkbox"
					className="sobiforms-context-menu-switch__input"
					name={ name }
					value="1"
					checked={ checked }
					disabled={ disabled }
					onChange={ ( e ) => onChange( e.target.checked ) }
				/>
				<span className="sobiforms-context-menu-switch__track" aria-hidden="true">
					<span className="sobiforms-context-menu-switch__thumb" />
				</span>
			</span>
		</label>
	);
}
