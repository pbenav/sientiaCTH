<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Documentation Repository Controller
 * 
 * Manages the application's documentation repository. Documents are organized by locale
 * in the public/docs directory:
 * 
 * Structure:
 * - public/docs/README.md (main index, shown to all users)
 * - public/docs/es/ (Spanish documents - only shown to es locale users)
 * - public/docs/en/ (English documents - only shown to en locale users)
 * - public/docs/*.md (root documents - shown to all users)
 * 
 * The system automatically filters documents based on the authenticated user's locale preference.
 * This allows maintaining separate documentation for different languages while providing
 * a seamless, localized experience.
 * 
 * Future expansion: This can be extended to support user-uploaded documents with
 * proper access control and categorization.
 */
class DocsController extends Controller
{
    /**
     * Show the documentation index.
     */
    /**
     * Show the documentation index.
     */
    public function index()
    {
        // Get user's locale preference
        $userLocale = auth()->check() && auth()->user()->locale 
            ? auth()->user()->locale 
            : app()->getLocale();

        $readmePath = public_path("docs/{$userLocale}/README.md");
        
        // Fallback to root README if localized one doesn't exist
        if (!File::exists($readmePath)) {
            $readmePath = public_path('docs/README.md');
        }

        $files = $this->getDocFiles();
        
        if (!File::exists($readmePath)) {
            // If README doesn't exist, just show the list
            return view('docs.index', [
                'title' => 'Documentation',
                'files' => $files
            ]);
        }

        $content = File::get($readmePath);
        $html = $this->parseMarkdown($content, $userLocale);

        return view('docs.show', [
            'title' => 'Documentation',
            'content' => $html,
            'currentPath' => Str::after($readmePath, public_path() . '/'),
            'files' => $files
        ]);
    }

    /**
     * Show a specific documentation file.
     */
    public function show($locale = null, $file = null)
    {
        // If only one parameter, it's a root-level file
        if ($locale && !$file) {
            $file = $locale;
            $locale = null;
        }

        // Build the file path
        $path = $locale 
            ? "docs/{$locale}/{$file}.md" 
            : "docs/{$file}.md";

        $fullPath = public_path($path);

        if (!File::exists($fullPath)) {
            abort(404, 'Documentation file not found');
        }

        $content = File::get($fullPath);
        $html = $this->parseMarkdown($content, $locale);

        // Extract title from first H1
        preg_match('/^#\s+(.+)$/m', $content, $matches);
        $title = $matches[1] ?? 'Documentation';

        return view('docs.show', [
            'title' => $title,
            'content' => $html,
            'currentPath' => $path,
            'locale' => $locale,
            'files' => $this->getDocFiles()
        ]);
    }

    /**
     * Get all documentation files.
     */
    private function getDocFiles()
    {
        $docsPath = public_path('docs');
        $files = [];

        if (!File::exists($docsPath)) {
            return [];
        }

        // Get user's locale preference
        $userLocale = auth()->check() && auth()->user()->locale 
            ? auth()->user()->locale 
            : app()->getLocale();

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($docsPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'md') {
                $relativePath = str_replace($docsPath . '/', '', $file->getPathname());
                
                // Skip README.md as it's the index
                if ($relativePath === 'README.md') {
                    continue;
                }

                $parts = explode('/', $relativePath);
                
                // Filter documents by user's locale
                // If the document is in a locale folder, only show it if it matches user's locale
                if (count($parts) > 1) {
                    $docLocale = $parts[0];
                    // Only show documents matching user's locale
                    if ($docLocale !== $userLocale) {
                        continue;
                    }
                }
                
                $name = str_replace('.md', '', end($parts));
                
                // Humanize name fallback
                $label = Str::title(str_replace(['-', '_'], ' ', $name));

                // Try to read the actual title from the file content
                try {
                    $fileContent = File::get($file->getPathname());
                    if (preg_match('/^#\s+(.+)$/m', $fileContent, $matches)) {
                        $label = trim($matches[1]);
                    }
                } catch (\Exception $e) {
                    // Keep fallback label if reading fails
                }

                if (count($parts) > 1) {
                    $folder = $parts[0];
                    // Translate folder names if possible or capitalize
                    $folderLabel = match($folder) {
                        'es' => 'Español',
                        'en' => 'English',
                        default => ucfirst($folder)
                    };
                    
                    $files[$folderLabel][] = [
                        'path' => $relativePath,
                        'label' => $label,
                        'url' => $this->buildUrl($relativePath)
                    ];
                } else {
                    // Root level documents are shown to everyone
                    $files['root'][] = [
                        'path' => $relativePath,
                        'label' => $label,
                        'url' => $this->buildUrl($relativePath)
                    ];
                }
            }
        }

        ksort($files);
        return $files;
    }

    private function buildUrl($relativePath)
    {
        $path = str_replace('.md', '', $relativePath);
        if (Str::contains($path, '/')) {
            $parts = explode('/', $path);
            return route('docs.show.locale', ['locale' => $parts[0], 'file' => $parts[1]]);
        }
        return route('docs.show.root', ['file' => $path]);
    }

    /**
     * Parse Markdown to HTML and fix internal links.
     */
    private function parseMarkdown($markdown, $locale = null)
    {
        $parsedown = new \Parsedown();
        $html = $parsedown->text($markdown);

        // Fix internal documentation links
        $html = preg_replace_callback('/<a href="([^"]+)"/', function($matches) use ($locale) {
            $url = $matches[1];

            // If it's a .md file, convert to route
            if (Str::endsWith($url, '.md')) {
                // Remove .md extension
                $cleanUrl = str_replace('.md', '', $url);
                
                if (Str::startsWith($url, 'http')) {
                    return $matches[0];
                }

                // If we are in a localized context and the link is relative, 
                // prepend the locale if not present
                if ($locale && !Str::contains($url, '/')) {
                    $url = "{$locale}/{$url}";
                }

                return '<a href="' . $this->buildUrl($url) . '"';
            }

            return $matches[0];
        }, $html);

        return $html;
    }
}
