# At beggining
- deep code investigation for full understanding of whole code logic, coding dialect and style
# Code 
- must be clean
- well documented: PHPDocs are mandatory
- use latest php features (if they are). f.e. named arguments
- must meet SOLID, DRY, KISS and latest modern principles
- must meet PSR standards

# Take care about performance
- choose algorythms with higher performance when it need
- keep balance between performance and logic usage

# All changes must be documented, documentation should be updated if needed.
## Documentation structure
### Top-level README.doc
 - Marketing style
 - Badges 
    - php version ( must be changed if code changes meet higher version)
    - licence
    - PSR standards
    - latest release version
 - Symbols
 - Philosophy
    - Concept-Labs Ecosystem (dependency injection, Singularity configuration according to core package https://github.com/Concept-Labs/singularity)
 - Conventions
 
 - basic "get started"
 - table of contents with links to sub documentation
 - "general" Code and configuration examples (if need)
 - Quick links
 - FAQs
 - Contributing
 - Inteligense Documentation links
 - Footer

#### Detailed documentation
- located in "docs" subdir
- Very detailed documentation with real-life examples


#All changes must be listed in CHANGELOG.md

#Tests should be created for new features or modified according code changes