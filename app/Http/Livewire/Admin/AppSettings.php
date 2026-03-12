<?php

namespace App\Http\Livewire\Admin;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Livewire\Component;
use Livewire\WithFileUploads;

class AppSettings extends Component
{
    use WithFileUploads;

    public $state = [];
    public $newBackground;

    public function mount()
    {
        $this->state = [
            'LOGIN_BACKGROUND_IMAGE' => env('LOGIN_BACKGROUND_IMAGE', '/images/login_bg.jpg'),
        ];
    }

    public function updateSettings()
    {
        if ($this->newBackground) {
            $this->validate([
                'newBackground' => 'image|max:2048', // 2MB Max
            ]);

            $filename = 'bg_' . time() . '.' . $this->newBackground->getClientOriginalExtension();
            $this->newBackground->storeAs('public/images', $filename);
            
            // Public path for the web
            $this->state['LOGIN_BACKGROUND_IMAGE'] = '/images/' . $filename;
            
            // Ensure the directory exists in public
            if (!File::exists(public_path('images'))) {
                File::makeDirectory(public_path('images'), 0755, true);
            }
            
            // Move file to public/images
            File::copy(storage_path('app/public/images/' . $filename), public_path('images/' . $filename));
        }

        try {
            $this->updateEnvFile();
            
            Artisan::call('config:clear');
            
            $this->newBackground = null;
            session()->flash('message', __('Settings updated successfully.'));
            session()->flash('messageType', 'success');
            
        } catch (\Exception $e) {
            session()->flash('message', __('Error updating settings: :error', ['error' => $e->getMessage()]));
            session()->flash('messageType', 'error');
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
            
            if (preg_match("/^{$key}=/m", $envContent)) {
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $envContent
                );
            } else {
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

        if (preg_match('/\s/', $value) || preg_match('/[#$]/', $value)) {
            return '"' . str_replace('"', '\\"', $value) . '"';
        }

        return $value;
    }

    public function render()
    {
        return view('livewire.admin.app-settings');
    }
}
