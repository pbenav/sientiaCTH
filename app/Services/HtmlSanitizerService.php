<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class HtmlSanitizerService
{
    protected $purifier;
    protected $initializationError = null;
    
    public function __construct()
    {
        try {
            $config = \HTMLPurifier_Config::createDefault();
            
            // Disable cache to avoid compatibility issues with PHP 8.2+
            $config->set('Cache.DefinitionImpl', null);
            
            // IMPORTANT: Prevent HTMLPurifier from adding extra newlines
            $config->set('Output.TidyFormat', false);
            $config->set('Core.NormalizeNewlines', false);
            
            // Allowed HTML tags with class and style attributes
            $config->set('HTML.Allowed', 'p[class|style],br,strong[class],b[class],em[class],i[class],u[class],strike[class],a[href|target|title|class],ul[class],ol[class],li[class],h1[class],h2[class],h3[class],h4[class],h5[class],h6[class],img[src|alt|width|height|class],span[class|style],div[class|style],blockquote[class]');
            
            // Allow class and id attributes globally
            $config->set('Attr.EnableID', false); // ID disabled for security (can cause conflicts)
            $config->set('Attr.AllowedClasses', null); // null = allow all CSS classes
            
            // Allow safe inline styles (expanded for better formatting)
            $config->set('CSS.AllowedProperties', 'color,background-color,text-align,font-size,font-weight,font-style,text-decoration,margin,margin-top,margin-bottom,margin-left,margin-right,padding,padding-top,padding-bottom,padding-left,padding-right,border,border-width,border-style,border-color,border-radius,width,height,max-width,max-height,min-width,min-height,display,float,clear,position,top,right,bottom,left,overflow,visibility');
            
            // Enable CSS3 proprietary properties like border-radius
            $config->set('CSS.Proprietary', true);
            
            // Allow tricky CSS properties like display, position, etc.
            $config->set('CSS.AllowTricky', true);
            
            // Definir las propiedades CSS personalizadas para position y otras que HTMLPurifier no soporta por defecto
            // Usar un cache buster único
            $config->set('CSS.DefinitionRev', 1);
            
            // Get the CSS definition (finalize it)
            $def = $config->getCSSDefinition(true);
            
            if ($def) {
                // Add custom properties
                $def->info['position'] = new \HTMLPurifier_AttrDef_Enum(['static', 'relative', 'absolute', 'fixed', 'sticky']);
                $def->info['display'] = new \HTMLPurifier_AttrDef_Enum(['none', 'inline', 'block', 'inline-block', 'flex', 'inline-flex', 'grid', 'inline-grid']);
                $def->info['visibility'] = new \HTMLPurifier_AttrDef_Enum(['visible', 'hidden', 'collapse']);
                $def->info['overflow'] = new \HTMLPurifier_AttrDef_Enum(['visible', 'hidden', 'scroll', 'auto']);
                $def->info['overflow-x'] = new \HTMLPurifier_AttrDef_Enum(['visible', 'hidden', 'scroll', 'auto']);
                $def->info['overflow-y'] = new \HTMLPurifier_AttrDef_Enum(['visible', 'hidden', 'scroll', 'auto']);
            }
            
            // Link configuration
            $config->set('HTML.TargetBlank', true); // Add target="_blank" to external links
            $config->set('HTML.Nofollow', true); // Add rel="nofollow" to external links
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
            
            // Don't allow JavaScript or iframes
            $config->set('HTML.SafeIframe', false);
            $config->set('HTML.SafeObject', false);
            
            // Allow data URI images (base64)
            $config->set('URI.AllowedSchemes', [
                'http' => true,
                'https' => true,
                'mailto' => true,
                'data' => true // For inline images
            ]);
            
            // Limitar longitud de URIs
            $config->set('URI.DisableExternal', false);
            $config->set('URI.DisableExternalResources', false);
            
            $this->purifier = new \HTMLPurifier($config);
            
        } catch (\Exception $e) {
            // Log the initialization error
            Log::error('HTMLPurifier initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->initializationError = $e->getMessage();
            $this->purifier = null;
        }
    }
    
    /**
     * Sanitizar HTML removiendo contenido potencialmente peligroso
     * 
     * @param string $html
     * @return string
     */
    public function sanitize(?string $html): string
    {
        // Check for empty input
        if (empty($html)) {
            return '';
        }
        
        // Check if purifier was initialized successfully
        if ($this->purifier === null) {
            Log::warning('HTMLPurifier not initialized, returning stripped HTML', [
                'error' => $this->initializationError
            ]);
            
            // Fallback: strip all tags as a safe default
            return strip_tags($html, '<p><br><strong><b><em><i><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6>');
        }
        
        try {
            // Attempt to purify the HTML
            $sanitized = $this->purifier->purify($html);
            
            // Check if purification resulted in empty string when input wasn't empty
            if (empty($sanitized) && !empty($html)) {
                Log::warning('HTMLPurifier returned empty result for non-empty input', [
                    'input_length' => strlen($html),
                    'input_preview' => substr($html, 0, 100)
                ]);
            }
            
            return $sanitized;
            
        } catch (\Exception $e) {
            // Log the purification error
            Log::error('HTMLPurifier purification failed', [
                'error' => $e->getMessage(),
                'input_preview' => substr($html, 0, 100),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback: return stripped HTML
            return strip_tags($html, '<p><br><strong><b><em><i><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6>');
        }
    }
}
