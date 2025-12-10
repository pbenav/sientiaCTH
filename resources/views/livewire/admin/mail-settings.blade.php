<div>
    <x-jet-form-section submit="updateMailSettings">
        <x-slot name="title">
            {{ __('Mail Server Configuration') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Configure your SMTP server settings to enable email notifications.') }}
        </x-slot>

        <x-slot name="form">
            <!-- SMTP Error Alert -->
            @if (session()->has('smtp_error'))
                <div class="col-span-6">
                    <div class="p-4 rounded-lg bg-red-100 border border-red-300">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-medium text-red-800">{{ __('SMTP Connection Error') }}</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p class="mb-2">{{ __('The application could not connect to the mail server with the current configuration.') }}</p>
                                    <details class="mt-2">
                                        <summary class="cursor-pointer font-semibold hover:underline">{{ __('Show technical details') }}</summary>
                                        <pre class="mt-2 p-2 bg-red-50 rounded text-xs overflow-x-auto">{{ session('smtp_error') }}</pre>
                                    </details>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Messages -->
            @if (session()->has('message'))
                <div class="col-span-6">
                    <div class="p-4 rounded-lg {{ session('messageType') === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' }}">
                        {{ session('message') }}
                    </div>
                </div>
            @endif

            <!-- Mail Mailer -->
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="mail_mailer" value="{{ __('Mail Driver') }}" />
                <select id="mail_mailer" wire:model="state.MAIL_MAILER" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full">
                    <option value="smtp">SMTP</option>
                    <option value="sendmail">Sendmail</option>
                    <option value="mailgun">Mailgun</option>
                    <option value="ses">Amazon SES</option>
                    <option value="log">Log (Solo desarrollo)</option>
                </select>
                <x-jet-input-error for="state.MAIL_MAILER" class="mt-2" />
            </div>

            <!-- Mail Host -->
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="mail_host" value="{{ __('SMTP Host') }}" />
                <x-jet-input id="mail_host" type="text" class="mt-1 block w-full" wire:model.defer="state.MAIL_HOST" />
                <x-jet-input-error for="state.MAIL_HOST" class="mt-2" />
                <p class="mt-1 text-sm text-gray-500">{{ __('Example: smtp.gmail.com, smtp.office365.com, sandbox.smtp.mailtrap.io') }}</p>
            </div>

            <!-- Mail Port -->
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="mail_port" value="{{ __('SMTP Port') }}" />
                <x-jet-input id="mail_port" type="number" class="mt-1 block w-full" wire:model.defer="state.MAIL_PORT" />
                <x-jet-input-error for="state.MAIL_PORT" class="mt-2" />
                <p class="mt-1 text-sm text-gray-500">{{ __('Common ports: 587 (TLS), 465 (SSL), 25 (default), 2525 (alternative)') }}</p>
            </div>

            <!-- Mail Username -->
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="mail_username" value="{{ __('SMTP Username') }}" />
                <x-jet-input id="mail_username" type="text" class="mt-1 block w-full" wire:model.defer="state.MAIL_USERNAME" />
                <x-jet-input-error for="state.MAIL_USERNAME" class="mt-2" />
            </div>

            <!-- Mail Password -->
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="mail_password" value="{{ __('SMTP Password') }}" />
                <x-jet-input id="mail_password" type="password" class="mt-1 block w-full" wire:model.defer="state.MAIL_PASSWORD" />
                <x-jet-input-error for="state.MAIL_PASSWORD" class="mt-2" />
            </div>

            <!-- Mail Encryption -->
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="mail_encryption" value="{{ __('Encryption') }}" />
                <select id="mail_encryption" wire:model="state.MAIL_ENCRYPTION" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full">
                    <option value="">{{ __('None') }}</option>
                    <option value="tls">TLS</option>
                    <option value="ssl">SSL</option>
                </select>
                <x-jet-input-error for="state.MAIL_ENCRYPTION" class="mt-2" />
            </div>

            <!-- Mail From Address -->
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="mail_from_address" value="{{ __('From Email Address') }}" />
                <x-jet-input id="mail_from_address" type="email" class="mt-1 block w-full" wire:model.defer="state.MAIL_FROM_ADDRESS" />
                <x-jet-input-error for="state.MAIL_FROM_ADDRESS" class="mt-2" />
            </div>

            <!-- Mail From Name -->
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="mail_from_name" value="{{ __('From Name') }}" />
                <x-jet-input id="mail_from_name" type="text" class="mt-1 block w-full" wire:model.defer="state.MAIL_FROM_NAME" />
                <x-jet-input-error for="state.MAIL_FROM_NAME" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="actions">
            <x-jet-action-message class="mr-3" on="saved">
                {{ __('Saved.') }}
            </x-jet-action-message>

            <x-jet-button wire:loading.attr="disabled">
                {{ __('Save Configuration') }}
            </x-jet-button>
        </x-slot>
    </x-jet-form-section>

    <!-- Test Email Section -->
    <x-jet-section-border />

    <x-jet-action-section>
        <x-slot name="title">
            {{ __('Test Email Connection') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Send a test email to verify your SMTP configuration is working correctly.') }}
        </x-slot>

        <x-slot name="content">
            <!-- Test Result -->
            @if ($testResult)
                <div class="mb-4 p-4 rounded-lg {{ $testResultType === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' }}">
                    {{ $testResult }}
                </div>
            @endif

            <div class="max-w-xl text-sm text-gray-600">
                <p>{{ __('Enter an email address to send a test email. Make sure to save your configuration before testing.') }}</p>
            </div>

            <div class="mt-4 max-w-xl">
                <x-jet-label for="test_email" value="{{ __('Test Email Address') }}" />
                <x-jet-input id="test_email" type="email" class="mt-1 block w-full" wire:model.defer="testEmail" placeholder="test@example.com" />
                <x-jet-input-error for="testEmail" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-jet-button wire:click="testConnection" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="testConnection">{{ __('Send Test Email') }}</span>
                    <span wire:loading wire:target="testConnection">{{ __('Sending...') }}</span>
                </x-jet-button>
            </div>
        </x-slot>
    </x-jet-action-section>
</div>
