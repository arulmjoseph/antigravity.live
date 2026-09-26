import { Plus, Trash2 } from 'lucide-react';
import type { Strings } from '../lib/i18n';
import { createHiddenField, isHiddenFieldConfigured, MAX_HIDDEN_FIELDS, type HiddenField } from '../types/hidden-field';
import { BuilderIcon } from './BuilderIcon';

type Props = {
	strings: Strings;
	fields: HiddenField[];
	onChange: ( fields: HiddenField[] ) => void;
};

export function HiddenFieldsSection( { strings, fields, onChange }: Props ) {
	const updateField = ( index: number, patch: Partial<HiddenField> ) => {
		onChange(
			fields.map( ( field, i ) => ( i === index ? { ...field, ...patch } : field ) )
		);
	};

	const removeField = ( index: number ) => {
		const next = fields.filter( ( _, i ) => i !== index );
		onChange( next.length > 0 ? next : [ createHiddenField() ] );
	};

	const canAddHiddenField = fields.every( isHiddenFieldConfigured );

	const addField = () => {
		if ( fields.length >= MAX_HIDDEN_FIELDS || ! canAddHiddenField ) {
			return;
		}
		onChange( [ ...fields, createHiddenField() ] );
	};

	return (
		<div className="sobiforms-hidden-fields">
			<div className="sobiforms-hidden-fields__table" role="table">
				<div className="sobiforms-hidden-fields__head" role="row">
					<span role="columnheader">{ strings.hiddenFieldLabel }</span>
					<span role="columnheader">{ strings.hiddenFieldParam }</span>
					<span role="columnheader">{ strings.hiddenFieldDefault }</span>
					<span className="sobiforms-hidden-fields__head-actions" aria-hidden="true" />
				</div>
				{ fields.map( ( field, index ) => (
					<div key={ field.id } className="sobiforms-hidden-fields__row" role="row">
						<input
							type="text"
							className="sobiforms-hidden-fields__input"
							value={ field.label }
							onChange={ ( e ) => updateField( index, { label: e.target.value } ) }
							placeholder={ strings.hiddenFieldLabelPlaceholder }
							aria-label={ strings.hiddenFieldLabel }
						/>
						<input
							type="text"
							className="sobiforms-hidden-fields__input"
							value={ field.prefillKey ?? '' }
							onChange={ ( e ) => updateField( index, { prefillKey: e.target.value } ) }
							placeholder={ strings.hiddenFieldParamPlaceholder }
							aria-label={ strings.hiddenFieldParam }
						/>
						<input
							type="text"
							className="sobiforms-hidden-fields__input"
							value={ field.defaultValue ?? '' }
							onChange={ ( e ) => updateField( index, { defaultValue: e.target.value } ) }
							placeholder={ strings.hiddenFieldDefaultPlaceholder }
							aria-label={ strings.hiddenFieldDefault }
						/>
						<button
							type="button"
							className="sobiforms-hidden-fields__delete"
							onClick={ () => removeField( index ) }
							aria-label={ strings.removeHiddenField }
						>
							<BuilderIcon icon={ Trash2 } size={ 14 } />
						</button>
					</div>
				) ) }
			</div>
			<button
				type="button"
				className="sobiforms-hidden-fields__add"
				onClick={ addField }
				disabled={ fields.length >= MAX_HIDDEN_FIELDS || ! canAddHiddenField }
			>
				<BuilderIcon icon={ Plus } size={ 14 } />
				<span>{ strings.addHiddenField }</span>
			</button>
		</div>
	);
}
