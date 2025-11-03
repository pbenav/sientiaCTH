<?php

namespace App\Services;

class HtmlSanitizerService
{
    protected $purifier;
    
    public function __construct()
    {
        $config = \HTMLPurifier_Config::createDefault();
        
        // Deshabilitar la caché para evitar errores de compatibilidad con PHP 8.2+
        $config->set('Cache.DefinitionImpl', null);
        
        // Etiquetas HTML permitidas con atributos de clase y estilo
        $config->set('HTML.Allowed', 'p[class|style],br,strong[class],b[class],em[class],i[class],u[class],strike[class],a[href|target|title|class],ul[class],ol[class],li[class],h1[class],h2[class],h3[class],h4[class],h5[class],h6[class],img[src|alt|width|height|class],span[class|style],div[class|style],blockquote[class]');
        
        // Permitir atributos class e id de forma global
        $config->set('Attr.EnableID', false); // ID deshabilitado por seguridad (puede causar conflictos)
        $config->set('Attr.AllowedClasses', null); // null = permitir todas las clases CSS
        
        // Permitir estilos inline seguros (ampliado para mejor formato)
        $config->set('CSS.AllowedProperties', 'color,background-color,text-align,font-size,font-weight,font-style,text-decoration,margin,margin-top,margin-bottom,margin-left,margin-right,padding,padding-top,padding-bottom,padding-left,padding-right,border,border-width,border-style,border-color,border-radius,width,height,max-width,max-height,display,float,clear');
        
        // Configuración de enlaces
        $config->set('HTML.TargetBlank', true); // Añadir target="_blank" a enlaces externos
        $config->set('HTML.Nofollow', true); // Añadir rel="nofollow" a enlaces externos
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
        
        // No permitir JavaScript ni iframes
        $config->set('HTML.SafeIframe', false);
        $config->set('HTML.SafeObject', false);
        
        // Permitir imágenes de data URI (base64)
        $config->set('URI.AllowedSchemes', [
            'http' => true,
            'https' => true,
            'mailto' => true,
            'data' => true // Para imágenes inline
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
