<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📚 {{ $title }}
            </h2>
            <a href="{{ route('docs.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                ← {{ __('Back to Documentation Index') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[90rem] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <!-- Breadcrumb -->
                    <nav class="mb-6 text-sm text-gray-500">
                        <a href="{{ route('docs.index') }}" class="hover:text-gray-700">Documentation</a>
                        @if(isset($locale))
                            <span class="mx-2">/</span>
                            <span class="text-gray-700">{{ strtoupper($locale) }}</span>
                        @endif
                        <span class="mx-2">/</span>
                        <span class="text-gray-700">{{ basename($currentPath, '.md') }}</span>
                    </nav>

                    <!-- Documentation Content -->
                    <div class="prose prose-blue max-w-none">
                        <style>
                            .prose h1 {
                                font-size: 2.25rem;
                                font-weight: 700;
                                margin-top: 0;
                                margin-bottom: 1.5rem;
                                color: #1f2937;
                                border-bottom: 2px solid #e5e7eb;
                                padding-bottom: 0.5rem;
                            }
                            .prose h2 {
                                font-size: 1.875rem;
                                font-weight: 600;
                                margin-top: 2rem;
                                margin-bottom: 1rem;
                                color: #374151;
                            }
                            .prose h3 {
                                font-size: 1.5rem;
                                font-weight: 600;
                                margin-top: 1.5rem;
                                margin-bottom: 0.75rem;
                                color: #4b5563;
                            }
                            .prose h4 {
                                font-size: 1.25rem;
                                font-weight: 600;
                                margin-top: 1.25rem;
                                margin-bottom: 0.5rem;
                                color: #6b7280;
                            }
                            .prose h5, .prose h6 {
                                font-size: 1.125rem;
                                font-weight: 600;
                                margin-top: 1rem;
                                margin-bottom: 0.5rem;
                                color: #6b7280;
                            }
                            .prose p {
                                margin-bottom: 1rem;
                                line-height: 1.75;
                                color: #374151;
                            }
                            .prose code {
                                background-color: #f3f4f6;
                                padding: 0.2rem 0.4rem;
                                border-radius: 0.25rem;
                                font-size: 0.875rem;
                                color: #dc2626;
                                font-family: 'Courier New', monospace;
                            }
                            .prose pre {
                                background-color: #1f2937;
                                color: #f9fafb;
                                padding: 1rem;
                                border-radius: 0.5rem;
                                overflow-x: auto;
                                margin: 1rem 0;
                            }
                            .prose pre code {
                                background-color: transparent;
                                color: #f9fafb;
                                padding: 0;
                            }
                            .prose ul {
                                list-style-type: disc;
                                margin-left: 1.5rem;
                                margin-bottom: 1rem;
                            }
                            .prose ol {
                                list-style-type: decimal;
                                margin-left: 1.5rem;
                                margin-bottom: 1rem;
                            }
                            .prose li {
                                margin-bottom: 0.5rem;
                                line-height: 1.75;
                            }
                            .prose a {
                                color: #2563eb;
                                text-decoration: none;
                            }
                            .prose a:hover {
                                color: #1d4ed8;
                                text-decoration: underline;
                            }
                            .prose strong {
                                font-weight: 600;
                                color: #1f2937;
                            }
                            .prose em {
                                font-style: italic;
                            }
                            .prose table {
                                width: 100%;
                                border-collapse: collapse;
                                margin: 1rem 0;
                            }
                            .prose th, .prose td {
                                border: 1px solid #e5e7eb;
                                padding: 0.5rem;
                                text-align: left;
                            }
                            .prose th {
                                background-color: #f3f4f6;
                                font-weight: 600;
                            }
                        </style>
                        {!! $content !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
