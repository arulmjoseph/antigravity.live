import { MessageCircle, FileText } from 'lucide-react';
import { BuilderIcon } from './BuilderIcon';
import { useState } from 'react';
import type { Strings } from '../lib/i18n';
import { formatCloseAtBadge } from '../lib/formatCloseAt';
import { CollapsibleSection } from './CollapsibleSection';
import { HiddenFieldsSection } from './HiddenFieldsSection';
import { SettingsToggle } from './SettingsToggle';
import type { HiddenField } from '../types/hidden-field';

export type PageOption = {
	id: number;
	title: string;
};

export type SuccessAction = 'message' | 'redirect';

export type EmailFieldOption = {
	id: string;
	label: string;
	required: boolean;
};

export type FormSettings = {
	recipient_email: string;
	success_message: string;
	error_message: string;
	success_action: SuccessAction;
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
};

type Props = {
	strings: Strings;
	settings: FormSettings;
	pages: PageOption[];
	paused: boolean;
	closeEnabled: boolean;
	closeAt: string;
	closeLimitEnabled: boolean;
	closeLimit: number;
	submissionCount: number;
	saveSubmissions: boolean;
	recipientEmail: string;
	emailEnabled: boolean;
	emailFields: EmailFieldOption[];
	confirmationEnabled: boolean;
	confirmationEmailFieldId: string;
	hiddenFields: HiddenField[];
	hasFileFields: boolean;
	onHiddenFieldsChange: ( fields: HiddenField[] ) => void;
	onPausedChange: ( value: boolean ) => void;
	onCloseEnabledChange: ( value: boolean ) => void;
	onCloseAtChange: ( value: string ) => void;
	onCloseLimitEnabledChange: ( value: boolean ) => void;
	onCloseLimitChange: ( value: number ) => void;
	onSaveSubmissionsChange: ( value: boolean ) => void;
	onRecipientEmailChange: ( value: string ) => void;
	onEmailEnabledChange: ( value: boolean ) => void;
	onConfirmationEnabledChange: ( value: boolean ) => void;
	onConfirmationEmailFieldIdChange: ( value: string ) => void;
};

const DEFAULT_PURGE_DAYS = 90;

function submissionLimitProgressLabel( count: number, limit: number ): string {
	const percent = limit > 0 ? Math.round( ( count / limit ) * 100 ) : 0;
	return `${ count } (${ percent }%)`;
}

export function SettingsPanel( {
	strings,
	settings,
	pages,
	paused,
	closeEnabled,
	closeAt,
	closeLimitEnabled,
	closeLimit,
	submissionCount,
	saveSubmissions,
	recipientEmail,
	emailEnabled,
	emailFields,
	confirmationEnabled,
	confirmationEmailFieldId,
	hiddenFields,
	hasFileFields,
	onHiddenFieldsChange,
	onPausedChange,
	onCloseEnabledChange,
	onCloseAtChange,
	onCloseLimitEnabledChange,
	onCloseLimitChange,
	onSaveSubmissionsChange,
	onRecipientEmailChange,
	onEmailEnabledChange,
	onConfirmationEnabledChange,
	onConfirmationEmailFieldIdChange,
}: Props ) {
	const [ successAction, setSuccessAction ] = useState<SuccessAction>(
		settings.success_action === 'redirect' ? 'redirect' : 'message'
	);
	const [ purgeOld, setPurgeOld ] = useState( settings.retention_days > 0 );

	const showUnavailableMessage = paused || closeEnabled || closeLimitEnabled;
	const submissionLimitReached =
		closeLimitEnabled && closeLimit > 0 && submissionCount >= closeLimit;
	const showNoDeliveryWarning =
		! saveSubmissions && ( ! emailEnabled || ! recipientEmail.trim() );
	const hasRequiredEmailField = emailFields.some( ( field ) => field.required );
	const showNoEmailFieldWarning = confirmationEnabled && emailFields.length === 0;
	const showOptionalEmailNotice =
		confirmationEnabled && emailFields.length > 0 && ! hasRequiredEmailField;
	const retentionDefault =
		settings.retention_days > 0 ? settings.retention_days : DEFAULT_PURGE_DAYS;
	const closeLabel = formatCloseAtBadge( closeAt );
	const availabilityHint = paused
		? strings.statusPaused
			: closeLimitEnabled
			? submissionLimitReached
				? strings.statusSubmissionLimitReached
				: closeLimit > 0
					? submissionLimitProgressLabel( submissionCount, closeLimit )
					: strings.statusSubmissionLimitPending
			: closeEnabled
			? closeLabel
				? `${ strings.statusAutoClose } ${ closeLabel }`
				: strings.statusAutoClosePending
			: strings.sectionAvailabilityHint;
	const availabilityHintStatus = paused
		? 'paused'
		: closeLimitEnabled && submissionLimitReached
			? 'limit'
			: closeEnabled
				? 'scheduled'
				: undefined;

	return (
		<aside className="sobiforms-settings-sidebar" aria-label={ strings.settingsSidebar }>
			<CollapsibleSection
				title={ strings.availability }
				hint={ availabilityHint }
				hintStatus={ availabilityHintStatus }
				defaultOpen={ false }
			>
				<SettingsToggle
					label={ strings.pauseForm }
					name="sobiforms_paused"
					checked={ paused }
					onChange={ onPausedChange }
				/>
				{ ! paused ? (
					<>
						<SettingsToggle
							label={ strings.autoClose }
							name="sobiforms_close_enabled"
							checked={ closeEnabled }
							onChange={ onCloseEnabledChange }
						/>
						{ closeEnabled ? (
							<label className="block">
								<span className="mb-1 block text-sm font-medium text-neutral-700">
									{ strings.closeAt }
								</span>
								<input
									type="datetime-local"
									name="sobiforms_close_at"
									value={ closeAt }
									onChange={ ( e ) => onCloseAtChange( e.target.value ) }
									className="sobiforms-settings-field sobiforms-settings-field--narrow"
									required
								/>
							</label>
						) : (
							<input type="hidden" name="sobiforms_close_at" value={ closeAt } />
						) }
						<SettingsToggle
							label={ strings.closeAfterSubmissions }
							name="sobiforms_close_limit_enabled"
							checked={ closeLimitEnabled }
							onChange={ onCloseLimitEnabledChange }
						/>
						{ closeLimitEnabled ? (
							<>
								<label className="block">
									<span className="mb-1 block text-sm font-medium text-neutral-700">
										{ strings.submissionLimit }
									</span>
									<input
										type="number"
										name="sobiforms_close_limit"
										min={ 1 }
										max={ 100000 }
										value={ closeLimit > 0 ? closeLimit : '' }
										onChange={ ( e ) =>
											onCloseLimitChange( Math.max( 0, parseInt( e.target.value, 10 ) || 0 ) )
										}
										className="sobiforms-settings-field sobiforms-settings-field--narrow"
										required
									/>
								</label>
								<p className="sobiforms-submission-limit__progress" aria-live="polite">
									{ submissionLimitProgressLabel( submissionCount, closeLimit ) }
								</p>
							</>
						) : (
							<>
								<input type="hidden" name="sobiforms_close_limit_enabled" value="0" />
								<input type="hidden" name="sobiforms_close_limit" value={ closeLimit } />
							</>
						) }
					</>
				) : (
					<>
						<input type="hidden" name="sobiforms_close_enabled" value="0" />
						<input type="hidden" name="sobiforms_close_at" value={ closeAt } />
						<input type="hidden" name="sobiforms_close_limit_enabled" value="0" />
						<input type="hidden" name="sobiforms_close_limit" value={ closeLimit } />
					</>
				) }
				{ showUnavailableMessage ? (
					<label className="block">
						<span className="mb-1 block text-sm font-medium text-neutral-700">
							{ strings.unavailableMessage }
						</span>
						<textarea
							name="sobiforms_unavailable_message"
							rows={ 3 }
							defaultValue={ settings.unavailable_message }
							className="sobiforms-settings-field"
							placeholder={ strings.unavailableMessagePlaceholder }
						/>
					</label>
				) : (
					<input
						type="hidden"
						name="sobiforms_unavailable_message"
						value={ settings.unavailable_message }
					/>
				) }
			</CollapsibleSection>

			<CollapsibleSection
				title={ strings.storage }
				hint={ strings.sectionStorageHint }
				defaultOpen={ false }
			>
				<SettingsToggle
					label={ strings.saveSubmissions }
					name="sobiforms_save_submissions"
					checked={ saveSubmissions || hasFileFields }
					disabled={ hasFileFields }
					title={ hasFileFields ? strings.saveSubmissionsFileRequired : undefined }
					onChange={ onSaveSubmissionsChange }
				/>
				{ saveSubmissions ? (
					<>
						<SettingsToggle
							label={ strings.purgeOldSubmissions }
							name="sobiforms_purge_old"
							checked={ purgeOld }
							onChange={ setPurgeOld }
						/>
						{ purgeOld ? (
							<label className="block">
								<span className="mb-1 block text-sm font-medium text-neutral-700">
									{ strings.purgeAfterDays }
								</span>
								<input
									type="number"
									name="sobiforms_retention_days"
									min={ 1 }
									step={ 1 }
									defaultValue={ retentionDefault }
									className="sobiforms-settings-field sobiforms-settings-field--narrow"
									required
								/>
								<p className="mt-1 text-xs text-neutral-500">{ strings.purgeAfterDaysHint }</p>
							</label>
						) : (
							<input type="hidden" name="sobiforms_retention_days" value="0" />
						) }
					</>
				) : (
					<>
						<input type="hidden" name="sobiforms_retention_days" value="0" />
						<p className="text-xs text-neutral-500">{ strings.saveSubmissionsHintOff }</p>
					</>
				) }
			</CollapsibleSection>

			<CollapsibleSection
				title={ strings.sectionNotifications }
				hint={ strings.sectionNotificationsHint }
			>
				{ showNoDeliveryWarning && (
					<p className="sobiforms-settings-warning" role="status">
						{ strings.warnNoDeliveryInline }
					</p>
				) }
				<SettingsToggle
					label={ strings.emailNotifications }
					hint={ strings.emailNotificationsHint }
					checked={ emailEnabled }
					onChange={ onEmailEnabledChange }
				/>
				{ emailEnabled ? (
					<label className="block">
						<span className="mb-1 block text-sm font-medium text-neutral-700">
							{ strings.recipientEmail }
						</span>
						<input
							type="text"
							name="sobiforms_recipient_email"
							value={ recipientEmail }
							onChange={ ( e ) => onRecipientEmailChange( e.target.value ) }
							className="sobiforms-settings-field"
							placeholder={ strings.recipientEmailPlaceholder }
							aria-describedby="sobiforms-recipient-email-hint"
						/>
						<p id="sobiforms-recipient-email-hint" className="mt-1 text-xs text-neutral-500">
							{ strings.recipientEmailHint }
						</p>
					</label>
				) : (
					<input type="hidden" name="sobiforms_recipient_email" value="" />
				) }
				<SettingsToggle
					label={ strings.sendConfirmationEmail }
					hint={ strings.sendConfirmationEmailHint }
					name="sobiforms_send_confirmation_email"
					checked={ confirmationEnabled }
					onChange={ onConfirmationEnabledChange }
				/>
				{ ! confirmationEnabled && (
					<input
						type="hidden"
						name="sobiforms_confirmation_email_field_id"
						value={ confirmationEmailFieldId }
					/>
				) }
				{ confirmationEnabled && (
					<>
						{ showNoEmailFieldWarning && (
							<p className="sobiforms-settings-warning" role="status">
								{ strings.warnNoConfirmationEmailField }
							</p>
						) }
						{ showOptionalEmailNotice && (
							<p className="sobiforms-settings-warning" role="status">
								{ strings.warnNoRequiredEmailField }
							</p>
						) }
						{ emailFields.length === 1 ? (
							<input
								type="hidden"
								name="sobiforms_confirmation_email_field_id"
								value={ confirmationEmailFieldId }
							/>
						) : emailFields.length > 1 ? (
							<label className="block">
								<span className="mb-1 block text-sm font-medium text-neutral-700">
									{ strings.confirmationSendTo }
								</span>
								<select
									name="sobiforms_confirmation_email_field_id"
									value={ confirmationEmailFieldId }
									onChange={ ( e ) =>
										onConfirmationEmailFieldIdChange( e.target.value )
									}
									className="sobiforms-settings-field"
									aria-describedby="sobiforms-confirmation-send-to-hint"
								>
									{ emailFields.map( ( field ) => (
										<option key={ field.id } value={ field.id }>
											{ field.label }
										</option>
									) ) }
								</select>
								<p
									id="sobiforms-confirmation-send-to-hint"
									className="mt-1 text-xs text-neutral-500"
								>
									{ strings.confirmationSendToHint }
								</p>
							</label>
						) : (
							<input
								type="hidden"
								name="sobiforms_confirmation_email_field_id"
								value=""
							/>
						) }
					</>
				) }
			</CollapsibleSection>

			<CollapsibleSection
				title={ strings.afterSubmit }
				hint={ strings.sectionAfterSubmitHint }
			>
				<div className="sobiforms-success-action-tabs" role="radiogroup" aria-label={ strings.afterSubmit }>
					<label
						className={
							'sobiforms-success-action-tab' +
							( successAction === 'message' ? ' is-active' : '' )
						}
					>
						<input
							type="radio"
							name="sobiforms_success_action"
							value="message"
							checked={ successAction === 'message' }
							onChange={ () => setSuccessAction( 'message' ) }
							className="sobiforms-success-action-tab__input"
						/>
						<span className="sobiforms-success-action-tab__label">
							<BuilderIcon icon={ MessageCircle } />
							<span>{ strings.afterSubmitMessageShort }</span>
						</span>
					</label>
					<label
						className={
							'sobiforms-success-action-tab' +
							( successAction === 'redirect' ? ' is-active' : '' )
						}
					>
						<input
							type="radio"
							name="sobiforms_success_action"
							value="redirect"
							checked={ successAction === 'redirect' }
							onChange={ () => setSuccessAction( 'redirect' ) }
							className="sobiforms-success-action-tab__input"
						/>
						<span className="sobiforms-success-action-tab__label">
							<BuilderIcon icon={ FileText } />
							<span>{ strings.afterSubmitRedirectShort }</span>
						</span>
					</label>
				</div>

				{ successAction === 'message' ? (
					<label className="block">
						<span className="mb-1 block text-sm font-medium text-neutral-700">
							{ strings.successMessage }
						</span>
						<input
							type="text"
							name="sobiforms_success_message"
							defaultValue={ settings.success_message }
							placeholder={ strings.successMessagePlaceholder }
							className="sobiforms-settings-field"
						/>
					</label>
				) : (
					<label className="block">
						<span className="mb-1 block text-sm font-medium text-neutral-700">
							{ strings.redirectPage }
						</span>
						<select
							name="sobiforms_redirect_page_id"
							defaultValue={ settings.redirect_page_id > 0 ? String( settings.redirect_page_id ) : '' }
							className="sobiforms-settings-field"
						>
							<option value="">{ strings.selectPage }</option>
							{ pages.map( ( page ) => (
								<option key={ page.id } value={ page.id }>
									{ page.title }
								</option>
							) ) }
						</select>
					</label>
				) }
			</CollapsibleSection>

			<CollapsibleSection
				title={ strings.hiddenFields }
				hint={ strings.sectionHiddenFieldsHint }
				defaultOpen={ false }
			>
				<HiddenFieldsSection
					strings={ strings }
					fields={ hiddenFields }
					onChange={ onHiddenFieldsChange }
				/>
			</CollapsibleSection>

			<CollapsibleSection
				title={ strings.sectionAdvanced }
				hint={ strings.sectionAdvancedHint }
				defaultOpen={ false }
			>
				<label className="block">
					<span className="mb-1 block text-sm font-medium text-neutral-700">
						{ strings.errorMessage }
					</span>
					<input
						type="text"
						name="sobiforms_error_message"
						defaultValue={ settings.error_message }
						className="sobiforms-settings-field"
					/>
				</label>
			</CollapsibleSection>
		</aside>
	);
}
