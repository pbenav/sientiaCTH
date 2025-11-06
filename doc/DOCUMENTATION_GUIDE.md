# Documentation Structure Guide

This document explains the organization and maintenance of the CTH application documentation.

## 📁 Directory Structure

```
doc/
├── README.md                    # Main documentation index
├── .gitignore                   # Documentation-specific ignore rules
├── es/                          # Spanish documentation
│   ├── README.md                # Spanish documentation index
│   ├── auditoria-seguridad.md   # Security audit report
│   ├── guia-instalacion.md      # Installation guide
│   ├── guia-migraciones.md      # Migration deployment guide
│   ├── parches-seguridad.md     # Security patches log
│   └── referencia-comandos.md   # Command reference
└── en/                          # English documentation
    ├── README.md                # English documentation index
    ├── command-reference.md     # Command reference
    ├── installation-guide.md    # Installation guide
    ├── migration-guide.md       # Migration deployment guide
    ├── security-audit.md        # Security audit report
    └── security-patches.md      # Security patches log
```

## 🌐 Language Support

### Primary Language: Spanish (es/)
The application's primary documentation is maintained in Spanish, as the development team and primary users are Spanish-speaking.

### Secondary Language: English (en/)
English documentation is provided for:
- International developers
- Open source community
- Technical documentation standards
- Future internationalization

## 📝 Document Types

### User Documentation
- **Installation Guides**: Setup and deployment instructions
- **User Manuals**: End-user feature documentation
- **Administrator Guides**: System administration tasks

### Technical Documentation
- **API References**: Technical API documentation
- **Command References**: CLI tools and utilities
- **Architecture Guides**: System design and structure
- **Migration Guides**: Deployment and upgrade procedures

### Security Documentation
- **Security Audits**: Regular security assessments
- **Security Patches**: Applied security fixes
- **Security Policies**: Guidelines and best practices

### Process Documentation
- **Development Guides**: Coding standards and practices
- **Deployment Procedures**: Production deployment steps
- **Maintenance Schedules**: Regular maintenance tasks

## 🔄 Documentation Maintenance

### Creating New Documents

1. **Spanish First**: Always create the Spanish version first
2. **Naming Convention**: Use descriptive, hyphenated filenames
3. **Template Structure**: Follow established document templates
4. **Index Updates**: Update relevant README.md files

Example workflow:
```bash
# Create Spanish document
touch doc/es/nueva-funcionalidad.md

# Write content in Spanish
# ...

# Create English translation
touch doc/en/new-feature.md

# Translate content to English
# ...

# Update indexes
# Edit doc/es/README.md
# Edit doc/en/README.md
# Edit doc/README.md
```

### Translation Process

1. **Source**: Spanish document is the authoritative source
2. **Translation**: Create equivalent English document
3. **Technical Terms**: Maintain consistency in technical terminology
4. **Code Examples**: Keep code examples identical
5. **Cultural Context**: Adapt cultural references appropriately

### Version Control

- **Synchronized Updates**: Keep language versions synchronized
- **Version Tags**: Tag major documentation releases
- **Change Logs**: Document significant changes in each version
- **Review Process**: All documentation changes require review

### Quality Assurance

#### Content Review Checklist
- [ ] Technical accuracy verified
- [ ] Code examples tested
- [ ] Links functional
- [ ] Images optimized
- [ ] Grammar and spelling checked
- [ ] Both language versions synchronized

#### Translation Quality
- [ ] Technical terms consistent
- [ ] Cultural adaptation appropriate
- [ ] Tone and voice maintained
- [ ] Command examples identical
- [ ] File paths and URLs correct

## 📋 Document Templates

### Standard Document Header
```markdown
# Document Title

Brief description of the document's purpose and scope.

## Overview
...

## Contents
...

---

*Last updated: [Date]*
```

### Security Document Template
```markdown
# Security Document Title

**Assessment Date**: [Date]
**Severity Level**: [Critical/High/Medium/Low]
**Status**: [Active/Resolved/Monitoring]

## Summary
...

## Details
...

## Recommendations
...

---

*Security Classification: Internal Use*
*Last updated: [Date]*
```

### Technical Guide Template
```markdown
# Technical Guide Title

This guide covers [specific topic] for the CTH application.

## Prerequisites
- Requirement 1
- Requirement 2

## Step-by-Step Instructions

### Step 1: [Action]
Description and commands...

### Step 2: [Action]
Description and commands...

## Troubleshooting
Common issues and solutions...

## Additional Resources
- Related documentation
- External references

---

*Last updated: [Date]*
```

## 🔧 Tools and Standards

### Markdown Standards
- Use GitHub Flavored Markdown (GFM)
- Consistent heading hierarchy
- Code blocks with language specification
- Tables for structured data
- Emoji for visual organization (sparingly)

### Code Documentation
```bash
# Command examples with clear comments
php artisan command:name --option=value  # Description of what this does
```

```php
// PHP code with clear explanations
$example = new ExampleClass();
$result = $example->performAction();  // Explain the result
```

### File Naming Conventions
- Use lowercase with hyphens: `installation-guide.md`
- Be descriptive: `user-management-manual.md`
- Include version if applicable: `api-v2-reference.md`
- Group related docs: `security-audit-2025.md`

## 📊 Documentation Metrics

### Maintenance Schedule

#### Daily
- Monitor for broken links
- Check for new issues requiring documentation

#### Weekly
- Review and update changelog sections
- Verify code examples still work
- Update any time-sensitive information

#### Monthly
- Comprehensive review of all documents
- Translation synchronization check
- Update screenshots and images
- Review and update metrics

#### Quarterly
- Major documentation review
- User feedback incorporation
- Documentation structure assessment
- Archive outdated documents

### Quality Metrics
- **Completeness**: All features documented
- **Accuracy**: Information is current and correct
- **Accessibility**: Documents are easy to find and read
- **Consistency**: Uniform style and terminology
- **Translation Parity**: Language versions synchronized

## 🎯 Future Improvements

### Planned Enhancements
- **Interactive Tutorials**: Step-by-step guided tutorials
- **Video Documentation**: Screen recordings for complex procedures
- **API Documentation**: Auto-generated API documentation
- **User Feedback System**: Integrated feedback collection
- **Search Functionality**: Advanced documentation search

### Automation Opportunities
- **Link Checking**: Automated broken link detection
- **Translation Sync**: Tools to detect translation drift
- **Code Validation**: Automated testing of code examples
- **Screenshot Updates**: Automated UI documentation updates

## 📞 Documentation Support

### Content Questions
- Technical accuracy: Development team
- Translation issues: Localization team
- User experience: Product team

### Process Questions
- Documentation standards: Technical writing team
- Tool usage: DevOps team
- Quality assurance: QA team

### Contributing
1. Follow the established templates
2. Test all code examples
3. Update both language versions
4. Submit for review before publishing
5. Update relevant indexes

---

*This document describes the documentation system itself and should be updated whenever structural changes are made to the documentation organization.*

*Last updated: November 6, 2025*