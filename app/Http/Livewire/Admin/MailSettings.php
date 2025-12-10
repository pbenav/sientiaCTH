<?php

namespace App\Http\Livewire\Admin;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Livewire\Component;

class MailSettings extends Component
{
    public $state = [];
    public $testEmail = '';
    public $testResult = '';
    public $testResultType = '';

    protected $rules = [
        'state.MAIL_MAILER' => 'required|string',
        'state.MAIL_HOST' => 'required|string',
        'state.MAIL_PORT' => 'required|numeric',
        'state.MAIL_USERNAME' => 'nullable|string',
        'state.MAIL_PASSWORD' => 'nullable|string',
        'state.MAIL_ENCRYPTION' => 'nullable|string',
        'state.MAIL_FROM_ADDRESS' => 'required|email',
        'state.MAIL_FROM_NAME' => 'required|string',
    ];

    public function mount()
    {
        $this->state = [
            'MAIL_MAILER' => config('mail.default'),
            'MAIL_HOST' => config('mail.mailers.smtp.host'),
            'MAIL_PORT' => config('mail.mailers.smtp.port'),
            'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
            'MAIL_PASSWORD' => config('mail.mailers.smtp.password'),
            'MAIL_ENCRYPTION' => config('mail.mailers.smtp.encryption'),
            'MAIL_FROM_ADDRESS' => config('mail.from.address'),
            'MAIL_FROM_NAME' => config('mail.from.name'),
        ];
    }

    public function updateMailSettings()
    {
        $this->validate();

        try {
            $this->updateEnvFile();
            
            // Clear config cache
            Artisan::call('config:clear');
            
            session()->flash('message', __('Mail settings updated successfully.'));
            session()->flash('messageType', 'success');
            
        } catch (\Exception $e) {
            session()->flash('message', __('Error updating mail settings: :error', ['error' => $e->getMessage()]));
            session()->flash('messageType', 'error');
        }
    }

    public function testConnection()
    {
        $this->validate([
            'testEmail' => 'required|email',
        ]);

        try {
            // Temporarily update config
            config([
                'mail.default' => $this->state['MAIL_MAILER'],
                'mail.mailers.smtp.host' => $this->state['MAIL_HOST'],
                'mail.mailers.smtp.port' => $this->state['MAIL_PORT'],
                'mail.mailers.smtp.username' => $this->state['MAIL_USERNAME'],
                'mail.mailers.smtp.password' => $this->state['MAIL_PASSWORD'],
                'mail.mailers.smtp.encryption' => $this->state['MAIL_ENCRYPTION'],
                'mail.from.address' => $this->state['MAIL_FROM_ADDRESS'],
                'mail.from.name' => $this->state['MAIL_FROM_NAME'],
            ]);

            \Mail::raw(__('This is a test email from :app', ['app' => config('app.name')]), function ($message) {
                $message->to($this->testEmail)
                        ->subject(__('Test Email'));
            });

            $this->testResult = __('Test email sent successfully to :email', ['email' => $this->testEmail]);
            $this->testResultType = 'success';
            
        } catch (\Exception $e) {
            $this->testResult = __('Error sending test email: :error', ['error' => $e->getMessage()]);
            $this->testResultType = 'error';
        }
    }

    private function updateEnvFile()
    {
        $envFile = base_path('.env');
        
        if (!File::exists($envFile)) {
            throw new \Exception('El archivo .env no existe');
        }

        $envContent = File::get($envFile);

        foreach ($this->state as $key => $value) {
            $value = $this->escapeEnvValue($value);
            
            // Check if the key exists in the .env file
            if (preg_match("/^{$key}=/m", $envContent)) {
                // Update existing key
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $envContent
                );
            } else {
                // Add new key
                $envContent .= "\n{$key}={$value}";
            }
        }

        File::put($envFile, $envContent);
    }

    private function escapeEnvValue($value)
    {
        if ($value === null || $value === '') {
            return '';
        }

        // If value contains spaces or special characters, wrap in quotes
        if (preg_match('/\s/', $value) || preg_match('/[#$]/', $value)) {
            return '"' . str_replace('"', '\\"', $value) . '"';
        }

        return $value;
    }

    public function render()
    {
        return view('livewire.admin.mail-settings');
    }
}
