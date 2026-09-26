import { createRoot } from 'react-dom/client';
import { useMemo } from 'react';
import { FormEditor } from './components/FormEditor';
import { useBlocksReducer } from './hooks/useBlocksReducer';
import { getStrings } from './lib/i18n';
import { defaultBlocks, parseFieldsSchema } from './lib/schema';
import type { FileExtensionOption } from './lib/fileExtensions';
import './styles.css';

export type BuilderConfig = {
	formId: number;
	ajaxUrl: string;
	pages: Array< { id: number; title: string } >;
	form: {
		title: string;
		slug: string;
		recipient_email: string;
		success_message: string;
		error_message: string;
		success_action: 'message' | 'redirect';
		redirect_page_id: number;
		save_submissions: boolean;
		retention_days: number;
		paused: boolean;
		close_enabled: boolean;
		close_at: string;
		close_limit_enabled: boolean;
		close_limit: number;
		submission_count: number;
		unavailable_message: string;
		send_confirmation_email: boolean;
		confirmation_email_field_id: string;
		submit_button_text: string;
		fields: unknown;
	};
	defaultSubmitButtonText: string;
	serverMaxUploadMb: number;
	fileExtensionOptions?: FileExtensionOption[];
	defaultFileExtensions?: string[];
};

declare global {
	interface Window {
		sobiformsBuilder?: BuilderConfig;
	}
}

type AppWithStateProps = {
	config: BuilderConfig;
	initialBlocks: ReturnType<typeof parseFieldsSchema>['blocks'];
	initialHiddenFields: ReturnType<typeof parseFieldsSchema>['hiddenFields'];
	form: HTMLFormElement;
};

function AppWithState( { config, initialBlocks, initialHiddenFields, form }: AppWithStateProps ) {
	const strings = useMemo( () => getStrings(), [] );
	const [ state, dispatch ] = useBlocksReducer( initialBlocks );

	const settings = useMemo(
		() => ( {
			recipient_email: config.form.recipient_email,
			success_message: config.form.success_message,
			error_message: config.form.error_message,
			success_action: config.form.success_action === 'redirect' ? 'redirect' : 'message',
			redirect_page_id: config.form.redirect_page_id || 0,
			save_submissions: !! config.form.save_submissions,
			retention_days: config.form.retention_days ?? 0,
			paused: !! config.form.paused,
			close_enabled: !! config.form.close_enabled,
			close_at: config.form.close_at || '',
			close_limit_enabled: !! config.form.close_limit_enabled,
			close_limit: config.form.close_limit || 0,
			submission_count: config.form.submission_count ?? 0,
			unavailable_message: config.form.unavailable_message || '',
			send_confirmation_email: !! config.form.send_confirmation_email,
			confirmation_email_field_id: config.form.confirmation_email_field_id || '',
		} ),
		[ config.form ]
	);

	return (
		<FormEditor
			state={ state }
			settings={ settings }
			pages={ config.pages || [] }
			strings={ strings }
			formId={ config.formId }
			slug={ config.form.slug || '' }
			formTitle={ config.form.title || '' }
			submitButtonText={ config.form.submit_button_text || '' }
			defaultSubmitButtonText={ config.defaultSubmitButtonText || strings.submitButtonDefault }
			dispatch={ dispatch }
			form={ form }
			ajaxUrl={ config.ajaxUrl }
			initialHiddenFields={ initialHiddenFields }
			serverMaxUploadMb={ config.serverMaxUploadMb ?? 5 }
			fileExtensionOptions={ config.fileExtensionOptions ?? [] }
			defaultFileExtensions={ config.defaultFileExtensions ?? [] }
		/>
	);
}

function mount() {
	const rootEl = document.getElementById( 'sobiforms-builder-root' );
	const form = document.getElementById( 'sobiforms-form-builder-form' ) as HTMLFormElement | null;
	const config = window.sobiformsBuilder;

	if ( ! rootEl || ! form || ! config ) {
		return;
	}

	const fileExtensionOptions = config.fileExtensionOptions ?? [];
	const parsed = parseFieldsSchema( config.form.fields, { fileExtensionOptions } );
	const initialBlocks = parsed.blocks.length > 0 ? parsed.blocks : defaultBlocks();
	const initialHiddenFields = parsed.hiddenFields;

	createRoot( rootEl ).render(
		<AppWithState
			config={ config }
			initialBlocks={ initialBlocks }
			initialHiddenFields={ initialHiddenFields }
			form={ form }
		/>
	);
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', mount );
} else {
	mount();
}
