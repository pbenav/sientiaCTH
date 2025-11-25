<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DocsController extends Controller
{
    /**
     * Show the documentation index.
     */
    public function index()
    {
        $readmePath = public_path('docs/README.md');
        
        if (!File::exists($readmePath)) {
            abort(404, 'Documentation not found');
        }

        $content = File::get($readmePath);
        $html = $this->parseMarkdown($content);

        return view('docs.show', [
            'title' => 'Documentation',
            'content' => $html,
            'currentPath' => 'README.md'
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
        $html = $this->parseMarkdown($content);

        // Extract title from first H1
        preg_match('/^#\s+(.+)$/m', $content, $matches);
        $title = $matches[1] ?? 'Documentation';

        return view('docs.show', [
            'title' => $title,
            'content' => $html,
            'currentPath' => $path,
            'locale' => $locale
        ]);
    }

    /**
     * Parse Markdown to HTML and fix internal links.
     */
    private function parseMarkdown($markdown)
    {
        $parsedown = new \Parsedown();
        $html = $parsedown->text($markdown);

        // Fix internal documentation links
        $html = preg_replace_callback('/<a href="([^"]+)"/', function($matches) {
            $url = $matches[1];

            // If it's a .md file, convert to route
            if (Str::endsWith($url, '.md')) {
                $url = str_replace('.md', '', $url);
                
                // Handle different path formats
                if (Str::startsWith($url, 'es/') || Str::startsWith($url, 'en/')) {
                    $parts = explode('/', $url);
                    $url = route('docs.show', $parts);
                } elseif (Str::contains($url, '/')) {
                    $parts = explode('/', $url);
                    $url = route('docs.show', $parts);
                } else {
                    $url = route('docs.show', [$url]);
                }
            }

            return '<a href="' . $url . '"';
        }, $html);

        return $html;
    }
}
