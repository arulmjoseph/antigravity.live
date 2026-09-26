import { Plus } from 'lucide-react';
import type { Strings } from '../lib/i18n';
import { BuilderIcon } from './BuilderIcon';

type Props = {
	strings: Strings;
	onAdd: () => void;
};

export function AddFieldSlot( { strings, onAdd }: Props ) {
	return (
		<div className="sobiforms-add-field-slot">
			<button
				type="button"
				className="sobiforms-add-field-slot__button"
				onClick={ onAdd }
				aria-label={ strings.addField }
			>
				<span className="sobiforms-add-field-slot__line" aria-hidden="true" />
				<span className="sobiforms-add-field-slot__icon">
					<BuilderIcon icon={ Plus } />
				</span>
				<span className="sobiforms-add-field-slot__line" aria-hidden="true" />
			</button>
		</div>
	);
}
