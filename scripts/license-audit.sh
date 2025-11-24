#!/usr/bin/env bash

# License audit script for the project
# Ensures all source files have an MIT header and generates a LICENSES summary

MIT_HEADER='/*
MIT License

Copyright (c) $(date +%Y) $(git config user.name)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/'

# Add MIT header to PHP files if missing
find . -type f -name "*.php" -print0 | while IFS= read -r -d '' file; do
  if ! grep -q "MIT License" "$file"; then
    echo "Adding MIT header to $file"
    tmp=$(mktemp)
    echo "$MIT_HEADER" > "$tmp"
    cat "$file" >> "$tmp"
    mv "$tmp" "$file"
  fi
done

# Generate LICENSES.md summary from composer licenses
composer licenses --format=markdown > LICENSES.md

echo "License audit completed. LICENSES.md generated."
