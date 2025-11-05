<?php

namespace App\Services;

class HtmlSanitizerService
{
    protected $purifier;
    
    public function __construct()
    {
        $config = \HTMLPurifier_Config::createDefault();
        
        // Disable cache to avoid compatibility issues with PHP 8.2+
        $config->set('Cache.DefinitionImpl', null);
        
        // Allowed HTML tags with class and style attributes
        $config->set('HTML.Allowed', 'p[class|style],br,strong[class],b[class],em[class],i[class],u[class],strike[class],a[href|target|title|class],ul[class],ol[class],li[class],h1[class],h2[class],h3[class],h4[class],h5[class],h6[class],img[src|alt|width|height|class],span[class|style],div[class|style],blockquote[class]');
        
        // Allow class and id attributes globally
        $config->set('Attr.EnableID', false); // ID disabled for security (can cause conflicts)
        $config->set('Attr.AllowedClasses', null); // null = allow all CSS classes
        
        // Allow safe inline styles (expanded for better formatting)
        $config->set('CSS.AllowedProperties', 'color,background-color,text-align,font-size,font-weight,font-style,text-decoration,margin,margin-top,margin-bottom,margin-left,margin-right,padding,padding-top,padding-bottom,padding-left,padding-right,border,border-width,border-style,border-color,border-radius,width,height,max-width,max-height,display,float,clear');
        
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
    }
    
    /**
     * Sanitizar HTML removiendo contenido potencialmente peligroso
     * 
     * @param string $html
     * @return string
     */
    public function sanitize(?string $html): string
    {
        if (empty($html)) {
            return '';
        }
        
        return $this->purifier->purify($html);
    }
}
